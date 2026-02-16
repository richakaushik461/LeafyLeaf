
<?php
include 'db_connect.php';
include 'counter_functions.php'; 
// Fetch new arrival products from database
$query = "SELECT * FROM products WHERE category = 'New arrivals' ORDER BY id DESC LIMIT 5";
$result = $conn->query($query);
?>
<?php
session_start();
$userIconLink = isset($_SESSION['user_id']) ? 'profile.php' : 'loginreg.php';
$counts = getCartWishlistCounts($conn, $_SESSION['user_id'] ?? null);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Home | LeafyLife </title>
	<link rel="icon" type="image/x-icon" href="images/favicon.svg">
    <link rel="stylesheet" href="style.css" />
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
      <!--Header End -->
     

      <!-- Hero Start -->
        <section class="carousel next">
            <div class="list">
                <article class="item other_1">
                    <div class="main-content" 
                    style="background-color: #ecf39e;">
                        <div class="content">
                            <h2>Bird of Paradise</h2>
                            <p class="price">&#8377; 399.99</p>
                            <p class="description">
                              With its broad vibrant green leaves, the Bird of Paradise brings a touch of the tropics to any room. It's named after its unique flowers, which resemble brightly colored birds in flight.
                            </p>
                            <button class="addToCard" style = "cursor: pointer;" onclick = "window.location.href='product.php?id=1';">
				Discover now
                            </button>
                        </div>
                    </div>
                    <figure class="image">

                        <img src="images/1.png" alt="">
                        <figcaption>Bird of Paradise</figcaption>
                    </figure>
                </article>
                <article class="item active">
                    <div class="main-content" 
                    style="background-color: #90a955;">
                        <div class="content">
                            <h2>African Milk Tree</h2>
                            <p class="price">&#8377; 399.99</p>
                            <p class="description">
                             Meet the African Milk Tree (Euphorbia trigona 'Rubra')! This dramatic, low-maintenance succulent has vibrant reddish-purple stems and leaves that are impossible to ignore.</p>
                            <button class="addToCard" style = "cursor: pointer;" onclick = "window.location.href='product.php?id=7';">
                                Discover now
                            </button>
                        </div>
                    </div>
                    <figure class="image">

                        <img src="images/2.png" alt="">
                        <figcaption>African Milk Tree</figcaption>
                    </figure>
                </article>
                <article class="item other_2">
                    <div class="main-content" 
                    style="background-color: #4f772d;">
                        <div class="content">
                            <h2>ZZ Plant</h2>
                            <p class="price">&#8377; 499.99</p>
                            <p class="description">
                              The ZZ Plant is characterized by its waxy green leaves above the surface of its potting mix, and its large potato-like rhizomes underneath.ZZ a hardy, drought-tolerant houseplant that only needs water every few weeks.</p>
                            <button class="addToCard" style = "cursor: pointer;" onclick = "window.location.href='product.php?id=15';">
                                Discover now
                            </button>
                        </div>
                    </div>
                    <figure class="image">
                        <img src="images/3.png" alt="">
                        <figcaption>ZZ Plant</figcaption>
                    </figure>
                </article>
                <article class="item">
                    <div class="main-content" 
                    style="background-color: #31572c;">
                        <div class="content">
                            <h2>Olive Tree</h2>
                            <p class="price">&#8377; 599.99</p>
                            <p class="description">
                              With their small, silvery, gray-green leaves, olive trees (this specific variety is the Common Olive Tree) make beautiful houseplants. These Mediterranean plants need a lot of bright, direct sunlight. South and west facing windows are ideal.</p>
                            <button class="addToCard" style = "cursor: pointer;" onclick = "window.location.href='product.php?id=5';">
                                Discover now
                            </button>
                        </div>
                    </div>
                    <figure class="image">

                        <img src="images/4.png" alt="">
                        <figcaption>Olive Tree</figcaption>
                    </figure>
                </article>
            </div>
            <div class="arrows">
                <button id="prev"><</button>
                <button id="next">></button>
            </div>
