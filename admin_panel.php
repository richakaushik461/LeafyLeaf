<?php
session_start();
require_once 'db_connect.php';

// Check admin authentication
if (!(isset($_SESSION['user_id']) && isset($_SESSION['isadmin']) && $_SESSION['isadmin'] == 1)) {
    header('Location: loginreg.php');
    exit();
}

// Get counts for dashboard
$products_count = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$orders_count = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$users_count = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$inquiries_count = $conn->query("SELECT COUNT(*) as count FROM contact_submissions")->fetch_assoc()['count'];
$blogs_count = $conn->query("SELECT COUNT(*) as count FROM blogs")->fetch_assoc()['count'];

// Get recent orders
$recent_orders_query = "SELECT o.*, u.name AS customer_name 
                      FROM orders o 
                      LEFT JOIN users u ON o.user_id = u.user_id 
                      ORDER BY o.created_at DESC LIMIT 5";
$recent_orders = $conn->query($recent_orders_query);

// Get recent inquiries
$recent_inquiries_query = "SELECT c.*
                    FROM contact_submissions c 
                    ORDER BY c.created_at DESC LIMIT 5";
$recent_inquiries = $conn->query($recent_inquiries_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Leafy Life</title>
    <link rel="stylesheet" href="admin.css">
	<link rel="icon" type="image/x-icon" href="images/favicon.svg">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
    <div class="admin-container">
        <?php include 'admin_nav.php'; ?>
        
        <main class="admin-main">
            <div class="admin-header">
                <h1><i class="material-icons">dashboard</i> Dashboard</h1>
                <p>Welcome back, <?php echo htmlspecialchars($_SESSION['name']); ?>!</p>
            </div>
            
            <div class="dashboard-stats">
                <div class="stat-card" onclick="window.location.href='products_mgmt.php';">
                    <i class="material-icons">shopping_bag</i>
                    <div class="stat-content">
                        <h2><?php echo $products_count; ?></h2>
                        <p>Products</p>
                    </div>
                </div>

                <div class="stat-card" onclick="window.location.href='orders.php';">
                    <i class="material-icons">shopping_cart</i>
                    <div class="stat-content">
                        <h2><?php echo $orders_count; ?></h2>
                        <p>Orders</p>
                    </div>
                </div>

                <div class="stat-card" onclick="window.location.href='users.php';">
                    <i class="material-icons">people</i>
                    <div class="stat-content">
                        <h2><?php echo $users_count; ?></h2>
                        <p>Users</p>
                    </div>
                </div>

                <div class="stat-card" onclick="window.location.href='inquiries.php';">
                    <i class="material-icons">contact_mail</i>
                    <div class="stat-content">
                        <h2><?php echo $inquiries_count; ?></h2>
                        <p>Inquiries</p>
                    </div>
                </div>

                <div class="stat-card" onclick="window.location.href='blogs_mgmt.php';">
                    <i class="material-icons">article</i>
                    <div class="stat-content">
                        <h2><?php echo $blogs_count; ?></h2>
                        <p>Blog Posts</p>
                    </div>
                </div>
            </div>
   
            <div class="dashboard-sections">
                <section class="admin-section">
                    <h2><i class="material-icons">shopping_cart</i> Recent Orders</h2>
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
                                <?php if ($recent_orders->num_rows > 0): ?>
                                    <?php while($order = $recent_orders->fetch_assoc()): ?>
                                        <tr>
                                            <td>#<?php echo $order['id']; ?></td>
                                            <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                            <td>₹<?php echo number_format($order['total_amount'], 2); ?></td>
                                            <td><span class="status-badge status-<?php echo strtolower($order['status']); ?>"><?php echo ucfirst($order['status']); ?></span></td>
                                            <td>
                                                <a href="orders.php?view=<?php echo $order['id']; ?>" class="btn-view">View</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="no-data">No recent orders</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="section-footer">
                        <a href="orders.php" class="btn btn-primary">View All Orders</a>
                    </div>
                </section>
                
                <section class="admin-section">
                <h2><i class="material-icons">contact_mail</i> Recent Inquiries</h2>
                        <div class="table-container">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Inquiry ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Message</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($recent_inquiries->num_rows > 0): ?>
                                        <?php while($inquiry = $recent_inquiries->fetch_assoc()): ?>
                                            <tr>
                                                <td>#<?php echo $inquiry['id']; ?></td>
                                                <td><?php echo htmlspecialchars($inquiry['name']); ?></td>
                                                <td><?php echo htmlspecialchars($inquiry['email']); ?></td>
                                                <td><?php echo htmlspecialchars($inquiry['message']); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($inquiry['created_at'])); ?></td>
                                                <td>
                                                    <a href="view_inquiry.php?id=<?php echo $inquiry['id']; ?>" class="btn-view">View</a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="no-data">No recent inquiries</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="section-footer">
                            <a href="inquiries.php" class="btn btn-primary">View All Inquiries</a>
                        </div>
                </section>
            </div>
        </main>
    </div>
</body>
</html>

