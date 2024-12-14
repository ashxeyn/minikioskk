<?php
session_start();
require_once '../classes/stocksClass.php';

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'manager') {
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

if (!isset($_GET['product_id'])) {
    echo json_encode(['error' => 'Product ID is required']);
    exit;
}

try {
    $stocks = new Stocks();
    $currentStock = $stocks->getCurrentStock($_GET['product_id']);
    
    echo json_encode([
        'success' => true,
        'current_stock' => $currentStock
    ]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error fetching stock: ' . $e->getMessage()]);
}
?> 