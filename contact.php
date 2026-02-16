<?php
include 'db_connect.php';
include 'counter_functions.php';

session_start();
$userIconLink = isset($_SESSION['user_id']) ? 'profile.php' : 'loginreg.php';
$counts = getCartWishlistCounts($conn, $_SESSION['user_id'] ?? null);

$message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $inquiry_type = mysqli_real_escape_string($conn, $_POST['inquiry_type']);
    $message_text = mysqli_real_escape_string($conn, $_POST['message']);
    $order_id = isset($_POST['order_id']) ? mysqli_real_escape_string($conn, $_POST['order_id']) : null;

    // Validate order_id if inquiry type requires it
    if (in_array($inquiry_type, ['product_return', 'delivery_assistance', 'complaint'])) {
        if (empty($order_id)) {
            $message = '<div class="alert alert-error">Order ID is required for this type of inquiry.</div>';
        } else {
            // Check if order exists
            $order_check = mysqli_query($conn, "SELECT id FROM orders WHERE id = '$order_id'");
            if (!$order_check) {
                $message = '<div class="alert alert-error">Database error: ' . mysqli_error($conn) . '</div>';
            } else if (mysqli_num_rows($order_check) == 0) {
                $message = '<div class="alert alert-error">Invalid Order ID. Please check and try again.</div>';
            }
        }
    }

    if (empty($message)) {
        // Begin transaction
        mysqli_begin_transaction($conn);
        
        try {
            $sql = "INSERT INTO contact_submissions (name, email, message, inquiry_type, order_id, created_at) 
                    VALUES ('$name', '$email', '$message_text', '$inquiry_type', " . 
                    ($order_id ? "'$order_id'" : "NULL") . ", NOW())";

            if (!mysqli_query($conn, $sql)) {
                // Check for foreign key constraint violation
                if (mysqli_errno($conn) == 1452) { // MySQL foreign key constraint error code
                    throw new Exception("Invalid Order ID reference. Please check the Order ID and try again.");
                } else {
                    throw new Exception("Database error: " . mysqli_error($conn));
                }
            }
            
            // If we get here, commit the transaction
            mysqli_commit($conn);
            $message = '<div class="alert alert-success">Thank you for your message. We will get back to you soon!</div>';
            
        } catch (Exception $e) {
            // Rollback on error
            mysqli_rollback($conn);
            $message = '<div class="alert alert-error">' . $e->getMessage() . '</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="images/favicon.svg">
    <link rel="stylesheet" href="contact.css">
    <title>Contact Us | LeafyLife</title>
    <style>
        .contact-section {
            padding: 80px 0;
            background: #fff;
        }
        
        .contact-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .contact-form {
            background: #f9f9f9;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: 500;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
        }
        
        .form-group textarea {
            height: 150px;
            resize: vertical;
        }
        
        .submit-btn {
            background: #b2cf6d;
            color: #000;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            transition: background-color 0.3s ease;
        }
        
        .submit-btn:hover {
            background: #9ab85c;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        #orderIdField {
            display: none;
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

    <div class="spacer"></div>

    <section class="contact-section">
        <div class="contact-container">
            <h1 class="text-center mb-4" style="text-align: center; margin-bottom: 30px; font-size: 2.5em;">Contact Us</h1>
            
            <?php if ($message) echo $message; ?>

            <form class="contact-form" method="POST" action="" id="contactForm" onsubmit="return validateForm()">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="inquiry_type">Inquiry Type</label>
                    <select id="inquiry_type" name="inquiry_type" required onchange="toggleOrderId()">
                        <option value="general">General Inquiry</option>
                        <option value="product_return">Product Return</option>
                        <option value="delivery_assistance">Delivery Assistance</option>
                        <option value="complaint">Complaint</option>
                        <option value="pesticide_spraying">Pesticide Spraying</option>
                        <option value="consultation">Consultation</option>
                    </select>
                </div>
                
                <div class="form-group" id="orderIdField">
                    <label for="order_id">Order ID</label>
                    <input type="text" id="order_id" name="order_id">
                </div>
                
                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea id="message" name="message" required></textarea>
                </div>
                
                <button type="submit" class="submit-btn">Send Message</button>
            </form>
        </div>
    </section>

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
    function toggleOrderId() {
        const inquiryType = document.getElementById('inquiry_type').value;
        const orderIdField = document.getElementById('orderIdField');
        const orderIdInput = document.getElementById('order_id');
        
        const requiresOrderId = ['product_return', 'delivery_assistance', 'complaint'].includes(inquiryType);
        
        orderIdField.style.display = requiresOrderId ? 'block' : 'none';
        orderIdInput.required = requiresOrderId;
    }

    function validateForm() {
        const inquiryType = document.getElementById('inquiry_type').value;
        const orderIdInput = document.getElementById('order_id');
        
        if (['product_return', 'delivery_assistance', 'complaint'].includes(inquiryType)) {
            if (!orderIdInput.value.trim()) {
                alert('Order ID is required for this type of inquiry.');
                orderIdInput.focus();
                return false;
            }
            
            // Basic order ID format validation (you can adjust this based on your order ID format)
            if (!/^[A-Za-z0-9-]+$/.test(orderIdInput.value.trim())) {
                alert('Invalid Order ID format. Please enter a valid Order ID.');
                orderIdInput.focus();
                return false;
            }
        }
        
        return true;
    }
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