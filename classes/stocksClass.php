<?php
require_once '../classes/databaseClass.php';

class Stocks
{
    protected $db;

    function __construct()
    {
        $this->db = new Database();
    }

    public function addStock($product_id, $quantity) {
        try {
            $conn = $this->db->connect();
            $conn->beginTransaction();

            // Check if stock record exists
            $sql = "SELECT stock_id, quantity FROM stocks WHERE product_id = :product_id";
            $stmt = $conn->prepare($sql);
            $stmt->execute(['product_id' => $product_id]);
            $existingStock = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existingStock) {
                // Update existing stock
                $newQuantity = $existingStock['quantity'] + $quantity;
                $sql = "UPDATE stocks 
                        SET quantity = :quantity, 
                            last_restock = CURRENT_TIMESTAMP 
                        WHERE product_id = :product_id";
                
                $stmt = $conn->prepare($sql);
                $result = $stmt->execute([
                    'quantity' => $newQuantity,
                    'product_id' => $product_id
                ]);
            } else {
                // Insert new stock record
                $sql = "INSERT INTO stocks (product_id, quantity, last_restock) 
                        VALUES (:product_id, :quantity, CURRENT_TIMESTAMP)";
                
                $stmt = $conn->prepare($sql);
                $result = $stmt->execute([
                    'product_id' => $product_id,
                    'quantity' => $quantity
                ]);
            }

            if ($result) {
                $conn->commit();
                return true;
            }

            $conn->rollBack();
            return false;

        } catch (PDOException $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            error_log("Error in addStock: " . $e->getMessage());
            throw new Exception("Failed to update stock");
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
