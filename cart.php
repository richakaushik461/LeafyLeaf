<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: loginreg.php');
    exit;
}

include 'db_connect.php';
include 'counter_functions.php';
$userId = $_SESSION['user_id'];

// Store scroll position if it was sent
if (isset($_POST['scrollPosition'])) {
    $_SESSION['scroll_position'] = $_POST['scrollPosition'];
}

// Handle quantity updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quantity'])) {
    $cartId = $_POST['cart_id'];
    $quantity = max(1, min(99, intval($_POST['quantity'])));
    
    // Get product stock quantity
    $stockQuery = "
        SELECT p.qty as stock_qty 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.cart_id = ? AND c.user_id = ?
    ";
    $stockStmt = $conn->prepare($stockQuery);
    $stockStmt->bind_param("ii", $cartId, $userId);
    $stockStmt->execute();
    $stockResult = $stockStmt->get_result();
    $stockData = $stockResult->fetch_assoc();
    
    if ($stockData && $quantity <= $stockData['stock_qty']) {
        $updateStmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ? AND user_id = ?");
        $updateStmt->bind_param("iii", $quantity, $cartId, $userId);
        $updateStmt->execute();
    }
    
    // Redirect back with scroll position
    header('Location: ' . $_SERVER['PHP_SELF'] . '#cart-item-' . $cartId);
    exit;
}

// Get cart items
$query = "
    SELECT c.cart_id, c.quantity, c.product_id, p.name, p.price, p.image1, p.qty as stock_qty 
    FROM cart c 
    JOIN products p ON c.product_id = p.id 
    WHERE c.user_id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$cartItems = [];
while ($row = $result->fetch_assoc()) {
    $cartItems[] = $row;
}

// Handle move to wishlist
if (isset($_GET['moveToWishlist']) && is_numeric($_GET['moveToWishlist'])) {
    $cartId = $_GET['moveToWishlist'];
    
    // Get product_id from cart
    $getProductStmt = $conn->prepare("SELECT product_id FROM cart WHERE cart_id = ? AND user_id = ?");
    $getProductStmt->bind_param("ii", $cartId, $userId);
    $getProductStmt->execute();
    $productResult = $getProductStmt->get_result();
    
    if ($row = $productResult->fetch_assoc()) {
        $productId = $row['product_id'];
        
        // Begin transaction
        $conn->begin_transaction();
        
        try {
            // Insert into wishlist if not exists
            $insertWishlistStmt = $conn->prepare("INSERT IGNORE INTO wishlist (user_id, product_id) VALUES (?, ?)");
            $insertWishlistStmt->bind_param("ii", $userId, $productId);
            $insertWishlistStmt->execute();
            
            // Delete from cart
            $deleteCartStmt = $conn->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
            $deleteCartStmt->bind_param("ii", $cartId, $userId);
            $deleteCartStmt->execute();
            
            $conn->commit();
        } catch (Exception $e) {
            $conn->rollback();
        }
    }
    
    header('Location: cart.php');
    exit;
}

// Handle item deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $deleteStmt = $conn->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
    $deleteStmt->bind_param("ii", $_GET['delete'], $userId);
    $deleteStmt->execute();
    header('Location: cart.php');
    exit;
}

