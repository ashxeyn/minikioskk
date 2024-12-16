<?php
require_once 'databaseClass.php';

class AdminProduct {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getAllProducts() {
        try {
            $sql = "SELECT p.*, pt.name as type_name, c.name as canteen_name,
                           s.quantity as stock_quantity, s.updated_at as last_stock_update 
                    FROM products p 
                    LEFT JOIN product_types pt ON p.type_id = pt.type_id 
                    LEFT JOIN canteens c ON p.canteen_id = c.canteen_id
                    LEFT JOIN stocks s ON p.product_id = s.product_id 
                    ORDER BY c.name, p.name";
            
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getAllProducts: " . $e->getMessage());
            throw new Exception("Failed to fetch products");
        }
    }

    public function getProduct($productId) {
        try {
            $sql = "SELECT p.*, pt.name as type_name, c.name as canteen_name,
                           s.quantity as stock_quantity, s.updated_at as last_stock_update 
                    FROM products p 
                    LEFT JOIN product_types pt ON p.type_id = pt.type_id 
                    LEFT JOIN canteens c ON p.canteen_id = c.canteen_id
                    LEFT JOIN stocks s ON p.product_id = s.product_id 
                    WHERE p.product_id = :product_id";
                
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->execute(['product_id' => $productId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                throw new Exception("Product not found");
            }
            
            return [
                'product_id' => $result['product_id'],
                'name' => $result['name'],
                'description' => $result['description'],
                'price' => $result['price'],
                'type_id' => $result['type_id'],
                'stock_quantity' => $result['stock_quantity'] ?? 0,
                'type_name' => $result['type_name'],
                'canteen_name' => $result['canteen_name'],
                'last_stock_update' => $result['last_stock_update']
            ];
        } catch (PDOException $e) {
            error_log("Error in getProduct: " . $e->getMessage());
            throw new Exception("Failed to fetch product details");
        }
    }

    public function updateProduct($data) {
        try {
            $db = $this->db->connect();
            $db->beginTransaction();

            // Update product details
            $sql = "UPDATE products 
                    SET name = :name,
                        description = :description,
                        type_id = :type_id,
                        price = :price,
                        status = :status
                    WHERE product_id = :product_id";

            $stmt = $db->prepare($sql);
            $result = $stmt->execute([
                'name' => $data['name'],
                'description' => $data['description'],
                'type_id' => $data['type_id'],
                'price' => $data['price'],
                'status' => $data['status'] ?? 'available',
                'product_id' => $data['product_id']
            ]);

            if (!$result) {
                throw new Exception("Failed to update product");
            }

            // Update stock if quantity is provided
            if (isset($data['quantity']) && $data['quantity'] > 0) {
                // First check if stock record exists
                $checkSql = "SELECT quantity FROM stocks WHERE product_id = :product_id";
                $checkStmt = $db->prepare($checkSql);
                $checkStmt->execute(['product_id' => $data['product_id']]);
                $currentStock = $checkStmt->fetch(PDO::FETCH_ASSOC);

                if ($currentStock) {
                    // Update existing stock
                    $stockSql = "UPDATE stocks 
                                SET quantity = quantity + :quantity,
                                    last_restock = CURRENT_TIMESTAMP 
                                WHERE product_id = :product_id";
                } else {
                    // Insert new stock record
                    $stockSql = "INSERT INTO stocks (product_id, quantity, last_restock) 
                                VALUES (:product_id, :quantity, CURRENT_TIMESTAMP)";
                }
                
                $stockStmt = $db->prepare($stockSql);
                $stockResult = $stockStmt->execute([
                    'product_id' => $data['product_id'],
                    'quantity' => $data['quantity']
                ]);

                if (!$stockResult) {
                    throw new Exception("Failed to update stock");
                }
            }

            $db->commit();
            return true;
        } catch (PDOException $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Error in updateProduct: " . $e->getMessage());
            throw new Exception("Failed to update product: " . $e->getMessage());
        }
    }

    public function addProduct($data) {
        try {
            $db = $this->db->connect();
            $db->beginTransaction();

            $sql = "INSERT INTO products (name, type_id, description, price, canteen_id, status) 
                    VALUES (:name, :type_id, :description, :price, :canteen_id, :status)";
            
            $stmt = $db->prepare($sql);
            $result = $stmt->execute([
                'name' => $data['name'],
                'type_id' => $data['type_id'],
                'description' => $data['description'],
                'price' => $data['price'],
                'canteen_id' => $data['canteen_id'],
                'status' => $data['status'] ?? 'available'
            ]);

            if (!$result) {
                throw new Exception("Failed to add product");
            }

            $productId = $db->lastInsertId();

            // Add initial stock if provided
            if (isset($data['initial_stock']) && $data['initial_stock'] > 0) {
                $stockSql = "INSERT INTO stocks (product_id, quantity, last_restock) 
                            VALUES (:product_id, :quantity, CURRENT_TIMESTAMP)";
                
                $stockStmt = $db->prepare($stockSql);
                $stockResult = $stockStmt->execute([
                    'product_id' => $productId,
                    'quantity' => $data['initial_stock']
                ]);

                if (!$stockResult) {
                    throw new Exception("Failed to add initial stock");
                }
            }

            $db->commit();
            return $productId;
        } catch (PDOException $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Error in addProduct: " . $e->getMessage());
            throw new Exception("Failed to add product: " . $e->getMessage());
        }
    }

    public function deleteProduct($productId) {
        try {
            $db = $this->db->connect();
            $db->beginTransaction();

            // Delete related records first
            $sql = "DELETE FROM stocks WHERE product_id = :product_id";
            $stmt = $db->prepare($sql);
            $stmt->execute(['product_id' => $productId]);

            // Delete the product
            $sql = "DELETE FROM products WHERE product_id = :product_id";
            $stmt = $db->prepare($sql);
            $result = $stmt->execute(['product_id' => $productId]);

            if (!$result) {
                throw new Exception("Failed to delete product");
            }

            $db->commit();
            return true;
        } catch (PDOException $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Error in deleteProduct: " . $e->getMessage());
            throw new Exception("Failed to delete product: " . $e->getMessage());
        }
    }

    public function getCanteens() {
        try {
            $sql = "SELECT canteen_id, name FROM canteens ORDER BY name";
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getCanteens: " . $e->getMessage());
            throw new Exception("Failed to fetch canteens");
        }
    }
}
?> 