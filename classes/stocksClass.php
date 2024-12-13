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
        try {
            $sql = "SELECT * FROM stocks WHERE product_id = :product_id";
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->execute(['product_id' => $product_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error fetching stock: " . $e->getMessage());
            return null;
        }
    }

    function updateStock($product_id, $quantity_change)
    {
        try {
            $db = $this->db->connect();
            
            if (!$db->inTransaction()) {
                $db->beginTransaction();
            }

            // Get current stock
            $sql = "SELECT quantity FROM stocks WHERE product_id = :product_id FOR UPDATE";
            $stmt = $db->prepare($sql);
            $stmt->execute(['product_id' => $product_id]);
            $current = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$current) {
                throw new Exception("Stock record not found");
            }

            // Add to existing quantity (stock in only)
            $new_quantity = $current['quantity'] + $quantity_change;

            // Update stock
            $sql = "UPDATE stocks 
                    SET quantity = :quantity,
                        updated_at = NOW()
                    WHERE product_id = :product_id";

            $stmt = $db->prepare($sql);
            $result = $stmt->execute([
                'quantity' => $new_quantity,
                'product_id' => $product_id
            ]);

            $db->commit();
            return $result;

        } catch (Exception $e) {
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Error updating stock: " . $e->getMessage());
            return false;
        }
    }
}
?>
