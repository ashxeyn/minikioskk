<?php
session_start();
require_once '../classes/productClass.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$productObj = new Product();
$products = $productObj->searchProducts();
echo json_encode($products);
?> 