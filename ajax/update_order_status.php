<?php
require_once '../classes/orderClass.php';

header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['order_id']) || !isset($data['status'])) {
        throw new Exception('Missing required parameters');
    }
    
    $orderObj = new Order();
    $success = $orderObj->updateOrderStatus($data['order_id'], $data['status']);
    
    if ($success) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Failed to update order status');
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 