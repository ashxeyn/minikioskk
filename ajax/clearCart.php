<?php
session_start();
require_once '../classes/orderClass.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

try {
    $orderObj = new Order();
    $user_id = $_SESSION['user_id'];
    
    $result = $orderObj->clearCart($user_id);
    
    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Failed to clear cart');
    }
} catch (Exception $e) {
    error_log('Cart Clear Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Error occurred: ' . $e->getMessage()
    ]);
} 