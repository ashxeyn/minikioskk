<?php
require_once '../classes/orderClass.php';

if (isset($_POST['order_id']) && isset($_POST['product_id']) && isset($_POST['quantity'])) {
    $orderObj = new Order();
    $order_id = $_POST['order_id'];
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $action = $_POST['action'] ?? 'update';

    try {
        switch ($action) {
            case 'add':
                $result = $orderObj->addQuantityToProduct($order_id, $product_id, $quantity);
                break;
            case 'remove':
                $result = $orderObj->removeQuantityFromProduct($order_id, $product_id, $quantity);
                break;
            default:
                $result = false;
        }
        
        echo $result ? 'success' : 'failure';
    } catch (Exception $e) {
        echo 'error: ' . $e->getMessage();
    }
} else {
    echo 'Invalid parameters';
}
?>