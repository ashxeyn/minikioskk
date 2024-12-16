<?php
require_once '../classes/databaseClass.php';

class Stocks
{
    protected $db;

    function __construct()
    {
        $this->db = new Database();
    }

    public function addStock($productId, $quantity) {
        try {
            // First check if a stock record exists
            $sql = "SELECT quantity FROM stocks WHERE product_id = ?";
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->execute([$productId]);
            $currentStock = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($currentStock) {
                // Update existing stock
                $sql = "UPDATE stocks 
                        SET quantity = quantity + ?, 
                            last_restock = NOW(), 
                            updated_at = NOW() 
                        WHERE product_id = ?";
                $stmt = $this->db->connect()->prepare($sql);
                return $stmt->execute([$quantity, $productId]);
            } else {
                // Insert new stock record
                $sql = "INSERT INTO stocks (product_id, quantity, last_restock, updated_at) 
                        VALUES (?, ?, NOW(), NOW())";
                $stmt = $this->db->connect()->prepare($sql);
                return $stmt->execute([$productId, $quantity]);
            }
        } catch (PDOException $e) {
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

    public function updateStock($productId, $quantity) {
        try {
            $sql = "INSERT INTO stocks (product_id, quantity, updated_at) 
                    VALUES (:product_id, :quantity, NOW())
                    ON DUPLICATE KEY UPDATE 
                    quantity = :quantity,
                    updated_at = NOW()";
                    
            $stmt = $this->db->connect()->prepare($sql);
            return $stmt->execute([
                'product_id' => $productId,
                'quantity' => $quantity
            ]);
        } catch (PDOException $e) {
            error_log("Error in updateStock: " . $e->getMessage());
            throw new Exception("Failed to update stock");
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

    public function addInitialStock($productId, $quantity) {
        try {
            $sql = "INSERT INTO stocks (product_id, quantity, last_restock, updated_at) 
                    VALUES (?, ?, NOW(), NOW())";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$productId, $quantity]);
        } catch (PDOException $e) {
            error_log("Error adding initial stock: " . $e->getMessage());
            return false;
        }
    }
}
?>
