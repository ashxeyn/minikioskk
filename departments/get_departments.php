<?php
require_once '../classes/databaseClass.php';

class Departments extends Database {
    public function getDepartments() {
        try {
            $conn = $this->connect();
            $sql = "SELECT d.department_id, d.department_name, c.college_name 
                   FROM departments d 
                   INNER JOIN colleges c ON d.college_id = c.college_id";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching departments: " . $e->getMessage());
            return false;
        }
    }
}

$departments = new Departments();
$result = $departments->getDepartments();
echo json_encode($result); 