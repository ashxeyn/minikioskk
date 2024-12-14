<?php
session_start();
require_once '../classes/productClass.php';
require_once '../classes/stocksClass.php';

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
    $db = new PDO("mysql:host=localhost;dbname=minikiosk1", "root", "");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->beginTransaction();

    try {
        // Add product first
        $product = new Product();
        $productId = $product->addProduct([
            'name' => $_POST['name'],
            'type_id' => $_POST['type_id'],
            'description' => $_POST['description'] ?? '',
            'price' => $_POST['price'],
            'canteen_id' => $_POST['canteen_id'],
            'status' => 'available'
        ]);

        // Then add initial stock
        if ($productId && $_POST['initial_stock'] > 0) {
            $stocks = new Stocks();
            $stocks->addStock($productId, $_POST['initial_stock']);
        }

        $db->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Product added successfully',
            'product_id' => $productId
        ]);
    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error adding product: ' . $e->getMessage()
    ]);
}
?> 