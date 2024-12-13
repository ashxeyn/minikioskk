<?php
require_once '../classes/orderClass.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'canteen_staff') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $orderId = $_POST['order_id'] ?? null;
    $action = $_POST['action'] ?? null;
    $canteenId = $_SESSION['canteen_id'] ?? null;

    if (!$orderId || !$action || !$canteenId) {
        throw new Exception('Missing required parameters');
    }

    $orderObj = new Order();
    
    // Map actions to status
    $statusMap = [
        'accept' => 'accepted',
        'prepare' => 'preparing',
        'ready' => 'ready',
        'complete' => 'completed',
        'cancel' => 'cancelled'
    ];

    if (!isset($statusMap[$action])) {
        throw new Exception('Invalid action');
    }

    $newStatus = $statusMap[$action];
    
    // Update the order status
    $result = $orderObj->updateOrderStatus($orderId, $newStatus, $canteenId);
    
    if ($result) {
        // Generate queue number if order is accepted
        if ($action === 'accept') {
            $queueNumber = $orderObj->generateQueueNumber($orderId);
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Order status updated successfully',
            'newStatus' => $newStatus,
            'queueNumber' => $queueNumber ?? null
        ]);
    } else {
        throw new Exception('Failed to update order status');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 