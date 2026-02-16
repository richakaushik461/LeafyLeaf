<?php
session_start();
require_once 'db_connect.php';

// Check admin authentication
if (!(isset($_SESSION['user_id']) && isset($_SESSION['isadmin']) && $_SESSION['isadmin'] == 1)) {
    header('Location: loginreg.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $image_path = $_POST['image_path'];
    $user_id = $_SESSION['user_id'];

    $insert_query = "INSERT INTO blogs (title, content,image,author,created_at) VALUES (?, ?, ?, ?,CURRENT_TIMESTAMP)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("ssis", $title, $content, $user_id, $image_path);

    if ($stmt->execute()) {
        header("Location: blogs.php?success=added");
        exit();
    } else {
        $error_message = "Error adding blog post: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Blog Post | Leafy Life Admin</title>
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
                <h1><i class="material-icons">post_add</i> Add Blog Post</h1>
                <p>Create new content for your blog</p>
            </div>

            <?php if (isset($error_message)): ?>
                <div class="alert danger">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <section class="admin-section">
                <form method="post" action="">
                    <div class="form-group">
                        <label for="title">Blog Title</label>
                        <input type="text" id="title" name="title" class="form-control" required>
                    </div>
				
                    <div class="form-group">
                        <label for="content">Blog Content</label>
                        <textarea id="content" name="content" class="form-control" rows="10" required></textarea>
                    </div>
		
                    <div class="form-group">
                        <label for="image_path">Image Path (relative to localhost)</label>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <input type="text" id="image_path" name="image_path" class="form-control" placeholder="images/example.jpg" required>
                            <button type="button" onclick="previewImage('image_path', 'imagePreview')" class="btn btn-secondary">Preview</button>
                        </div>
                    </div>
			
			<div class="form-group">
                        <label for="author">Blog Author</label>
                        <input type="text" id="title" name="title" class="form-control" required>
                    </div>

                    <div class="centered-preview">
                        <p>Image Preview:</p>
                        <img id="imagePreview" class="image-preview" style="display: none;">
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Publish Post</button>
                        <a href="blogs_mgmt.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </section>
        </main>
    </div>
</body>
</html>
