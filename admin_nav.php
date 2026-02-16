<nav class="admin-nav">
    <div class="admin-brand">
    <h2 class="nav_logo"><a href="index.php"><img src="images/logo.png" id="logo"></a></h2>
    </div>
    
    <div class="nav-links">
        <a href="admin_panel.php" <?php echo basename($_SERVER['PHP_SELF']) == 'admin_panel.php' ? 'class="active"' : ''; ?>>
            <i class="material-icons">dashboard</i> Dashboard
        </a>
        
        <div class="nav-category">Products</div>
        <a href="products_mgmt.php" <?php echo basename($_SERVER['PHP_SELF']) == 'products_mgmt.php' ? 'class="active"' : ''; ?>>
            <i class="material-icons">shopping_bag</i> Manage Products
        </a>
      
        
        <div class="nav-category">Orders</div>
        <a href="orders.php" <?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'class="active"' : ''; ?>>
            <i class="material-icons">shopping_cart</i> Manage Orders
        </a>
        
        <div class="nav-category">Content</div>
        <a href="blogs_mgmt.php" <?php echo basename($_SERVER['PHP_SELF']) == 'blogs_mgmt.php' ? 'class="active"' : ''; ?>>
            <i class="material-icons">article</i> Blog Posts
        </a>
        <a href="inquiries.php" <?php echo basename($_SERVER['PHP_SELF']) == 'inquiries.php' ? 'class="active"' : ''; ?>>
            <i class="material-icons">contact_mail</i> Inquiries
        </a>
        
        <div class="nav-category">Users</div>
        <a href="users.php" <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'class="active"' : ''; ?>>
            <i class="material-icons">people</i> Manage Users
        </a>
        
        <div class="nav-category">System</div>

        <a href="logout.php" <?php echo basename($_SERVER['PHP_SELF']) == 'logout.php' ? 'class="active"' : ''; ?>>
            <i class="material-icons">logout</i> Logout
        </a>
    </div>
</nav>

