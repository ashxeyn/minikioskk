<?php
session_start();
require_once '../classes/productClass.php';
require_once '../classes/stocksClass.php';

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'manager') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

try {
    if (!isset($_POST['product_id'])) {
        throw new Exception('Product ID is required');
    }

    $productObj = new Product();
    $stocksObj = new Stocks();

    // Update product details
    $updateData = [
        'product_id' => $_POST['product_id'],
        'name' => $_POST['name'],
        'description' => $_POST['description'],
        'type_id' => $_POST['type_id'],
        'price' => $_POST['price'],
        'canteen_id' => $_SESSION['canteen_id']
    ];

    $success = $productObj->updateProduct($updateData);

    // Update stock if quantity is provided
    if (isset($_POST['quantity']) && $_POST['quantity'] > 0) {
        $stocksObj->addStock(
            $_POST['product_id'],
            $_POST['quantity'],
            $_SESSION['user_id']
        );
    }

    echo json_encode([
        'success' => $success,
        'message' => $success ? 'Product updated successfully' : 'Failed to update product'
    ]);

} catch (Exception $e) {
    error_log("Error in updateProduct.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error updating product: ' . $e->getMessage()
    ]);
}
?> 