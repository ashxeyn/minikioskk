<?php
session_start();
require_once '../classes/productClass.php';

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'manager') {
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

if (!isset($_GET['product_id'])) {
    echo json_encode(['error' => 'Product ID is required']);
    exit;
}

try {
    $productObj = new Product();
    $productData = $productObj->getProduct($_GET['product_id']);
    
    if ($productData) {
        echo json_encode($productData);
    } else {
        echo json_encode(['error' => 'Product not found']);
    }
} catch (Exception $e) {
    error_log("Error in getProduct.php: " . $e->getMessage());
    echo json_encode(['error' => 'Error fetching product: ' . $e->getMessage()]);
}
?> 