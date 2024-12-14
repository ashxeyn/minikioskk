<?php
require_once '../classes/productClass.php';
require_once '../classes/stocksClass.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate required fields
        $required = ['name', 'type_id', 'price', 'canteen_id', 'initial_stock'];
        foreach ($required as $field) {
            if (!isset($_POST[$field]) || empty($_POST[$field])) {
                throw new Exception("Missing required field: $field");
            }
        }

        $productObj = new Product();
        $stockObj = new Stocks();

        // Prepare product data
        $productData = [
            'name' => $_POST['name'],
            'type_id' => $_POST['type_id'],
            'description' => $_POST['description'] ?? '',
            'price' => $_POST['price'],
            'canteen_id' => $_POST['canteen_id'],
            'status' => 'available'
        ];

        // Start transaction
        $db = $productObj->getConnection();
        $db->beginTransaction();

        // Add product
        $productId = $productObj->addProduct($productData);

        // Add initial stock
        if ($productId && $_POST['initial_stock'] > 0) {
            $stockObj->addStock($productId, $_POST['initial_stock']);
        }

        $db->commit();
        echo json_encode(['success' => true]);

    } catch (Exception $e) {
        if (isset($db) && $db->inTransaction()) {
            $db->rollBack();
        }
        error_log("Error adding product: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?> 