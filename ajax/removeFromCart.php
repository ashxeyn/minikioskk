<?php
session_start();
require_once '../classes/cartClass.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to remove items from cart']);
    exit;
}

try {
    $cart = new Cart();
    $user_id = $_SESSION['user_id'];
    $product_id = $_POST['product_id'] ?? null;

    if (!$product_id) {
        throw new Exception("Invalid product");
    }

    $result = $cart->removeFromCart($user_id, $product_id);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Item removed from cart successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to remove item from cart']);
    }

} catch (Exception $e) {
    error_log("Error in removeFromCart.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 