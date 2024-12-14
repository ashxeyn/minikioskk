<?php
require_once '../classes/productClass.php';

if (isset($_GET['product_id'])) {
    $productObj = new Product();
    $product = $productObj->fetchProductById($_GET['product_id']);
    
    if ($product) {
        echo json_encode($product);
    } else {
        echo json_encode(['error' => 'Product not found']);
    }
}
?> 