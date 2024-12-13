<?php
session_start();
require_once '../classes/cartClass.php';

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
    $cartObj = new Cart();
    $user_id = $_SESSION['user_id'];
    $product_id = (int)$_POST['product_id'];
    
    $result = $cartObj->removeFromCart($user_id, $product_id);
    
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