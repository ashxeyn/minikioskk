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
            
          
            if (!$this->productExists($product_id)) {
                throw new Exception("Product not found");
            }
            
            $db->beginTransaction();
            
         
            $stmt = $db->prepare("SELECT quantity FROM stocks WHERE product_id = ? FOR UPDATE");
            $stmt->execute([$product_id]);
            $current = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($current) {
           
                $newQuantity = $current['quantity'] + $quantity;
                $sql = "UPDATE stocks SET quantity = ?, last_restock = NOW() WHERE product_id = ?";
                $stmt = $db->prepare($sql);
                $result = $stmt->execute([$newQuantity, $product_id]);
            } else {
              
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
            error_log("" . $e->getMessage());
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            error_log("" . $e->getMessage());
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
