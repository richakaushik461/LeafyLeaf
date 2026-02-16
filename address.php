<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: loginreg.php');
    exit;
}

include 'db_connect.php';
include 'counter_functions.php';

$userId = $_SESSION['user_id'];
$userIconLink = isset($_SESSION['user_id']) ? 'profile.php' : 'loginreg.php';
$counts = getCartWishlistCounts($conn, $_SESSION['user_id'] ?? null);

// Get user's default address and name
$userQuery = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($userQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$userResult = $stmt->get_result();
$userData = $userResult->fetch_assoc();

// Get cart total
$cartQuery = "
    SELECT SUM(c.quantity * p.price) as total
    FROM cart c 
    JOIN products p ON c.product_id = p.id 
    WHERE c.user_id = ?
";
$cartStmt = $conn->prepare($cartQuery);
$cartStmt->bind_param("i", $userId);
$cartStmt->execute();
$cartResult = $cartStmt->get_result();
$cartData = $cartResult->fetch_assoc();
$total = $cartData['total'] ?? 0;

// Handle address update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['shipping_address'] = [
        'name' => $_POST['name'],
        'mobile' => $_POST['mobile'],
        'street' => $_POST['street'],
        'city' => $_POST['city'],
        'state' => $_POST['state'],
        'zip' => $_POST['zip'],
        'country' => $_POST['country']
    ];
    header('Location: checkout.php');
    exit;
}

