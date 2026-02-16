<?php
session_start();
require_once 'db_connect.php';

// Check admin authentication
if (!(isset($_SESSION['user_id']) && isset($_SESSION['isadmin']) && $_SESSION['isadmin'] == 1)) {
    header('Location: loginreg.php');
    exit();
}

// Handle order status update
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];
    
    $update_query = "UPDATE orders SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("si", $status, $order_id);
    
    if ($stmt->execute()) {
        $success_message = "Order status updated successfully!";
    } else {
        $error_message = "Error updating order status: " . $conn->error;
    }
}

// Handle viewing a specific order
$viewing_order = false;
$order_details = null;
$order_items = null;

if (isset($_GET['view'])) {
    $viewing_order = true;
    $order_id = $_GET['view'];
    
    // Get order details
    $order_query = "SELECT o.*, u.name, u.email, u.mobile FROM orders o 
                   LEFT JOIN users u ON o.user_id = u.user_id 
                   WHERE o.id = ?";
    $stmt = $conn->prepare($order_query);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $order_details = $result->fetch_assoc();
        
        // Get order items
        $items_query = "SELECT oi.*, p.name as product_name, p.image1 FROM order_items oi 
                       LEFT JOIN products p ON oi.product_id = p.id 
                       WHERE oi.order_id = ?";
        $stmt = $conn->prepare($items_query);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $order_items = $stmt->get_result();
    } else {
        header('Location: orders.php');
        exit();
    }
} else {
    // Get all orders
    $orders_query = "SELECT o.*, u.name AS customer_name 
                    FROM orders o 
                    LEFT JOIN users u ON o.user_id = u.user_id 
                    ORDER BY o.created_at DESC";
    $orders_result = $conn->query($orders_query);

    // Fetch cancelled orders
    $cancelled_query = "SELECT co.*, u.name as customer_name 
                       FROM cancelled_orders co
                       LEFT JOIN users u ON co.user_id = u.user_id 
                       ORDER BY co.cancelled_at DESC";
    $cancelled_result = $conn->query($cancelled_query);
}

// Handle order cancellation
if (isset($_POST['cancel_order'])) {
    $orderId = $_POST['order_id'];
    
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
            throw new Exception("Order not found or already delivered");
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
        
        // Get cancellation reason
        $cancellationReason = $_POST['cancellation_reason'] ?? "Admin cancelled";
        if ($cancellationReason === 'other' && !empty($_POST['custom_reason'])) {
            $cancellationReason = $_POST['custom_reason'];
        }
        
        // Insert into cancelled_orders
        $insertCancelledOrder = "INSERT INTO cancelled_orders 
            (id, original_order_id, user_id, shipping_name, shipping_mobile, 
             total_amount, shipping_address, payment_id, status, created_at, 
             cancellation_reason)
            VALUES (?, ?, ?, ?, ?, ?, ?,?, ?, ?, ?)";
        
        $stmt = $conn->prepare($insertCancelledOrder);
        $stmt->bind_param("sssssdsssss", 
            $cancelledOrderId, $order['id'], $order['user_id'], 
            $order['shipping_name'], $order['shipping_mobile'], 
            $order['total_amount'], $order['shipping_address'], 
            $order['payment_id'], $order['status'], 
            $order['created_at'], 
            $cancellationReason
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
        
        $_SESSION['success_message'] = "Order cancelled successfully";
        header('Location: orders.php');
        exit();
        
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        error_log("Order cancellation failed: " . $e->getMessage());
        $_SESSION['error_message'] = "Failed to cancel order: " . $e->getMessage();
        header('Location: orders.php');
        exit();
    }
}

