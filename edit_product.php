<?php
session_start();
require_once 'db_connect.php';

// Check admin authentication
if (!(isset($_SESSION['user_id']) && isset($_SESSION['isadmin']) && $_SESSION['isadmin'] == 1)) {
    header('Location: loginreg.php');
    exit();
}

$product_id = isset($_GET['id']) ? $_GET['id'] : 0;

// Get all categories and sizes for dropdowns
$categories_query = "SELECT DISTINCT category FROM products WHERE qty > 0 ORDER BY category";
$sizes_query = "SELECT DISTINCT size FROM products ORDER BY size"; // Query for sizes
$categoryResult = $conn->query($categories_query);
$sizeResult = $conn->query($sizes_query); // Fetch sizes

// Get product details
$product_query = "SELECT * FROM products WHERE id = ?";
$stmt = $conn->prepare($product_query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: products_mgmt.php');
    exit();
}

$product = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock_quantity = $_POST['qty'];
    $category_id = $_POST['category'];
    $size = $_POST['size']; // Handle size
    
    // Get the image paths from the form
    $image_paths = [
        'image1' => $_POST['image1'],
        'image2' => $_POST['image2'],
        'image3' => $_POST['image3'],
        'image4' => $_POST['image4']
    ];

    // Update product query
    $update_query = "UPDATE products SET 
    name = ?, 
    description = ?,  
    price = ?, 
    qty = ?, 
    category = ?, 
    size = ?, 
    image1 = ?, 
    image2 = ?, 
    image3 = ?, 
    image4 = ?, 
    LDesc = ? 
    WHERE id = ?";

    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ssdisssssssi", $product_name, $description, $price, $stock_quantity, $category_id, $size, 
        $image_paths['image1'], $image_paths['image2'], $image_paths['image3'], $image_paths['image4'], $_POST['LDesc'], $product_id);

    if ($stmt->execute()) {
        $success_message = "Product updated successfully!";
        // Refresh product data
        $stmt = $conn->prepare($product_query);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
    } else {
        $error_message = "Error updating product: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product | Leafy Life Admin</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
	<link rel="icon" type="image/x-icon" href="images/favicon.svg">
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <div class="admin-container">
         <?php include 'admin_nav.php'; ?>
        
        <main class="admin-main">
            <div class="admin-header">
                <h1>Edit Product</h1>
                <p>Update product information</p>
            </div>
            
            <?php if (isset($success_message)): ?>
                <div class="alert success">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert danger">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <section class="admin-section">
                <form method="post" action="">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="product_name">Product Name</label>
                            <input type="text" id="product_name" name="name" class="form-control" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="price">Price (₹)</label>
                            <input type="number" id="price" name="price" class="form-control" step="0.01" min="0" value="<?php echo $product['price']; ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="category_id">Category</label>
                            <select id="category_id" name="category" class="form-control">
                                <option value="">Select Category</option>
                                <?php 
                                $categoryResult->data_seek(0);
                                while($category = $categoryResult->fetch_assoc()): 
                                ?>
                                    <option value="<?php echo $category['category']; ?>" <?php echo ($product['category'] == $category['category']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['category']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="size">Size</label>
                            <select id="size" name="size" class="form-control">
                                <option value="">Select Size</option>
                                <?php 
                                $sizeResult->data_seek(0);
                                while($size = $sizeResult->fetch_assoc()): 
                                ?>
                                    <option value="<?php echo $size['size']; ?>" <?php echo ($product['size'] == $size['size']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($size['size']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="stock_quantity">Stock Quantity</label>
                            <input type="number" id="stock_quantity" name="qty" class="form-control" min="0" value="<?php echo $product['qty']; ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="5" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="long_description">Long Description</label>
                        <textarea id="long_description" name="LDesc" class="form-control" rows="6"><?php echo htmlspecialchars($product['LDesc']); ?></textarea>
                    </div>

                    <!-- Image Path Inputs -->
                    <div class="form-group">
                        <label for="image1">Image 1 Path</label>
                        <input type="text" name="image1" id="image1" class="form-control" value="<?php echo htmlspecialchars($product['image1']); ?>" required>
                        <button type="button" onclick="previewImage('image1', 'preview1')">Preview</button>
                        <div class="image-preview">
                            <img id="preview1" src="http://localhost/<?php echo htmlspecialchars($product['image1']); ?>" alt="Image 1" class="product-thumb-small">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="image2">Image 2 Path</label>
                        <input type="text" name="image2" id="image2" class="form-control" value="<?php echo htmlspecialchars($product['image2']); ?>" required>
                        <button type="button" onclick="previewImage('image2', 'preview2')">Preview</button>
                        <div class="image-preview">
                            <img id="preview2" src="http://localhost/<?php echo htmlspecialchars($product['image2']); ?>" alt="Image 2" class="product-thumb-small">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="image3">Image 3 Path</label>
                        <input type="text" name="image3" id="image3" class="form-control" value="<?php echo htmlspecialchars($product['image3']); ?>" required>
                        <button type="button" onclick="previewImage('image3', 'preview3')">Preview</button>
                        <div class="image-preview">
                            <img id="preview3" src="http://localhost/<?php echo htmlspecialchars($product['image3']); ?>" alt="Image 3" class="product-thumb-small">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="image4">Image 4 Path</label>
                        <input type="text" name="image4" id="image4" class="form-control" value="<?php echo htmlspecialchars($product['image4']); ?>" required>
                        <button type="button" onclick="previewImage('image4', 'preview4')">Preview</button>
                        <div class="image-preview">
                            <img id="preview4" src="http://localhost/<?php echo htmlspecialchars($product['image4']); ?>" alt="Image 4" class="product-thumb-small">
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Update Product</button>
                        <a href="products_mgmt.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </section>
        </main>
    </div>
    <script>
        function previewImage(inputId, imgId) {
            const input = document.getElementById(inputId);
            const img = document.getElementById(imgId);

            if (input.value.trim() !== "") {
                img.src = "http://localhost/" + input.value.trim();
            } else {
                img.src = "";
            }
        }
    </script>
</body>
</html>
