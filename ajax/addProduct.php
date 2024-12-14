<?php
session_start();
require_once '../classes/productClass.php';

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'manager') {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

// Validate required fields
$requiredFields = ['name', 'type_id', 'price', 'initial_stock', 'canteen_id'];
foreach ($requiredFields as $field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        echo json_encode([
            'success' => false,
            'message' => "Missing required field: $field"
        ]);
        exit;
    }
}

try {
    $product = new Product();
    $result = $product->addProduct($_POST);
    
    echo json_encode([
        'success' => true,
        'message' => 'Product added successfully',
        'product_id' => $result
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error adding product: ' . $e->getMessage()
    ]);
}
?> 