// Use session address if set, otherwise use default address
$address = $_SESSION['shipping_address'] ?? [
    'name' => $userData['name'],
    'mobile' => $userData['mobile'],
    'street' => $userData['address_street'],
    'city' => $userData['address_city'],
    'state' => $userData['address_state'],
    'zip' => $userData['address_zip'],
    'country' => $userData['address_country']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shipping Address | LeafyLife</title>
    <link rel="stylesheet" href="try.css">
    <link rel="icon" type="image/x-icon" href="images/favicon.svg">
    <style>
        .address-container {
            max-width: 800px;
            margin: 180px auto 50px;
            padding: 20px;
        }
        
        .address-form {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #b2cf6d;
            box-shadow: 0 0 5px rgba(178, 207, 109, 0.3);
        }

        .form-group input.invalid {
            border-color: #ff4444;
        }

        .form-group input.valid {
            border-color: #00C851;
        }
        
        .error {
            color: #ff4444;
            font-size: 14px;
            margin-top: 5px;
            display: block;
            min-height: 20px;
        }
        
        .order-summary {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .order-summary h2 {
            color: #333;
            margin-bottom: 15px;
        }
        
        .total-amount {
            font-size: 24px;
            color: #2c3e50;
            font-weight: 600;
            margin-top: 10px;
        }
        
        .submit-btn {
            background: #b2cf6d;
            color: #000;
            padding: 15px 30px;
            border: none;
            border-radius: 6px;
            font-size: 18px;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s ease;
        }
        
        .submit-btn:hover {
            background: #9ab85c;
        }

        .submit-btn:disabled {
            background: #cccccc;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <header>
        <br>
        <nav class="nav container">
            <h2 class="nav_logo"><a href="index.php"><img src="images/logo.png" id="logo"></a></h2>
             <ul class="menu_items">
            <li><a href="index.php" class="nav_link">Home</a></li>
            <li><a href="shop.php" class="nav_link">Shop</a></li>
		<li><a href="blogs.php" class="nav_link">Blogs</a></li>
            <li><a href="about.php" class="nav_link">About Us</a></li>
            <li><a href="contact.php" class="nav_link">Contact Us</a></li>
            <?php echo renderNavbarCounters($counts); ?>
            <li class="user-dropdown">
    <a href="<?php echo $userIconLink; ?>" class="nav_link1" id="userIcon">
        <img src="images/user.svg" alt="user" id="svg">
    </a>
    <?php if(isset($_SESSION['user_id'])): ?>
    <div class="dropdown-content" id="userDropdown">
        <a href="profile.php">Profile</a>
        <a href="track_order.php">Track Order</a>
	<a href="cancel_order.php">Cancel Order</a>
	 <?php if(isset($_SESSION['isadmin']) && $_SESSION['isadmin']): ?>
        <a href="admin_panel.php" class="admin-link">Admin Panel</a>
        <?php endif; ?>
	<a href="logout_process.php" style="color:red;">Log Out</a>  
    </div>
    <?php endif; ?>          
          </ul>

        </nav>
    </header>

    <div class="address-container">
        <div class="address-form">
            <div class="order-summary">
                <h2>Order Summary</h2>
                <div class="total-amount">
                    Total Amount: ₹<?php echo number_format($total, 2); ?>
                </div>
            </div>

            <form method="POST" action="" id="shippingForm" onsubmit="return validateShippingForm(event)">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($address['name']); ?>" required>
                    <span class="error" id="nameError"></span>
                </div>

                <div class="form-group">
                    <label for="mobile">Mobile Number</label>
                    <input type="tel" id="mobile" name="mobile" value="<?php echo htmlspecialchars($address['mobile']); ?>" required>
                    <span class="error" id="mobileError"></span>
                </div>

                <div class="form-group">
                    <label for="street">Street Address</label>
                    <input type="text" id="street" name="street" value="<?php echo htmlspecialchars($address['street']); ?>" required>
                    <span class="error" id="streetError"></span>
                </div>

                <div class="form-group">
                    <label for="city">City</label>
                    <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($address['city']); ?>" required>
                    <span class="error" id="cityError"></span>
                </div>

                <div class="form-group">
                    <label for="state">State</label>
                    <input type="text" id="state" name="state" value="<?php echo htmlspecialchars($address['state']); ?>" required>
                    <span class="error" id="stateError"></span>
                </div>

                <div class="form-group">
                    <label for="zip">ZIP Code</label>
                    <input type="text" id="zip" name="zip" value="<?php echo htmlspecialchars($address['zip']); ?>" required>
                    <span class="error" id="zipError"></span>
                </div>

                <div class="form-group">
                    <label for="country">Country</label>
                    <input type="text" id="country" name="country" value="<?php echo htmlspecialchars($address['country']); ?>" required>
                    <span class="error" id="countryError"></span>
                </div>

                <button type="submit" class="submit-btn" id="submitBtn">Continue to Payment</button>
            </form>
        </div>
    </div>

    <footer>
        <div class="footerContainer">
            <div class="socialIcons">
                <a href=""><img src="images/facebook.svg" alt=""></a>
                <a href=""><img src="images/instagram.svg" alt=""></a>
                <a href=""><img src="images/twitter.svg" alt=""></a>
                <a href=""><img src="images/tumblr.svg" alt=""></a>
            </div>
            <div class="footerNav">
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="shop.php">Shop</a></li>
                    <li><a href="blogs.php">Blogs</a></li>
                    <li><a href="about.php">About</a></li>
                    <li><a href="contact.php">Contact Us</a></li>
                </ul>
            </div>
        </div>
        <div class="footerBottom">
            <p>Copyright &copy; 2025</p>
        </div>
    </footer>

    <script>
    function validateName(name) {
        if (!name) return 'Name is required';
        if (!/^[a-zA-Z\s]{2,}$/.test(name)) return 'Name should only contain letters and spaces';
        if (name.length < 2) return 'Name must be at least 2 characters long';
        return '';
    }

    function validateMobile(mobile) {
        if (!mobile) return 'Mobile number is required';
        if (!/^[0-9]{10}$/.test(mobile)) return 'Please enter a valid 10-digit mobile number';
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

    function validateShippingForm(event) {
        event.preventDefault();
        
        const form = event.target;
        const fields = {
            name: form.name.value,
            mobile: form.mobile.value,
            street: form.street.value,
            city: form.city.value,
            state: form.state.value,
            zip: form.zip.value,
            country: form.country.value
        };
        
        // Clear previous errors
        document.querySelectorAll('.error').forEach(error => error.textContent = '');
        document.querySelectorAll('input').forEach(input => {
            input.classList.remove('invalid');
            input.classList.remove('valid');
        });
        
        let isValid = true;

        // Validate name
        const nameError = validateName(fields.name);
        if (nameError) {
            document.getElementById('nameError').textContent = nameError;
            document.getElementById('name').classList.add('invalid');
            isValid = false;
        } else {
            document.getElementById('name').classList.add('valid');
        }

        // Validate mobile
        const mobileError = validateMobile(fields.mobile);
        if (mobileError) {
            document.getElementById('mobileError').textContent = mobileError;
            document.getElementById('mobile').classList.add('invalid');
            isValid = false;
        } else {
            document.getElementById('mobile').classList.add('valid');
        }
        
        // Validate street
        const streetError = validateStreetAddress(fields.street);
        if (streetError) {
            document.getElementById('streetError').textContent = streetError;
            document.getElementById('street').classList.add('invalid');
            isValid = false;
        } else {
            document.getElementById('street').classList.add('valid');
        }
        
        // Validate city
        const cityError = validateCity(fields.city);
        if (cityError) {
            document.getElementById('cityError').textContent = cityError;
            document.getElementById('city').classList.add('invalid');
            isValid = false;
        } else {
            document.getElementById('city').classList.add('valid');
        }
        
        // Validate state
        const stateError = validateState(fields.state);
        if (stateError) {
            document.getElementById('stateError').textContent = stateError;
            document.getElementById('state').classList.add('invalid');
            isValid = false;
        } else {
            document.getElementById('state').classList.add('valid');
        }
        
        // Validate ZIP
        const zipError = validateZip(fields.zip);
        if (zipError) {
            document.getElementById('zipError').textContent = zipError;
            document.getElementById('zip').classList.add('invalid');
            isValid = false;
        } else {
            document.getElementById('zip').classList.add('valid');
        }
        
        // Validate country
        const countryError = validateCountry(fields.country);
        if (countryError) {
            document.getElementById('countryError').textContent = countryError;
            document.getElementById('country').classList.add('invalid');
            isValid = false;
        } else {
            document.getElementById('country').classList.add('valid');
        }
        
        if (isValid) {
            form.submit();
        }
        
        return false;
    }

    // Real-time validation
    document.addEventListener('DOMContentLoaded', () => {
        const inputs = document.querySelectorAll('input');
        const submitBtn = document.getElementById('submitBtn');
        
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                let error = '';
                const errorElement = document.getElementById(`${this.id}Error`);
                
                switch(this.id) {
                    case 'name':
                        error = validateName(this.value);
                        break;
                    case 'mobile':
                        error = validateMobile(this.value);
                        break;
                    case 'street':
                        error = validateStreetAddress(this.value);
                        break;
                    case 'city':
                        error = validateCity(this.value);
                        break;
                    case 'state':
                        error = validateState(this.value);
                        break;
                    case 'zip':
                        error = validateZip(this.value);
                        break;
                    case 'country':
                        error = validateCountry(this.value);
                        break;
                }
                
                if (error) {
                    this.classList.add('invalid');
                    this.classList.remove('valid');
                    errorElement.textContent = error;
                } else {
                    this.classList.remove('invalid');
                    this.classList.add('valid');
                    errorElement.textContent = '';
                }
                
                // Check if all fields are valid
                const allValid = Array.from(inputs).every(input => 
                    input.classList.contains('valid')
                );
                
                submitBtn.disabled = !allValid;
            });
            
            // Trigger validation on initial load
            input.dispatchEvent(new Event('blur'));
        });
    });
