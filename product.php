<?php
include 'db_connect.php';
include 'counter_functions.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $query = "SELECT * FROM products WHERE id = $id";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        echo "<script>
            alert('Product not found');
            window.location.href = 'shop.php';
        </script>";
        exit();
    }
} else {
    echo "<script>
        alert('Invalid product ID');
        window.location.href = 'shop.php';
    </script>";
    exit();
}

session_start();
$userIconLink = isset($_SESSION['user_id']) ? 'profile.php' : 'loginreg.php';

$counts = getCartWishlistCounts($conn, $_SESSION['user_id'] ?? null);

// Handle POST requests for cart and wishlist
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $action = $_POST['action'] ?? '';
    $message = '';
    
    if ($product_id > 0) {
        if ($action === 'cart') {
            $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
            
            // Check if quantity is valid
            if ($quantity > $product['qty']) {
                $message = "Error: Only " . $product['qty'] . " items available in stock";
                header("Location: product.php?id=$id&error=" . urlencode($message));
                exit();
            }
            
            $stmt = $conn->prepare("SELECT cart_id FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param("ii", $user_id, $product_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
                $stmt->bind_param("ii", $user_id, $product_id);
                $success = $stmt->execute();
                $message = $success ? "Product removed from cart" : "Error removing from cart";
            } else {
                $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
                $stmt->bind_param("iii", $user_id, $product_id, $quantity);
                $success = $stmt->execute();
                $message = $success ? "Product added to cart" : "Error adding to cart";
            }
        } elseif ($action === 'wishlist') {
            $stmt = $conn->prepare("SELECT wishlist_id FROM wishlist WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param("ii", $user_id, $product_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
                $stmt->bind_param("ii", $user_id, $product_id);
                $success = $stmt->execute();
                $message = $success ? "Product removed from wishlist" : "Error removing from wishlist";
            } else {
                $stmt = $conn->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
                $stmt->bind_param("ii", $user_id, $product_id);
                $success = $stmt->execute();
                $message = $success ? "Product added to wishlist" : "Error adding to wishlist";
            }
        }
    }
    
    // Refresh the page with the message
    header("Location: product.php?id=$id&message=" . urlencode($message));
    exit();
}

