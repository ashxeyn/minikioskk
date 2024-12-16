<?php
session_start();
require_once '../classes/stocksClass.php';

header('Content-Type: application/json');

try {
    if (!isset($_POST['product_id']) || !isset($_POST['quantity'])) {
        throw new Exception('Missing required parameters');
    }

    $stockObj = new Stocks();
    $result = $stockObj->addInitialStock(
        $_POST['product_id'],
        $_POST['quantity']
    );

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Initial stock added successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add initial stock']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 