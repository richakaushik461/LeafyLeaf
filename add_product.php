<?php
session_start();
require_once 'db_connect.php';

// Check admin authentication
if (!(isset($_SESSION['user_id']) && isset($_SESSION['isadmin']) && $_SESSION['isadmin'] == 1)) {
    header('Location: loginreg.php');
    exit();
}

// Get categories
$categories_query = "SELECT DISTINCT category FROM products ORDER BY category";
$categoryResult = $conn->query($categories_query);

// Get sizes
$sizes_query = "SELECT DISTINCT TRIM(size) AS size FROM products ORDER BY size";
$sizeResult = $conn->query($sizes_query);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['product_name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $size = trim($_POST['size']);
    $qty = intval($_POST['stock_quantity']);
    $category = trim($_POST['category_id']);
    $image1 = trim($_POST['image1']);
    $image2 = trim($_POST['image2']);
    $image3 = trim($_POST['image3']);
    $image4 = trim($_POST['image4']);
    $LDesc = trim($_POST['LDesc']);

    if (!empty($name) && !empty($description) && !empty($size) && !empty($category) && !empty($LDesc) && $qty >= 0 && $price >= 0) {
        $insert_query = "INSERT INTO products 
        (name, description, price, size, qty, category, image1, image2, image3, image4, LDesc)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ssdisssssss", $name, $description, $price, $size, $qty, $category, $image1, $image2, $image3, $image4, $LDesc);

        if ($stmt->execute()) {
            header("Location: products_mgmt.php?success=added");
            exit();
        } else {
            $error_message = "Error adding product: " . $conn->error;
        }
    } else {
        $error_message = "Please fill all required fields correctly.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Product | Leafy Life Admin</title>
    <link rel="stylesheet" href="admin.css">
	<link rel="icon" type="image/x-icon" href="images/favicon.svg">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        .preview-image {
            margin-top: 10px;
            max-width: 200px;
            display: none;
        }
        .image-input-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
    </style>
</head>
<body>
<div class="admin-container">
    <?php include 'admin_nav.php'; ?>

    <main class="admin-main">
        <div class="admin-header">
            <h1><i class="material-icons">add_shopping_cart</i> Add New Product</h1>
            <p>Create a new product for your store</p>
        </div>

        <?php if (isset($error_message)): ?>
            <div class="alert danger">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <section class="admin-section">
            <form method="post" action="">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="product_name">Product Name</label>
                        <input type="text" id="product_name" name="product_name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="price">Price (₹)</label>
                        <input type="number" id="price" name="price" class="form-control" step="0.01" min="0" required oninput="this.value = this.value.replace(/[^0-9]/g, '');"   onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="size">Size</label>
                        <select id="size" name="size" class="form-control" required>
                            <?php while ($size = $sizeResult->fetch_assoc()): ?>
                                <option value="<?= htmlspecialchars($size['size']) ?>"><?= htmlspecialchars($size['size']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="stock_quantity">Stock Quantity</label>
                        <input type="number" id="stock_quantity" name="stock_quantity" class="form-control" min="0" required 
                                oninput="this.value = this.value.replace(/[^0-9]/g, '');"   onkeypress="return event.charCode >= 48 && event.charCode <= 57">

                    </div>
                </div>

                <div class="form-group">
                    <label for="category_id">Category</label>
                    <select id="category_id" name="category_id" class="form-control" required>
                        <?php while ($category = $categoryResult->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($category['category']) ?>"><?= htmlspecialchars($category['category']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="description">Short Description</label>
                    <textarea id="description" name="description" class="form-control" rows="3" required></textarea>
                </div>

                <div class="form-group">
                    <label for="LDesc">Long Description</label>
                    <textarea id="LDesc" name="LDesc" class="form-control" rows="5" required></textarea>
                </div>

                <!-- IMAGE INPUTS WITH PREVIEW BUTTON -->
                <?php for ($i = 1; $i <= 4; $i++): ?>
                    <div class="form-group">
                        <label for="image<?= $i ?>">Image <?= $i ?> Path</label>
                        <div class="image-input-group">
                            <input type="text" id="image<?= $i ?>" name="image<?= $i ?>" placeholder="leafylife/images/example.jpg" class="form-control" required>
                            <button type="button" onclick="previewImage('image<?= $i ?>', 'preview_image<?= $i ?>')" class="btn btn-secondary">Preview</button>
                        </div>
                        <img id="preview_image<?= $i ?>" class="preview-image" alt="Image <?= $i ?> Preview">
                    </div>
                <?php endfor; ?>


                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Add Product</button>
                    <a href="products_mgmt.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </section>
    </main>
</div>

<script>


        function previewImage(inputId,imgId) {
            const input = document.getElementById(inputId);
            const img = document.getElementById(imgId);

            if (input.value.trim() !== "") {
                img.src = "http://localhost/" + input.value.trim();
                img.style.display = "block";
            } else {
                img.src = "";
                img.style.display = "none";
            }
        }
    </script>

</body>
</html>
