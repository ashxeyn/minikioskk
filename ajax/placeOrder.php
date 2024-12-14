<?php
session_start();
require_once '../classes/orderClass.php';
require_once '../classes/cartClass.php';
require_once '../classes/stocksClass.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

try {
    $orderObj = new Order();
    $cartObj = new Cart();
    
    $user_id = $_SESSION['user_id'];
    $cartItems = $cartObj->getCartItems($user_id);
    $total = $cartObj->getCartTotal($user_id);
    
    if (empty($cartItems)) {
        echo json_encode(['success' => false, 'message' => 'Cart is empty']);
        exit;
    }

   
    $orderResult = $orderObj->placeOrder($user_id, $cartItems, $total);
    
    if (!$orderResult['success']) {
        throw new Exception($orderResult['message'] ?? "Failed to place order");
    }
   
    $cartObj->clearCart($user_id);
    
    
    $_SESSION['last_order'] = [
        'order_id' => $orderResult['order_id'],
        'total_amount' => $total,
        'status' => 'placed',
        'payment_status' => 'unpaid'
    ];
    
    echo json_encode([
        'success' => true,
        'order_id' => $orderResult['order_id']
    ]);
    
} catch (Exception $e) {
    error_log("Order placement error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 