// User dropdown functionality
document.addEventListener('DOMContentLoaded', () => {
    const userIcon = document.getElementById('userIcon');
    const dropdown = document.getElementById('userDropdown');

    if (userIcon && dropdown) {
        userIcon.addEventListener('click', (e) => {
            if (isUserLoggedIn()) {
                e.preventDefault();
                dropdown.classList.toggle('show');
                e.stopPropagation();
            }
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.matches('#userIcon') && !e.target.matches('#svg')) {
                if (dropdown.classList.contains('show')) {
                    dropdown.classList.remove('show');
                }
            }
        });
    }
});
// Function to check if user is logged in
function isUserLoggedIn() {
    return document.querySelector('a[href="profile.php"]') !== null;
}

// Function to show auth popup
function showAuthPopup() {
    const popup = document.createElement('div');
    popup.className = 'auth-popup';
    popup.innerHTML = `
        <div class="popup-content">
            <h3>Please Log In</h3>
            <p>You need to be logged in to use this feature.</p>
            <div class="popup-buttons">
                <button onclick="window.location.href='loginreg.php'" class="login-btn">Log In</button>
                <button onclick="closePopup()" class="close-btn">Close</button>
            </div>
        </div>
    `;
    document.body.appendChild(popup);

    // Close popup when clicking outside
    popup.addEventListener('click', (e) => {
        if (e.target === popup) {
            closePopup();
        }
    });
}

// Function to close auth popup
function closePopup() {
    const popup = document.querySelector('.auth-popup');
    if (popup) {
        popup.remove();
    }
}

// Function to show cart popup
function showCartPopup(productName, price) {
    const popup = document.createElement('div');
    popup.className = 'cart-popup';
    popup.innerHTML = `
        <div class="cart-popup-content">
            <button class="close-popup" onclick="closeCartPopup()">&times;</button>
            <h3>Added to Cart!</h3>
            <p>${productName} has been added to your cart.</p>
            <p>Price: ₹${price}</p>
            <div class="cart-popup-buttons">
                <button class="view-cart-btn" onclick="viewCart()">View Cart</button>
                <button class="continue-shopping-btn" onclick="closeCartPopup()">Continue Shopping</button>
            </div>
        </div>
    `;
    document.body.appendChild(popup);

    // Close popup when clicking outside
    popup.addEventListener('click', (e) => {
        if (e.target === popup) {
            closeCartPopup();
        }
    });
}

// Function to close cart popup
function closeCartPopup() {
    const popup = document.querySelector('.cart-popup');
    if (popup) {
        popup.remove();
    }
}

// Function to handle view cart action
function viewCart() {
    closeCartPopup();
    // You can add redirect to cart page here if needed
}

// Add event listeners when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    // Wishlist buttons
    const wishlistButtons = document.querySelectorAll('.wishlist');
    wishlistButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            if (!isUserLoggedIn()) {
                showAuthPopup();
            } else {
                handleWishlistButtonClick(button);
            }
        });
    });


    const cartIcon = document.querySelector('a[href="cart.php"]');
    if (cartIcon) {
        cartIcon.addEventListener('click', (e) => {
            if (!isUserLoggedIn()) {
                e.preventDefault();
                showAuthPopup();
            }
        });
    }

    // Header wishlist icon
    const wishlistIcon = document.querySelector('a[href="wishlist.php"]');
    if (wishlistIcon) {
        wishlistIcon.addEventListener('click', (e) => {
            if (!isUserLoggedIn()) {
                e.preventDefault();
                showAuthPopup();
            }
        });
    }
});
</script>

</body>
</html>