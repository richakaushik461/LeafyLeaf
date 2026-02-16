<?php
session_start();
require_once 'db_connect.php';

// Check admin authentication
if (!(isset($_SESSION['user_id']) && isset($_SESSION['isadmin']) && $_SESSION['isadmin'] == 1)) {
    header('Location: loginreg.php');
    exit();
}

$inquiry_id = isset($_GET['id']) ? $_GET['id'] : 0;

// Get inquiry details
$inquiry_query = "SELECT c.*
                 FROM contact_submissions c 
           
                 WHERE c.id = ?";
$stmt = $conn->prepare($inquiry_query);
$stmt->bind_param("i", $inquiry_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: inquiries.php'); // Updated the redirect to inquiries.php
    exit();
}

$inquiry = $result->fetch_assoc();

// Handle status update
if (isset($_POST['update_status'])) {
    $status = $_POST['status'];

    $update_query = "UPDATE contact_submissions SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("si", $status, $inquiry_id);

    if ($stmt->execute()) {
        $success_message = "Inquiry status updated successfully!";
        $inquiry['status'] = $status;
    } else {
        $error_message = "Error updating inquiry status: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Inquiry | Leafy Life Admin</title>
    <link rel="stylesheet" href="admin.css">
	<link rel="icon" type="image/x-icon" href="images/favicon.svg">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
    <div class="admin-container">
        <?php include 'admin_nav.php'; ?>
        
        <main class="admin-main">
            <div class="admin-header">
                <h1><i class="material-icons">contact_mail</i> View Inquiry</h1>
                <p>Inquiry #<?php echo $inquiry['id']; ?></p>
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
                <div class="section-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h2>Inquiry Details</h2>
                    <a href="inquiries.php" class="btn btn-secondary">Back to Inquiries</a>
                </div>
                
                <div class="inquiry-details">
                    <div class="form-grid">
                        <div>
                            <p><strong>Name:</strong> <?php echo htmlspecialchars($inquiry['name']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($inquiry['email']); ?></p>
                            <p><strong>Date:</strong> <?php echo date('M d, Y h:i A', strtotime($inquiry['created_at'])); ?></p>
                            <p><strong>Status:</strong> 
                                <form method="post" action="">
                                    <select name="status" class="form-control" onchange="this.form.submit()">
                                        <option value="new" <?php echo (!isset($inquiry['status']) || $inquiry['status'] == 'new') ? 'selected' : ''; ?>>New</option>
                                        <option value="in_progress" <?php echo (isset($inquiry['status']) && $inquiry['status'] == 'in_progress') ? 'selected' : ''; ?>>In Progress</option>
                                        <option value="resolved" <?php echo (isset($inquiry['status']) && $inquiry['status'] == 'resolved') ? 'selected' : ''; ?>>Resolved</option>
                                    </select>
                                    <input type="hidden" name="update_status" value="1">
                                </form>
                            </p>
                        </div>
                        
                        <?php if (!empty($inquiry['user_id'])): ?>
                            <div>
                                <p><strong>User Account:</strong> Yes</p>
                                <p><strong>User Name:</strong> <?php echo htmlspecialchars($inquiry['user_name']); ?></p>
                                <p><strong>User Email:</strong> <?php echo htmlspecialchars($inquiry['user_email']); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label><strong>Message:</strong></label>
                        <div class="message-content" style="background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin-top: 10px;">
                            <?php echo nl2br(htmlspecialchars($inquiry['message'])); ?>
                        </div>
                    </div>
                    
                    <div class="form-actions" style="margin-top: 20px;">
                        <a href="inquiries.php?delete=<?php echo $inquiry['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this inquiry?')">Delete Inquiry</a>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
