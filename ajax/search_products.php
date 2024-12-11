<?php
session_start();
require_once '../classes/productClass.php';

header('Content-Type: application/json');

$productObj = new Product();
$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';

try {
    if (isset($_SESSION['role']) && $_SESSION['role'] == 'manager') {
        $canteen_id = $_SESSION['canteen_id'];
        $products = $productObj->searchProductsByCanteen($canteen_id, $keyword, $category);
    } else {
        $products = $productObj->searchProducts($keyword, $category);
    }
    echo json_encode($products);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} 