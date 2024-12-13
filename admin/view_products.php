<?php
session_start();
require_once '../classes/productClass.php';
require_once '../classes/stocksClass.php';
require_once '../tools/functions.php';

// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

try {
    $productObj = new Product();
    $stockObj = new Stocks();
    
    // For admin, fetch all products across all canteens
    $products = $productObj->searchProducts();
    
} catch (Exception $e) {
    error_log("Error in admin view_products: " . $e->getMessage());
    $products = [];
}
?> 