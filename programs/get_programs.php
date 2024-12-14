<?php
require_once '../classes/databaseClass.php';

class Programs extends Database {
    public function getPrograms() {
        try {
            $conn = $this->connect();
            $sql = "SELECT p.program_id, p.program_name, p.department_id 
                   FROM programs p 
                   INNER JOIN departments d ON p.department_id = d.department_id";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching programs: " . $e->getMessage());
            return false;
        }
    }
}

$programs = new Programs();
$result = $programs->getPrograms();
echo json_encode($result);
?> 