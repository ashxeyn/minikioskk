<?php
require_once '../classes/databaseClass.php';

class College {
    protected $db;

    function __construct() {
        $this->db = new Database();
    }

    function getColleges() {
        try {
            $sql = "SELECT college_id, college_name, abbreviation 
                    FROM colleges 
                    ORDER BY college_name";
            $query = $this->db->connect()->prepare($sql);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching colleges: " . $e->getMessage());
            return [];
        }
    }
}

$college = new College();
echo json_encode($college->getColleges());
?> 