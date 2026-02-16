<?php
session_start();
include 'db_connect.php';
include 'counter_functions.php';

$userId = $_SESSION['user_id'] ?? null;
$userIconLink = isset($_SESSION['user_id']) ? 'profile.php' : 'loginreg.php';
$counts = getCartWishlistCounts($conn, $_SESSION['user_id'] ?? null);

// Get order details if order ID is provided
$orderId = $_GET['order_id'] ?? null;
$order = null;
$error = null;

if ($orderId) {
    $stmt = $conn->prepare("
        SELECT o.*, oi.product_id, oi.quantity, oi.price, p.name as product_name
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        LEFT JOIN products p ON oi.product_id = p.id
        WHERE o.id = ? AND o.user_id = ?
    ");
    $stmt->bind_param("ii", $orderId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $order = [];
        $order['items'] = [];
        while ($row = $result->fetch_assoc()) {
            if (empty($order['details'])) {
                $order['details'] = [
                    'id' => $row['id'],
                    'shipping_name' => $row['shipping_name'],
                    'shipping_mobile' => $row['shipping_mobile'],
                    'shipping_address' => $row['shipping_address'],
                    'total_amount' => $row['total_amount'],
                    'status' => $row['status'],
                    'created_at' => $row['created_at']
                ];
            }
            $order['items'][] = [
                'product_name' => $row['product_name'],
                'quantity' => $row['quantity'],
                'price' => $row['price']
            ];
        }
    } else {
        $error = "Order not found";
    }
}

// Get all orders for the user
$allOrders = [];
if ($userId) {
    $orderStmt = $conn->prepare("
        SELECT id, created_at, total_amount, status 
        FROM orders 
        WHERE user_id = ? 
        ORDER BY created_at DESC
    ");
    $orderStmt->bind_param("i", $userId);
    $orderStmt->execute();
    $orderResult = $orderStmt->get_result();
    while ($row = $orderResult->fetch_assoc()) {
        $allOrders[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Order | LeafyLife</title>
    <link rel="stylesheet" href="try.css">
    <link rel="icon" type="image/x-icon" href="images/favicon.svg">
    <style>
        .track-order-container {
            max-width: 1000px;
            margin: 180px auto 50px;
            padding: 20px;
        }
        
        .order-list {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .order-details {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .order-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        .order-table th,
        .order-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .order-table th {
            background: #f8f9fa;
            font-weight: 600;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }
        
        .status-received { background: #ffd700; color: #000; }
        .status-processing { background: #87ceeb; color: #000; }
        .status-shipped { background: #98fb98; color: #000; }
        .status-delivered { background: #90ee90; color: #000; }
        .status-completed { background: #3cb371; color: #fff; }
        
        .tracking-steps {
            display: flex;
            justify-content: space-between;
            margin: 40px 0;
            position: relative;
        }
        
        .tracking-steps::before {
            content: '';
            position: absolute;
            top: 25px;
            left: 0;
            right: 0;
            height: 2px;
            background: #ddd;
            z-index: 1;
        }
        
        .step {
            position: relative;
            z-index: 2;
            background: #fff;
            padding: 10px;
            text-align: center;
            width: 150px;
        }
        
        .step-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #ddd;
            margin: 0 auto 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        
        .step.active .step-icon {
            background: #b2cf6d;
            color: #fff;
        }
        
        .step-label {
            font-size: 14px;
            color: #666;
        }
        
        .step.active .step-label {
            color: #000;
            font-weight: 600;
        }
        
        .order-items {
            margin: 20px 0;
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        
        .view-details {
            color: #b2cf6d;
            text-decoration: none;
        }
        
        .view-details:hover {
            text-decoration: underline;
        }

        .login-icon {
            width: 80px;
            height: 80px;
            background: #f5f5f5;
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
            margin-bottom: 10px;
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


        .no-orders-icon {
            width: 80px;
            height: 80px;
            background: #f5f5f5;
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
            margin-bottom: 10px;
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

    <div class="track-order-container">
        <?php if (!$userId): ?>
            <div class="order-details">
                <div class="login-container">
                    <div class="login-icon">
                        <img src="images/user.svg" alt="Login">
                    </div>
                    <h2 class="login-title">Please Log In</h2>
                    <p class="login-message">You need to be logged in to track your orders</p>
                    <a href="loginreg.php" class="login-button">Login Now</a>
                </div>
            </div>
        <?php else: ?>
            <?php if (empty($allOrders)): ?>
                <div class="order-details">
                    <div class="no-orders-container">
                        <div class="no-orders-icon">
                            <img src="images/shopping-bag.svg" alt="No Orders">
                        </div>
                        <h2 class="no-orders-title">No Orders Yet</h2>
                        <p class="no-orders-message">You haven't placed any orders yet. Start shopping to see your orders here!</p>
                        <a href="shop.php" class="shop-now-button">Shop Now</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="order-list">
                    <h2>Your Orders</h2>
                    <table class="order-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allOrders as $orderItem): ?>
                                <tr>
                                    <td>#<?php echo $orderItem['id']; ?></td>
                                    <td><?php echo date('M d, Y', strtotime($orderItem['created_at'])); ?></td>
                                    <td>₹<?php echo number_format($orderItem['total_amount'], 2); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower($orderItem['status']); ?>">
                                            <?php echo ucfirst($orderItem['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="?order_id=<?php echo $orderItem['id']; ?>" class="view-details">View Details</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <?php if ($order): ?>
                <div class="order-details">
                    <h2>Order #<?php echo $order['details']['id']; ?></h2>
                    
                    <div class="tracking-steps">
                        <?php
                        $steps = ['order received', 'processing', 'shipped', 'delivered'];
                        $currentStatus = strtolower($order['details']['status']);
                        $currentStepIndex = array_search($currentStatus, $steps);
                        
                        foreach ($steps as $index => $step):
                            $isActive = $index <= $currentStepIndex;
                        ?>
                            <div class="step <?php echo $isActive ? 'active' : ''; ?>">
                                <div class="step-icon">●</div>
                                <div class="step-label"><?php echo ucfirst($step); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="order-info">
                        <h3>Shipping Details</h3>
                        <p><?php echo htmlspecialchars($order['details']['shipping_address']); ?></p>
                        
                        <h3>Order Items</h3>
                        <div class="order-items">
                            <?php foreach ($order['items'] as $item): ?>
                                <div class="order-item">
                                    <div><?php echo htmlspecialchars($item['product_name']); ?> × <?php echo $item['quantity']; ?></div>
                                    <div>₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div style="text-align: right; margin-top: 20px;">
                            <strong>Total Amount: ₹<?php echo number_format($order['details']['total_amount'], 2); ?></strong>
                        </div>
                    </div>
                </div>
            <?php elseif ($error): ?>
                <div class="order-details">
                    <p><?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php endif; ?>
        <?php endif; ?>
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
        // Function to check if user is logged in
        function isUserLoggedIn() {
            return document.querySelector('a[href="profile.php"]') !== null;
        }

        // Function to show auth popup
        function showAuthPopup() {
            const popup = document.createElement('div');
            popup.className = 'auth-popup';
            popup.innerHTML = `
                <div class="popup-content">
                    <h3>Please Log In</h3>
                    <p>You need to be logged in to use this feature.</p>
                    <div class="popup-buttons">
                        <button onclick="window.location.href='loginreg.php'" class="login-btn">Log In</button>
                        <button onclick="closePopup()" class="close-btn">Close</button>
                    </div>
                </div>
            `;
            document.body.appendChild(popup);

            // Close popup when clicking outside
            popup.addEventListener('click', (e) => {
                if (e.target === popup) {
                    closePopup();
                }
            });
        }

        // Function to close auth popup
        function closePopup() {
            const popup = document.querySelector('.auth-popup');
            if (popup) {
                popup.remove();
            }
        }

        // Function to show cart popup
        function showCartPopup(productName, price) {
            const popup = document.createElement('div');
            popup.className = 'cart-popup';
            popup.innerHTML = `
                <div class="cart-popup-content">
                    <button class="close-popup" onclick="closeCartPopup()">&times;</button>
                    <h3>Added to Cart!</h3>
                    <p>${productName} has been added to your cart.</p>
                    <p>Price: ₹${price}</p>
                    <div class="cart-popup-buttons">
                        <button class="view-cart-btn" onclick="viewCart()">View Cart</button>
                        <button class="continue-shopping-btn" onclick="closeCartPopup()">Continue Shopping</button>
                    </div>
                </div>
            `;
            document.body.appendChild(popup);

            // Close popup when clicking outside
            popup.addEventListener('click', (e) => {
                if (e.target === popup) {
                    closeCartPopup();
                }
            });
        }

        // Function to close cart popup
        function closeCartPopup() {
            const popup = document.querySelector('.cart-popup');
            if (popup) {
                popup.remove();
            }
        }

        // Function to handle view cart action
        function viewCart() {
            closeCartPopup();
            // You can add redirect to cart page here if needed
        }

        // Add event listeners when DOM is loaded
        document.addEventListener('DOMContentLoaded', () => {
            // Wishlist buttons
            const wishlistButtons = document.querySelectorAll('.wishlist');
            wishlistButtons.forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    if (!isUserLoggedIn()) {
                        showAuthPopup();
                    } else {
                        handleWishlistButtonClick(button);
                    }
                });
            });

            const cartIcon = document.querySelector('a[href="cart.php"]');
            if (cartIcon) {
                cartIcon.addEventListener('click', (e) => {
                    if (!isUserLoggedIn()) {
                        e.preventDefault();
                        showAuthPopup();
                    }
                });
            }

            // Header wishlist icon
            const wishlistIcon = document.querySelector('a[href="wishlist.php"]');
            if (wishlistIcon) {
                wishlistIcon.addEventListener('click', (e) => {
                    if (!isUserLoggedIn()) {
                        e.preventDefault();
                        showAuthPopup();
                    }
                });
            }
        });

        document.addEventListener('DOMContentLoaded', () => {
            const userIcon = document.getElementById('userIcon');
            const dropdown = document.getElementById('userDropdown');

            if (userIcon && dropdown) {
                userIcon.addEventListener('click', (e) => {
                    if (isUserLoggedIn()) {
                        e.preventDefault();
                        dropdown.classList.toggle('show');
                        e.stopPropagation();
                    }
                });

                // Close dropdown when clicking outside
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