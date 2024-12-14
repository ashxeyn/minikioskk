<?php
require_once '../classes/productClass.php';
session_start();

header('Content-Type: application/json');

// Check if user is authorized
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'manager') {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

try {
    if (!isset($_POST['product_id'])) {
        throw new Exception('Product ID is required');
    }

    $productId = filter_var($_POST['product_id'], FILTER_VALIDATE_INT);
    if ($productId === false) {
        throw new Exception('Invalid product ID');
    }

    $productObj = new Product();
    
    // Check if the product belongs to the manager's canteen
    if (!$productObj->isProductOwnedByCanteen($productId, $_SESSION['canteen_id'])) {
        throw new Exception('Unauthorized to delete this product');
    }
    
    if ($productObj->deleteProduct($productId)) {
        echo json_encode([
            'success' => true,
            'message' => 'Product deleted successfully'
        ]);
    } else {
        throw new Exception('Failed to delete product');
    }
} catch (Exception $e) {
    error_log("Error in deleteProduct.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
