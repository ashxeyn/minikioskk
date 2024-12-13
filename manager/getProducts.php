<?php
session_start();
require_once '../classes/productClass.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'manager' || !isset($_SESSION['canteen_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

try {
    $productObj = new Product();
    $canteen_id = $_SESSION['canteen_id'];
    
    // Get search parameters if they exist
    $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
    $category = isset($_GET['category']) ? $_GET['category'] : '';
    
    $products = $productObj->searchProductsByCanteen($canteen_id, $keyword, $category);
    echo json_encode(['status' => 'success', 'data' => $products]);
} catch (Exception $e) {
    error_log("Error in manager getProducts: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Failed to fetch products']);
}
?> 