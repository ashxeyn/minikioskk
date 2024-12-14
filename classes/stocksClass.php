<?php
require_once '../classes/databaseClass.php';

class Stocks
{
    protected $db;

    function __construct()
    {
        $this->db = new Database();
    }

    function addStock($productId, $quantity) {
        try {
            $sql = "INSERT INTO stocks (product_id, quantity, last_restock) 
                    VALUES (:product_id, :quantity, NOW())
                    ON DUPLICATE KEY UPDATE 
                    quantity = quantity + :new_quantity,
                    last_restock = NOW()";
            
            $query = $this->db->connect()->prepare($sql);
            $query->bindValue(':product_id', $productId);
            $query->bindValue(':quantity', $quantity);
            $query->bindValue(':new_quantity', $quantity);
            
            return $query->execute();
        } catch (PDOException $e) {
            error_log("Error in addStock: " . $e->getMessage());
            return false;
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

            // Log the incoming data
            error_log("Updating stock for product_id: $product_id, quantity: $quantity_to_add");

            $result = $this->addStock($product_id, $quantity_to_add);
            
            if ($result) {
                $db->commit();
                return true;
            }

            $db->rollBack();
            return false;

        } catch (Exception $e) {
            error_log("Error updating stock: " . $e->getMessage());
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }
}
?>
