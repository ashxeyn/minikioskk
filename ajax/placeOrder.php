<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../error.log');

session_start();
require_once '../classes/orderClass.php';
require_once '../classes/stocksClass.php';

ob_start();

$response = ['success' => false, 'message' => ''];

try {
    // Log session data
    error_log("Session data: " . print_r($_SESSION, true));
    
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not logged in');
    }

    $orderObj = new Order();
    $stocksObj = new Stocks();
    $user_id = $_SESSION['user_id'];

    error_log("Starting order placement for user: " . $user_id);

    // Get cart items
    $cartItems = $orderObj->getCartItems($user_id);
    error_log("Retrieved cart items: " . print_r($cartItems, true));
    
    if (empty($cartItems)) {
        throw new Exception('Cart is empty');
    }

    // Log stock check
    foreach ($cartItems as $item) {
        $stock = $stocksObj->fetchStockByProductId($item['product_id']);
        error_log("Stock check for product {$item['product_id']}: " . print_r($stock, true));
        
        if (!$stock || $stock['quantity'] < $item['quantity']) {
            throw new Exception("Insufficient stock for {$item['name']}");
        }
    }

    // Place the order
    error_log("Attempting to place order...");
    $orderResult = $orderObj->placeOrder($user_id, $cartItems);
    error_log("Order result: " . print_r($orderResult, true));
    
    if (!isset($orderResult['success']) || !$orderResult['success']) {
        throw new Exception("Failed to place order");
    }
    
    // Update stock quantities
    error_log("Updating stock quantities...");
    foreach ($cartItems as $item) {
        if (!$stocksObj->updateStock($item['product_id'], -$item['quantity'])) {
            throw new Exception("Failed to update stock for {$item['name']}");
        }
    }
    
    // Clear the cart
    error_log("Clearing cart...");
    if (!$orderObj->clearCart($user_id)) {
        throw new Exception("Failed to clear cart");
    }
    
    $_SESSION['last_order'] = [
        'order_id' => $orderResult['order_id'],
        'queue_number' => $orderResult['queue_number'],
        'canteen_name' => $orderResult['canteen_name']
    ];
    error_log("Order completed successfully. Session updated with order info.");
    
    $response['success'] = true;
    $response['message'] = 'Order placed successfully';
    $response['redirect'] = 'orderStatus.php';
    
} catch (Exception $e) {
    error_log("Order placement error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

while (ob_get_level()) {
    ob_end_clean();
}

header('Content-Type: application/json');
echo json_encode($response);
exit; 