<?php
require_once '../classes/databaseClass.php';

class Department {
    protected $db;

    function __construct() {
        $this->db = new Database();
    }

    function getDepartments($college_id = null) {
        try {
            $sql = "SELECT d.department_id, d.department_name, c.college_name, c.college_id 
                    FROM departments d
                    JOIN colleges c ON d.college_id = c.college_id";
            
            if ($college_id) {
                $sql .= " WHERE d.college_id = :college_id";
            }
            
            $sql .= " ORDER BY c.college_name, d.department_name";
            
            $query = $this->db->connect()->prepare($sql);
            
            if ($college_id) {
                $query->bindParam(':college_id', $college_id);
            }
            
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching departments: " . $e->getMessage());
            return [];
        }
    }
}

$dept = new Department();
$college_id = isset($_GET['college_id']) ? $_GET['college_id'] : null;
echo json_encode($dept->getDepartments($college_id));
?> 