<div class="spacer"></div>
        </section>
        <!-- Why Shop at LeafyLife Start -->
<section class="why-shop">
  <div class="container">
      <h2 class="section-title">Why Shop at LeafyLife?</h2>
      <br>
      <div class="reasons">
        <div class="reason">
          <img src="images/plant.svg" alt="">
            <h3>Fresh and Healthy Plants</h3>
        </div>
        <div class="reason">
          <img src="images/earth.svg" alt="">
            <h3>Fast and Sustainable Delivery</h3>
        </div>
        <div class="reason">
          <img src="images/scientist.svg" alt="">
            <h3>Expert Care Tips</h3>
        </div>
    </div>
  </div>
</section>
<div id="plant-slider">
        <div id="plant-slide-1" class="plant-slide">
            <img src="images/imagesec1.webp" alt="Snake Plant">
            <div id="plant-slide-content-1">
                <h2>Summer Collection</h2>
                <p>Starting at ₹350</p>
                <a href="shop.php?category=Summer+Collection";" id="plant-cta-1">Shop Now →</a>
            </div>
        </div>
        
        <div id="plant-slide-2" class="plant-slide">
            <img src="images/imagesec2.webp" alt="Plant Care">
            <div id="plant-slide-content-2">
                <h2>What's New?</h2>
                <p>Plant care</p>
                <a href="blogs.php" id="plant-cta-2">Discover Now →</a>
            </div>
        </div>
        
        <div id="plant-slide-3" class="plant-slide">
            <img src="images/imagesec3.webp" alt="Monstera">
            <div id="plant-slide-content-3">
                <h2>Buy 1 Get 1</h2>
                <p>Starting at ₹450</p>
                <a href="shop.php?category=Buy+1+Get+1" id="plant-cta-3">Discover Now →</a>
            </div>
        </div>
    </div>

 <div class="new-arrivals-container">
        <div class="new-arrivals-header">
            <h1>New Arrivals</h1>
            <a href="shop.php" class="shop-all">Shop All Products →</a>
        </div>
        
        <div class="products-grid">
            <?php while($product = $result->fetch_assoc()): ?>
                <a href="product.php?id=<?php echo $product['id']; ?>" class="product-card">
                    <?php if(isset($product['LDesc']) && strpos(strtolower($product['LDesc']), 'sale') !== false): ?>
                        <span class="discount-badge">-20%</span>
                    <?php endif; ?>
                    <div class="product-image">
                        <img src="/leafylife/images/<?php echo htmlspecialchars(basename($product['image1'])); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    </div>
                    <div class="product-info">
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        <div class="price-info">
                            <?php if(isset($product['LDesc']) && strpos(strtolower($product['LDesc']), 'sale') !== false): ?>
                                
                            <?php endif; ?>
                            <span class="current-price">₹<?php echo $product['price']; ?></span>
                        </div>
                    </div>
                </a>
            <?php endwhile; ?>
        </div>
    </div>

    <section id="more-to-discover">
        <div class="section-title">
            <span class="subtitle">Explore</span>
            <h1>More to Discover</h1>
        </div>
        
        <div class="discover-grid">
            <div class="discover-card">
                <img src="images/blog-1.jpg" alt="Store front with plants">
                <h2>Our Story</h2>
                <p>Learn about our journey and what drives us forward</p>
                <a href="about.php" class="discover-link">About Us →</a>
            </div>

            <div class="discover-card">
                <img src="images/blog-2.jpg" alt="Laptop and workspace">
                <h2>From Our Blog</h2>
                <p>Get inspired with our latest articles and updates</p>
                <a href="blogs.php" class="discover-link">Read more →</a>
            </div>

            <div class="discover-card">
                <img src="images/blog-3.jpg" alt="People in meeting">
                <h2>Get in Touch</h2>
                <p>We’d love to hear from you — reach out with questions, feedback, or just to say hello!</p>
                <a href="contact.php" class="discover-link">Contact Us →</a>
            </div>
        </div>
    </section>
        <script src="app.js"></script>
    </main>
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
