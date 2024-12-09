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

    function addProgram($program_name, $department, $college)
    {
        $sql = "INSERT INTO programs (program_name, department, college) VALUES (:program_name, :department, :college)";
        $query = $this->db->connect()->prepare($sql);

        $query->bindParam(':program_name', $program_name);
        $query->bindParam(':department', $department);
        $query->bindParam(':college', $college);

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

    
    function updateProgram($program_id, $program_name, $department, $college)
    {
        $sql = "UPDATE programs 
                SET program_name = :program_name, 
                    department = :department, 
                    college = :college
                WHERE program_id = :program_id"; 
        $query = $this->db->connect()->prepare($sql);
    
        $query->bindParam(':program_name', $program_name);
        $query->bindParam(':department', $department);
        $query->bindParam(':college', $college); 
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
        $sql = "SELECT * FROM programs";
        $query = $this->db->connect()->prepare($sql);
        $query->execute();

        return $query->fetchAll();
    }

    function searchPrograms($keyword = '') {
        $sql = "SELECT * FROM programs 
                WHERE program_name LIKE :keyword 
                OR department LIKE :keyword";
        
        $query = $this->db->connect()->prepare($sql);
        $query->execute([':keyword' => '%' . $keyword . '%']);
        
        return $query->fetchAll();
    }    
}
?>
