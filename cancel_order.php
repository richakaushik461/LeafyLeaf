<?php
session_start();
include 'db_connect.php';
include 'counter_functions.php';

$userId = $_SESSION['user_id'] ?? null;
$userIconLink = isset($_SESSION['user_id']) ? 'profile.php' : 'loginreg.php';
$counts = getCartWishlistCounts($conn, $_SESSION['user_id'] ?? null);

// Function to cancel order
function cancelOrder($orderId) {
    global $conn;
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Generate new UUID for cancelled order
        $cancelledOrderId = uniqid('', true);
        
        // Get order details
        $orderQuery = "SELECT * FROM orders WHERE id = ? AND status != 'delivered'";
        $stmt = $conn->prepare($orderQuery);
        $stmt->bind_param("s", $orderId);
        $stmt->execute();
        $orderResult = $stmt->get_result();
        $order = $orderResult->fetch_assoc();
        
        if (!$order) {
            throw new Exception("Order not found");
        }
        
        // Get order items
        $itemsQuery = "SELECT oi.*, p.name as product_name FROM order_items oi 
                      LEFT JOIN products p ON oi.product_id = p.id 
                      WHERE oi.order_id = ?";
        $stmt = $conn->prepare($itemsQuery);
        $stmt->bind_param("s", $orderId);
        $stmt->execute();
        $itemsResult = $stmt->get_result();
        $orderItems = $itemsResult->fetch_all(MYSQLI_ASSOC);
        
        if (count($orderItems) === 0) {
            throw new Exception("No items found for this order");
        }
        
        // Insert into cancelled_orders
        $insertCancelledOrder = "INSERT INTO cancelled_orders 
            (id, original_order_id, user_id, shipping_name, shipping_mobile, 
             total_amount, shipping_address, payment_id, status, created_at, 
             agent_name, cancellation_reason)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($insertCancelledOrder);
        $cancelReason = "Customer cancelled";
        $stmt->bind_param("sssssdssssss", 
            $cancelledOrderId, $order['id'], $order['user_id'], 
            $order['shipping_name'], $order['shipping_mobile'], 
            $order['total_amount'], $order['shipping_address'], 
            $order['payment_id'], $order['status'], 
            $order['created_at'], $order['agent_name'], 
            $cancelReason
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to insert cancelled order: " . $stmt->error);
        }
        
        // Process each order item
        foreach ($orderItems as $item) {
            // Generate new UUID for cancelled order item
            $cancelledItemId = uniqid('', true);
            
            // Restore product quantity
            $updateProduct = "UPDATE products SET qty = qty + ? WHERE id = ?";
            $stmt = $conn->prepare($updateProduct);
            $stmt->bind_param("is", $item['quantity'], $item['product_id']);
            if (!$stmt->execute()) {
                throw new Exception("Failed to update product quantity: " . $stmt->error);
            }
            
            // Insert into cancelled_order_items
            $insertCancelledItem = "INSERT INTO cancelled_order_items 
                (id, cancelled_order_id, original_order_item_id, product_id, 
                 quantity, price)
                VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insertCancelledItem);
            $stmt->bind_param("ssssid",
                $cancelledItemId, $cancelledOrderId, $item['id'],
                $item['product_id'], $item['quantity'], $item['price']
            );
            if (!$stmt->execute()) {
                throw new Exception("Failed to insert cancelled order item: " . $stmt->error);
            }
        }
        
        // Delete original order items
        $deleteItems = "DELETE FROM order_items WHERE order_id = ?";
        $stmt = $conn->prepare($deleteItems);
        $stmt->bind_param("s", $orderId);
        if (!$stmt->execute()) {
            throw new Exception("Failed to delete order items: " . $stmt->error);
        }
        
        // Delete original order
        $deleteOrder = "DELETE FROM orders WHERE id = ?";
        $stmt = $conn->prepare($deleteOrder);
        $stmt->bind_param("s", $orderId);
        if (!$stmt->execute()) {
            throw new Exception("Failed to delete order: " . $stmt->error);
        }
        
        // Commit transaction
        $conn->commit();
        return true;
        
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        error_log("Order cancellation failed: " . $e->getMessage());
        return false;
    }
}

// Handle POST request for order cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
    $orderId = $_POST['order_id'];
    $success = cancelOrder($orderId);
    
    if ($success) {
        $message = "Order cancelled successfully";
    } else {
        $error = "Failed to cancel order. Please try again later.";
    }
}

