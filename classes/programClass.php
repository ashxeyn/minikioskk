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

    function addProgram($program_name, $department_id, $description)
    {
        $sql = "INSERT INTO programs (program_name, department_id, description) 
                VALUES (:program_name, :department_id, :description)";
        $query = $this->db->connect()->prepare($sql);

        $query->bindParam(':program_name', $program_name);
        $query->bindParam(':department_id', $department_id);
        $query->bindParam(':description', $description);

        return $query->execute();  
    }

   
    function fetchProgramById($program_id)
    {
        $sql = "SELECT * FROM programs WHERE program_id = :program_id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':program_id', $program_id);
        $query->execute();
        return $query->fetch();
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
}
?>
