<?php
require_once '../classes/stocksClass.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id']) && isset($_POST['quantity'])) {
    $stockObj = new Stocks();
    try {
        $result = $stockObj->updateStock($_POST['product_id'], $_POST['quantity']);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?> 