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
    
    // Get original order details
    $originalOrder = $orderObj->getOrderById($orderId);
    if (!$originalOrder) {
        throw new Exception('Original order not found');
    }

    // Check if there's already an active reorder
    if ($orderObj->hasActiveReorder($_SESSION['user_id'], $orderId)) {
        echo json_encode([
            'success' => false,
            'message' => 'You already have an active order with these items'
        ]);
        exit;
    }

    // Get order items with product details
    $orderItems = $orderObj->getOrderItems($orderId);
    if (empty($orderItems)) {
        throw new Exception('No items found in original order');
    }

    // Start transaction for new order
    $db = $orderObj->db->connect();
    $db->beginTransaction();

    try {
        // Create new order
        $newOrderData = [
            'user_id' => $_SESSION['user_id'],
            'total_amount' => $originalOrder['total_amount'],
            'status' => 'placed',
            'payment_status' => 'unpaid',
            'canteen_id' => $originalOrder['canteen_id']
        ];

        // Create the new order
        $newOrderId = $orderObj->createOrder($newOrderData);
        
        // Add items to the new order
        foreach ($orderItems as $item) {
            // Check stock availability
            $stockSql = "SELECT quantity FROM stocks WHERE product_id = :product_id FOR UPDATE";
            $stockStmt = $db->prepare($stockSql);
            $stockStmt->execute(['product_id' => $item['product_id']]);
            $stock = $stockStmt->fetch(PDO::FETCH_ASSOC);

            if (!$stock || $stock['quantity'] < $item['quantity']) {
                throw new Exception("Insufficient stock for product: " . $item['name']);
            }

            // Add order item
            $itemData = [
                'order_id' => $newOrderId,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price']
            ];

            $orderObj->addOrderItem($itemData);

            // Update stock
            $updateStockSql = "UPDATE stocks 
                              SET quantity = quantity - :quantity 
                              WHERE product_id = :product_id";
            $updateStockStmt = $db->prepare($updateStockSql);
            $updateStockStmt->execute([
                'quantity' => $item['quantity'],
                'product_id' => $item['product_id']
            ]);
        }

        $db->commit();
        echo json_encode([
            'success' => true,
            'message' => 'Order successfully reordered',
            'order_id' => $newOrderId
        ]);

    } catch (Exception $e) {
        $db->rollBack();
        error_log("Error in reorderItems: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Failed to create new order: ' . $e->getMessage()
        ]);
    }

} catch (Exception $e) {
    error_log("Error in reorderItems: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to create new order: ' . $e->getMessage()
    ]);
} 