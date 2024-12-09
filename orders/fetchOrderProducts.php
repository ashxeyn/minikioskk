<?php
require_once '../classes/orderClass.php';

if (isset($_GET['order_id'])) {
    $orderObj = new Order();
    $order_id = $_GET['order_id'];
    $products = $orderObj->getOrderProducts($order_id);
    echo json_encode($products);
}
?>
