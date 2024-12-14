<?php
require_once '../classes/stocksClass.php';

if (isset($_GET['product_id'])) {
    $stockObj = new Stocks();
    $stock = $stockObj->fetchStockByProductId($_GET['product_id']);
    
    if ($stock) {
        echo json_encode($stock);
    } else {
        echo json_encode(['quantity' => 0]);
    }
}
?> 