<?php
require_once 'databaseClass.php';

class ProductType {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function fetchAllTypes() {
        try {
            $sql = "SELECT pt.type_id, pt.name, pt.type, pt.description, c.name as category_name 
                    FROM product_types pt 
                    JOIN categories c ON pt.category_id = c.category_id 
                    ORDER BY c.name, pt.name";
            
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching product types: " . $e->getMessage());
            return [];
        }
    }

    public function fetchTypeById($typeId) {
        try {
            $sql = "SELECT pt.*, c.name as category_name 
                    FROM product_types pt 
                    JOIN categories c ON pt.category_id = c.category_id 
                    WHERE pt.type_id = :type_id";
            
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->execute(['type_id' => $typeId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching product type: " . $e->getMessage());
            return null;
        }
    }

    public function fetchTypesByCategory($categoryId) {
        try {
            $sql = "SELECT * FROM product_types WHERE category_id = :category_id ORDER BY name";
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->execute(['category_id' => $categoryId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching types by category: " . $e->getMessage());
            return [];
        }
    }
}
?> 