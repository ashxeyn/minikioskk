<?php
require_once '../classes/orderClass.php';

if (isset($_GET['canteen_id'])) {
    $orderObj = new Order();
    $canteen_id = $_GET['canteen_id'];
    $products = $orderObj->getAvailableProductsByCanteen($canteen_id);
    echo json_encode($products);
}

?>