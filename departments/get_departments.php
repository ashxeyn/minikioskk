<?php
session_start();
require_once '../classes/databaseClass.php';

class Departments extends Database {
    public function getDepartments() {
        try {
            if (!isset($_SESSION['canteen_id'])) {
                throw new Exception('Canteen ID not found in session');
            }
            
            $conn = $this->connect();
            $sql = "SELECT d.department_id, d.department_name, c.college_name 
                   FROM departments d 
                   INNER JOIN colleges c ON d.college_id = c.college_id 
                   WHERE d.canteen_id = :canteen_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':canteen_id', $_SESSION['canteen_id']);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching departments: " . $e->getMessage());
            return ['error' => 'Database error occurred'];
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}

$departments = new Departments();
$result = $departments->getDepartments();
header('Content-Type: application/json');
echo json_encode($result); 