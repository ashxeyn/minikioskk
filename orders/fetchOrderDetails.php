<?php
require_once '../classes/orderClass.php';

if (isset($_GET['order_id'])) {
    $orderObj = new Order();
    $order = $orderObj->fetchOrderById($_GET['order_id']);
    echo json_encode($order);
}
?>
