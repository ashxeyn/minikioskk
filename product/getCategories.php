<?php
require_once '../classes/productClass.php';
require_once '../tools/functions.php';

header('Content-Type: application/json');

try {
    $productObj = new Product();
    $categories = $productObj->getCategories();
    echo json_encode($categories);
} catch (Exception $e) {
    error_log("Error fetching categories: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}
?> 