<?php
require_once '../classes/stocksClass.php';

header('Content-Type: application/json');

try {
    if (!isset($_GET['product_id'])) {
        throw new Exception('Product ID is required');
    }

    // Validate product_id
    $product_id = filter_var($_GET['product_id'], FILTER_VALIDATE_INT);
    if ($product_id === false || $product_id <= 0) {
        error_log("Invalid product ID received: " . $_GET['product_id']);
        throw new Exception('Invalid product ID format');
    }

    $stocksObj = new Stocks();
    
    // Verify product exists first
    if (!$stocksObj->productExists($product_id)) {
        throw new Exception('Product not found');
    }
    
    $stockDetails = $stocksObj->fetchStockByProductId($product_id);
    
    echo json_encode([
        'success' => true,
        'quantity' => $stockDetails ? (int)$stockDetails['quantity'] : 0
    ]);

} catch (Exception $e) {
    error_log("Error in fetchStockByProductId: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
?>
