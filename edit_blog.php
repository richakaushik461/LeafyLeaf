<?php
session_start();
require_once 'db_connect.php';

// Check admin authentication
if (!(isset($_SESSION['user_id']) && isset($_SESSION['isadmin']) && $_SESSION['isadmin'] == 1)) {
    header('Location: loginreg.php');
    exit();
}

$post_id = isset($_GET['id']) ? $_GET['id'] : 0;

// Get blog post details
$post_query = "SELECT * FROM blogs WHERE id = ?";
$stmt = $conn->prepare($post_query);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: blogs_mgmt.php');
    exit();
}

$post = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $image_path = $_POST['image_path'];

    $update_query = "UPDATE blogs SET title = ?, content = ?, image = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("sssi", $title, $content, $image_path, $post_id);

    if ($stmt->execute()) {
        $success_message = "Blog post updated successfully!";
        // Refresh blog data
        $stmt = $conn->prepare($post_query);
        $stmt->bind_param("i", $post_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $post = $result->fetch_assoc();
    } else {
        $error_message = "Error updating blog post: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Blog Post | Leafy Life Admin</title>
    <link rel="stylesheet" href="admin.css">
	<link rel="icon" type="image/x-icon" href="images/favicon.svg">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        .centered-preview {
            text-align: center;
            margin: 20px 0;
        }

        .image-preview {
            max-width: 300px;
            max-height: 300px;
            display: block;
            margin: 0 auto;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        .form-group input[type="text"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
        }

        .form-group .btn-secondary {
            padding: 8px 12px;
            font-size: 14px;
        }

        .form-actions {
            margin-top: 20px;
        }
    </style>
    <script>
        function previewImage(inputId, imgId) {
            const input = document.getElementById(inputId);
            const img = document.getElementById(imgId);

            if (input.value.trim() !== "") {
                img.src = "http://localhost/" + input.value.trim();
                img.style.display = 'block';
            } else {
                img.src = "";
                img.style.display = 'none';
            }
        }
    </script>
</head>
<body>
    <div class="admin-container">
        <?php include 'admin_nav.php'; ?>

        <main class="admin-main">
            <div class="admin-header">
                <h1><i class="material-icons">edit</i> Edit Blog Post</h1>
                <p>Update blog post content</p>
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
                    <div class="form-group">
                        <label for="title">Blog Title</label>
                        <input type="text" id="title" name="title" class="form-control" value="<?php echo htmlspecialchars($post['title']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="content">Blog Content</label>
                        <textarea id="content" name="content" class="form-control" rows="10" required><?php echo htmlspecialchars($post['content']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="image_path">Image Path (relative to localhost)</label>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <input type="text" id="image_path" name="image_path" class="form-control" placeholder="images/example.jpg" value="<?php echo htmlspecialchars($post['image']); ?>" required>
                            <button type="button" onclick="previewImage('image_path', 'imagePreview')" class="btn btn-secondary">Preview</button>
                        </div>
                    </div>

                    <div class="centered-preview">
                        <p>Image Preview:</p>
                        <img id="imagePreview" class="image-preview" src="<?php echo !empty($post['image']) ? 'http://localhost/' . htmlspecialchars($post['image']) : ''; ?>" style="<?php echo empty($post['image']) ? 'display:none;' : ''; ?>">
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Update Post</button>
                        <a href="blogs_mgmt.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </section>
        </main>
    </div>
</body>
</html>
