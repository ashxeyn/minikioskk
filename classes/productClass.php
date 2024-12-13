<?php
require_once '../classes/databaseClass.php';

class Product
{
    protected $db;

    function __construct()
    {
        $this->db = new Database();
    }
    
    function searchProducts($keyword = '', $category = '') {
        try {
            $sql = "SELECT p.*, c.name AS canteen_name 
                    FROM products p 
                    LEFT JOIN canteens c ON p.canteen_id = c.canteen_id
                    WHERE 1";  
        
            if ($keyword) {
                $sql .= " AND (p.name LIKE :keyword OR c.name LIKE :keyword OR p.description LIKE :keyword)";
            }
        
            if ($category) {
                $sql .= " AND p.type_id = :category";  
            }
        
            $query = $this->db->connect()->prepare($sql);
        
            if ($keyword) {
                $searchKeyword = '%' . $keyword . '%';
                $query->bindParam(':keyword', $searchKeyword);
            }
        
            if ($category) {
                $query->bindParam(':category', $category);  
            }
        
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error in searchProducts: " . $e->getMessage());
            return [];
        }
    }
    
    
    function fetchProductsByCanteen($canteen_id)
    {
        try {
            $sql = "SELECT p.*, c.name as canteen_name 
                    FROM products p 
                    LEFT JOIN canteens c ON p.canteen_id = c.canteen_id 
                    WHERE p.canteen_id = :canteen_id";
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->bindParam(':canteen_id', $canteen_id);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in fetchProductsByCanteen: " . $e->getMessage());
            return [];
        }
    }

    function fetchProductById($product_id)
    {
        $sql = "SELECT * FROM products WHERE product_id = :product_id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':product_id', $product_id);
        $query->execute();
        return $query->fetch();
    }

    function isProductAvailable($product_id)
    {
        $sql = "SELECT availability FROM products WHERE product_id = :product_id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':product_id', $product_id);
        $query->execute();
        $result = $query->fetch();
        return $result['availability'] == 1;
    }

