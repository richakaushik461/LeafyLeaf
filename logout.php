<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout Confirmation | Leafy Life Admin</title>
    <link rel="stylesheet" href="admin.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
    <div class="admin-container">
        <?php include 'admin_nav.php'; ?>
        
        <main class="admin-main">
            <div class="logout-container">
                <i class="material-icons">logout</i>
                <h1>Are you sure you want to log out?</h1>
                <p>You will be logged out of your administrator account.</p>
                
                <div class="logout-actions">
                    <a href="logout_process.php" class="btn btn-primary">Yes, Log Out</a>
                    <a href="admin_index.php" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>

