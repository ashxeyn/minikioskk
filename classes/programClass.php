<?php
require_once '../classes/databaseClass.php';

class Program
{
    public $program_name = '';
    public $department = '';
    public $college = '';

    protected $db;

    function __construct()
    {
        $this->db = new Database();
    }

    function getOrCreateDepartment($department_name, $college_name) {
        try {
            $conn = $this->db->connect();
            
            // First check if college exists
            $sql = "SELECT college_id FROM colleges WHERE college_name = :college_name";
            $stmt = $conn->prepare($sql);
            $stmt->execute(['college_name' => $college_name]);
            $college = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$college) {
                // Create new college
                $sql = "INSERT INTO colleges (college_name) VALUES (:college_name)";
                $stmt = $conn->prepare($sql);
                $stmt->execute(['college_name' => $college_name]);
                $college_id = $conn->lastInsertId();
            } else {
                $college_id = $college['college_id'];
            }
            
            // Check if department exists
            $sql = "SELECT department_id FROM departments 
                    WHERE department_name = :department_name 
                    AND college_id = :college_id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                'department_name' => $department_name,
                'college_id' => $college_id
            ]);
            $department = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$department) {
                // Create new department
                $sql = "INSERT INTO departments (department_name, college_id) 
                        VALUES (:department_name, :college_id)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    'department_name' => $department_name,
                    'college_id' => $college_id
                ]);
                return $conn->lastInsertId();
            }
            
            return $department['department_id'];
            
        } catch (PDOException $e) {
            error_log("Error in getOrCreateDepartment: " . $e->getMessage());
            return false;
        }
    }

    function addProgram($program_name, $department_id, $description)
    {
        try {
            $sql = "INSERT INTO programs (program_name, department_id, description) 
                    VALUES (:program_name, :department_id, :description)";
            $stmt = $this->db->connect()->prepare($sql);
            return $stmt->execute([
                'program_name' => $program_name,
                'department_id' => $department_id,
                'description' => $description
            ]);
        } catch (PDOException $e) {
            error_log("Error adding program: " . $e->getMessage());
            return false;
        }
    }

   
    function fetchProgramById($program_id)
    {
        try {
            $sql = "SELECT p.*, d.department_name, d.department_id, c.college_name, c.college_id 
                    FROM programs p
                    JOIN departments d ON p.department_id = d.department_id
                    JOIN colleges c ON d.college_id = c.college_id
                    WHERE p.program_id = :program_id";
            $query = $this->db->connect()->prepare($sql);
            $query->bindParam(':program_id', $program_id);
            $query->execute();
            return $query->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching program: " . $e->getMessage());
            return false;
        }
    }

    
    function updateProgram($program_id, $program_name, $department_id, $description)
    {
        $sql = "UPDATE programs 
                SET program_name = :program_name, 
                    department_id = :department_id, 
                    description = :description
                WHERE program_id = :program_id"; 
        $query = $this->db->connect()->prepare($sql);
    
        $query->bindParam(':program_name', $program_name);
        $query->bindParam(':department_id', $department_id);
        $query->bindParam(':description', $description);
        $query->bindParam(':program_id', $program_id);
    
        return $query->execute(); 
    }
    

   
    function deleteProgram($program_id)
    {
        $sql = "DELETE FROM programs WHERE program_id = :program_id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':program_id', $program_id); 
        return $query->execute(); 
    }

    
    function fetchPrograms()
    {
        $sql = "SELECT p.*, d.department_name, c.college_name 
                FROM programs p
                JOIN departments d ON p.department_id = d.department_id
                JOIN colleges c ON d.college_id = c.college_id";
        $query = $this->db->connect()->prepare($sql);
        $query->execute();
        return $query->fetchAll();
    }

    function searchPrograms($keyword = '') {
        try {
            $sql = "SELECT * FROM programs 
                    WHERE program_name LIKE :keyword 
                    OR department LIKE :keyword 
                    OR college LIKE :keyword 
                    ORDER BY program_name ASC";
            
            $query = $this->db->connect()->prepare($sql);
            $searchTerm = "%" . $keyword . "%";
            $query->bindParam(':keyword', $searchTerm);
            $query->execute();
            
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error searching programs: " . $e->getMessage());
            throw $e;
        }
    }    

    function fetchColleges() {
        try {
            $sql = "SELECT college_id, college_name FROM colleges ORDER BY college_name";
            $query = $this->db->connect()->prepare($sql);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching colleges: " . $e->getMessage());
            return [];
        }
    }
}
?>
