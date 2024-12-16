<?php
session_start();
require_once '../classes/productClass.php';

header('Content-Type: application/json');



if (!isset($_POST['product_id']) || empty($_POST['product_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Product ID is required'
    ]);
    exit;
}

try {
    $product = new Product();
    $result = $product->deleteProductWithRelations($_POST['product_id']);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Product deleted successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to delete product'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error deleting product: ' . $e->getMessage()
    ]);
}
?> 