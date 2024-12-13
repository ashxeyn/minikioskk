<?php
require_once '../classes/productClass.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productObj = new Product();

    $product_id = $_POST['product_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $price = $_POST['price'];

    $result = $productObj->updateProduct($product_id, $name, $description, $category, $price);

    echo $result ? 'success' : 'failure';
}
?>
