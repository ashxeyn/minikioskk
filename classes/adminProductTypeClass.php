<?php
require_once 'databaseClass.php';

class AdminProductType {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getAllProductTypesAdmin() {
        try {
            $sql = "SELECT 
                    pt.type_id, 
                    pt.name, 
                    pt.type, 
                    c.name as category_name, 
                    c.category_id,
                    CONCAT(pt.name, ' (', c.name, ')') as full_name
                    FROM product_types pt 
                    JOIN categories c ON pt.category_id = c.category_id 
                    ORDER BY c.name, pt.name";
            
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->execute();
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (empty($results)) {
                error_log("No product types found in database");
                return [];
            }
            return $results;
        } catch (PDOException $e) {
            error_log("Error in getAllProductTypesAdmin: " . $e->getMessage());
            throw new Exception("Failed to fetch product types for admin");
        }
    }
}
?> 