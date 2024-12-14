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
                // Update existing st   ock
                $newQuantity = $existingStock['quantity'] + $quantity;
                $sql = "UPDATE stocks 
                        SET quantity = :quantity, 
                            last_restock = CURRENT_TIMESTAMP,
                            updated_at = CURRENT_TIMESTAMP
                        WHERE product_id = :product_id";
                
                $stmt = $conn->prepare($sql);
                $result = $stmt->execute([
                    'quantity' => $newQuantity,
                    'product_id' => $product_id
                ]);
            } else {
                // Insert new stock record
                $sql = "INSERT INTO stocks (product_id, quantity, last_restock, updated_at) 
                        VALUES (:product_id, :quantity, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
                
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
            throw new Exception("Failed to update stock: " . $e->getMessage());
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

    public function updateStock($product_id, $quantity) {
        try {
            $db = $this->db->connect();
            // Validate inputs
            $product_id = filter_var($product_id, FILTER_VALIDATE_INT);
            $quantity = filter_var($quantity, FILTER_VALIDATE_INT);
            
            if ($product_id === false || $product_id <= 0) {
                throw new Exception("Invalid product ID");
            }
            
            if ($quantity === false || $quantity <= 0) {
                throw new Exception("Invalid quantity");
            }
            
            // Check if product exists
            if (!$this->productExists($product_id)) {
                throw new Exception("Product not found");
            }
            $db->beginTransaction();

             // Get current stock
             $stmt = $db->prepare("SELECT quantity FROM stocks WHERE product_id = ? FOR UPDATE");
             $stmt->execute([$product_id]);
             $current = $stmt->fetch(PDO::FETCH_ASSOC);
             
             if ($current) {
                 // Update existing stock
                 $newQuantity = $current['quantity'] + $quantity;
                 $sql = "UPDATE stocks SET quantity = ?, last_restock = NOW() WHERE product_id = ?";
                 $stmt = $db->prepare($sql);
                 $result = $stmt->execute([$newQuantity, $product_id]);
             } else {
                 // Insert new stock record
                 $sql = "INSERT INTO stocks (product_id, quantity, last_restock) VALUES (?, ?, NOW())";
                 $stmt = $db->prepare($sql);
                 $result = $stmt->execute([$product_id, $quantity]);
             }

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
    public function productExists($product_id) {
        try {
            $db = $this->db->connect();
            $stmt = $db->prepare("SELECT 1 FROM products WHERE product_id = ? LIMIT 1");
            $stmt->execute([$product_id]);
            return $stmt->fetchColumn() !== false;
        } catch (Exception $e) {
            error_log("Error checking product existence: " . $e->getMessage());
            return false;
        }
    }
}
?>
