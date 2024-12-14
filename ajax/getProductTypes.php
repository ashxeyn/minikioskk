<?php
require_once '../classes/productTypeClass.php';

try {
    $productTypeObj = new ProductType();
    $types = $productTypeObj->getAllProductTypes();
    echo json_encode($types);
} catch (Exception $e) {
    error_log("Error getting product types: " . $e->getMessage());
    echo json_encode(['error' => $e->getMessage()]);
}
?> 