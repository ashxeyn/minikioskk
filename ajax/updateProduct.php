<?php
require_once '../classes/adminProductClass.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    if (!isset($_POST['product_id'])) {
        throw new Exception('Product ID is required');
    }

    $adminProduct = new AdminProduct();
    $success = $adminProduct->updateProduct($_POST);

    echo json_encode([
        'success' => true,
        'message' => 'Product updated successfully'
    ]);
} catch (Exception $e) {
    error_log("Error in updateProduct.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 