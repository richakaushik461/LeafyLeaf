<?php
session_start();
require_once 'db_connect.php';

// Check admin authentication
if (!(isset($_SESSION['user_id']) && isset($_SESSION['isadmin']) && $_SESSION['isadmin'] == 1)) {
    header('Location: loginreg.php');
    exit();
}

// Handle user role updates
if (isset($_POST['update_role'])) {
    $user_id = $_POST['user_id'];
    $isAdmin = $_POST['isadmin'];
    
    if ($user_id != $_SESSION['user_id']) { // Prevent admins from demoting themselves
        $update_query = "UPDATE users SET isadmin = ? WHERE user_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ii", $isAdmin, $user_id);
        
        if ($stmt->execute()) {
            $success_message = "User role updated successfully!";
        } else {
            $error_message = "Error updating user role: " . $conn->error;
        }
    } else {
        $error_message = "You cannot change your own admin status!";
    }
}

// Handle user deletion
if (isset($_GET['delete'])) {
    $user_id = $_GET['delete'];
    
    // Check if it's not the current user
    if ($user_id != $_SESSION['user_id']) {
        $delete_query = "DELETE FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($delete_query);
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            $success_message = "User deleted successfully!";
        } else {
            $error_message = "Error deleting user: " . $conn->error;
        }
    } else {
        $error_message = "You cannot delete your own account!";
    }
}

// Get all users
$users_query = "SELECT * FROM users ORDER BY isadmin DESC, name ASC";
$users_result = $conn->query($users_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management | Leafy Life Admin</title>
    <link rel="stylesheet" href="admin.css">
	<link rel="icon" type="image/x-icon" href="images/favicon.svg">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
    <div class="admin-container">
        <?php include 'admin_nav.php'; ?>
        
        <main class="admin-main">
            <div class="admin-header">
                <h1><i class="material-icons">people</i> User Management</h1>
                <p>Manage user accounts and privileges</p>
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
                <h2>All Users</h2>
                
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Address</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($user = $users_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $user['user_id']; ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($user['name']); ?>
                                        <?php if($user['user_id'] == $_SESSION['user_id']): ?>
                                            <span class="current-user">(You)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <form method="post" action="">
                                            <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                            <select name="isadmin" class="form-control" onchange="this.form.submit()" <?php echo ($user['user_id'] == $_SESSION['user_id']) ? 'disabled' : ''; ?>>
                                                <option value="0" <?php echo ($user['isadmin'] == 0) ? 'selected' : ''; ?>>User</option>
                                                <option value="1" <?php echo ($user['isadmin'] == 1) ? 'selected' : ''; ?>>Admin</option>
                                            </select>
                                            <input type="hidden" name="update_role" value="1">
                                        </form>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['address_street'] . ', ' . $user['address_city'] . ', ' . $user['address_state'] . ', ' . $user['address_zip']); ?>
                                    </td>
                                    <td>
                                        <div class="table-actions">
                                            <a href="edit_user.php?id=<?php echo $user['user_id']; ?>" class="btn-edit">Edit</a>
                                            <?php if($user['user_id'] != $_SESSION['user_id']): ?>
                                                <a href="users.php?delete=<?php echo $user['user_id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
</body>
</html>

