<?php
require_once '../classes/orderClass.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $orderObj = new Order();
    $order_id = $_POST['order_id'];
    $result = $orderObj->deleteOrder($order_id);
    echo $result ? 'success' : 'failure';
}
?>
