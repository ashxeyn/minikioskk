<?php
require_once '../classes/stocksClass.php';

if (isset($_GET['product_id'])) {
    $stocksobj = new Stocks();
    $stockDetails = $stocksobj->fetchStockByProductId($_GET['product_id']);
    echo json_encode($stocks);
}
?>
