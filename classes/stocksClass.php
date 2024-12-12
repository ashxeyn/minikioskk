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
        $sql = "SELECT quantity, last_restock 
                FROM stocks 
                WHERE product_id = '" . $product_id . "'";
        $result = $this->db->connect()->query($sql);
        
        if ($result) {
            $stock = $result->fetch(PDO::FETCH_ASSOC);
            if ($stock) {
                $stock['status'] = $stock['quantity'] > 0 ? 'In Stock' : 'Out of Stock';
                return $stock;
            }
        }
        
        return ['quantity' => 0, 'status' => 'Out of Stock', 'last_restock' => null];
    }

    function updateStock($product_id, $quantity, $operation = 'subtract')
    {
        try {
            $sql = "SELECT quantity FROM stocks WHERE product_id = :product_id";
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->bindParam(':product_id', $product_id);
            $stmt->execute();
            $currentStock = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$currentStock) {
                throw new Exception("Stock record not found");
            }

            $newQuantity = $operation === 'add' 
                ? $currentStock['quantity'] + $quantity 
                : $currentStock['quantity'] - $quantity;

            if ($newQuantity < 0) {
                throw new Exception("Cannot reduce stock below 0");
            }

            $sql = "UPDATE stocks 
                    SET quantity = :quantity, 
                        last_restock = CASE 
                            WHEN :operation = 'add' THEN CURRENT_TIMESTAMP 
                            ELSE last_restock 
                        END 
                    WHERE product_id = :product_id";

            $stmt = $this->db->connect()->prepare($sql);
            $stmt->bindParam(':quantity', $newQuantity);
            $stmt->bindParam(':operation', $operation);
            $stmt->bindParam(':product_id', $product_id);
            return $stmt->execute();

        } catch (Exception $e) {
            error_log("Error updating stock: " . $e->getMessage());
            throw $e;
        }
    }
}
?>
