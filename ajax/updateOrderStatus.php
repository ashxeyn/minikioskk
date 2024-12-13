<?php
session_start();
require_once '../classes/orderClass.php';

header('Content-Type: application/json');

try {
    // Check if user is logged in and is a manager
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'manager') {
        throw new Exception('Unauthorized access');
    }

    // Get the POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['order_id']) || !isset($data['status'])) {
        throw new Exception('Missing required parameters');
    }

    $orderId = $data['order_id'];
    $newStatus = $data['status'];
    $canteenId = $_SESSION['canteen_id'] ?? null;

    if (!$canteenId) {
        throw new Exception('Canteen ID not found in session');
    }

    $orderObj = new Order();
    
    // Update the order status
    $orderObj->updateOrderStatus($orderId, $newStatus, $canteenId);
    
    $queueNumber = null;
    // Generate queue number if the order is being accepted
    if ($newStatus === 'accepted') {
        try {
            $queueNumber = $orderObj->generateQueueNumber($orderId);
        } catch (Exception $e) {
            error_log("Queue number generation failed: " . $e->getMessage());
            // Continue execution even if queue number generation fails
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Order status updated successfully',
        'queue_number' => $queueNumber
    ]);

} catch (Exception $e) {
    error_log("Error updating order status: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 