    function addProduct($name, $type_id, $description, $price, $canteen_id) {
        try {
            // First verify that the canteen exists
            $checkCanteen = "SELECT canteen_id FROM canteens WHERE canteen_id = :canteen_id";
            $stmt = $this->db->connect()->prepare($checkCanteen);
            $stmt->bindParam(':canteen_id', $canteen_id);
            $stmt->execute();
            
            if ($stmt->rowCount() === 0) {
                throw new Exception("Invalid canteen selected");
            }

            $sql = "INSERT INTO products (name, type_id, description, price, canteen_id) 
                    VALUES (:name, :type_id, :description, :price, :canteen_id)";
            
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':type_id', $type_id);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':price', $price);
            $stmt->bindParam(':canteen_id', $canteen_id);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in addProduct: " . $e->getMessage());
            throw new Exception("Failed to add product");
        }
    }

    function updateProduct($product_id, $name, $description, $type_id, $price, $image_url = null) {
        $sql = "UPDATE products 
                SET name = :name, 
                    description = :description, 
                    type_id = :type_id, 
                    price = :price,
                    image_url = :image_url
                WHERE product_id = :product_id";
        $query = $this->db->connect()->prepare($sql);

        $query->bindParam(':product_id', $product_id);
        $query->bindParam(':name', $name);
        $query->bindParam(':description', $description);
        $query->bindParam(':type_id', $type_id);
        $query->bindParam(':price', $price);
        $query->bindParam(':image_url', $image_url);

        return $query->execute();
    }

    function deleteProduct($product_id)
    {
        $sql = "DELETE FROM products WHERE product_id = :product_id";
        $query = $this->db->connect()->prepare($sql);

        $query->bindParam(':product_id', $product_id);

        return $query->execute();
    }

    function fetchProducts()
    {
        $sql = "SELECT * FROM products";
        $query = $this->db->connect()->prepare($sql);
        $query->execute();

        return $query->fetchAll();
    }

    function getCanteenNameById($canteen_id)
    {
        $sql = "SELECT name FROM canteens WHERE canteen_id = :canteen_id";
        $query = $this->db->connect()->prepare($sql);

        $query->bindParam(':canteen_id', $canteen_id);

        if ($query->execute()) {
            $canteen = $query->fetch();
            return $canteen ? $canteen['name'] : 'Unknown Canteen';
        }

        return 'Unknown Canteen'; // Return default value if query fails
    }

    function getStockStatus($product_id) {
        $sql = "SELECT quantity, status FROM stocks WHERE product_id = :product_id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':product_id', $product_id);
        $query->execute();

        $stock = $query->fetch();
        return $stock ? $stock : ['quantity' => 0, 'status' => 'Out of Stock'];
    }

    function getProductsByCanteen($canteen_id)
    {
        $sql = "SELECT p.product_id, p.name, p.description, p.category, p.price, p.availability, s.quantity, c.name AS canteen_name
                FROM products p
                LEFT JOIN stocks s ON p.product_id = s.product_id
                LEFT JOIN canteens c ON p.canteen_id = c.canteen_id
                WHERE p.canteen_id = :canteen_id AND p.availability = 1";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':canteen_id', $canteen_id);
        $query->execute();
        return $query->fetchAll();
    }
    

    function fetchAllCanteens()
    {
        $sql = "SELECT DISTINCT name FROM canteens";
        $query = $this->db->connect()->prepare($sql);
        $query->execute();
        return $query->fetchAll();
    }
    
    function deleteProductWithRelations($product_id) {
        try {
            $db = $this->db->connect();
            $db->beginTransaction();

            // First delete related records in stocks table
            $sql = "DELETE FROM stocks WHERE product_id = :product_id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':product_id', $product_id);
            $stmt->execute();

            // Then delete the product
            $sql = "DELETE FROM products WHERE product_id = :product_id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':product_id', $product_id);
            $stmt->execute();

            $db->commit();
            return true;
        } catch (PDOException $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Error deleting product: " . $e->getMessage());
            return false;
        }
    }

    function getFeaturedProducts()
    {
        try {
            $sql = "SELECT p.*, pt.name as type_name, c.name as canteen_name, s.quantity 
                    FROM products p
                    LEFT JOIN product_types pt ON p.type_id = pt.type_id
                    LEFT JOIN canteens c ON p.canteen_id = c.canteen_id
                    LEFT JOIN stocks s ON p.product_id = s.product_id
                    WHERE p.status = 'available'
                    AND s.quantity > 0
                    ORDER BY p.created_at DESC
                    LIMIT 8";
                    
            $query = $this->db->connect()->prepare($sql);
            $query->execute();
            return $query->fetchAll();
        } catch (PDOException $e) {
            error_log("Error in getFeaturedProducts: " . $e->getMessage());
            return [];
        }
    }

    function searchProductsByCanteen($canteen_id, $keyword = '', $category = '') {
        try {
            $sql = "SELECT p.*, pt.name as category_name, s.quantity, 
                    c.name AS canteen_name 
                    FROM products p
                    LEFT JOIN stocks s ON p.product_id = s.product_id
                    LEFT JOIN product_types pt ON p.type_id = pt.type_id
                    LEFT JOIN canteens c ON p.canteen_id = c.canteen_id
                    WHERE p.canteen_id = :canteen_id";

            if ($keyword) {
                $sql .= " AND (p.name LIKE :keyword OR p.description LIKE :keyword)";
            }

            if ($category) {
                $sql .= " AND p.type_id = :category";
            }

            $query = $this->db->connect()->prepare($sql);
            $query->bindParam(':canteen_id', $canteen_id);

            if ($keyword) {
                $searchKeyword = '%' . $keyword . '%';
                $query->bindParam(':keyword', $searchKeyword);
            }

            if ($category) {
                $query->bindParam(':category', $category);
            }

            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error in searchProductsByCanteen: " . $e->getMessage());
            return [];
        }
    }

    function getCategories() {
        try {
            $sql = "SELECT * FROM product_types ORDER BY name";
            $query = $this->db->connect()->prepare($sql);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching categories: " . $e->getMessage());
            return [];
        }
    }

    function addCategory($name) {
        try {
            $sql = "INSERT INTO product_types (name) VALUES (:name)";
            $query = $this->db->connect()->prepare($sql);
            return $query->execute(['name' => $name]);
        } catch (PDOException $e) {
            error_log("Error adding category: " . $e->getMessage());
            return false;
        }
    }

    function updateCategory($typeId, $name) {
        try {
            $sql = "UPDATE product_types SET name = :name WHERE type_id = :type_id";
            $query = $this->db->connect()->prepare($sql);
            return $query->execute([
                'name' => $name,
                'type_id' => $typeId
            ]);
        } catch (PDOException $e) {
            error_log("Error updating category: " . $e->getMessage());
            return false;
        }
    }

    function deleteCategory($typeId) {
        try {
            $sql = "DELETE FROM product_types WHERE type_id = :type_id";
            $query = $this->db->connect()->prepare($sql);
            return $query->execute(['type_id' => $typeId]);
        } catch (PDOException $e) {
            error_log("Error deleting category: " . $e->getMessage());
            return false;
        }
    }

    function getCategoryName($type_id) {
        try {
            $sql = "SELECT name FROM product_types WHERE type_id = :type_id";
            $query = $this->db->connect()->prepare($sql);
            $query->execute(['type_id' => $type_id]);
            $result = $query->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['name'] : '';
        } catch (PDOException $e) {
            error_log("Error fetching category name: " . $e->getMessage());
            return '';
        }
    }
}
?>
