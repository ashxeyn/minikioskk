<?php
session_start();
require_once '../classes/stocksClass.php';

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'manager') {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

if (!isset($_POST['product_id']) || !isset($_POST['quantity'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields'
    ]);
    exit;
}

try {
    $stocks = new Stocks();
    $result = $stocks->updateStock(
        $_POST['product_id'],
        $_POST['quantity']
    );
    
    echo json_encode([
        'success' => true,
        'message' => 'Stock updated successfully'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error updating stock: ' . $e->getMessage()
    ]);
}
?> 