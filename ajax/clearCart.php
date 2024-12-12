<?php
session_start();
require_once '../classes/orderClass.php';

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Please login to clear cart');
    }

    $user_id = $_SESSION['user_id'];
    $orderObj = new Order();
    
    if ($orderObj->clearCart($user_id)) {
        echo json_encode([
            'success' => true,
            'message' => 'Cart cleared successfully'
        ]);
    } else {
        throw new Exception('Failed to clear cart');
    }

} catch (Exception $e) {
    error_log("Error clearing cart: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 