<?php
require_once '../classes/adminProductClass.php';

header('Content-Type: application/json');

try {
    if (!isset($_GET['product_id'])) {
        throw new Exception("Product ID is required");
    }
    
    $productId = $_GET['product_id'];
    $adminProduct = new AdminProduct();
    $details = $adminProduct->getProduct($productId);
    
    echo json_encode($details);
} catch (Exception $e) {
    error_log("Error in getProduct.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?> 