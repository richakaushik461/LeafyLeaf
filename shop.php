<?php
include 'db_connect.php';
include 'counter_functions.php';
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
            // First check if the item exists in cart
            $check_stmt = $conn->prepare("SELECT cart_id FROM cart WHERE user_id = ? AND product_id = ?");
            if (!$check_stmt) {
                die("Prepare failed: " . $conn->error);
            }
            $check_stmt->bind_param("ii", $user_id, $product_id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            $check_stmt->close();

            if ($result->num_rows > 0) {
                // Remove from cart
                $delete_stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
                if (!$delete_stmt) {
                    die("Prepare failed: " . $conn->error);
                }
                $delete_stmt->bind_param("ii", $user_id, $product_id);
                $success = $delete_stmt->execute();
                $delete_stmt->close();
                $message = $success ? "Product removed from cart" : "Error removing from cart";
            } else {
                // Add to cart
                $insert_stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
                if (!$insert_stmt) {
                    die("Prepare failed: " . $conn->error);
                }
                $insert_stmt->bind_param("ii", $user_id, $product_id);
                $success = $insert_stmt->execute();
                $insert_stmt->close();
                $message = $success ? "Product added to cart" : "Error adding to cart: " . $conn->error;
            }
        } elseif ($action === 'wishlist') {
            // First check if the item exists in wishlist
            $check_stmt = $conn->prepare("SELECT wishlist_id FROM wishlist WHERE user_id = ? AND product_id = ?");
            if (!$check_stmt) {
                die("Prepare failed: " . $conn->error);
            }
            $check_stmt->bind_param("ii", $user_id, $product_id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            $check_stmt->close();

            if ($result->num_rows > 0) {
                // Remove from wishlist
                $delete_stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
                if (!$delete_stmt) {
                    die("Prepare failed: " . $conn->error);
                }
                $delete_stmt->bind_param("ii", $user_id, $product_id);
                $success = $delete_stmt->execute();
                $delete_stmt->close();
                $message = $success ? "Product removed from wishlist" : "Error removing from wishlist";
            } else {
                // Add to wishlist
                $insert_stmt = $conn->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
                if (!$insert_stmt) {
                    die("Prepare failed: " . $conn->error);
                }
                $insert_stmt->bind_param("ii", $user_id, $product_id);
                $success = $insert_stmt->execute();
                $insert_stmt->close();
                $message = $success ? "Product added to wishlist" : "Error adding to wishlist: " . $conn->error;
            }
        }
        
        // Redirect back with message
        $redirectUrl = "shop.php";
        if (isset($_GET['category'])) {
            $redirectUrl .= "?category=" . urlencode($_GET['category']) . "&message=" . urlencode($message);
        } else {
            $redirectUrl .= "?message=" . urlencode($message);
        }
        header("Location: " . $redirectUrl);
        exit();
    }
}

// Get all unique categories
$categoryQuery = "SELECT DISTINCT category FROM products WHERE qty > 0 ORDER BY category";
$categoryResult = $conn->query($categoryQuery);

// Get selected category from URL parameter and escape it
$selectedCategory = isset($_GET['category']) ? $conn->real_escape_string($_GET['category']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop | LeafyLife</title>
    <link rel="icon" type="image/x-icon" href="images/favicon.svg">
    <link rel="stylesheet" href="style1.css">
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
            opacity: 1;
            transition: opacity 0.3s ease;
        }

        .notification-success {
            background-color: #22863a;
        }

        .notification-error {
            background-color: #cb2431;
        }

        .fade-out {
            opacity: 0;
        }
    </style>
<script src="ss.js"></script>
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
    </main>
    <div class="spacer"></div>

    <?php if (isset($_GET['message'])): ?>
    <div class="notification-popup notification-success">
        <?php echo htmlspecialchars($_GET['message']); ?>
    </div>
    <script>
        const notification = document.querySelector('.notification-popup');
        if (notification) {
            setTimeout(() => {
                notification.classList.add('fade-out');
                setTimeout(() => {
                    notification.style.display = 'none';
                }, 300);
            }, 3000);
        }
    </script>
    <?php endif; ?>

    <!-- Category Filter -->
    <div class="filter-container">
        <form method="GET" action="shop.php" class="filter-form">
            <select name="category" class="category-select" onchange="this.form.submit()">
                <option value="">All Categories</option>
                <?php
                while ($category = $categoryResult->fetch_assoc()) {
                    $selected = ($selectedCategory === $category['category']) ? 'selected' : '';
                    echo "<option value='" . htmlspecialchars($category['category']) . "' $selected>" . 
                         htmlspecialchars($category['category']) . "</option>";
                }
                ?>
            </select>
        </form>
    </div>

    <div class="shop-container">
        <?php
        // Base query with quantity > 0 condition and cart/wishlist status
        $query = "SELECT p.*, 
                  CASE WHEN c.cart_id IS NOT NULL THEN 1 ELSE 0 END as in_cart,
                  CASE WHEN w.wishlist_id IS NOT NULL THEN 1 ELSE 0 END as in_wishlist
                  FROM products p 
                  LEFT JOIN cart c ON p.id = c.product_id AND c.user_id = ?
                  LEFT JOIN wishlist w ON p.id = w.product_id AND w.user_id = ?
                  WHERE p.qty > 0";
        
        // Add category filter if selected
        if (!empty($selectedCategory)) {
            $query .= " AND p.category = ?";
        }
        
        // Prepare and execute query
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        if (!empty($selectedCategory)) {
            $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
            $stmt->bind_param("iis", $user_id, $user_id, $selectedCategory);
        } else {
            $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
            $stmt->bind_param("ii", $user_id, $user_id);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($product = $result->fetch_assoc()) {
                echo "<div class='product-card'>";
                echo "<a href='product.php?id=" . $product['id'] . "'>";
                echo "<img src='http://localhost/" . htmlspecialchars($product['image1']) . "' alt='" . htmlspecialchars($product['name']) . "' class='product-img'>";
                echo "</a>";

                echo "<div class='product-info'>";
                echo "<div class='product-header'>";
                echo "<h2>" . htmlspecialchars($product['name']) . "</h2>";
                echo "<p class='price'>₹" . number_format($product['price'], 2) . "</p>";
                echo "</div>";
                echo "<p class='product-description'>" . htmlspecialchars($product['description']) . "</p>";
                echo "<div class='product-buttons'>";
                
                // Always show the buttons, they will trigger the auth popup if not logged in
                echo "<form method='POST' style='display: inline; flex: 1;'>";
                echo "<input type='hidden' name='product_id' value='" . $product['id'] . "'>";
                echo "<input type='hidden' name='action' value='cart'>";
                echo "<button type='submit' class='btn " . ($product['in_cart'] ? 'in-cart' : '') . "'>" . 
                     ($product['in_cart'] ? 'Remove from Cart' : 'Add to Cart') . "</button>";
                echo "</form>";
                
                echo "<form method='POST' style='display: inline;'>";
                echo "<input type='hidden' name='product_id' value='" . $product['id'] . "'>";
                echo "<input type='hidden' name='action' value='wishlist'>";
                echo "<button type='submit' class='wishlist " . ($product['in_wishlist'] ? 'active' : '') . "'>" . 
                     ($product['in_wishlist'] ? '❤' : '♡') . "</button>";
                echo "</form>";
                
                echo "</div>";
                echo "</div>";
                echo "</div>";
            }
        } else {
            echo "<p class='no-products'>No products available in this category.</p>";
        }
        $stmt->close();
        ?>
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