<?php
session_start();
require_once '../classes/orderClass.php';

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'manager') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if (!isset($_GET['order_id'])) {
    echo json_encode(['success' => false, 'message' => 'Order ID is required']);
    exit;
}

try {
    $orderObj = new Order();
    $orderId = $_GET['order_id'];
    $canteenId = $_SESSION['canteen_id'];
    
    // Fetch order details
    $order = $orderObj->getOrderById($orderId, $canteenId);
    
    // Fetch order products
    $products = $orderObj->getOrderProducts($orderId);
    
    echo json_encode([
        'success' => true,
        'order' => $order,
        'products' => $products
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 