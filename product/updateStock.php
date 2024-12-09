<?php
require_once '../classes/stocksClass.php';
require_once '../tools/functions.php';

$stockObj = new Stocks();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = clean_input($_POST['product_id']);
    $quantity = clean_input($_POST['quantity']);
    $status = clean_input($_POST['status']);
    $res = array("status" => "failure");
    if (empty($product_id) || empty($quantity) || empty($status)) {
        // echo 'failure';
        echo json_encode($res);
        exit;
    }

    $stock = $stockObj->fetchStockByProductId($product_id);
    if ($stock) {
        $updateStock = $stockObj->updateStock($product_id, $quantity, $status);
        // echo $updateStock ? 'success' : 'failure';
        $res['status'] = $updateStock ? 'success' : 'failure';
    } else {
        $addStock = $stockObj->addStock($product_id, $quantity, $status);
        // echo $addStock ? 'success' : 'failure';
        $res['status'] = $addStock ? 'success' : 'failure';
    }
    echo json_encode($res);
} else {
    echo 'failure';
}
?>
