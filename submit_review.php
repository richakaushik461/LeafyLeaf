<?php
session_start();
include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'You must be logged in to submit reviews';
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit();
}

// Validate input
if (!isset($_POST['product_id']) || !isset($_POST['rating']) || !isset($_POST['review_text'])) {
    $_SESSION['error'] = 'Missing required fields';
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit();
}

$product_id = intval($_POST['product_id']);
$user_id = $_SESSION['user_id'];
$rating = intval($_POST['rating']);
$review_text = trim($_POST['review_text']);

// Validate rating
if ($rating < 1 || $rating > 5) {
    $_SESSION['error'] = 'Invalid rating';
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit();
}


// Insert the review
$insert_query = "INSERT INTO reviews (product_id, user_id, rating, review_text) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($insert_query);
$stmt->bind_param("iiis", $product_id, $user_id, $rating, $review_text);

if ($stmt->execute()) {
    $_SESSION['success'] = 'Review submitted successfully!';
} else {
    $_SESSION['error'] = 'Failed to submit review';
}

header('Location: ' . $_SERVER['HTTP_REFERER']);
exit();
