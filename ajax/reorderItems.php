<?php
session_start();
require_once '../classes/orderClass.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to reorder items']);
    exit;
}

try {
    $orderId = $_POST['order_id'] ?? null;
    if (!$orderId) {
        throw new Exception('Invalid order ID');
    }

    $orderObj = new Order();
    
    // Get the original order details
    $originalOrder = $orderObj->getOrderById($orderId, null); // Pass null for canteen_id to bypass restriction
    $orderItems = $orderObj->getOrderItems($orderId);
    
    if (empty($orderItems)) {
        throw new Exception('No items found in the original order');
    }

    // Create new order with same details
    $orderData = [
        'user_id' => $_SESSION['user_id'],
        'total_amount' => $originalOrder['total_amount'],
        'status' => 'placed',
        'payment_status' => 'unpaid',
        'canteen_id' => $originalOrder['canteen_id']
    ];

    // Place the new order
    $newOrderId = $orderObj->createOrder($orderData);
    
    if (!$newOrderId) {
        throw new Exception('Failed to create new order');
    }

    // Add order items
    foreach ($orderItems as $item) {
        $itemData = [
            'order_id' => $newOrderId,
            'product_id' => $item['product_id'],
            'quantity' => $item['quantity'],
            'unit_price' => $item['unit_price']
        ];
        
        if (!$orderObj->addOrderItem($itemData)) {
            throw new Exception('Failed to add order items');
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Order placed successfully! Order ID: ' . $newOrderId,
        'order_id' => $newOrderId
    ]);

} catch (Exception $e) {
    error_log("Error in reorderItems.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 