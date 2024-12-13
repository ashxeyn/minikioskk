<?php
require_once '../classes/productClass.php';
require_once '../classes/stocksClass.php';
require_once '../tools/functions.php';

header('Content-Type: application/json');

try {
    // Validate input
    if (empty($_POST['name']) || empty($_POST['type_id']) || 
        empty($_POST['price']) || empty($_POST['canteen_id']) || 
        empty($_POST['initial_stock'])) {
        throw new Exception("All required fields must be filled");
    }

    // Clean and prepare data
    $product_data = [
        'canteen_id' => clean_input($_POST['canteen_id']),
        'type_id' => clean_input($_POST['type_id']),
        'name' => clean_input($_POST['name']),
        'description' => clean_input($_POST['description'] ?? ''),
        'price' => clean_input($_POST['price']),
        'status' => 'available'
    ];

    $initial_stock = clean_input($_POST['initial_stock']);

    // Validate numeric fields
    if (!is_numeric($product_data['price']) || $product_data['price'] <= 0) {
        throw new Exception("Invalid price value");
    }

    if (!is_numeric($initial_stock) || $initial_stock < 0) {
        throw new Exception("Invalid stock value");
    }

    // Begin transaction
    $productObj = new Product();
    $stocksObj = new Stocks();
    
    $db = $productObj->getConnection();
    $db->beginTransaction();

    try {
        // Add the product
        $product_id = $productObj->addProduct($product_data);
        
        if (!$product_id) {
            throw new Exception("Failed to add product");
        }

        // Add initial stock
        if (!$stocksObj->addStock($product_id, $initial_stock)) {
            throw new Exception("Failed to add initial stock");
        }

        $db->commit();
        echo json_encode(['status' => 'success', 'message' => 'Product added successfully']);
        
    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
