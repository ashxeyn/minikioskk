<?php
session_start();
require_once '../classes/orderClass.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_POST['order_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

try {
    $orderObj = new Order();
    $orderId = $_POST['order_id'];
    
    // Verify the order belongs to the user - now only passing orderId
    $order = $orderObj->getOrderById($orderId);
    
    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }

    if ($order['user_id'] != $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'message' => 'Access denied']);
        exit;
    }
    
    // Check if order can be cancelled
    if ($order['status'] !== 'placed') {
        echo json_encode(['success' => false, 'message' => 'Only pending orders can be cancelled']);
        exit;
    }
    
    // Cancel the order
    $success = $orderObj->cancelOrder($orderId);
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Order cancelled successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Unable to cancel order at this time']);
    }
} catch (Exception $e) {
    error_log("Error cancelling order: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'System error: ' . $e->getMessage()
    ]);
} 