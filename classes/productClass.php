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
        $sql = "SELECT p.product_id, p.name, p.description, p.category, p.price, s.quantity, c.name AS canteen_name
                FROM products p
                LEFT JOIN stocks s ON p.product_id = s.product_id
                LEFT JOIN canteens c ON p.canteen_id = c.canteen_id
                WHERE 1";  
    
        if ($keyword) {
            $sql .= " AND (p.name LIKE :keyword OR c.name LIKE :keyword OR p.description LIKE :keyword)";
        }
    
        if ($category) {
            $sql .= " AND p.category = :category";  
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
    }
    
    
    function fetchProductsByCanteen($canteen_id)
    {
        $sql = "SELECT * FROM products WHERE canteen_id = :canteen_id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':canteen_id', $canteen_id);
        $query->execute();
        return $query->fetchAll();
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

    function addProduct($canteen_id, $name, $category, $description, $price)
    {
        $sql = "INSERT INTO products (canteen_id, name, category, description, price) 
                VALUES (:canteen_id, :name, :category, :description, :price)";
        $query = $this->db->connect()->prepare($sql);

        $query->bindParam(':canteen_id', $canteen_id);
        $query->bindParam(':name', $name);
        $query->bindParam(':category', $category);
        $query->bindParam(':description', $description);
        $query->bindParam(':price', $price);

        return $query->execute();
    }

    function updateProduct($product_id, $name, $description, $category, $price) {
        $sql = "UPDATE products 
                SET name = :name, description = :description, category = :category, price = :price 
                WHERE product_id = :product_id";
        $query = $this->db->connect()->prepare($sql);

        $query->bindParam(':product_id', $product_id);
        $query->bindParam(':name', $name);
        $query->bindParam(':description', $description);
        $query->bindParam(':category', $category);
        $query->bindParam(':price', $price);

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

            // Delete from order_items first
            $sql = "DELETE FROM order_items WHERE product_id = :product_id";
            $query = $db->prepare($sql);
            $query->bindParam(':product_id', $product_id);
            $query->execute();

            // Delete from stocks
            $sql = "DELETE FROM stocks WHERE product_id = :product_id";
            $query = $db->prepare($sql);
            $query->bindParam(':product_id', $product_id);
            $query->execute();

            // Finally delete the product
            $sql = "DELETE FROM products WHERE product_id = :product_id";
            $query = $db->prepare($sql);
            $query->bindParam(':product_id', $product_id);
            $result = $query->execute();

            if ($result) {
                $db->commit();
                return true;
            } else {
                $db->rollBack();
                return false;
            }
        } catch (Exception $e) {
            if (isset($db)) {
                $db->rollBack();
            }
            error_log("Error deleting product: " . $e->getMessage());
            return false;
        }
    }

    function getFeaturedProducts()
    {
        $sql = "SELECT p.*, c.name as canteen_name 
                FROM products p 
                LEFT JOIN canteens c ON p.canteen_id = c.canteen_id 
                LEFT JOIN stocks s ON p.product_id = s.product_id 
                WHERE s.status = 'In Stock' AND s.quantity > 0 
                ORDER BY RAND() 
                LIMIT 8";
        
        $stmt = $this->db->connect()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    function searchProductsByCanteen($canteen_id, $keyword = '', $category = '') {
        $sql = "SELECT p.product_id, p.name, p.description, p.category, p.price, s.quantity, c.name AS canteen_name
                FROM products p
                LEFT JOIN stocks s ON p.product_id = s.product_id
                LEFT JOIN canteens c ON p.canteen_id = c.canteen_id
                WHERE p.canteen_id = :canteen_id";

        if ($keyword) {
            $sql .= " AND (p.name LIKE :keyword OR p.description LIKE :keyword)";
        }

        if ($category) {
            $sql .= " AND p.category = :category";
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
}
?>
