<?php
session_start();
require_once '../classes/orderClass.php';

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Please login to add items to cart');
    }

    $user_id = $_SESSION['user_id'];
    
    // Add debug logging
    error_log("Attempting to add to cart for user_id: " . $user_id);
    
    $product_id = $_POST['product_id'] ?? null;
    $quantity = $_POST['quantity'] ?? null;

    if (!$product_id || !$quantity) {
        throw new Exception('Missing required parameters');
    }

    $orderObj = new Order();
    
    // Use the updated addToCart method
    if ($orderObj->addToCart($user_id, $product_id, $quantity)) {
        echo json_encode([
            'success' => true, 
            'message' => 'Item added to cart successfully'
        ]);
    } else {
        throw new Exception('Failed to add item to cart');
    }

} catch (Exception $e) {
    error_log("Cart error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
} 