<?php
require_once '../classes/productClass.php';
require_once '../tools/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $product_id = clean_input($_POST['product_id']);
        
        if (empty($product_id)) {
            throw new Exception('Product ID is required');
        }

        $productObj = new Product();
        $result = $productObj->deleteProductWithRelations($product_id);

        if ($result) {
            echo json_encode(['status' => 'success']);
        } else {
            throw new Exception('Failed to delete product');
        }
    } catch (Exception $e) {
        error_log("Error in deleteProduct.php: " . $e->getMessage());
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
}
?>
