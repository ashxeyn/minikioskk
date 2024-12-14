<?php
session_start();
require_once '../classes/productClass.php';

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'manager') {
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

try {
    $productObj = new Product();
    

    $productId = $_POST['product_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $typeId = $_POST['type_id'];
    $price = $_POST['price'];
    
   
    $result = $productObj->updateProduct($productId, $name, $description, $typeId, $price);
    
    if (isset($_POST['quantity']) && !empty($_POST['quantity'])) {
        require_once '../classes/stocksClass.php';
        $stocksObj = new Stocks();
        $stocksObj->addStock($productId, $_POST['quantity']);
    }
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Product updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update product']);
    }
    
} catch (Exception $e) {
    error_log("Error in updateProduct.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error updating product: ' . $e->getMessage()]);
}
?> 