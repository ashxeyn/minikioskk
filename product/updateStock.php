<?php
require_once '../classes/stocksClass.php';
require_once '../tools/functions.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    if (empty($_POST['product_id']) || empty($_POST['quantity'])) {
        throw new Exception('Missing required fields');
    }

    $product_id = clean_input($_POST['product_id']);
    $quantity = clean_input($_POST['quantity']);

    if (!is_numeric($quantity) || $quantity <= 0) {
        throw new Exception('Invalid quantity value');
    }

    $stockObj = new Stocks();
    $currentStock = $stockObj->fetchStockByProductId($product_id);

    if ($currentStock) {
        // Update existing stock
        $result = $stockObj->updateStock($product_id, $quantity);
    } else {
        // Create new stock record
        $stock_data = [
            'product_id' => $product_id,
            'quantity' => $quantity
        ];
        $result = $stockObj->addStock($stock_data);
    }

    if ($result) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Stock updated successfully'
        ]);
    } else {
        throw new Exception('Failed to update stock');
    }

} catch (Exception $e) {
    error_log("Error in updateStock.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
