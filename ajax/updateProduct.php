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

    // Handle stock update
    if (isset($_POST['quantity']) && $_POST['quantity'] > 0) {
        error_log("Adding stock: " . $_POST['quantity'] . " for product: " . $_POST['product_id']);
        $stockResult = $stocksObj->addStock(
            $_POST['product_id'],
            $_POST['quantity']
        );
        if (!$stockResult) {
            throw new Exception('Failed to update stock');
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Product and stock updated successfully'
    ]);

} catch (Exception $e) {
    error_log("Error in updateProduct.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error updating product: ' . $e->getMessage()
    ]);
}
?> 