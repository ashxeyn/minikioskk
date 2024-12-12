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
    $stocksObj = new Stocks();
    
    $user_id = $_SESSION['user_id'];
    $cartItems = $cartObj->getCartItems($user_id);
    $total = $cartObj->getCartTotal($user_id);
    
    if (empty($cartItems)) {
        echo json_encode(['success' => false, 'message' => 'Cart is empty']);
        exit;
    }

    // Place the order first
    $orderResult = $orderObj->placeOrder($user_id, $cartItems, $total);
    
    if (!$orderResult['success']) {
        throw new Exception($orderResult['message'] ?? "Failed to place order");
    }
    
    // Update stock quantities
    foreach ($cartItems as $item) {
        if (!$stocksObj->updateStock($item['product_id'], -$item['quantity'])) {
            throw new Exception("Failed to update stock for {$item['name']}");
        }
    }
    
    // Clear the cart after successful order
    $cartObj->clearCart($user_id);
    
    // Store order info in session
    $_SESSION['last_order'] = [
        'order_id' => $orderResult['order_id'],
        'total_amount' => $total,
        'status' => 'pending',
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