<?php
require_once '../classes/productClass.php';
header('Content-Type: application/json');

try {
    $productObj = new Product();
    $categories = $productObj->getCategories();
    echo json_encode($categories);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?> 