// Check if product is in cart/wishlist for the current user
$inCart = false;
$inWishlist = false;

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    // Check cart
    $cartStmt = $conn->prepare("SELECT cart_id FROM cart WHERE user_id = ? AND product_id = ?");
    $cartStmt->bind_param("ii", $user_id, $id);
    $cartStmt->execute();
    $inCart = $cartStmt->get_result()->num_rows > 0;
    
    // Check wishlist
    $wishlistStmt = $conn->prepare("SELECT wishlist_id FROM wishlist WHERE user_id = ? AND product_id = ?");
    $wishlistStmt->bind_param("ii", $user_id, $id);
    $wishlistStmt->execute();
    $inWishlist = $wishlistStmt->get_result()->num_rows > 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> | LeafyLife</title>
    <link rel="icon" type="image/x-icon" href="images/favicon.svg">
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Google+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        .notification-popup {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 5px;
            color: white;
            font-weight: 500;
            z-index: 1000;
            animation: slideIn 0.5s ease-out;
        }

        .notification-success {
            background-color: #22863a;
        }

        .notification-error {
            background-color: #cb2431;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    </style>
</head>
<body>
<main>
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
    
    <?php if (isset($_GET['message']) || isset($_GET['error'])): ?>
    <div class="notification-popup <?php echo isset($_GET['error']) ? 'notification-error' : 'notification-success'; ?>">
        <?php echo htmlspecialchars(isset($_GET['error']) ? $_GET['error'] : $_GET['message']); ?>
    </div>
    <script>
        setTimeout(() => {
            const notification = document.querySelector('.notification-popup');
            if (notification) {
                notification.remove();
            }
        }, 3000);
    </script>
    <?php endif; ?>

    <div class="spacer"></div>
    <div class="product-container">
        <div class="image-section">
            <div class="thumbnails">
                <img src="http://localhost/<?php echo htmlspecialchars($product['image1']); ?>" class="thumbnail active" data-index="0" alt="Product Thumbnail 1">
                <img src="http://localhost/<?php echo htmlspecialchars($product['image2']); ?>" class="thumbnail" data-index="1" alt="Product Thumbnail 2">
                <img src="http://localhost/<?php echo htmlspecialchars($product['image3']); ?>" class="thumbnail" data-index="2" alt="Product Thumbnail 3">
                <img src="http://localhost/<?php echo htmlspecialchars($product['image4']); ?>" class="thumbnail" data-index="3" alt="Product Thumbnail 4">
            </div>
            <div class="main-image-container">
                <button class="nav-arrow prev-arrow" onclick="prevImage()">←</button>
                <img id="mainImage" src="http://localhost/<?php echo htmlspecialchars($product['image1']); ?>" alt="Product Image">
                <button class="nav-arrow next-arrow" onclick="nextImage()">→</button>
            </div>
        </div>
        
        <div class="details-section">
            <a href="shop.php" class="btn">←Back to Shop</a>
            <h1><?php echo htmlspecialchars($product['name']); ?></h1>
            <p><?php echo htmlspecialchars($product['description']); ?></p>
            
            <div class="price-section">
                <span class="current-price">₹<?php echo number_format($product['price'], 2); ?></span>
            </div>

            <div class="cart-section">
                <div class="quantity-selector">
                    <button type="button" class="quantity-btn" onclick="updateQuantity(-1)">-</button>
                    <input type="number" class="quantity-input" value="1" min="1" max="<?php echo (int)$product['qty']; ?>" id="quantity">
                    <button type="button" class="quantity-btn" onclick="updateQuantity(1)">+</button>
                </div>
                <div class="cart-buttons">
                    <form method="POST" id="cartForm" onsubmit="return validateQuantityAndAuth(event)">
                        <input type="hidden" name="product_id" value="<?php echo $id; ?>">
                        <input type="hidden" name="action" value="cart">
                        <input type="hidden" name="quantity" id="form-quantity" value="1">
                        <button type="submit" class="add-to-cart <?php echo $inCart ? 'in-cart' : ''; ?>" style="width:400px;">
                            <span class="material-icons cart-icon">shopping_bag</span>
                            <?php echo $inCart ? 'Remove from cart' : 'Add to cart'; ?>
                        </button>
                    </form>
                    <form method="POST" style="display: inline;" onsubmit="return validateAuth(event)">
                        <input type="hidden" name="product_id" value="<?php echo $id; ?>">
                        <input type="hidden" name="action" value="wishlist">
                        <button type="submit" class="wishlist-btn">
                            <span class="material-icons heart-icon"><?php echo $inWishlist ? 'favorite' : 'favorite_border'; ?></span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Store all images in an array
        const images = Array.from(document.querySelectorAll('.thumbnail')).map(img => img.src);
        let currentImageIndex = 0;

        // Add click event listeners to all thumbnails
        document.querySelectorAll('.thumbnail').forEach(thumbnail => {
            thumbnail.addEventListener('click', function() {
                currentImageIndex = parseInt(this.dataset.index);
                updateImage();
            });
        });

        function updateImage() {
            document.getElementById('mainImage').src = images[currentImageIndex];
            document.querySelectorAll('.thumbnail').forEach((thumb, index) => {
                if (index === currentImageIndex) {
                    thumb.classList.add('active');
                } else {
                    thumb.classList.remove('active');
                }
            });
        }

        function nextImage() {
            currentImageIndex = (currentImageIndex + 1) % images.length;
            updateImage();
        }

        function prevImage() {
            currentImageIndex = (currentImageIndex - 1 + images.length) % images.length;
            updateImage();
        }

        function showError(message) {
            const notification = document.createElement('div');
            notification.className = 'notification-popup notification-error';
            notification.textContent = message;
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 3000);
        }

        function updateQuantity(change) {
            const input = document.getElementById('quantity');
            const formInput = document.getElementById('form-quantity');
            const currentValue = parseInt(input.value) || 1;
            const newValue = currentValue + change;
            const max = parseInt(input.max);
            const min = parseInt(input.min);
            
            if (newValue >= min && newValue <= max) {
                input.value = newValue;
                formInput.value = newValue;
            } else if (newValue > max) {
                showError(`Sorry, only ${max} items are available in stock.`);
            }
        }

        // Update form quantity when input changes directly
        document.getElementById('quantity').addEventListener('change', function() {
            const max = parseInt(this.max);
            const value = parseInt(this.value) || 1;
            
            if (value > max) {
                this.value = max;
                showError(`Sorry, only ${max} items are available in stock.`);
            } else if (value < 1) {
                this.value = 1;
            }
            
            document.getElementById('form-quantity').value = this.value;
        });

        function validateQuantityAndAuth(event) {
            if (!isUserLoggedIn()) {
                event.preventDefault();
                showAuthPopup();
                return false;
            }
            
            const quantityInput = document.getElementById('quantity');
            const max = parseInt(quantityInput.max);
            const value = parseInt(quantityInput.value);
            
            if (value > max) {
                showError(`Sorry, only ${max} items are available in stock.`);
                return false;
            }
            return true;
        }

        function validateAuth(event) {
            if (!isUserLoggedIn()) {
                event.preventDefault();
                showAuthPopup();
                return false;
            }
            return true;
        }

