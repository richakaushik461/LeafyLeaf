<?php
session_start();
require_once 'db_connect.php';

// Store last visited page - Only update if it's not the login/register page itself
$current_page = basename($_SERVER['PHP_SELF']);
$referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
$referrer_page = basename(parse_url($referrer, PHP_URL_PATH));

// Only update last_page if:
// 1. Coming from a different page (not loginreg.php)
// 2. Not a direct access or refresh of loginreg.php
// 3. The referrer exists

if ($referrer_page !== 'loginreg.php' && $current_page === 'loginreg.php' && !empty($referrer)) {
    $_SESSION['last_page'] = $referrer;
}

if ($referrer_page == 'change_password.php') {
    $_SESSION['last_page'] = 'index.php';
}

// If no last_page is set, default to index.php
if (!isset($_SESSION['last_page'])) {
    $_SESSION['last_page'] = 'index.php';
}

$successMessage = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $email = isset($_POST['email']) ? $conn->real_escape_string($_POST['email']) : '';
    $password = isset($_POST['password']) ? $conn->real_escape_string($_POST['password']) : '';

    if ($action === 'login') {
        $result = $conn->query("SELECT * FROM users WHERE email='$email'");
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['isadmin'] = $user['isadmin'];
                $_SESSION['name'] = $user['name'];
                
                // Determine redirect URL
                $redirect = 'index.php'; // Default redirect
                
                if ($user['isadmin']) {
                    $redirect = 'admin_panel.php';
                } elseif (isset($_POST['redirect']) && !empty($_POST['redirect'])) {
                    $redirectUrl = filter_var($_POST['redirect'], FILTER_SANITIZE_URL);
                    // Only use the redirect URL if it's not the login page
                    if (basename($redirectUrl) !== 'loginreg.php') {
                        $redirect = $redirectUrl;
                    }
                } elseif (isset($_SESSION['intended_redirect'])) {
                    $redirect = $_SESSION['intended_redirect'];
                    unset($_SESSION['intended_redirect']); // Clear the stored redirect
                } elseif (isset($_SESSION['last_page']) && basename($_SESSION['last_page']) !== 'loginreg.php') {
                    $redirect = $_SESSION['last_page'];
                }
                
                // Set success message in session so it persists after redirect
                $_SESSION['success_message'] = "Welcome back, " . htmlspecialchars($user['name']) . "! 🌿";
                
                // Perform redirect
                header("Location: $redirect");
                exit();
            } else {
                $errorMessage = "Invalid password! Please try again.";
            }
        } else {
            $errorMessage = "User not found! Please check your email.";
        }
    } elseif ($action === 'register') {
        $checkEmail = $conn->query("SELECT email FROM users WHERE email='$email'");
        if ($checkEmail->num_rows > 0) {
            // Store the intended redirect URL in session before showing error
            if (isset($_POST['redirect']) && !empty($_POST['redirect'])) {
                $_SESSION['intended_redirect'] = $_POST['redirect'];
            }
            $errorMessage = "This email address is already registered! Please use a different email.";
        } else {
            $name = isset($_POST['name']) ? $conn->real_escape_string($_POST['name']) : '';
            $mobile = isset($_POST['mobile']) ? $conn->real_escape_string($_POST['mobile']) : '';
            $address_street = isset($_POST['address_street']) ? $conn->real_escape_string($_POST['address_street']) : '';
            $address_city = isset($_POST['address_city']) ? $conn->real_escape_string($_POST['address_city']) : '';
            $address_state = isset($_POST['address_state']) ? $conn->real_escape_string($_POST['address_state']) : '';
            $address_zip = isset($_POST['address_zip']) ? $conn->real_escape_string($_POST['address_zip']) : '';
            $address_country = isset($_POST['address_country']) ? $conn->real_escape_string($_POST['address_country']) : '';
            $security_question = isset($_POST['security_question']) ? $conn->real_escape_string($_POST['security_question']) : '';
            $security_answer = isset($_POST['security_answer']) ? $conn->real_escape_string($_POST['security_answer']) : '';
            $confirm_password = isset($_POST['confirm_password']) ? $conn->real_escape_string($_POST['confirm_password']) : '';

            if ($password !== $confirm_password) {
                $errorMessage = "Passwords do not match! Please try again.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $conn->query("INSERT INTO users (name, mobile, email, password, isadmin, address_street, address_city, address_state, address_zip, address_country, security_question, security_answer) 
                              VALUES ('$name', '$mobile', '$email', '$hashed_password', 0, '$address_street', '$address_city', '$address_state', '$address_zip','$address_country', '$security_question', '$security_answer')");
                $successMessage = "Welcome to LeafyLife! 🌱 Please login to continue.";
            }
        }
    }
}

