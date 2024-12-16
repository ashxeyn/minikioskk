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
            $sql = "INSERT INTO products (name, type_id, description, price, canteen_id, status) 
                    VALUES (:name, :type_id, :description, :price, :canteen_id, :status)";
            
            $stmt = $this->db->connect()->prepare($sql);
            
            $stmt->execute([
                'name' => $data['name'],
                'type_id' => $data['type_id'],
                'description' => $data['description'],
                'price' => $data['price'],
                'canteen_id' => $data['canteen_id'],
                'status' => $data['status']
            ]);
            
            $productId = $this->db->connect()->lastInsertId();
            
            if (!$productId) {
                throw new Exception("Failed to get product ID after insertion");
            }
            
            return $productId;
        } catch (PDOException $e) {
            error_log("Error in addProduct: " . $e->getMessage());
            throw new Exception("Failed to add product: " . $e->getMessage());
        }
    }


  

    public function fetchProducts($canteen_id) {
        try {
            $sql = "SELECT p.*, pt.name as type, pt.type as type_category,
                    COALESCE(s.quantity, 0) as stock_quantity
                    FROM products p 
                    LEFT JOIN product_types pt ON p.type_id = pt.type_id
                    LEFT JOIN (
                        SELECT product_id, quantity 
                        FROM stocks 
                        GROUP BY product_id
                    ) s ON p.product_id = s.product_id
                    WHERE p.canteen_id = :canteen_id";
            
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->execute(['canteen_id' => $canteen_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching products: " . $e->getMessage());
            return [];
        }
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

        return 'Unknown Canteen';
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

          
            $sql = "DELETE FROM stocks WHERE product_id = :product_id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':product_id', $product_id);
            $stmt->execute();

          
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

    public function getProducts() {
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
            error_log("Error in getProducts: " . $e->getMessage());
            throw new Exception("Failed to fetch products");
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

            if ($result) {
                $db->commit();
                return true;
            } else {
                $db->rollBack();
                return false;
            }
        } catch (PDOException $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Error in deleteProduct: " . $e->getMessage());
            return false;
        }
    }

    public function isProductOwnedByCanteen($productId, $canteenId) {
        try {
            $sql = "SELECT 1 FROM products WHERE product_id = ? AND canteen_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$productId, $canteenId]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Database Error: " . $e->getMessage());
            throw new Exception("Database error occurred");
        }
    }

    public function getProduct($productId) {
        try {
            $sql = "SELECT p.*, pt.name as type_name, pt.type,
                    COALESCE(s.quantity, 0) as stock_quantity
                    FROM products p
                    LEFT JOIN product_types pt ON p.type_id = pt.type_id
                    LEFT JOIN (
                        SELECT product_id, quantity 
                        FROM stocks 
                        GROUP BY product_id
                    ) s ON p.product_id = s.product_id
                    WHERE p.product_id = :product_id";
            
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->execute(['product_id' => $productId]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getProduct: " . $e->getMessage());
            throw new Exception("Failed to fetch product");
        }
    }

    public function updateProduct($data) {
        try {
            $sql = "UPDATE products 
                    SET name = :name,
                        description = :description,
                        type_id = :type_id,
                        price = :price
                    WHERE product_id = :product_id 
                    AND canteen_id = :canteen_id";

            $stmt = $this->db->connect()->prepare($sql);
            
            return $stmt->execute([
                ':product_id' => $data['product_id'],
                ':name' => $data['name'],
                ':description' => $data['description'],
                ':type_id' => $data['type_id'],
                ':price' => $data['price'],
                ':canteen_id' => $data['canteen_id']
            ]);
        } catch (PDOException $e) {
            error_log("Error updating product: " . $e->getMessage());
            throw $e;
        }
    }

    public function getProductById($productId) {
        try {
            $sql = "SELECT p.*, pt.name as type_name, c.name as canteen_name,
                           s.quantity as stock_quantity 
                    FROM products p 
                    LEFT JOIN product_types pt ON p.type_id = pt.type_id 
                    LEFT JOIN canteens c ON p.canteen_id = c.canteen_id
                    LEFT JOIN stocks s ON p.product_id = s.product_id 
                    WHERE p.product_id = :product_id";
                    
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->execute(['product_id' => $productId]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                return null;
            }
            
            return [
                'product_id' => $result['product_id'],
                'name' => $result['name'],
                'description' => $result['description'],
                'price' => $result['price'],
                'type_id' => $result['type_id'],
                'stock_quantity' => $result['stock_quantity'] ?? 0,
                'type_name' => $result['type_name'],
                'canteen_name' => $result['canteen_name']
            ];
        } catch (PDOException $e) {
            error_log("Error in getProductById: " . $e->getMessage());
            throw new Exception("Failed to fetch product details");
        }
    }
}
?>
