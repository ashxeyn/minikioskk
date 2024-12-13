<?php
require_once '../classes/stocksClass.php';
require_once '../tools/functions.php';

$stockObj = new Stocks();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = clean_input($_POST['product_id']);
    $quantity = clean_input($_POST['quantity']);
    $status = 'In Stock'; // Always set to In Stock
    
    $res = array("status" => "failure");
    
    if (empty($product_id) || empty($quantity)) {
        echo json_encode($res);
        exit;
    }

    $stock = $stockObj->fetchStockByProductId($product_id);
    if ($stock) {
        // Add to existing stock
        $updateStock = $stockObj->updateStock($product_id, $quantity);
        $res['status'] = $updateStock ? 'success' : 'failure';
    } else {
        // Create new stock record
        $addStock = $stockObj->addStock($product_id, $quantity, $status);
        $res['status'] = $addStock ? 'success' : 'failure';
    }
    echo json_encode($res);
} else {
    echo json_encode(array("status" => "failure"));
}
?>
