<?php
session_start();
require_once '../classes/cartClass.php';

header('Content-Type: application/json');

// Log the request
error_log("Add to cart request: " . print_r($_POST, true));
error_log("Session user_id: " . ($_SESSION['user_id'] ?? 'not set'));

if (!isset($_SESSION['user_id'])) {
    error_log("User not logged in - cannot add to cart");
    echo json_encode(['success' => false, 'message' => 'Please login to add items to cart']);
    exit;
}

try {
    $cart = new Cart();
    $user_id = $_SESSION['user_id'];
    $product_id = $_POST['product_id'] ?? null;
    $quantity = intval($_POST['quantity'] ?? 1);

    error_log("Processing add to cart: user_id=$user_id, product_id=$product_id, quantity=$quantity");

    if (!$product_id || $quantity < 1) {
        throw new Exception("Invalid product or quantity");
    }

    $result = $cart->addToCart($user_id, $product_id, $quantity);
    
    if ($result) {
        error_log("Successfully added item to cart");
        echo json_encode(['success' => true, 'message' => 'Item added to cart successfully']);
    } else {
        error_log("Failed to add item to cart");
        echo json_encode(['success' => false, 'message' => 'Failed to add item to cart']);
    }

} catch (Exception $e) {
    error_log("Error in addToCart.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 