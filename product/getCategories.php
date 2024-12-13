<?php
require_once '../classes/productClass.php';

$product = new Product();
$categories = $product->getCategories();
echo json_encode($categories);
?> 