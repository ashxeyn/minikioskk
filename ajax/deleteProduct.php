<?php
require_once '../classes/productClass.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $productObj = new Product();
    try {
        $result = $productObj->deleteProductWithRelations($_POST['product_id']);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?> 