// Get success or error messages from session if they exist
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management | Leafy Life Admin</title>
    <link rel="stylesheet" href="admin.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
	<link rel="icon" type="image/x-icon" href="images/favicon.svg">
    <style>
        /* Additional styles for cancel modal */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s;
        }
        
        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        
        .modal-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            padding: 20px;
            position: relative;
            transform: translateY(-50px);
            transition: all 0.3s;
        }
        
        .modal-overlay.active .modal-container {
            transform: translateY(0);
        }
        
        .custom-reason-container {
            margin-top: 15px;
            display: none;
        }
        
        .custom-reason-container.active {
            display: block;
        }
        
        .btn-cancel {
            background-color: #ffebee;
            color: #d32f2f;
            border: none;
            display: inline-block;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
        }
        
        .btn-cancel:hover {
            background-color: #ffcdd2;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'admin_nav.php'; ?>
        
        <main class="admin-main">
            <div class="admin-header">
                <h1><i class="material-icons">shopping_cart</i> Order Management</h1>
                <p><?php echo $viewing_order ? "Viewing Order #" . $order_details['id'] : "Manage customer orders"; ?></p>
            </div>
            
            <?php if (isset($success_message)): ?>
                <div class="alert success">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert danger">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($viewing_order): ?>
                <section class="admin-section">
                    <div class="section-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <h2>Order Details</h2>
                        <div>
                            <?php if ($order_details['status'] != 'delivered'): ?>
                                <button class="btn-cancel" onclick="openCancelModal(<?php echo $order_details['id']; ?>)">
                                    <i class="material-icons">cancel</i> Cancel Order
                                </button>
                            <?php endif; ?>
                            <a href="orders.php" class="btn btn-secondary">Back to Orders</a>
                        </div>
                    </div>
                    
                    <div class="order-details">
                        <div class="form-grid">
                            <div>
                                <p><strong>Order ID:</strong> #<?php echo $order_details['id']; ?></p>
                                <p><strong>Date:</strong> <?php echo date('M d, Y h:i A', strtotime($order_details['created_at'])); ?></p>
                                <p><strong>Status:</strong> 
                                    <form method="post" action="">
                                        <input type="hidden" name="order_id" value="<?php echo $order_details['id']; ?>">
                                        <select name="status" class="form-control" onchange="this.form.submit()">
                                            <option value="received" <?php echo ($order_details['status'] == 'received') ? 'selected' : ''; ?>>Received</option>
                                            <option value="processing" <?php echo ($order_details['status'] == 'processing') ? 'selected' : ''; ?>>Processing</option>
                                            <option value="shipped" <?php echo ($order_details['status'] == 'shipped') ? 'selected' : ''; ?>>Shipped</option>
                                            <option value="delivered" <?php echo ($order_details['status'] == 'delivered') ? 'selected' : ''; ?>>Delivered</option>
                                        </select>
                                        <input type="hidden" name="update_status" value="1">
                                    </form>
                                </p>
                                <p><strong>Total Amount:</strong> ₹<?php echo number_format($order_details['total_amount'], 2); ?></p>
                            </div>
                            
                            <div>
                                <p><strong>Customer Name:</strong> <?php echo htmlspecialchars($order_details['name']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($order_details['email']); ?></p>
                                <p><strong>Phone:</strong> <?php echo htmlspecialchars($order_details['mobile'] ?? 'N/A'); ?></p>
                                <p><strong>Shipping Address:</strong> <?php echo htmlspecialchars($order_details['shipping_address']); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <h3>Order Items</h3>
                    <div class="table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($item = $order_items->fetch_assoc()): ?>
                                    <tr>
                                        <td>   
                                            <?php if (!empty($item['image1'])||($item['image2'])||($item['image3'])||($item['image4'])): ?>
                                                <img src='<?php echo 'http://localhost/' . htmlspecialchars($item['image1']); ?>' alt="<?php echo htmlspecialchars($item['product_name']); ?>" class="product-thumb-small">
                                            <?php else: ?>
                                                <span>No image</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                        <td>₹<?php echo number_format($item['price'], 2); ?></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td>₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-right"><strong>Total</strong></td>
                                    <td><strong>₹<?php echo number_format($order_details['total_amount'], 2); ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </section>
            <?php else: ?>
                <section class="admin-section">
                    <h2>All Orders</h2>
                    
                    <div class="table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Date</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($orders_result->num_rows > 0): ?>
                                    <?php while($order = $orders_result->fetch_assoc()): ?>
                                        <tr>
                                            <td>#<?php echo $order['id']; ?></td>
                                            <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                            <td>₹<?php echo number_format($order['total_amount'], 2); ?></td>
                                            <td>
                                                <form method="post" action="">
                                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                    <select name="status" class="form-control" onchange="this.form.submit()">
                                                        <option value="received" <?php echo ($order['status'] == 'received') ? 'selected' : ''; ?>>Received</option>
                                                        <option value="processing" <?php echo ($order['status'] == 'processing') ? 'selected' : ''; ?>>Processing</option>
                                                        <option value="shipped" <?php echo ($order['status'] == 'shipped') ? 'selected' : ''; ?>>Shipped</option>
                                                        <option value="delivered" <?php echo ($order['status'] == 'delivered') ? 'selected' : ''; ?>>Delivered</option>
                                                    </select>
                                                    <input type="hidden" name="update_status" value="1">
                                                </form>
                                            </td>
                                            <td>
                                                <div class="table-actions">
                                                    <a href="orders.php?view=<?php echo $order['id']; ?>" class="btn-view">View</a>
                                                    <?php if ($order['status'] != 'delivered'): ?>
                                                        <button class="btn-cancel" onclick="openCancelModal(<?php echo $order['id']; ?>)">Cancel</button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="no-data">No orders found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
                
                <section class="admin-section">
                    <h2>Cancelled Orders</h2>
                    <div class="table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Cancelled ID</th>
                                    <th>Original Order ID</th>
                                    <th>Customer</th>
                                    <th>Total</th>
                                    <th>Cancelled At</th>
                                    <th>Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($cancelled_result->num_rows > 0): ?>
                                    <?php while($cancel = $cancelled_result->fetch_assoc()): ?>
                                        <tr>
                                            <td>#<?php echo htmlspecialchars($cancel['id']); ?></td>
                                            <td>#<?php echo htmlspecialchars($cancel['original_order_id']); ?></td>
                                            <td><?php echo htmlspecialchars($cancel['customer_name']); ?></td>
                                            <td>₹<?php echo number_format($cancel['total_amount'], 2); ?></td>
                                            <td><?php echo date('M d, Y h:i A', strtotime($cancel['cancelled_at'])); ?></td>
                                            <td><?php echo nl2br(htmlspecialchars($cancel['cancellation_reason'] ?? 'N/A')); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="no-data">No cancelled orders found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            <?php endif; ?>
        </main>
    </div>
    
    <!-- Cancel Order Modal -->
    <div id="cancelOrderModal" class="modal-overlay">
        <div class="modal-container">
            <div class="modal-header">
                <h3><i class="material-icons">cancel</i> Cancel Order</h3>
                <button class="modal-close" onclick="closeCancelModal()">&times;</button>
            </div>
            <form action="" method="POST">
                <input type="hidden" name="order_id" id="cancel_order_id" value="">
                
                <div class="form-group">
                    <label for="cancellation_reason">Cancellation Reason:</label>
                    <select name="cancellation_reason" id="cancellation_reason" class="form-control" onchange="toggleCustomReason()">
                        <option value="">Select a reason</option>
                        <option value="Out of stock">Out of stock</option>
                        <option value="Shipping issues">Shipping issues</option>
                        <option value="Payment issues">Payment issues</option>
                        <option value="Customer request">Customer request</option>
                        <option value="Duplicate order">Duplicate order</option>
                        <option value="Fraud">Fraud</option>
                        <option value="other">Other (please specify)</option>
                    </select>
                </div>
                
                <div id="customReasonContainer" class="custom-reason-container">
                    <div class="form-group">
                        <label for="custom_reason">Specify Reason:</label>
                        <textarea name="custom_reason" id="custom_reason" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                
                <div class="modal-actions">
                    <button type="submit" name="cancel_order" class="btn btn-danger">Confirm Cancellation</button>
                    <button type="button" class="btn btn-secondary" onclick="closeCancelModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Function to open the cancel modal
        function openCancelModal(orderId) {
            document.getElementById('cancel_order_id').value = orderId;
            document.getElementById('cancelOrderModal').classList.add('active');
            
            // Reset form
            document.getElementById('cancellation_reason').value = '';
            document.getElementById('custom_reason').value = '';
            document.getElementById('customReasonContainer').classList.remove('active');
        }
        
        // Function to close the cancel modal
        function closeCancelModal() {
            document.getElementById('cancelOrderModal').classList.remove('active');
        }
        
        // Function to toggle custom reason field
        function toggleCustomReason() {
            const reasonSelect = document.getElementById('cancellation_reason');
            const customReasonContainer = document.getElementById('customReasonContainer');
            
            if (reasonSelect.value === 'other') {
                customReasonContainer.classList.add('active');
            } else {
                customReasonContainer.classList.remove('active');
            }
        }
        
        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('cancelOrderModal');
            if (event.target === modal) {
                closeCancelModal();
            }
        });
    </script>
</body>
</html>