<?php
require_once '../classes/databaseClass.php';

class Department {
    protected $db;

    function __construct() {
        $this->db = new Database();
    }

    function getDepartments() {
        $sql = "SELECT d.department_id, d.department_name, c.college_name 
                FROM departments d
                JOIN colleges c ON d.college_id = c.college_id
                ORDER BY c.college_name, d.department_name";
        $query = $this->db->connect()->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
}

$dept = new Department();
echo json_encode($dept->getDepartments());
?> 