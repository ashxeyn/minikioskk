<?php
session_start();
require_once '../classes/cartClass.php';
require_once '../classes/stocksClass.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

if (!isset($_POST['product_id']) || !isset($_POST['new_quantity'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

try {
    $cartObj = new Cart();
    $stocksObj = new Stocks();
    
    $product_id = $_POST['product_id'];
    $new_quantity = (int)$_POST['new_quantity'];
    
    // Check stock availability
    $stock = $stocksObj->fetchStockByProductId($product_id);
    if (!$stock || $stock['quantity'] < $new_quantity) {
        echo json_encode(['success' => false, 'message' => 'Insufficient stock available']);
        exit;
    }
    
    // Update the quantitiez
    $cartObj->updateCartQuantity($_SESSION['user_id'], $product_id, $new_quantity);
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 