$userIconLink = isset($_SESSION['user_id']) ? 'profile.php' : 'loginreg.php';
$counts = getCartWishlistCounts($conn, $_SESSION['user_id'] ?? null);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Shopping Cart | LeafyLife</title>
    <link rel="stylesheet" href="try.css">
    <link rel="icon" type="image/x-icon" href="images/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .cart-item {
            scroll-margin-top: 150px; /* Accounts for fixed header */
        }
        
        .quantity-form {
            transition: opacity 0.3s ease;
        }
        
        .quantity-form.updating {
            opacity: 0.5;
        }

        /* Delete Popup Styles */
        .delete-popup {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .delete-popup.active {
            display: flex;
        }

        .delete-popup-content {
            background: white;
            padding: 30px;
            border-radius: 12px;
            max-width: 400px;
            width: 90%;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            animation: popupSlideIn 0.3s ease;
        }

        @keyframes popupSlideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .delete-popup h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 1.5em;
        }

        .delete-popup p {
            color: #666;
            margin-bottom: 25px;
            font-size: 1.1em;
        }

        .delete-popup-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .delete-popup-buttons button {
            padding: 12px 25px;
            border-radius: 8px;
            border: none;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .confirm-delete {
            background-color: #dc3545;
            color: white;
        }

        .confirm-delete:hover {
            background-color: #c82333;
            transform: translateY(-2px);
        }

        .cancel-delete {
            background-color: #e9ecef;
            color: #2c3e50;
        }

        .cancel-delete:hover {
            background-color: #dee2e6;
            transform: translateY(-2px);
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

<!-- Delete Confirmation Popup -->
<div class="delete-popup" id="deletePopup">
    <div class="delete-popup-content">
        <h3>Remove Item</h3>
        <p>Are you sure you want to remove this item from your cart?</p>
        <div class="delete-popup-buttons">
            <button class="confirm-delete" onclick="confirmDelete()">Remove</button>
            <button class="cancel-delete" onclick="closeDeletePopup()">Cancel</button>
        </div>
    </div>
</div>

<div class="cart-container">
    <h1 class="page-title">Your Shopping Cart</h1>
    
    <?php if (empty($cartItems)): ?>
        <div class="empty-cart">
            <p>Your cart is empty</p>
            <a href="shop.php" class="checkout-btn" style="display: inline-block; text-decoration: none; margin-top: 20px;">
                Continue Shopping
            </a>
        </div>
    <?php else: ?>
        <div class="cart-items">
            <?php 
            $total = 0;
            foreach ($cartItems as $item): 
                $itemTotal = $item['price'] * $item['quantity'];
                $total += $itemTotal;
            ?>
                <div class="cart-item" id="cart-item-<?php echo $item['cart_id']; ?>">
                    <img src="http://localhost/<?php echo htmlspecialchars($item['image1']); ?>" 
                         alt="<?php echo htmlspecialchars($item['name']); ?>"
                         onerror="this.src='images/placeholder.jpg'">
                    <div class="item-details">
                        <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                        <p class="price">&#x20B9;<?php echo number_format($item['price'], 2); ?></p>
                        
                        <form method="POST" class="quantity-form" id="form-<?php echo $item['cart_id']; ?>">
                            <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                            <input type="hidden" name="scrollPosition" id="scroll-<?php echo $item['cart_id']; ?>">
                            <label for="quantity-<?php echo $item['cart_id']; ?>">Quantity:</label>
                            <div class="quantity-controls">
                                <button type="button" class="quantity-btn minus" onclick="updateQuantity(<?php echo $item['cart_id']; ?>, -1, <?php echo $item['stock_qty']; ?>)">−</button>
                                <input type="number" 
                                       id="quantity-<?php echo $item['cart_id']; ?>"
                                       name="quantity" 
                                       value="<?php echo $item['quantity']; ?>" 
                                       min="1" 
                                       max="<?php echo $item['stock_qty']; ?>"
                                       readonly>
                                <button type="button" class="quantity-btn plus" onclick="updateQuantity(<?php echo $item['cart_id']; ?>, 1, <?php echo $item['stock_qty']; ?>)">+</button>
                            </div>
                            <?php if ($item['stock_qty'] < 10): ?>
                                <span class="stock-warning">Only <?php echo $item['stock_qty']; ?> left in stock</span>
                            <?php endif; ?>
                        </form>
                        
                        <p class="item-total">
                            Subtotal: &#x20B9;<?php echo number_format($itemTotal, 2); ?>
                        </p>
                        <div class="item-actions">
                            <a href="?moveToWishlist=<?php echo $item['cart_id']; ?>" 
                               class="wishlist-btn">
                                Move to Wishlist
                            </a>
                            <button class="delete-btn" 
                                    onclick="showDeletePopup(<?php echo $item['cart_id']; ?>)">
                                Remove Item
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="cart-summary">
            <h2>Cart Total: &#x20B9;<?php echo number_format($total, 2); ?></h2>
            <button onclick="window.location.href='checkout.php'" class="checkout-btn">
                Proceed to Checkout
            </button>
        </div>
    <?php endif; ?>
</div>

<script>
let deleteItemId = null;

function showDeletePopup(cartId) {
    deleteItemId = cartId;
    document.getElementById('deletePopup').classList.add('active');
}

function closeDeletePopup() {
    document.getElementById('deletePopup').classList.remove('active');
    deleteItemId = null;
}

function confirmDelete() {
    if (deleteItemId) {
        window.location.href = `?delete=${deleteItemId}`;
    }
}

// Close popup when clicking outside
document.getElementById('deletePopup').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeletePopup();
    }
});

function updateQuantity(cartId, change, stockQty) {
    const form = document.getElementById(`form-${cartId}`);
    const input = document.getElementById(`quantity-${cartId}`);
    const scrollInput = document.getElementById(`scroll-${cartId}`);
    const currentValue = parseInt(input.value);
    const newValue = Math.max(1, Math.min(stockQty, currentValue + change));
    
    if (newValue !== currentValue) {
        // Store current scroll position
        scrollInput.value = window.scrollY;
        
        // Add visual feedback
        form.classList.add('updating');
        
        // Update quantity and submit form
        input.value = newValue;
        form.submit();
    }
}

// Restore scroll position if we have one in the URL hash
if (window.location.hash) {
    const element = document.querySelector(window.location.hash);
    if (element) {
        element.scrollIntoView();
    }
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
<script>// Function to check if user is logged in
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
});</script>
</body>
</html>