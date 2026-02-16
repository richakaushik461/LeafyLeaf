<?php
session_start();
require_once 'db_connect.php';

// Check admin authentication
if (!(isset($_SESSION['user_id']) && isset($_SESSION['isadmin']) && $_SESSION['isadmin'] == 1)) {
    header('Location: loginreg.php');
    exit();
}

// Handle blog deletion
if (isset($_GET['delete'])) {
    $post_id = $_GET['delete'];
    
    // First, get image path to delete file
    $image_query = "SELECT b.image FROM blogs b WHERE id = ?";
    $stmt = $conn->prepare($image_query);
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $post = $result->fetch_assoc();
        if (!empty($post['image']) && file_exists($post['image'])) {
            unlink($post['image']);
        }
    }
    
    // Now delete the blog post
    $delete_query = "DELETE FROM blogs WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $post_id);
    
    if ($stmt->execute()) {
        $success_message = "Blog post deleted successfully!";
    } else {
        $error_message = "Error deleting blog post: " . $conn->error;
    }
}

// Get all blog posts
$posts_query = "SELECT bp.*
               FROM blogs bp 
               ORDER BY bp.created_at DESC";
$posts_result = $conn->query($posts_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Management | Leafy Life Admin</title>
    <link rel="stylesheet" href="admin.css">
	<link rel="icon" type="image/x-icon" href="images/favicon.svg">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
    <div class="admin-container">
        <?php include 'admin_nav.php'; ?>
        
        <main class="admin-main">
            <div class="admin-header">
                <h1><i class="material-icons">article</i> Blog Management</h1>
                <p>Create and manage your blog content</p>
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
                <div class="section-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h2>All Blog Posts</h2>
                    <a href="add_blog.php" class="btn btn-primary">Add New Post</a>
                </div>
                
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Blog ID</th>
                                <th>Image</th>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($posts_result->num_rows > 0): ?>
                                <?php while($post = $posts_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $post['id']; ?></td>
                                        <td>
                                            <?php if (!empty($post['image'])): ?>
                                                <img src="<?php echo 'http://localhost/'.htmlspecialchars($post['image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" class="blog-thumb-small">
                                            <?php else: ?>
                                                <span>No image</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($post['title']); ?></td>
                                        <td><?php echo htmlspecialchars($post['author']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($post['created_at'])); ?></td>
                                        <td>
                                            <div class="table-actions">
                                                <a href="edit_blog.php?id=<?php echo $post['id']; ?>" class="btn-edit">Edit</a>
                                                <a href="blogs_mgmt.php?delete=<?php echo $post['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this blog post?')">Delete</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="no-data">No blog posts found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
</body>
</html>

