<?php
session_start();
require_once '../classes/orderClass.php';

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception("Please log in to add items to cart");
    }

    if (!isset($_POST['product_id']) || !isset($_POST['quantity'])) {
        throw new Exception("Missing required parameters");
    }

    $orderObj = new Order();
    $result = $orderObj->addToCart(
        $_SESSION['user_id'],
        $_POST['product_id'],
        $_POST['quantity']
    );

    echo json_encode([
        'success' => true,
        'message' => 'Item added to cart successfully'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 