// Check for success message in session
if (isset($_SESSION['success_message'])) {
    $successMessage = $_SESSION['success_message'];
    unset($_SESSION['success_message']); // Clear the message after displaying
}

// Get the redirect URL from either POST, Session, or default to index.php
$redirectUrl = isset($_POST['redirect']) ? $_POST['redirect'] : 
              (isset($_SESSION['intended_redirect']) ? $_SESSION['intended_redirect'] : 
              (isset($_SESSION['last_page']) && basename($_SESSION['last_page']) !== 'loginreg.php' ? $_SESSION['last_page'] : 'index.php'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Register | LeafyLife</title>
    <link rel="icon" type="image/x-icon" href="images/favicon.svg">
    <link rel="stylesheet" href="style2.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <form action="" method="POST" class="form" id="login-form" onsubmit="return validateForm(event)">
                <h2>Welcome Back</h2>
                <?php if (isset($errorMessage)): ?>
                    <div class="message error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $errorMessage; ?>
                    </div>
                <?php endif; ?>
                <input type="hidden" name="action" value="login">
                <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirectUrl); ?>">
                <div class="input-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" placeholder="Email" required>
                    <span class="error" id="emailError"></span>
                </div>
                <div class="input-group">
                    <div class="password-container">
                        <label for="password">Password</label>
                        <input type="password" name="password" id="password" placeholder="Password" required>
                    </div>
                    <div class="show-password">
                        <input type="checkbox" id="showPassword" onchange="togglePasswordVisibility()">
                        <label for="showPassword">Show Password</label>
                    </div>
                    <span class="error" id="passwordError"></span>
                </div>
                <button type="submit">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
                <p>Don't have an account? <a href="#" onclick="toggleForm()">Register here</a></p>
                <p><a href="index.php">&larr; Go Back to the Home Page</a></p>
                <p><a href="change_password.php"><i class="fas fa-key"></i> Forgot Password?</a></p>
            </form>

            <form action="" method="POST" class="form hidden" id="register-form" onsubmit="return validateRegisterForm(event)">
                <h2>Join LeafyLife</h2>
                <input type="hidden" name="action" value="register">
                <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirectUrl); ?>">
                <div class="input-group">
                    <label for="name">Full name</label>
                    <input type="text" name="name" placeholder="Full Name" required>
                    <span class="error"></span>
                </div>
                <div class="input-group">
                    <label for="mobile">Mobile</label>
                    <input type="text" name="mobile" placeholder="Mobile" required>
                    <span class="error"></span>
                </div>
                <div class="input-group">
                    <label for="address_street">Street</label>
                    <input type="text" name="address_street" placeholder="Street Address" required>
                    <span class="error"></span>
                </div>
                <div class="input-group">
                    <label for="address_city">City</label>
                    <input type="text" name="address_city" placeholder="City" required>
                    <span class="error"></span>
                </div>
                <div class="input-group">
                    <label for="address_state">State</label>
                    <input type="text" name="address_state" placeholder="State" required>
                    <span class="error"></span>
                </div>
                <div class="input-group">
                    <label for="address_zip">ZIP Code</label>
                    <input type="text" name="address_zip" placeholder="ZIP Code" required>
                    <span class="error"></span>
                </div>
                <div class="input-group">
                    <label for="address_country">Country</label>
                    <input type="text" name="address_country" placeholder="Country" required>
                    <span class="error"></span>
                </div>
                <div class="input-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" placeholder="Email" required>
                    <span class="error" id="registerEmailError"></span>
                </div>
                <div class="input-group">
                    <div class="password-container">
                        <label for="registerPassword">Password</label>
                        <input type="password" name="password" id="registerPassword" placeholder="Password" required>
                    </div>
                    <div class="show-password">
                        <input type="checkbox" id="showRegisterPassword" onchange="toggleRegisterPasswordVisibility('registerPassword', 'showRegisterPassword')">
                        <label for="showRegisterPassword">Show Password</label>
                    </div>
                    <span class="error" id="registerPasswordError"></span>
                </div>
                <div class="input-group">
                    <div class="password-container">
                        <label for="confirmPassword">Confirm Password</label>
                        <input type="password" name="confirm_password" id="confirmPassword" placeholder="Confirm Password" required>
                    </div>
                    <div class="show-password">
                        <input type="checkbox" id="showConfirmPassword" onchange="toggleRegisterPasswordVisibility('confirmPassword', 'showConfirmPassword')">
                        <label for="showConfirmPassword">Show Password</label>
                    </div>
                    <span class="error" id="confirmPasswordError"></span>
                </div>
                <div class="input-group">
                    <select name="security_question" required>
                        <option value="">Select Security Question</option>
                        <option value="pet">What was your first pet's name?</option>
                        <option value="school">What was your first school's name?</option>
                        <option value="city">What city were you born in?</option>
                    </select>
                    <span class="error"></span>
                </div>
                <div class="input-group">
                    <label for="security_answer">Security Answer</label>
                    <input type="text" name="security_answer" placeholder="Security Answer" required>
                    <span class="error"></span>
                </div>
                <button type="submit">
                    <i class="fas fa-user-plus"></i> Register
                </button>
                <p>Already have an account? <a href="#" onclick="toggleForm()">Login here</a></p>
            </form>
        </div>
    </div>
    <?php if ($successMessage): ?>
    <div id="popup" class="popup">
        <i class="fas fa-check-circle"></i> <?php echo $successMessage; ?>
    </div>
    <script>
        setTimeout(() => {
            const popup = document.getElementById('popup');
            popup.classList.add('hide');
            setTimeout(() => popup.remove(), 500);
        }, 2000);
    </script>
    <?php endif; ?>
    <script>
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

    function validateZip(zip) {
        const zipRegex = /^\d{6}$/;
        if (!zip) return 'ZIP code is required';
        if (!zipRegex.test(zip)) return 'Please enter a valid 6-digit ZIP code';
        return '';
    }

    function validateCountry(country) {
        if (!country) return 'Country is required';
        if (!/^[a-zA-Z\s]{2,}$/.test(country)) return 'Country should only contain letters and spaces';
        return '';
    }

    function validateEmail(email) {
        const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        if (!email) return 'Email is required';
        if (!emailRegex.test(email)) return 'Please enter a valid email address';
        return '';
    }

    function validateSecurityQuestion(question, answer) {
        if (!question) return 'Please select a security question';
        if (!answer) return 'Security answer is required';
        if (answer.length < 2) return 'Security answer must be at least 2 characters long';
        if (!/^[a-zA-Z0-9\s]{2,}$/.test(answer)) return 'Security answer should only contain letters, numbers and spaces';
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

    function validateForm(event) {
        event.preventDefault();
        
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        
        document.getElementById('emailError').textContent = '';
        document.getElementById('passwordError').textContent = '';
        
        let isValid = true;
        
        const emailError = validateEmail(email);
        if (emailError) {
            document.getElementById('emailError').textContent = emailError;
            isValid = false;
        }
        
        const passwordErrors = validatePassword(password);
        if (passwordErrors.length > 0) {
            document.getElementById('passwordError').textContent = passwordErrors.join('. ');
            isValid = false;
        }
        
        if (isValid) {
            event.target.submit();
        }
        
        return false;
    }

    function validateRegisterForm(event) {
        event.preventDefault();
        
        const form = event.target;
        const fields = {
            name: form.name.value,
            mobile: form.mobile.value,
            address_street: form.address_street.value,
            address_city: form.address_city.value,
            address_state: form.address_state.value,
            address_zip: form.address_zip.value,
            address_country: form.address_country.value,
            email: form.email.value,
            password: document.getElementById('registerPassword').value,
            confirmPassword: document.getElementById('confirmPassword').value,
            security_question: form.security_question.value,
            security_answer: form.security_answer.value
        };
        
        form.querySelectorAll('.error').forEach(error => error.textContent = '');
        
        let isValid = true;
        
        const nameError = validateName(fields.name);
        if (nameError) {
            form.querySelector('[name="name"]').nextElementSibling.textContent = nameError;
            isValid = false;
        }
        
        const mobileError = validateMobile(fields.mobile);
        if (mobileError) {
            form.querySelector('[name="mobile"]').nextElementSibling.textContent = mobileError;
            isValid = false;
        }
        
        const streetError = validateStreetAddress(fields.address_street);
        if (streetError) {
            form.querySelector('[name="address_street"]').nextElementSibling.textContent = streetError;
            isValid = false;
        }
        
        const cityError = validateCity(fields.address_city);
        if (cityError) {
            form.querySelector('[name="address_city"]').nextElementSibling.textContent = cityError;
            isValid = false;
        }
        
        const stateError = validateState(fields.address_state);
        if (stateError) {
            form.querySelector('[name="address_state"]').nextElementSibling.textContent = stateError;
            isValid = false;
        }
        
        const zipError = validateZip(fields.address_zip);
        if (zipError) {
            form.querySelector('[name="address_zip"]').nextElementSibling.textContent = zipError;
            isValid = false;
        }

        const countryError = validateCountry(fields.address_country);
        if (countryError) {
            form.querySelector('[name="address_country"]').nextElementSibling.textContent = countryError;
            isValid = false;
        }

        const emailError = validateEmail(fields.email);
        if (emailError) {
            document.getElementById('registerEmailError').textContent = emailError;
            isValid = false;
        }
        
        const passwordErrors = validatePassword(fields.password);
        if (passwordErrors.length > 0) {
            document.getElementById('registerPasswordError').textContent = passwordErrors.join('. ');
            isValid = false;
        }
        
        if (fields.password !== fields.confirmPassword) {
            document.getElementById('confirmPasswordError').textContent = 'Passwords do not match';
            isValid = false;
        }
        
        const securityError = validateSecurityQuestion(fields.security_question, fields.security_answer);
        if (securityError) {
            form.querySelector('[name="security_answer"]').nextElementSibling.textContent = securityError;
            isValid = false;
        }
        
        if (isValid) {
            form.submit();
        }
        
        return false;
    }

    function togglePasswordVisibility() {
        const passwordInput = document.getElementById('password');
        const checkbox = document.getElementById('showPassword');
        
        passwordInput.type = checkbox.checked ? 'text' : 'password';
    }

    function toggleRegisterPasswordVisibility(inputId, checkboxId) {
        const passwordInput = document.getElementById(inputId);
        const checkbox = document.getElementById(checkboxId);
        
        passwordInput.type = checkbox.checked ? 'text' : 'password';
    }

    function toggleForm() {
        document.getElementById('login-form').classList.toggle('hidden');
        document.getElementById('register-form').classList.toggle('hidden');
        document.querySelectorAll('.message').forEach(msg => msg.remove());
        
        document.querySelectorAll('.error').forEach(error => error.textContent = '');
        
        document.getElementById('login-form').reset();
        document.getElementById('register-form').reset();
    }

    document.addEventListener('DOMContentLoaded', () => {
        const inputs = document.querySelectorAll('input[type="text"], input[type="email"], input[type="password"]');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                let error = '';
                const errorElement = this.nextElementSibling;
                
                switch(this.name) {
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
                    case 'address_zip':
                        error = validateZip(this.value);
                        break;
                    case 'address_country':
                        error = validateCountry(this.value);
                        break;
                    case 'email':
                        error = validateEmail(this.value);
                        break;
                    case 'security_answer':
                        error = validateSecurityQuestion(
                            document.querySelector('[name="security_question"]').value,
                            this.value
                        );
                        break;
                }
                
                if (errorElement) {
                    errorElement.textContent = error;
                    if (error) {
                        this.classList.add('invalid');
                        this.classList.remove('valid');
                    } else {
                        this.classList.add('valid');
                        this.classList.remove('invalid');
                    }
                }
            });
        });
    });
    </script>
</body>
</html>