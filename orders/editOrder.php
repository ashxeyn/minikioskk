<?php
require_once '../classes/orderClass.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $orderObj = new Order();
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];
    $queue_number = $_POST['queue_number'];
    
    // Update basic order information
    $result = $orderObj->updateOrder($order_id, $status, $queue_number);
    
    // Handle new products
    if (isset($_POST['new_products']) && isset($_POST['new_quantities'])) {
        $new_products = $_POST['new_products'];
        $new_quantities = $_POST['new_quantities'];
        
        for ($i = 0; $i < count($new_products); $i++) {
            if (!empty($new_products[$i]) && $new_quantities[$i] > 0) {
                $orderObj->addProductToOrder($order_id, $new_products[$i], $new_quantities[$i]);
            }
        }
    }
    
    echo $result ? 'success' : 'failure';
}
?>
