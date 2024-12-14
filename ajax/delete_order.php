<?php
require_once '../classes/orderClass.php';

header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['order_id'])) {
        throw new Exception('Missing order ID');
    }
    
    $orderObj = new Order();
    $success = $orderObj->deleteOrder($data['order_id']);
    
    if ($success) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Failed to delete order');
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 