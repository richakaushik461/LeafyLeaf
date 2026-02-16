<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['shipping_address'])) {
    header('Location: address.php');
    exit;
}

include 'db_connect.php';
include 'counter_functions.php';

$userId = $_SESSION['user_id'];
$userIconLink = isset($_SESSION['user_id']) ? 'profile.php' : 'loginreg.php';
$counts = getCartWishlistCounts($conn, $_SESSION['user_id'] ?? null);

// Get cart total and items
$cartQuery = "
    SELECT c.product_id, c.quantity, p.price, p.name
    FROM cart c 
    JOIN products p ON c.product_id = p.id 
    WHERE c.user_id = ?
";
$cartStmt = $conn->prepare($cartQuery);
$cartStmt->bind_param("i", $userId);
$cartStmt->execute();
$cartResult = $cartStmt->get_result();

$total = 0;
$cartItems = [];
while ($item = $cartResult->fetch_assoc()) {
    $cartItems[] = $item;
    $total += $item['price'] * $item['quantity'];
}

// Format address for display
$address = $_SESSION['shipping_address'];
$formattedAddress = implode(', ', array_filter([
    $address['name'],
    $address['mobile'],
    $address['street'],
    $address['city'],
    $address['state'],
    $address['zip'],
    $address['country']
]));

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['razorpay_payment_id'])) {
    $paymentId = $_POST['razorpay_payment_id'];
    
    try {
        $conn->begin_transaction();

        // Insert order with shipping name and mobile
        $orderStmt = $conn->prepare("
            INSERT INTO orders (
                user_id, 
                shipping_name, 
                shipping_mobile, 
                total_amount, 
                shipping_address, 
                payment_id, 
                status, 
                created_at
            )
            VALUES (?, ?, ?, ?, ?, ?, 'received', NOW())
        ");
        $orderStmt->bind_param(
            "issdss", 
            $userId, 
            $address['name'], 
            $address['mobile'], 
            $total, 
            $formattedAddress, 
            $paymentId
        );
        $orderStmt->execute();
        $orderId = $conn->insert_id;

        // Insert order items and update stock
        foreach ($cartItems as $item) {
            $itemStmt = $conn->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, price)
                VALUES (?, ?, ?, ?)
            ");
            $itemStmt->bind_param("iiid", $orderId, $item['product_id'], $item['quantity'], $item['price']);
            $itemStmt->execute();

            // Update stock
            $updateStockStmt = $conn->prepare("
                UPDATE products 
                SET qty = qty - ? 
                WHERE id = ?
            ");
            $updateStockStmt->bind_param("ii", $item['quantity'], $item['product_id']);
            $updateStockStmt->execute();
        }

        // Clear cart
        $clearCartStmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $clearCartStmt->bind_param("i", $userId);
        $clearCartStmt->execute();

        $conn->commit();
        
        // Clear shipping address from session
        unset($_SESSION['shipping_address']);
        
        header("Location: order_success.php?order_id=" . $orderId);
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        $error = "Error processing order. Please try again.";
    }
}

// Dummy Razorpay key (replace with your actual test key)
$razorpayKey = "rzp_test_TnFoMvCBMQYyEU";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | LeafyLife</title>
    <link rel="stylesheet" href="try.css">
    <link rel="icon" type="image/x-icon" href="images/favicon.svg">
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <style>
        .checkout-container {
            max-width: 800px;
            margin: 180px auto 50px;
            padding: 20px;
        }
        
        .checkout-summary {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        
        .order-details {
            margin-bottom: 30px;
        }
        
        .order-items {
            margin: 20px 0;
            border-top: 1px solid #eee;
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        
        .item-name {
            flex: 1;
        }
        
        .item-quantity {
            width: 100px;
            text-align: center;
        }
        
        .item-price {
            width: 150px;
            text-align: right;
        }
        
        .address-section {
            margin: 20px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .total-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #eee;
            text-align: right;
        }
        
        .total-amount {
            font-size: 24px;
            color: #2c3e50;
            font-weight: 600;
        }
        
        .pay-btn {
            background: #b2cf6d;
            color: #000;
            padding: 15px 30px;
            border: none;
            border-radius: 6px;
            font-size: 18px;
            cursor: pointer;
            width: 100%;
            margin-top: 20px;
            transition: background-color 0.3s ease;
        }
        
        .pay-btn:hover {
            background: #9ab85c;
        }
        
        .edit-address {
            color: #666;
            text-decoration: none;
            font-size: 14px;
            margin-left: 10px;
        }
        
        .edit-address:hover {
            text-decoration: underline;
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
	<a href="logout_process.php" style="color:red;">Log Out</a>  
        <?php endif; ?>
    </div>
    <?php endif; ?>          
          </ul>

        </nav>
    </header>

    <div class="checkout-container">
        <div class="checkout-summary">
            <h1>Order Summary</h1>
            
            <div class="order-details">
                <div class="order-items">
                    <?php foreach ($cartItems as $item): ?>
                        <div class="order-item">
                            <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                            <div class="item-quantity">×<?php echo $item['quantity']; ?></div>
                            <div class="item-price">₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="address-section">
                    <h3>Shipping Details <a href="address.php" class="edit-address">(Edit)</a></h3>
                    <p><?php echo htmlspecialchars($formattedAddress); ?></p>
                </div>
                
                <div class="total-section">
                    <div class="total-amount">
                        Total: ₹<?php echo number_format($total, 2); ?>
                    </div>
                    <button id="pay-btn" class="pay-btn">Pay Now</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.getElementById('pay-btn').onclick = function(e) {
        var options = {
            "key": "<?php echo $razorpayKey; ?>",
            "amount": "<?php echo $total * 100; ?>",
            "currency": "INR",
            "name": "LeafyLife",
            "description": "Order Payment",
            "handler": function(response) {
                // Create form and submit
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = '';

                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'razorpay_payment_id';
                input.value = response.razorpay_payment_id;

                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            },
            "prefill": {
                "name": "<?php echo htmlspecialchars($address['name']); ?>",
                "contact": "<?php echo htmlspecialchars($address['mobile']); ?>"
            },
            "theme": {
                "color": "#b2cf6d"
            }
        };
        var rzp = new Razorpay(options);
        rzp.open();
        e.preventDefault();
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
</body>
</html>