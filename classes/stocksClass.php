<?php
require_once '../classes/databaseClass.php';

class Stocks
{
    protected $db;

    function __construct()
    {
        $this->db = new Database();
    }

    function addStock($data) {
        try {
            $sql = "INSERT INTO stocks (product_id, quantity) 
                    VALUES (:product_id, :quantity)";
                    
            $stmt = $this->db->connect()->prepare($sql);
            return $stmt->execute([
                'product_id' => $data['product_id'],
                'quantity' => $data['quantity']
            ]);
        } catch (PDOException $e) {
            error_log("Error in addStock: " . $e->getMessage());
            throw new Exception("Failed to add stock: " . $e->getMessage());
        }
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

    public function updateStock($product_id, $quantity_to_add) {
        try {
            $db = $this->db->connect();
            $db->beginTransaction();

            // Get current stock
            $sql = "SELECT quantity FROM stocks WHERE product_id = :product_id FOR UPDATE";
            $stmt = $db->prepare($sql);
            $stmt->execute(['product_id' => $product_id]);
            $current = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($current) {
                // Update existing stock
                $new_quantity = $current['quantity'] + $quantity_to_add;
                $sql = "UPDATE stocks 
                        SET quantity = :quantity,
                            updated_at = NOW()
                        WHERE product_id = :product_id";
            } else {
                // Insert new stock record
                $new_quantity = $quantity_to_add;
                $sql = "INSERT INTO stocks (product_id, quantity) 
                        VALUES (:product_id, :quantity)";
            }

            $stmt = $db->prepare($sql);
            $result = $stmt->execute([
                'product_id' => $product_id,
                'quantity' => $new_quantity
            ]);

            if ($result) {
                $db->commit();
                return true;
            }

            $db->rollBack();
            return false;

        } catch (Exception $e) {
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Error updating stock: " . $e->getMessage());
            throw new Exception("Failed to update stock: " . $e->getMessage());
        }
    }
}
?>
