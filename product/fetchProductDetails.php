<?php
require_once '../classes/productClass.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['product_id'])) {
    $productObj = new Product();
    $product_id = $_GET['product_id'];

    $product = $productObj->fetchProductById($product_id);

    echo json_encode($product);
}
?>
