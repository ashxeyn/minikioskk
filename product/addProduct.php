<?php
require_once '../classes/productClass.php';
require_once '../classes/stocksClass.php';
require_once '../tools/functions.php';

header('Content-Type: application/json');

try {
    // Debug incoming data
    error_log("Received POST data: " . print_r($_POST, true));

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
        'description' => clean_input($_POST['description']),
        'price' => clean_input($_POST['price'])
    ];

    $initial_stock = clean_input($_POST['initial_stock']);

    // Validate numeric fields
    if (!is_numeric($product_data['price']) || $product_data['price'] <= 0) {
        throw new Exception("Invalid price value");
    }

    if (!is_numeric($initial_stock) || $initial_stock < 0) {
        throw new Exception("Invalid stock value");
    }

    // Verify type_id exists
    $db = new Database();
    $conn = $db->connect();
    $stmt = $conn->prepare("SELECT type_id FROM product_types WHERE type_id = ?");
    $stmt->execute([$product_data['type_id']]);
    if (!$stmt->fetch()) {
        throw new Exception("Invalid product type selected");
    }

    // Start transaction
    $conn->beginTransaction();

    try {
        $productObj = new Product();
        // Add product
        $product_id = $productObj->addProduct($product_data);
        error_log("Product added with ID: " . $product_id);

        if (!$product_id) {
            throw new Exception("Failed to add product");
        }

        // Add initial stock
        $stockObj = new Stocks();
        $stock_data = [
            'product_id' => $product_id,
            'quantity' => $initial_stock
        ];

        $stock_result = $stockObj->addStock($stock_data);
        error_log("Stock addition result: " . ($stock_result ? "success" : "failure"));

        if (!$stock_result) {
            throw new Exception("Failed to add initial stock");
        }

        $conn->commit();
        echo json_encode([
            'status' => 'success',
            'message' => 'Product added successfully',
            'product_id' => $product_id
        ]);

    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    error_log("Error in addProduct.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
