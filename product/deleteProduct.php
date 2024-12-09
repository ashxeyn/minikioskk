<?php
require_once '../classes/productClass.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $productObj = new Product();
    $product_id = $_POST['product_id'];
    $result = $productObj->deleteProduct($product_id);
    echo $result ? 'success' : 'failure';
}
?>
