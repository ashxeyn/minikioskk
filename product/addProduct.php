<?php
session_start();
require_once '../classes/productClass.php';
require_once '../classes/stocksClass.php';

header('Content-Type: application/json');

try {
    // Validate session and role
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'manager') {
        throw new Exception('Unauthorized access');
    }

    // Validate required fields
    $requiredFields = ['name', 'type_id', 'price', 'canteen_id', 'initial_stock'];
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("Missing required field: {$field}");
        }
    }

    // Sanitize and validate input
    $productData = [
        'name' => htmlspecialchars(trim($_POST['name'])),
        'type_id' => (int)$_POST['type_id'],
        'description' => htmlspecialchars(trim($_POST['description'] ?? '')),
        'price' => (float)$_POST['price'],
        'canteen_id' => (int)$_POST['canteen_id'],
        'initial_stock' => (int)$_POST['initial_stock']
    ];

    // Validate numeric values
    if ($productData['price'] <= 0) {
        throw new Exception('Price must be greater than zero');
    }
    if ($productData['initial_stock'] < 0) {
        throw new Exception('Initial stock cannot be negative');
    }

    // Initialize classes
    $productObj = new Product();
    $stockObj = new Stocks();

    // Start transaction
    $db = new PDO("mysql:host=localhost;dbname=minikiosk1", "root", "");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->beginTransaction();

    try {
        // Add product
        $productId = $productObj->addProduct([
            'name' => $productData['name'],
            'type_id' => $productData['type_id'],
            'description' => $productData['description'],
            'price' => $productData['price'],
            'canteen_id' => $productData['canteen_id'],
            'status' => 'available'
        ]);

        // Add initial stock
        if ($productId && $productData['initial_stock'] > 0) {
            $stockObj->addStock($productId, $productData['initial_stock']);
        }

        // Commit transaction
        $db->commit();

        echo json_encode([
            'status' => 'success',
            'message' => 'Product added successfully',
            'product_id' => $productId
        ]);

    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    error_log("Error adding product: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
