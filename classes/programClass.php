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
            
            
            $sql = "SELECT college_id FROM colleges WHERE college_name = :college_name";
            $stmt = $conn->prepare($sql);
            $stmt->execute(['college_name' => $college_name]);
            $college = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$college) {
               
                $sql = "INSERT INTO colleges (college_name) VALUES (:college_name)";
                $stmt = $conn->prepare($sql);
                $stmt->execute(['college_name' => $college_name]);
                $college_id = $conn->lastInsertId();
            } else {
                $college_id = $college['college_id'];
            }
            
         
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
            $result = $stmt->execute([
                'program_name' => $program_name,
                'department_id' => $department_id,
                'description' => $description
            ]);
            return $result;
        } catch (PDOException $e) {
            error_log("Error adding program: " . $e->getMessage());
            return false;
        }
    }

   
    function fetchProgramById($programId) {
        try {
            $sql = "SELECT p.*, d.department_id, d.college_id, d.department_name, c.college_name 
                    FROM programs p 
                    JOIN departments d ON p.department_id = d.department_id 
                    JOIN colleges c ON d.college_id = c.college_id 
                    WHERE p.program_id = :program_id";
            
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->execute(['program_id' => $programId]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$result) {
                throw new Exception("Program not found");
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error in fetchProgramById: " . $e->getMessage());
            return false;
        }
    }

    
    function updateProgram($program_id, $program_name, $department_id, $description)
    {
        try {
            $sql = "UPDATE programs 
                    SET program_name = :program_name, 
                        department_id = :department_id, 
                        description = :description
                    WHERE program_id = :program_id";
            
            $query = $this->db->connect()->prepare($sql);
            
            $result = $query->execute([
                'program_name' => $program_name,
                'department_id' => $department_id,
                'description' => $description,
                'program_id' => $program_id
            ]);
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error in updateProgram: " . $e->getMessage());
            return false;
        }
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
        try {
            $sql = "SELECT p.*, d.department_name, c.college_name 
                    FROM programs p
                    JOIN departments d ON p.department_id = d.department_id
                    JOIN colleges c ON d.college_id = c.college_id
                    ORDER BY p.program_name";
            $query = $this->db->connect()->prepare($sql);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching programs: " . $e->getMessage());
            return [];
        }
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
            $sql = "SELECT * FROM colleges ORDER BY college_name";
            $query = $this->db->connect()->prepare($sql);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching colleges: " . $e->getMessage());
            return [];
        }
    }

    function addCollege($college_name, $abbreviation, $description = null) {
        try {
            $sql = "INSERT INTO colleges (college_name, abbreviation, description) 
                    VALUES (:college_name, :abbreviation, :description)";
            $stmt = $this->db->connect()->prepare($sql);
            $result = $stmt->execute([
                'college_name' => $college_name,
                'abbreviation' => $abbreviation,
                'description' => $description
            ]);
            return $result;
        } catch (PDOException $e) {
            error_log("Error adding college: " . $e->getMessage());
            return false;
        }
    }

    function addDepartment($college_id, $department_name, $description = null) {
        try {
            $sql = "INSERT INTO departments (college_id, department_name, description) 
                    VALUES (:college_id, :department_name, :description)";
            $stmt = $this->db->connect()->prepare($sql);
            $result = $stmt->execute([
                'college_id' => $college_id,
                'department_name' => $department_name,
                'description' => $description
            ]);
            return $result;
        } catch (PDOException $e) {
            error_log("Error adding department: " . $e->getMessage());
            return false;
        }
    }

    function fetchAllDepartments() {
        try {
            $sql = "SELECT d.*, c.college_name 
                    FROM departments d
                    JOIN colleges c ON d.college_id = c.college_id
                    ORDER BY c.college_name, d.department_name";
            $query = $this->db->connect()->prepare($sql);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching all departments: " . $e->getMessage());
            return [];
        }
    }
}
?>
