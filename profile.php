<?php
session_start();
$mysqli = new mysqli('localhost', 'root', '', 'leafylife');
if ($mysqli->connect_error) die('Connection failed: ' . $mysqli->connect_error);

// Store last visited page - Only update if it's not the profile page itself
$current_page = basename($_SERVER['PHP_SELF']);
$referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
$referrer_page = basename(parse_url($referrer, PHP_URL_PATH));

// Only update last_page if:
// 1. Coming from a different page (not profile.php)
// 2. Not a direct access or refresh of profile.php
// 3. The referrer exists
if ($referrer_page !== 'profile.php' && $current_page === 'profile.php' && !empty($referrer)) {
    $_SESSION['last_page'] = $referrer;
}

// If no last_page is set, default to index.php
if (!isset($_SESSION['last_page'])) {
    $_SESSION['last_page'] = 'index.php';
}

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";
$message_type = "";

// Fetch user details
$result = $mysqli->query("SELECT * FROM users WHERE user_id = '$user_id'");
$user = $result->fetch_assoc();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = $mysqli->real_escape_string($_POST['name']);
    $mobile = $mysqli->real_escape_string($_POST['mobile']);
    $address_street = $mysqli->real_escape_string($_POST['address_street']);
    $address_city = $mysqli->real_escape_string($_POST['address_city']);
    $address_state = $mysqli->real_escape_string($_POST['address_state']);
    $address_country = $mysqli->real_escape_string($_POST['address_country']);
    $address_zip = $mysqli->real_escape_string($_POST['address_zip']);

    $mysqli->query("UPDATE users SET name='$name', mobile='$mobile', address_street='$address_street', address_city='$address_city', address_state='$address_state', address_country='$address_country', address_zip='$address_zip' WHERE user_id='$user_id'");
    $message = "Profile updated successfully!";
    $message_type = "success";

    // Refresh user details
    $result = $mysqli->query("SELECT * FROM users WHERE user_id = '$user_id'");
    $user = $result->fetch_assoc();
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $mysqli->real_escape_string($_POST['current_password']);
    $new_password = $mysqli->real_escape_string($_POST['new_password']);
    
    // Verify current password
    $result = $mysqli->query("SELECT password FROM users WHERE user_id = '$user_id'");
    $user_pwd = $result->fetch_assoc();
    
    if (password_verify($current_password, $user_pwd['password'])) {
        // Hash new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update password
        $mysqli->query("UPDATE users SET password = '$hashed_password' WHERE user_id = '$user_id'");
        
        if ($mysqli->affected_rows > 0) {
            $message = "Password updated successfully!";
            $message_type = "success";
        } else {
            $message = "Error updating password.";
            $message_type = "error";
        }
    } else {
        $message = "Current password is incorrect.";
        $message_type = "error";
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    $last_page = $_SESSION['last_page'] ?? 'index.php';
    session_destroy();
    header('Location: ' . $last_page);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile | LeafyLife</title>
    <link rel="icon" type="image/x-icon" href="images/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background: #f5f5f5;
            color: #333;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            width: 100%;
            max-width: 500px;
            margin: 20px;
            position: relative;
            perspective: 1000px;
        }
        .forms-wrapper {
            position: relative;
            width: 100%;
            height: 600px;
        }
        .profile-container, .password-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            backface-visibility: hidden;
            transform-style: preserve-3d;
            transition: transform 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .profile-container {
            transform: rotateY(0deg);
        }
        .password-container {
            transform: rotateY(180deg);
        }
        #form-toggle {
            display: none;
        }
        #form-toggle:checked ~ .forms-wrapper .profile-container {
            transform: rotateY(180deg);
        }
        #form-toggle:checked ~ .forms-wrapper .password-container {
            transform: rotateY(360deg);
        }
        .section-title {
            text-align: center;
            color: #27ae60;
            margin-bottom: 20px;
        }
        .form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        label {
            font-weight: 600;
            color: #555;
        }
        input {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }
        button {
            background: #27ae60;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.2s;
        }
        button:hover {
            background: #219653;
        }
        .toggle-form {
            display: block;
            margin-top: 20px;
            text-align: center;
            color: #27ae60;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
        }
        .toggle-form:hover {
            text-decoration: underline;
        }
        .logout-btn {
            display: block;
            margin-top: 20px;
            text-align: center;
            color: #e74c3c;
            text-decoration: none;
            font-weight: 600;
        }
        .logout-btn:hover {
            text-decoration: underline;
        }
        .message {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: center;
        }
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .password-requirements {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        .show-password {
            position: relative;
            display: inline-block;
            margin-top: 5px;
        }
        .show-password input {
            margin-right: 5px;
        }
        .error-message {
            color: #dc3545;
            font-size: 12px;
            margin-top: 5px;
        }
        input.error {
            border-color: #dc3545;
        }
        input.valid {
            border-color: #28a745;
        }
        .back-btn {
            display: block;
            margin-top: 20px;
            text-align: center;
            text-decoration: none;
            font-weight: 600;
            color: #134611;
            margin-bottom: 10px;
        }
        .button-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <input type="checkbox" id="form-toggle">
        
        <div class="forms-wrapper">
            <!-- Profile Section -->
            <div class="profile-container">
                <h2 class="section-title">My Profile</h2>
                <?php if ($message && isset($_POST['update_profile'])): ?>
                    <div class="message <?php echo $message_type; ?>"><?php echo $message; ?></div>
                <?php endif; ?>
                <form method="POST" class="form">
                    <input type="hidden" name="update_profile" value="1">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="mobile">Mobile</label>
                        <input type="text" id="mobile" name="mobile" value="<?php echo htmlspecialchars($user['mobile']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="address_street">Street Address</label>
                        <input type="text" id="address_street" name="address_street" value="<?php echo htmlspecialchars($user['address_street']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="address_city">City</label>
                        <input type="text" id="address_city" name="address_city" value="<?php echo htmlspecialchars($user['address_city']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="address_state">State</label>
                        <input type="text" id="address_state" name="address_state" value="<?php echo htmlspecialchars($user['address_state']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="address_zip">ZIP Code</label>
                        <input type="text" id="address_zip" name="address_zip" value="<?php echo htmlspecialchars($user['address_zip']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="address_country">Country</label>
                        <input type="text" id="address_country" name="address_country" value="<?php echo htmlspecialchars($user['address_country']); ?>" required>
                    </div>
                    <button type="submit">Update Profile</button>
                    <label for="form-toggle" class="toggle-form">Change Password →</label>
                </form>
                <div class="button-group">
                    <a href="<?php echo htmlspecialchars($_SESSION['last_page']); ?>" class="back-btn">← Back to Previous Page</a>
                    <a href="?logout=true" class="logout-btn">Log Out</a>
                </div>
            </div>

            <!-- Password Change Section -->
            <div class="password-container">
                <h2 class="section-title">Change Password</h2>
                <?php if ($message && isset($_POST['change_password'])): ?>
                    <div class="message <?php echo $message_type; ?>"><?php echo $message; ?></div>
                <?php endif; ?>
                <form method="POST" class="form" id="passwordForm">
                    <input type="hidden" name="change_password" value="1">
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" required>
                        <div class="show-password">
                            <input type="checkbox" id="show_current_password">
                            <label for="show_current_password">Show password</label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" required>
                        <div class="password-requirements">
                            Password must be at least 8 characters long and contain at least one uppercase letter, 
                            one lowercase letter, one number, and one special character.
                        </div>
                        <div class="show-password">
                            <input type="checkbox" id="show_new_password">
                            <label for="show_new_password">Show password</label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                        <div class="show-password">
                            <input type="checkbox" id="show_confirm_password">
                            <label for="show_confirm_password">Show password</label>
                        </div>
                    </div>
                    
                    <button type="submit">Change Password</button>
                    <label for="form-toggle" class="toggle-form">← Back to Profile</label>
                    <div class="button-group">
                        <a href="<?php echo htmlspecialchars($_SESSION['last_page']); ?>" class="back-btn">← Back to Previous Page</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    // Validation helper functions
    function validateName(name) {
        const nameRegex = /^[a-zA-Z\s]{2,50}$/;
        if (!name) return 'Name is required';
        if (!nameRegex.test(name)) return 'Name should only contain letters and spaces (2-50 characters)';
        return '';
    }

    function validateMobile(mobile) {
        const mobileRegex = /^\d{10}$/;
        if (!mobile) return 'Mobile number is required';
        if (!mobileRegex.test(mobile)) return 'Please enter a valid 10-digit mobile number';
        return '';
    }

    function validateStreetAddress(street) {
        if (!street) return 'Street address is required';
        if (street.length < 5) return 'Street address must be at least 5 characters long';
        if (!/^[a-zA-Z0-9\s\.,#-]+$/.test(street)) return 'Please enter a valid street address';
        return '';
    }

    function validateCity(city) {
        if (!city) return 'City is required';
        if (!/^[a-zA-Z\s]{2,}$/.test(city)) return 'City should only contain letters and spaces';
        return '';
    }

    function validateState(state) {
        if (!state) return 'State is required';
        if (!/^[a-zA-Z\s]{2,}$/.test(state)) return 'State should only contain letters and spaces';
        return '';
    }

    function validateCountry(country) {
        if (!country) return 'Country is required';
        if (!/^[a-zA-Z\s]{2,}$/.test(country)) return 'Country should only contain letters and spaces';
        return '';
    }

    function validateZip(zip) {
        const zipRegex = /^\d{6}$/;
        if (!zip) return 'ZIP code is required';
        if (!zipRegex.test(zip)) return 'Please enter a valid 6-digit ZIP code';
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

    // Add error display functionality
    function showError(input, message) {
        const errorDiv = input.nextElementSibling;
        if (!errorDiv || !errorDiv.classList.contains('error-message')) {
            const div = document.createElement('div');
            div.className = 'error-message';
            div.style.color = '#dc3545';
            div.style.fontSize = '12px';
            div.style.marginTop = '5px';
            input.parentNode.insertBefore(div, input.nextSibling);
            input.style.borderColor = '#dc3545';
        }
        input.nextElementSibling.textContent = message;
    }

    function clearError(input) {
        const errorDiv = input.nextElementSibling;
        if (errorDiv && errorDiv.classList.contains('error-message')) {
            errorDiv.textContent = '';
            input.style.borderColor = '#ccc';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Profile form validation
        const profileForm = document.querySelector('form');
        const passwordForm = document.getElementById('passwordForm');

        // Real-time validation for profile fields
        const inputs = {
            name: document.getElementById('name'),
            mobile: document.getElementById('mobile'),
            address_street: document.getElementById('address_street'),
            address_city: document.getElementById('address_city'),
            address_state: document.getElementById('address_state'),
            address_country: document.getElementById('address_country'),
            address_zip: document.getElementById('address_zip')
        };

        // Add validation to each input
        Object.entries(inputs).forEach(([field, input]) => {
            if (input) {
                input.addEventListener('blur', function() {
                    let error = '';
                    switch(field) {
                        case 'name':
                            error = validateName(this.value);
                            break;
                        case 'mobile':
                            error = validateMobile(this.value);
                            break;
                        case 'address_street':
                            error = validateStreetAddress(this.value);
                            break;
                        case 'address_city':
                            error = validateCity(this.value);
                            break;
                        case 'address_state':
                            error = validateState(this.value);
                            break;
                        case 'address_country':
                            error = validateCountry(this.value);
                            break;
                        case 'address_zip':
                            error = validateZip(this.value);
                            break;
                    }
                    
                    if (error) {
                        showError(this, error);
                    } else {
                        clearError(this);
                    }
                });
            }
        });

        // Profile form submission validation
        if (profileForm) {
            profileForm.addEventListener('submit', function(e) {
                if (!this.querySelector('[name="change_password"]')) {
                    e.preventDefault();
                    let isValid = true;
                    
                    // Validate all fields
                    Object.entries(inputs).forEach(([field, input]) => {
                        if (input) {
                            let error = '';
                            switch(field) {
                                case 'name':
                                    error = validateName(input.value);
                                    break;
                                case 'mobile':
                                    error = validateMobile(input.value);
                                    break;
                                case 'address_street':
                                    error = validateStreetAddress(input.value);
                                    break;
                                case 'address_city':
                                    error = validateCity(input.value);
                                    break;
                                case 'address_state':
                                    error = validateState(input.value);
                                    break;
                                case 'address_country':
                                    error = validateCountry(input.value);
                                    break;
                                case 'address_zip':
                                    error = validateZip(input.value);
                                    break;
                            }
                            
                            if (error) {
                                showError(input, error);
                                isValid = false;
                            } else {
                                clearError(input);
                            }
                        }
                    });

                    if (isValid) {
                        this.submit();
                    }
                }
            });
        }

        // Password form validation
        if (passwordForm) {
            passwordForm.addEventListener('submit', function(e) {
                e.preventDefault();
                let isValid = true;

                const currentPassword = document.getElementById('current_password');
                const newPassword = document.getElementById('new_password');
                const confirmPassword = document.getElementById('confirm_password');

                // Validate current password
                if (!currentPassword.value) {
                    showError(currentPassword, 'Current password is required');
                    isValid = false;
                } else {
                    clearError(currentPassword);
                }

                // Validate new password
                const passwordErrors = validatePassword(newPassword.value);
                if (passwordErrors.length > 0) {
                    showError(newPassword, passwordErrors.join('. '));
                    isValid = false;
                } else {
                    clearError(newPassword);
                }

                // Validate password confirmation
                if (newPassword.value !== confirmPassword.value) {
                    showError(confirmPassword, 'Passwords do not match');
                    isValid = false;
                } else {
                    clearError(confirmPassword);
                }

                if (isValid) {
                    this.submit();
                }
            });
        }

        // Toggle password visibility
        document.querySelectorAll('.show-password input').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const inputId = this.id.replace('show_', '');
                const passwordInput = document.getElementById(inputId);
                passwordInput.type = this.checked ? 'text' : 'password';
            });
        });
    });
    </script>
</body>
</html>