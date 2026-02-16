<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in and is admin, or has a valid user session
if (!(isset($_SESSION['user_id']))) {
    header('Location: loginreg.php');
    exit();
}

if (isset($_POST['cancel_order'])) {
    $orderId = $_POST['order_id'];
    $isAdmin = isset($_SESSION['isadmin']) && $_SESSION['isadmin'] == 1;
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Generate new UUID for cancelled order
        $cancelledOrderId = uniqid('', true);
        
        // Get order details - if admin, don't check user_id
        $orderQuery = $isAdmin 
            ? "SELECT * FROM orders WHERE id = ? AND status != 'delivered'" 
            : "SELECT * FROM orders WHERE id = ? AND user_id = ? AND status != 'delivered'";
        
        $stmt = $conn->prepare($orderQuery);
        
        if ($isAdmin) {
            $stmt->bind_param("s", $orderId);
        } else {
            $stmt->bind_param("si", $orderId, $_SESSION['user_id']);
        }
        
        $stmt->execute();
        $orderResult = $stmt->get_result();
        $order = $orderResult->fetch_assoc();
        
        if (!$order) {
            throw new Exception("Order not found, already delivered, or you don't have permission");
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
        if ($isAdmin) {
            $cancellationReason = $_POST['cancellation_reason'] ?? "Admin cancelled";
            if ($cancellationReason === 'other' && !empty($_POST['custom_reason'])) {
                $cancellationReason = $_POST['custom_reason'];
            }
        } else {
            $cancellationReason = "Customer cancelled";
        }
        
        // Insert into cancelled_orders
        $insertCancelledOrder = "INSERT INTO cancelled_orders 
            (id, original_order_id, user_id, shipping_name, shipping_mobile, 
             total_amount, shipping_address, payment_id, status, created_at, 
             agent_name, cancellation_reason)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($insertCancelledOrder);
        $stmt->bind_param("sssssdssssss", 
            $cancelledOrderId, $order['id'], $order['user_id'], 
            $order['shipping_name'], $order['shipping_mobile'], 
            $order['total_amount'], $order['shipping_address'], 
            $order['payment_id'], $order['status'], 
            $order['created_at'], $order['agent_name'], 
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
        
        // Set success message in session
        $_SESSION['success_message'] = "Order cancelled successfully";
        
        // Redirect based on user type
        if ($isAdmin) {
            header('Location: orders.php');
        } else {
            header('Location: cancel_order.php');
        }
        exit();
        
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        error_log("Order cancellation failed: " . $e->getMessage());
        
        // Set error message in session
        $_SESSION['error_message'] = "Failed to cancel order: " . $e->getMessage();
        
        // Redirect based on user type
        if ($isAdmin) {
            header('Location: orders.php');
        } else {
            header('Location: cancel_order.php');
        }
        exit();
    }
} else {
    // Redirect if accessed directly without form submission
    header('Location: index.php');
    exit();
}
?>