<?php
require_once '../tools/functions.php';
require_once '../classes/orderClass.php';  // Include your Order class to handle order logic

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get POST data
    $order_title = $_POST['order_title'];
    $order_description = $_POST['order_description'];
    $order_quantity = $_POST['order_quantity'];

    // Instantiate the Order class and add the order to the database
    $order = new Order();
    if ($order->addOrder($order_title, $order_description, $order_quantity)) {
        echo 'success';
    } else {
        echo 'error';
    }
}
?>
