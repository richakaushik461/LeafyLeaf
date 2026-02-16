<?php
session_start();
require_once 'db_connect.php';

// Check admin authentication
if (!(isset($_SESSION['user_id']) && isset($_SESSION['isadmin']) && $_SESSION['isadmin'] == 1)) {
    header('Location: loginreg.php');
    exit();
}

// Handle inquiry status update
if (isset($_POST['update_status'])) {
    $inquiry_id = $_POST['inquiry_id'];
    $status = $_POST['status'];

    $update_query = "UPDATE contact_submissions SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("si", $status, $inquiry_id);

    if ($stmt->execute()) {
        $success_message = "Inquiry status updated successfully!";
    } else {
        $error_message = "Error updating inquiry status: " . $conn->error;
    }
}

// Handle inquiry deletion
if (isset($_GET['delete'])) {
    $inquiry_id = $_GET['delete'];
    
    $delete_query = "DELETE FROM contact_submissions WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $inquiry_id);
    
    if ($stmt->execute()) {
        $success_message = "Inquiry deleted successfully!";
    } else {
        $error_message = "Error deleting inquiry: " . $conn->error;
    }
}

// Get all inquiries
$inquiries_query = "SELECT c.*
                FROM contact_submissions c 
                ORDER BY c.created_at DESC";
$inquiries_result = $conn->query($inquiries_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inquiries Management | Leafy Life Admin</title>
    <link rel="stylesheet" href="admin.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
    <div class="admin-container">
        <?php include 'admin_nav.php'; ?>
        <main class="admin-main">
            <div class="admin-header">
                <h1><i class="material-icons">contact_mail</i> Inquiries Management</h1>
                <p>Manage customer inquiries and messages</p>
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
            
            <section class="admin-section">
                <h2>All Inquiries</h2>
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Message</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($inquiries_result->num_rows > 0): ?>
                                <?php while($inquiry = $inquiries_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $inquiry['id']; ?></td>
                                        <td><?php echo htmlspecialchars($inquiry['name']); ?></td>
                                        <td><?php echo htmlspecialchars($inquiry['email']); ?></td>
                                        <td><?php echo substr(htmlspecialchars($inquiry['message']), 0, 50) . '...'; ?></td>
                                        <td><?php echo date('M d, Y', strtotime($inquiry['created_at'])); ?></td>
                                        <td>
                                            <form method="post" action="">
                                                <input type="hidden" name="inquiry_id" value="<?php echo $inquiry['id']; ?>">
                                                <select name="status" class="form-control" onchange="this.form.submit()">
                                                    <option value="new" <?php echo (!isset($inquiry['status']) || $inquiry['status'] == 'new') ? 'selected' : ''; ?>>New</option>
                                                    <option value="in_progress" <?php echo (isset($inquiry['status']) && $inquiry['status'] == 'in_progress') ? 'selected' : ''; ?>>In Progress</option>
                                                    <option value="resolved" <?php echo (isset($inquiry['status']) && $inquiry['status'] == 'resolved') ? 'selected' : ''; ?>>Resolved</option>
                                                </select>
                                                <input type="hidden" name="update_status" value="1">
                                            </form>
                                        </td>
                                        <td>
                                            <div class="table-actions">
                                                <a href="view_inquiry.php?id=<?php echo $inquiry['id']; ?>" class="btn-view">View</a>
                                                <a href="inquiries.php?delete=<?php echo $inquiry['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this inquiry?')">Delete</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="no-data">No inquiries found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
</body>
</html>