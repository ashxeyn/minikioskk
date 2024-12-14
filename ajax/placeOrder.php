<?php
session_start();
require_once '../classes/orderClass.php';
require_once '../classes/cartClass.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to place an order']);
    exit;
}

try {
    $cart = new Cart();
    $order = new Order();
    $user_id = $_SESSION['user_id'];
    
    // Get cart items
    $cartItems = $cart->getCartItems($user_id);
    if (empty($cartItems)) {
        throw new Exception("Your cart is empty");
    }
    
    // Get cart total
    $total = $cart->getCartTotal($user_id);
    
    // Place the order
    $result = $order->placeOrder($user_id, $cartItems, $total);
    
    if ($result['success']) {
        // Clear the cart after successful order
        $cart->clearCart($user_id);
        echo json_encode([
            'success' => true, 
            'message' => 'Order placed successfully',
            'order_id' => $result['order_id']
        ]);
    } else {
        throw new Exception($result['message'] ?? 'Failed to place order');
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 