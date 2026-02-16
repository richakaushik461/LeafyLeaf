<?php
session_start();
require_once 'db_connect.php';

// Check admin authentication
if (!(isset($_SESSION['user_id']) && isset($_SESSION['isadmin']) && $_SESSION['isadmin'] == 1)) {
    header('Location: loginreg.php');
    exit();
}

$user_id = isset($_GET['id']) ? $_GET['id'] : 0;

// Get user details
$user_query = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();

if ($user_result->num_rows == 0) {
    header('Location: users.php');
    exit();
}

$user = $user_result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $isadmin = isset($_POST['isadmin']) ? 1 : 0;

    // Don't allow admins to demote themselves
    if ($user_id == $_SESSION['user_id']) {
        $isadmin = 1;
    }

    // Only update password if a new one is provided
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $update_query = "UPDATE users SET name = ?, email = ?, password = ?, isadmin = ? WHERE user_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("sssii", $name, $email, $password, $isadmin, $user_id);
    } else {
        $update_query = "UPDATE users SET name = ?, email = ?, isadmin = ? WHERE user_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ssii", $name, $email, $isadmin, $user_id);
    }

    if ($stmt->execute()) {
        $success_message = "User updated successfully!";

        // Refresh user data
        $stmt = $conn->prepare($user_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $user_result = $stmt->get_result();
        $user = $user_result->fetch_assoc();
    } else {
        $error_message = "Error updating user: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User | Leafy Life Admin</title>
    <link rel="stylesheet" href="admin.css">
	<link rel="icon" type="image/x-icon" href="images/favicon.svg">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        .form-error {
            color: red;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'admin_nav.php'; ?>

        <main class="admin-main">
            <div class="admin-header">
                <h1><i class="material-icons">person_edit</i> Edit User</h1>
                <p>Update user information</p>
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
                <form method="post" action="" onsubmit="return validateForm(event)">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            <small id="emailError" class="form-error"></small>
                        </div>
                    </div>

                    <?php if ((isset($_SESSION['user_id']) && isset($_SESSION['isadmin']) && $_SESSION['isadmin'] == 1)): ?>
                        <div class="form-group">
                            <label for="password">New Password</label>
                            <input type="password" id="password" name="password" class="form-control" placeholder="Leave blank to keep current password">
                            <small id="passwordError" class="form-error"></small>
                        </div>
                        <div class="form-group">
                            <label for="confirmPassword">Confirm Password</label>
                            <input type="password" id="confirmPassword" name="confirmPassword" class="form-control">
                            <small id="confirmPasswordError" class="form-error"></small>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="isadmin" value="1" <?php echo $user['isadmin'] == 1 ? 'checked' : ''; ?> <?php echo $user_id == $_SESSION['user_id'] ? 'disabled' : ''; ?>>
                            Administrator Role
                        </label>
                        <?php if ($user_id == $_SESSION['user_id']): ?>
                            <p class="current-user">You cannot remove your own admin status.</p>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn">Update User</button>
                </form>
            </section>
        </main>
    </div>

    <script>
        function validateEmail(email) {
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailPattern.test(email) ? '' : 'Invalid email address';
        }

        function validatePassword(password) {
            const minLength = 8;
            const hasUpperCase = /[A-Z]/.test(password);
            const hasLowerCase = /[a-z]/.test(password);
            const hasNumber = /[0-9]/.test(password);
            const hasSpecialChar = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password);

            const errors = [];

            if (password.length < minLength) {
                errors.push(`Password must be at least ${minLength} characters long`);
            }
            if (!hasUpperCase) {
                errors.push('Password must contain at least one uppercase letter');
            }
            if (!hasLowerCase) {
                errors.push('Password must contain at least one lowercase letter');
            }
            if (!hasNumber) {
                errors.push('Password must contain at least one number');
            }
            if (!hasSpecialChar) {
                errors.push('Password must contain at least one special character');
            }

            return errors;
        }

        function validateForm(event) {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password')?.value || '';
            const confirmPassword = document.getElementById('confirmPassword')?.value || '';

            document.getElementById('emailError').textContent = '';
            if (document.getElementById('passwordError')) document.getElementById('passwordError').textContent = '';
            if (document.getElementById('confirmPasswordError')) document.getElementById('confirmPasswordError').textContent = '';

            let isValid = true;

            const emailError = validateEmail(email);
            if (emailError) {
                document.getElementById('emailError').textContent = emailError;
                isValid = false;
            }

            if (password !== '') {
                const passwordErrors = validatePassword(password);
                if (passwordErrors.length > 0) {
                    document.getElementById('passwordError').textContent = passwordErrors.join('. ');
                    isValid = false;
                }

                if (password !== confirmPassword) {
                    document.getElementById('confirmPasswordError').textContent = 'Passwords do not match.';
                    isValid = false;
                }
            }

            return isValid;
        }
    </script>
</body>
</html>
