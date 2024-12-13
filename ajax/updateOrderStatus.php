<?php
session_start();
require_once '../classes/orderClass.php';

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['canteen_id'])) {
        throw new Exception('Unauthorized access');
    }

    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['order_id']) || !isset($data['status'])) {
        throw new Exception('Missing required parameters');
    }

    $orderObj = new Order();
    $result = $orderObj->updateOrderStatus($data['order_id'], $data['status']);

    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Failed to update order status');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 