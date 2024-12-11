<?php
session_start();
require_once '../classes/orderClass.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

if (!isset($_POST['product_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request: Missing product_id']);
    exit;
}

try {
    $orderObj = new Order();
    $user_id = $_SESSION['user_id'];
    $product_id = (int)$_POST['product_id'];
    
    error_log("Attempting to remove product $product_id for user $user_id");
    
    $result = $orderObj->removeFromCart($user_id, $product_id);
    
    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Failed to remove item from cart');
    }
} catch (Exception $e) {
    error_log('Cart Remove Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Error occurred: ' . $e->getMessage()
    ]);
} 