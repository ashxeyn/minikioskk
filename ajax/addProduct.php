<?php
require_once '../classes/adminProductClass.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $adminProduct = new AdminProduct();
    $productId = $adminProduct->addProduct($_POST);

    echo json_encode([
        'success' => true,
        'product_id' => $productId,
        'message' => 'Product added successfully'
    ]);
} catch (Exception $e) {
    error_log("Error in addProduct.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 