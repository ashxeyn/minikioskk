<?php
require_once '../classes/databaseClass.php';

class Stocks
{
    protected $db;

    function __construct()
    {
        $this->db = new Database();
    }

    function addStock($product_id, $quantity, $status)
    {
        $sql = "INSERT INTO stocks (product_id, quantity, status) 
                VALUES ('" . $product_id . "', '" . $quantity . "', '" . $status . "')";
        return $this->db->connect()->query($sql);  
    }

    function fetchStockByProductId($product_id)
    {
        $sql = "SELECT quantity, status FROM stocks WHERE product_id = '" . $product_id . "'";
        $query = $this->db->connect()->query($sql);
        return $query->fetch();
    }

    function updateStock($product_id, $quantity, $status)
    {
        $currentStock = $this->fetchStockByProductId($product_id);

        if (!$currentStock) {
            return "Product not found.";
        }

        $newQuantity = $currentStock['quantity'];

        if ($status === 'In Stock') {
            $newQuantity += $quantity;
        } elseif ($status === 'Out of Stock') {
            if ($newQuantity <= 0) {
                return "Cannot reduce stock below 0.";
            }
            if ($newQuantity >= $quantity) {
                $newQuantity -= $quantity;
            } else {
                return "Insufficient stock to subtract.";
            }
        }

        $newStatus = $newQuantity > 0 ? 'In Stock' : 'Out of Stock';

        $sql = "UPDATE stocks SET quantity = '" . $newQuantity . "', status = '" . $newStatus . "' WHERE product_id = '" . $product_id . "'";
        return $this->db->connect()->query($sql);
    }
}
?>
