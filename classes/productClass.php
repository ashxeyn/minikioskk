<?php
require_once '../classes/databaseClass.php';

class Product
{
    public $db;

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

    function addProduct($data) {
        try {
            $sql = "INSERT INTO products (canteen_id, type_id, name, description, price, status) 
                    VALUES (:canteen_id, :type_id, :name, :description, :price, :status)";
            
            $stmt = $this->db->connect()->prepare($sql);
            
            $result = $stmt->execute([
                'canteen_id' => $data['canteen_id'],
                'type_id' => $data['type_id'],
                'name' => $data['name'],
                'description' => $data['description'],
                'price' => $data['price'],
                'status' => $data['status']
            ]);

            if ($result) {
                return $this->db->connect()->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error adding product: " . $e->getMessage());
            throw new Exception("Error adding product to database");
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
        try {
            $sql = "SELECT p.product_id, p.name, p.description, p.category, p.price, p.availability, 
                           s.quantity, c.name AS canteen_name
                    FROM products p
                    LEFT JOIN stocks s ON p.product_id = s.product_id
                    LEFT JOIN canteens c ON p.canteen_id = c.canteen_id
                    WHERE p.canteen_id = :canteen_id 
                    AND p.availability = 1";
                    
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->bindParam(':canteen_id', $canteen_id);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error in getProductsByCanteen: " . $e->getMessage());
            return [];
        }
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
        $sql = "SELECT p.product_id, p.name, p.description, p.type_id, p.price, 
                s.quantity, s.updated_at, c.name AS canteen_name
                FROM products p
                LEFT JOIN stocks s ON p.product_id = s.product_id
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
    }

    function getCategories() {
        try {
            $sql = "SELECT type_id, name FROM product_types WHERE status = 'active'";
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching categories: " . $e->getMessage());
            throw new Exception("Error fetching categories");
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

    function fetchAllProductsWithCanteens() {
        $sql = "SELECT p.*, c.name as canteen_name 
                FROM products p 
                JOIN canteens c ON p.canteen_id = c.canteen_id 
                ORDER BY c.name, p.name";
        
        $query = $this->db->connect()->prepare($sql);
        $query->execute();
        return $query->fetchAll();
    }

    public function getConnection() {
        return $this->db->connect();
    }
}
?>