// Fetch active orders if user is logged in
$orders = [];
if ($userId) {
    $ordersQuery = "SELECT o.*, 
        GROUP_CONCAT(CONCAT(p.name, ' (', oi.quantity, ')') SEPARATOR ', ') as items
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        LEFT JOIN products p ON oi.product_id = p.id
        WHERE o.user_id = ? AND o.status != 'Cancelled' AND o.status != 'delivered'
        GROUP BY o.id
        ORDER BY o.created_at DESC";

    $stmt = $conn->prepare($ordersQuery);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $ordersResult = $stmt->get_result();
    while ($row = $ordersResult->fetch_assoc()) {
        $orders[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cancel Order | LeafyLife</title>
    <link rel="stylesheet" href="try.css">
    <link rel="icon" type="image/x-icon" href="images/favicon.svg">
    <style>
        .container1 {
            max-width: 1200px;
            margin: 180px auto 50px;
            padding: 30px;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
            font-size: 2.2em;
        }
        
        .message1 {
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 8px;
            text-align: center;
            font-size: 1.1em;
        }
        
        .success1 {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .error1 {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        
        tr:hover {
            background-color: #f8f9fa;
            transition: background-color 0.3s ease;
        }
        
        .btn-cancel {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            background-color: #b2cf6d;
            color: white;
            transition: all 0.3s ease;
        }
        
        .btn-cancel:hover {
            background-color: #9ab85c;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(178, 207, 109, 0.3);
        }
        
        .modal1 {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .modal-content1 {
            background-color: #fff;
            margin: 15% auto;
            padding: 30px;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            position: relative;
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        .close1 {
            position: absolute;
            right: 20px;
            top: 15px;
            font-size: 28px;
            font-weight: bold;
            color: #666;
            cursor: pointer;
            transition: color 0.3s ease;
        }
        
        .close1:hover {
            color: #333;
        }

        .modal-content1 h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 1.8em;
        }

        .modal-content1 p {
            color: #666;
            margin-bottom: 25px;
            font-size: 1.1em;
            line-height: 1.5;
        }

        .modal-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 30px;
        }

        .btn-confirm {
            padding: 10px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-confirm.cancel {
            background-color: #b2cf6d;
            color: white;
        }

        .btn-confirm.cancel:hover {
            background-color: #9ab85c;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(178, 207, 109, 0.3);
        }

        .btn-confirm.close {
            background-color: #e9ecef;
            color: #495057;
        }

        .btn-confirm.close:hover {
            background-color: #dee2e6;
        }

        /* Login Container Styles */
        .login-container {
            text-align: center;
            padding: 40px 20px;
        }

        .login-icon {
            width: 80px;
            height: 80px;
            background: #f8f9fa;
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-icon img {
            width: 40px;
            height: 40px;
            opacity: 0.7;
        }

        .login-title {
            color: #333;
            font-size: 24px;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .login-message {
            color: #666;
            margin-bottom: 25px;
            font-size: 16px;
        }

        .login-button {
            display: inline-block;
            background: #b2cf6d;
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .login-button:hover {
            background: #9ab85c;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(178, 207, 109, 0.3);
        }

        /* No Orders Container Styles */
        .no-orders-container {
            text-align: center;
            padding: 40px 20px;
        }

        .no-orders-icon {
            width: 80px;
            height: 80px;
            background: #f8f9fa;
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .no-orders-icon img {
            width: 40px;
            height: 40px;
            opacity: 0.7;
        }

        .no-orders-title {
            color: #333;
            font-size: 24px;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .no-orders-message {
            color: #666;
            margin-bottom: 25px;
            font-size: 16px;
        }

        .shop-now-button {
            display: inline-block;
            background: #b2cf6d;
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .shop-now-button:hover {
            background: #9ab85c;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(178, 207, 109, 0.3);
        }
    </style>
</head>
<body>
    <header>
        <br>
        <nav class="nav container">
            <h2 class="nav_logo"><a href="index.php"><img src="images/logo.png" id="logo"></a></h2>
           <ul class="menu_items">
            <li><a href="index.php" class="nav_link">Home</a></li>
            <li><a href="shop.php" class="nav_link">Shop</a></li>
            <li><a href="blogs.php" class="nav_link">Blogs</a></li>
            <li><a href="about.php" class="nav_link">About Us</a></li>
            <li><a href="contact.php" class="nav_link">Contact Us</a></li>
            <?php echo renderNavbarCounters($counts); ?>
            <li class="user-dropdown">
                <a href="<?php echo $userIconLink; ?>" class="nav_link1" id="userIcon">
                    <img src="images/user.svg" alt="user" id="svg">
                </a>
                <?php if(isset($_SESSION['user_id'])): ?>
                <div class="dropdown-content" id="userDropdown">
                    <a href="profile.php">Profile</a>
                    <a href="track_order.php">Track Order</a>
                    <a href="cancel_order.php">Cancel Order</a>
		     <?php if(isset($_SESSION['isadmin']) && $_SESSION['isadmin']): ?>
        <a href="admin_panel.php" class="admin-link">Admin Panel</a>
        <?php endif; ?>
	<a href="logout_process.php" style="color:red;">Log Out</a>  
                </div>
                <?php endif; ?>          
            </li>
          </ul>
        </nav>
    </header>

    <div class="container1">
        <h1>Order Cancellation</h1>
        
        <?php if (!$userId): ?>
            <!-- Please Login Section -->
            <div class="login-container">
                <div class="login-icon">
                    <img src="images/user.svg" alt="Login">
                </div>
                <h2 class="login-title">Please Log In</h2>
                <p class="login-message">You need to be logged in to cancel orders</p>
                <a href="loginreg.php" class="login-button">Login Now</a>
            </div>
        <?php elseif (empty($orders)): ?>
            <!-- No Orders Section -->
            <div class="no-orders-container">
                <div class="no-orders-icon">
                    <img src="images/shopping-bag.svg" alt="No Orders">
                </div>
                <h2 class="no-orders-title">No Orders Found</h2>
                <p class="no-orders-message">You don't have any active orders that can be cancelled</p>
                <a href="shop.php" class="shop-now-button">Continue Shopping</a>
            </div>
        <?php else: ?>
            <?php if (isset($message)): ?>
                <div class="message1 success1"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="message1 error1"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Items</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Order Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?php echo htmlspecialchars($order['id']); ?></td>
                            <td><?php echo htmlspecialchars($order['items']); ?></td>
                            <td>₹<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($order['status']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                            <td>
                                <button class="btn-cancel" 
                                        onclick="showCancelConfirmation('<?php echo htmlspecialchars($order['id']); ?>')">
                                    Cancel Order
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Cancel Confirmation Modal -->
    <div id="cancelModal" class="modal1">
        <div class="modal-content1">
            <span class="close1" onclick="closeCancelModal()">&times;</span>
            <h2>Cancel Order</h2>
            <p>Are you sure you want to cancel this order? This action cannot be undone.</p>
            <form method="POST" class="modal-buttons">
                <input type="hidden" name="order_id" id="cancelOrderId">
                <button type="button" class="btn-confirm close" onclick="closeCancelModal()">Keep Order</button>
                <button type="submit" name="cancel_order" class="btn-confirm cancel">
                    Cancel Order
                </button>
            </form>
        </div>
    </div>

    <footer>
        <div class="footerContainer">
            <div class="socialIcons">
                <a href=""><img src="images/facebook.svg" alt=""></a>
                <a href=""><img src="images/instagram.svg" alt=""></a>
                <a href=""><img src="images/twitter.svg" alt=""></a>
                <a href=""><img src="images/tumblr.svg" alt=""></a>
            </div>
            <div class="footerNav">
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="shop.php">Shop</a></li>
                    <li><a href="blogs.php">Blogs</a></li>
                    <li><a href="about.php">About</a></li>
                    <li><a href="contact.php">Contact Us</a></li>
                </ul>
            </div>
        </div>
        <div class="footerBottom">
            <p>Copyright &copy; 2025</p>
        </div>
    </footer>

    <script>
        function showCancelConfirmation(orderId) {
            document.getElementById('cancelModal').style.display = 'block';
            document.getElementById('cancelOrderId').value = orderId;
        }
        
        function closeCancelModal() {
            document.getElementById('cancelModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('cancelModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

        // User dropdown functionality
        document.addEventListener('DOMContentLoaded', () => {
            const userIcon = document.getElementById('userIcon');
            const dropdown = document.getElementById('userDropdown');

            if (userIcon && dropdown) {
                userIcon.addEventListener('click', (e) => {
                    if (document.querySelector('a[href="profile.php"]')) {
                        e.preventDefault();
                        dropdown.classList.toggle('show');
                        e.stopPropagation();
                    }
                });

                document.addEventListener('click', (e) => {
                    if (!e.target.matches('#userIcon') && !e.target.matches('#svg')) {
                        if (dropdown.classList.contains('show')) {
                            dropdown.classList.remove('show');
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>