// Function to check if user is logged in
function isUserLoggedIn() {
    return document.querySelector('a[href="profile.php"]') !== null;
}

// Function to show popup
function showAuthPopup() {
    const existingPopup = document.querySelector('.auth-popup');
    if (existingPopup) return;

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

    // Trigger reflow and add active class for animation
    setTimeout(() => popup.classList.add('active'), 10);

    // Close popup when clicking outside
    popup.addEventListener('click', (e) => {
        if (e.target === popup) {
            closePopup();
        }
    });

    // Close on escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closePopup();
    });
}

// Function to close popup
function closePopup() {
    const popup = document.querySelector('.auth-popup');
    if (popup) {
        popup.classList.remove('active');
        setTimeout(() => popup.remove(), 300); // Wait for animation
    }
}

// Add event listeners when document is loaded
document.addEventListener('DOMContentLoaded', () => {
    // Cart and wishlist buttons
    const actionButtons = document.querySelectorAll('.btn, .wishlist');
    actionButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            if (!isUserLoggedIn()) {
                e.preventDefault();
                showAuthPopup();
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

    <div class="reviews-section">
        <h2>Customer Reviews</h2>
        
        <div class="review-form">
            <h3>Write a Review</h3>
            <form action="submit_review.php" method="POST" onsubmit="return validateAuth(event)">
                <input type="hidden" name="product_id" value="<?php echo $id; ?>">
                <div class="rating-input">
                    <label>Your Rating:</label>
                    <div class="stars">
                        <input type="radio" id="star5" name="rating" value="5" required>
                        <label for="star5">★</label>
                        <input type="radio" id="star4" name="rating" value="4">
                        <label for="star4">★</label>
                        <input type="radio" id="star3" name="rating" value="3">
                        <label for="star3">★</label>
                        <input type="radio" id="star2" name="rating" value="2">
                        <label for="star2">★</label>
                        <input type="radio" id="star1" name="rating" value="1">
                        <label for="star1">★</label>
                    </div>
                </div>
                <div class="review-input">
                    <label for="review">Your Review:</label>
                    <textarea id="review" name="review_text" required></textarea>
                </div>
                <button type="submit" class="submit-review">Submit Review</button>
            </form>
        </div>

        <div class="reviews-container">
            <?php
 $review_query = "SELECT r.*, u.name as user_name,
                            EXISTS (
                                SELECT 1 FROM orders o 
                                JOIN order_items oi ON o.id = oi.order_id 
                                WHERE o.user_id = r.user_id 
                                AND oi.product_id = r.product_id
                                AND o.status = 'delivered'
                            ) as is_verified
                            FROM reviews r 
                            JOIN users u ON r.user_id = u.user_id 
                            WHERE r.product_id = ? 
                            ORDER BY r.created_at DESC";
            
            $stmt = $conn->prepare($review_query);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $reviews = $stmt->get_result();

            if($reviews->num_rows > 0):
                while($review = $reviews->fetch_assoc()):
            ?>
                <div class="review-card">
                    <div class="review-header">
                        <div class="review-author-section">
                            <span class="review-author">By <?php echo htmlspecialchars($review['user_name']); ?></span>
                            <?php if($review['is_verified']): ?>
                                <span class="verified-badge">Verified Purchase</span>
                            <?php endif; ?>
                        </div>
                        <div class="stars-display">
                            <?php
                            for($i = 1; $i <= 5; $i++) {
                                echo ($i <= $review['rating']) ? '★' : '☆';
                            }
                            ?>
                        </div>
                        <span class="review-date"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></span>
                    </div>
                    <div class="review-content">
                        <?php echo htmlspecialchars($review['review_text']); ?>
                    </div>
                </div>
            <?php 
                endwhile;
            else:
            ?>
                <p class="no-reviews">No reviews yet. Be the first to review this product!</p>
            <?php endif; ?>
        </div>

        <style>
                    
            .verified-badge {
                background-color: #000000;
                color: white;
                padding: 2px 8px;
                border-radius: 12px;
                font-size: 12px;
                display: inline-flex;
                align-items: center;
                gap: 4px;
            }
            
                   </style>

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
                    <li><a href="about.php">About Us</a></li>
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