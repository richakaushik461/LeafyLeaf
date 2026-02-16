<?php
function getCartWishlistCounts($conn, $user_id = null) {
    $counts = array(
        'cart' => 0,
        'wishlist' => 0
    );
    
    if ($user_id) {
        // Get cart count
        $cartQuery = "SELECT COUNT(DISTINCT product_id) as count FROM cart WHERE user_id = ?";
        $stmt = $conn->prepare($cartQuery);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $counts['cart'] = $row['count'];
        }
        
        // Get wishlist count
        $wishlistQuery = "SELECT COUNT(DISTINCT product_id) as count FROM wishlist WHERE user_id = ?";
        $stmt = $conn->prepare($wishlistQuery);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $counts['wishlist'] = $row['count'];
        }
    }
    
    return $counts;
}

function renderNavbarCounters($counts) {
    $html = '<style>
        .nav_link1 {
            position: relative;
            display: inline-block;
        }
        
        .counter {
            position: absolute;
            top: -8px;
            right: -8px;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .wishlist-counter {
            background-color: #000000;
        }
        
        .cart-counter {
            background-color: #000000;
        }
    </style>';
    
    $html .= '<li>
        <a href="wishlist.php" class="nav_link1">
            <img src="images/heart.svg" alt="wishlist" id="svg">';
    if ($counts['wishlist'] > 0) {
        $html .= '<span class="counter wishlist-counter">' . $counts['wishlist'] . '</span>';
    }
    $html .= '</a>
    </li>
    <li>
        <a href="cart.php" class="nav_link1">
            <img src="images/cart.svg" alt="cart" id="svg">';
    if ($counts['cart'] > 0) {
        $html .= '<span class="counter cart-counter">' . $counts['cart'] . '</span>';
    }
    $html .= '</a>
    </li>';
    
    return $html;
}
?>