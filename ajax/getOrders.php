<?php
session_start();
require_once '../classes/orderClass.php';

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'manager') {
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

try {
    $orderObj = new Order();
    $canteenId = $_SESSION['canteen_id'] ?? null;
    
    if (!$canteenId) {
        throw new Exception("Canteen ID not found in session");
    }
    
    $orders = $orderObj->fetchOrders($canteenId);
    
    // Handle DataTables search
    if (isset($_POST['search']['value'])) {
        $searchTerm = $_POST['search']['value'];
        $orders = array_filter($orders, function($order) use ($searchTerm) {
            return stripos($order['order_id'], $searchTerm) !== false ||
                   stripos($order['username'], $searchTerm) !== false ||
                   stripos($order['customer_name'], $searchTerm) !== false ||
                   stripos($order['product_names'], $searchTerm) !== false ||
                   stripos($order['status'], $searchTerm) !== false;
        });
    }
    
    // Format data for DataTables
    $data = [];
    foreach ($orders as $order) {
        $actions = '';
        foreach ($orderObj->getAvailableActions($order['status']) as $action) {
            $actions .= sprintf(
                '<button onclick="handleOrderAction(%d, \'%s\')" class="btn %s btn-sm">%s</button> ',
                $order['order_id'],
                $action['action'],
                $action['class'],
                $action['label']
            );
        }
        
        $data[] = [
            "DT_RowId" => "order_" . $order['order_id'],
            "order_id" => $order['order_id'],
            "username" => htmlspecialchars($order['username']),
            "customer_name" => htmlspecialchars($order['customer_name']),
            "product_names" => htmlspecialchars($order['product_names']),
            "total_quantity" => htmlspecialchars($order['total_quantity']),
            "total_price" => '₱' . number_format($order['total_price'], 2),
            "status" => sprintf(
                '<span class="badge badge-%s">%s</span>',
                $order['status'],
                ucfirst($order['status'])
            ),
            "queue_number" => $order['queue_number'] ? sprintf(
                '<span class="queue-number">%s</span>',
                htmlspecialchars($order['queue_number'])
            ) : '',
            "actions" => $actions
        ];
    }
    
    // Return the response in DataTables format
    echo json_encode([
        "draw" => isset($_POST['draw']) ? intval($_POST['draw']) : 0,
        "recordsTotal" => count($orders),
        "recordsFiltered" => count($data),
        "data" => $data
    ]);
    
} catch (Exception $e) {
    error_log("Error fetching orders: " . $e->getMessage());
    echo json_encode(['error' => 'Error fetching orders']);
} 