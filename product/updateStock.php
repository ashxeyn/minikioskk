<?php
require_once '../classes/stocksClass.php';
require_once '../tools/functions.php';

header('Content-Type: application/json');

try {
    // Debug logging
    error_log("Raw POST data: " . file_get_contents('php://input'));
    error_log("POST array: " . print_r($_POST, true));
    
    if (!isset($_POST['product_id']) || !isset($_POST['quantity'])) {
        error_log("Missing parameters. POST data: " . print_r($_POST, true));
        throw new Exception('Missing required parameters');
    }
    
    // Validate product_id
    $product_id = filter_var($_POST['product_id'], FILTER_VALIDATE_INT);
    if ($product_id === false || $product_id <= 0) {
        error_log("Invalid product ID received: " . $_POST['product_id']);
        throw new Exception('Invalid product ID format');
    }
    
    // Validate quantity
    $quantity = filter_var($_POST['quantity'], FILTER_VALIDATE_INT);
    if ($quantity === false || $quantity <= 0) {
        error_log("Invalid quantity received: " . $_POST['quantity']);
        throw new Exception('Invalid quantity. Must be a positive number.');
    }
    
    $stockObj = new Stocks();
    
    // Verify product exists
    if (!$stockObj->productExists($product_id)) {
        error_log("Product not found: " . $product_id);
        throw new Exception('Product not found');
    }
    
    // Update stock
    if ($stockObj->updateStock($product_id, $quantity)) {
        echo json_encode([
            'success' => true,
            'message' => 'Stock updated successfully'
        ]);
    } else {
        throw new Exception('Failed to update stock');
    }
    
} catch (Exception $e) {
    error_log("Error in updateStock.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
