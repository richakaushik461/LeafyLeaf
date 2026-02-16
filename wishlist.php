<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: loginreg.php');
    exit;
}

include 'db_connect.php';
include 'counter_functions.php';
$userId = $_SESSION['user_id'];

// Get wishlist items
$query = "
    SELECT w.wishlist_id, w.product_id, p.name, p.price, p.image1, p.qty as stock_qty 
    FROM wishlist w 
    JOIN products p ON w.product_id = p.id 
    WHERE w.user_id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$wishlistItems = [];
while ($row = $result->fetch_assoc()) {
    $wishlistItems[] = $row;
}

// Handle move to cart
if (isset($_GET['moveToCart']) && is_numeric($_GET['moveToCart'])) {
    $wishlistId = $_GET['moveToCart'];
    
    // Get product_id from wishlist
    $getProductStmt = $conn->prepare("SELECT product_id FROM wishlist WHERE wishlist_id = ? AND user_id = ?");
    $getProductStmt->bind_param("ii", $wishlistId, $userId);
    $getProductStmt->execute();
    $productResult = $getProductStmt->get_result();
    
    if ($row = $productResult->fetch_assoc()) {
        $productId = $row['product_id'];
        
        // Begin transaction
        $conn->begin_transaction();
        
        try {
            // Check if product already exists in cart
            $checkCartStmt = $conn->prepare("SELECT cart_id FROM cart WHERE user_id = ? AND product_id = ?");
            $checkCartStmt->bind_param("ii", $userId, $productId);
            $checkCartStmt->execute();
            $cartResult = $checkCartStmt->get_result();
            
            if ($cartResult->num_rows === 0) {
                // Insert into cart if not exists
                $insertCartStmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
                $insertCartStmt->bind_param("ii", $userId, $productId);
                $insertCartStmt->execute();
            }
            
            // Delete from wishlist
            $deleteWishlistStmt = $conn->prepare("DELETE FROM wishlist WHERE wishlist_id = ? AND user_id = ?");
            $deleteWishlistStmt->bind_param("ii", $wishlistId, $userId);
            $deleteWishlistStmt->execute();
            
            $conn->commit();
        } catch (Exception $e) {
            $conn->rollback();
        }
    }
    
    header('Location: wishlist.php');
    exit;
}

// Handle item deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $deleteStmt = $conn->prepare("DELETE FROM wishlist WHERE wishlist_id = ? AND user_id = ?");
    $deleteStmt->bind_param("ii", $_GET['delete'], $userId);
    $deleteStmt->execute();
    header('Location: wishlist.php');
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
    <title>Your Wishlist | LeafyLife</title>
    <link rel="stylesheet" href="try.css">
    <link rel="icon" type="image/x-icon" href="images/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
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

        .wishlist-container {
            max-width: 1200px;
            margin: 150px auto 40px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
        }

        .wishlist-items {
            margin-bottom: 30px;
        }

        .wishlist-item {
            display: flex;
            border: 1px solid #e9ecef;
            margin-bottom: 25px;
            padding: 30px;
            border-radius: 16px;
            background: #fff;
            transition: all 0.3s ease;
        }

        .wishlist-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
        }

        .wishlist-item img {
            width: 180px;
            height: 180px;
            object-fit: cover;
            margin-right: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .wishlist-item img:hover {
            transform: scale(1.05);
        }

        .item-details {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 10px 0;
        }

        .item-details h3 {
            margin: 0 0 15px 0;
            color: #2c3e50;
            font-size: 1.6em;
            font-weight: 700;
            letter-spacing: -0.3px;
        }

        .price {
            font-size: 1.5em;
            color: #2c3e50;
            margin: 15px 0;
            font-weight: 600;
        }

        .item-actions {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .cart-btn {
            color: #2ecc71;
            text-decoration: none;
            font-weight: 600;
            padding: 12px 20px;
            border-radius: 8px;
            background-color: #fff;
            border: 2px solid #2ecc71;
            transition: all 0.3s ease;
            display: inline-block;
            text-align: center;
            font-size: 1.1em;
        }

        .cart-btn:hover {
            background-color: #2ecc71;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(46, 204, 113, 0.2);
        }

        .delete-btn {
            color: #dc3545;
            text-decoration: none;
            font-weight: 600;
            padding: 12px 20px;
            border-radius: 8px;
            background-color: #fff;
            border: 2px solid #dc3545;
            transition: all 0.3s ease;
            display: inline-block;
            text-align: center;
            font-size: 1.1em;
        }

        .delete-btn:hover {
            background-color: #dc3545;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.2);
        }

        .empty-wishlist {
            text-align: center;
            padding: 80px 40px;
            font-size: 1.5em;
            color: #6c757d;
            background: #fff;
            border-radius: 16px;
            margin: 40px 0;
            border: 2px solid #e9ecef;
        }

        .empty-wishlist p {
            margin-bottom: 30px;
            font-weight: 500;
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
        <p>Are you sure you want to remove this item from your wishlist?</p>
        <div class="delete-popup-buttons">
            <button class="confirm-delete" onclick="confirmDelete()">Remove</button>
            <button class="cancel-delete" onclick="closeDeletePopup()">Cancel</button>
        </div>
    </div>
</div>

<div class="wishlist-container">
    <h1 class="page-title">Your Wishlist</h1>
    
    <?php if (empty($wishlistItems)): ?>
        <div class="empty-wishlist">
            <p>Your wishlist is empty</p>
            <a href="shop.php" class="cart-btn" style="display: inline-block; text-decoration: none; margin-top: 20px;">
                Continue Shopping
            </a>
        </div>
    <?php else: ?>
        <div class="wishlist-items">
            <?php foreach ($wishlistItems as $item): ?>
                <div class="wishlist-item">
                    <img src="http://localhost/<?php echo htmlspecialchars($item['image1']); ?>" 
                         alt="<?php echo htmlspecialchars($item['name']); ?>"
                         onerror="this.src='images/placeholder.jpg'">
                    <div class="item-details">
                        <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                        <p class="price">&#x20B9;<?php echo number_format($item['price'], 2); ?></p>
                        
                        <div class="item-actions">
                            <a href="?moveToCart=<?php echo $item['wishlist_id']; ?>" 
                               class="cart-btn">
                                Move to Cart
                            </a>
                            <button class="delete-btn" 
                                    onclick="showDeletePopup(<?php echo $item['wishlist_id']; ?>)">
                                Remove Item
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
let deleteItemId = null;

function showDeletePopup(wishlistId) {
    deleteItemId = wishlistId;
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
</script>
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