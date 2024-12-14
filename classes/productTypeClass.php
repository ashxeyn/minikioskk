<?php
require_once 'databaseClass.php';

class ProductType {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getAllProductTypes() {
        try {
            $sql = "SELECT pt.type_id, pt.name, pt.type, c.name as category_name 
                    FROM product_types pt 
                    JOIN categories c ON pt.category_id = c.category_id 
                    ORDER BY c.name, pt.name";
            
            error_log("Executing SQL: " . $sql);
            
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->execute();
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Query results: " . print_r($results, true));
            
            return $results;
        } catch (PDOException $e) {
            error_log("Error in getAllProductTypes: " . $e->getMessage());
            throw new Exception("Failed to fetch product types");
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
            throw new Exception("Failed to fetch product type");
        }
    }
}
?> 