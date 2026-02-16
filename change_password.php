<?php
session_start();
$mysqli = new mysqli('localhost', 'root', '', 'leafylife');
if ($mysqli->connect_error) die('Connection failed: ' . $mysqli->connect_error);

$message = '';
$isAdmin = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $mysqli->real_escape_string($_POST['email'] ?? '');
    $security_answer = $mysqli->real_escape_string($_POST['security_answer'] ?? '');
    $new_password = $mysqli->real_escape_string($_POST['new_password'] ?? '');
    $confirm_password = $mysqli->real_escape_string($_POST['confirm_password'] ?? '');

    // Check if email belongs to admin only on form submission
    if (!empty($email)) {
        $adminCheck = $mysqli->query("SELECT isadmin FROM users WHERE email='$email'");
        if ($adminCheck && $adminCheck->num_rows > 0) {
            $row = $adminCheck->fetch_assoc();
            if ($row['isadmin'] == 1) {
                $isAdmin = true;
                $message = "Admin accounts must contact supervisor for password changes.";
            }
        }
    }

    if (!$isAdmin && !empty($new_password) && !empty($confirm_password)) {
        if ($new_password !== $confirm_password) {
            $message = "Passwords do not match!";
        } else {
            $result = $mysqli->query("SELECT * FROM users WHERE email='$email' AND security_answer='$security_answer'");
            if ($result->num_rows === 1) {
                $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
                $mysqli->query("UPDATE users SET password='$hashed_password' WHERE email='$email'");
                $message = "Password changed successfully! Please login with your new password.";
                header("refresh:2;url=loginreg.php");
            } else {
                $message = "Invalid email or security answer!";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password | LeafyLife</title>
    <link rel="icon" type="image/x-icon" href="images/favicon.svg">
    <link rel="stylesheet" href="style2.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.3);
            z-index: 1000;
        }
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }
    </style>
</head>
<body>
    <div class="overlay" id="overlay"></div>
    <div class="popup" id="adminPopup">
        <center><h3 style="color:grey;">Admin Account Notice</h3></center>
        <p>Please contact your supervisor for password changes.</p>
        <button onclick="closePopup()">Close</button>
    </div>

    <div class="container">
        <form action="" method="POST" class="form" onsubmit="return validateChangePasswordForm(event)">
            <h2>Change Password</h2>
            <?php if ($message): ?>
                <div class="message"><?php echo $message; ?></div>
            <?php endif; ?>
            <div class="input-group">
                <input type="email" name="email" id="emailInput" placeholder="Email" required>
                <span class="error" id="emailError"></span>
            </div>
            <div class="input-group">
                <select name="security_question" required>
                    <option value="">Select Security Question</option>
                    <option value="pet">What was your first pet's name?</option>
                    <option value="school">What was your first school's name?</option>
                    <option value="city">What city were you born in?</option>
                </select>
                <span class="error" id="questionError"></span>
            </div>
            <div class="input-group">
                <input type="text" name="security_answer" placeholder="Security Answer" required>
                <span class="error" id="answerError"></span>
            </div>
            <div class="input-group">
                <div class="password-container">
                    <input type="password" name="new_password" id="newPassword" placeholder="New Password" required>
                </div>
                <div class="show-password">
                    <input type="checkbox" id="showNewPassword" onchange="togglePasswordVisibility('newPassword', 'showNewPassword')">
                    <label for="showNewPassword">Show Password</label>
                </div>
                <span class="error" id="newPasswordError"></span>
            </div>
            <div class="input-group">
                <div class="password-container">
                    <input type="password" name="confirm_password" id="confirmPassword" placeholder="Confirm New Password" required>
                </div>
                <div class="show-password">
                    <input type="checkbox" id="showConfirmPassword" onchange="togglePasswordVisibility('confirmPassword', 'showConfirmPassword')">
                    <label for="showConfirmPassword">Show Password</label>
                </div>
                <span class="error" id="confirmPasswordError"></span>
            </div>
            <button type="submit">
                <i class="fas fa-key"></i> Change Password
            </button>
            <p><a href="loginreg.php"><i class="fas fa-arrow-left"></i> Back to Login</a></p>
        </form>
    </div>

    <script>
    function showPopup() {
        document.getElementById('overlay').style.display = 'block';
        document.getElementById('adminPopup').style.display = 'block';
    }

    function closePopup() {
        document.getElementById('overlay').style.display = 'none';
        document.getElementById('adminPopup').style.display = 'none';
    }

    function validateEmail(email) {
        const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        if (!email) return 'Email is required';
        if (!emailRegex.test(email)) return 'Please enter a valid email address';
        return '';
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

    function validateChangePasswordForm(event) {
        event.preventDefault();
        
        const form = event.target;
        const email = form.email.value;
        const securityQuestion = form.security_question.value;
        const securityAnswer = form.security_answer.value;
        const newPassword = document.getElementById('newPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
        
        // Reset error messages
        document.querySelectorAll('.error').forEach(error => error.textContent = '');
        
        let isValid = true;
        
        // Email validation
        const emailError = validateEmail(email);
        if (emailError) {
            document.getElementById('emailError').textContent = emailError;
            isValid = false;
        }
        
        // Security question validation
        if (!securityQuestion) {
            document.getElementById('questionError').textContent = 'Please select a security question';
            isValid = false;
        }
        
        if (!securityAnswer) {
            document.getElementById('answerError').textContent = 'Security answer is required';
            isValid = false;
        }
        
        // Password validation
        const passwordErrors = validatePassword(newPassword);
        if (passwordErrors.length > 0) {
            document.getElementById('newPasswordError').textContent = passwordErrors.join('. ');
            isValid = false;
        }
        
        // Confirm password validation
        if (newPassword !== confirmPassword) {
            document.getElementById('confirmPasswordError').textContent = 'Passwords do not match';
            isValid = false;
        }
        
        if (isValid) {
            form.submit();
        }
        
        return false;
    }

    function togglePasswordVisibility(inputId, checkboxId) {
        const passwordInput = document.getElementById(inputId);
        const checkbox = document.getElementById(checkboxId);
        
        passwordInput.type = checkbox.checked ? 'text' : 'password';
    }

    // Show popup if admin message is present
    window.onload = function() {
        const message = document.querySelector('.message');
        if (message && message.textContent.includes('Admin accounts must contact supervisor')) {
            showPopup();
        }
    };
    </script>
</body>
</html>