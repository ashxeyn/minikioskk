<?php
require_once '../classes/adminProductClass.php';

header('Content-Type: application/json');

try {
    if (!isset($_POST['product_id']) || empty($_POST['product_id'])) {
        throw new Exception('Product ID is required');
    }

    $productId = (int)$_POST['product_id'];
    $adminProduct = new AdminProduct();
    
    if ($adminProduct->deleteProduct($productId)) {
        echo json_encode(['success' => true, 'message' => 'Product deleted successfully']);
    } else {
        throw new Exception('Failed to delete product');
    }
    
} catch (Exception $e) {
    error_log('Error in deleteProduct.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
?> 