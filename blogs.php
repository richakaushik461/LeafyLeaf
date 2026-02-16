<?php
// Database Connection
$conn = new mysqli("localhost", "root", "", "leafylife");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all blogs
$blog_sql = "SELECT * FROM blogs ORDER BY created_at DESC";
$blog_result = $conn->query($blog_sql);
?>

<?php
include 'db_connect.php';
include 'counter_functions.php';
?>
<?php
session_start();
$userIconLink = isset($_SESSION['user_id']) ? 'profile.php' : 'loginreg.php';
$counts = getCartWishlistCounts($conn, $_SESSION['user_id'] ?? null);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog | LeafyLife</title>
	<link rel="icon" type="image/x-icon" href="images/favicon.svg">
    <link rel="stylesheet" href="blog.css">
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
<div class="blog-container">
    <?php while ($blog = $blog_result->fetch_assoc()): ?>
        <div class="blog-card">
            <img src="<?php echo 'http://localhost/'.htmlspecialchars($blog['image']); ?>" alt="<?php echo $blog['title']; ?>">
            <h2><?php echo $blog['title']; ?></h2>
            <p class="author">By <?php echo $blog['author']; ?></p>
            <a href="blog.php?id=<?php echo $blog['id']; ?>" class="read-more">Read More</a>
        </div>
    <?php endwhile; ?>
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
              <ul><li><a href="index.php">Home</a></li>
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
</body>
</html>

<?php $conn->close(); ?>
