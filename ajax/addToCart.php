<?php
session_start();
require_once '../classes/cartClass.php';

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception("Please login to add items to cart");
    }

    if (!isset($_POST['product_id'], $_POST['quantity'])) {
        throw new Exception("Invalid request parameters");
    }

    $cartObj = new Cart();
    $user_id = $_SESSION['user_id'];
    $product_id = $_POST['product_id'];
    $quantity = intval($_POST['quantity']);

    // Add to cart
    $result = $cartObj->addToCart($user_id, $product_id, $quantity);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Item added to cart successfully'
        ]);
    } else {
        throw new Exception("Failed to add item to cart");
    }

} catch (Exception $e) {
    error_log("Add to cart error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 