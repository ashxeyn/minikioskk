<?php
require_once '../classes/databaseClass.php';

class Canteen
{
    public $name = '';
    public $campus_location = '';
    public $description = '';
    public $opening_time = '';
    public $closing_time = '';
    public $status = 'open';

    protected $db;

    function __construct()
    {
        $this->db = new Database();
    }

    function addCanteen()
    {
        $sql = "INSERT INTO canteens (name, campus_location, description, opening_time, closing_time, status) 
                VALUES (:name, :campus_location, :description, :opening_time, :closing_time, :status)";
        $query = $this->db->connect()->prepare($sql);

        $query->bindParam(':name', $this->name);
        $query->bindParam(':campus_location', $this->campus_location);
        $query->bindParam(':description', $this->description);
        $query->bindParam(':opening_time', $this->opening_time);
        $query->bindParam(':closing_time', $this->closing_time);
        $query->bindParam(':status', $this->status);

        return $query->execute();
    }

    function fetchCanteens()
    {
        $sql = "SELECT * FROM canteens";
        $query = $this->db->connect()->query($sql);

        $canteens = [];
        if ($query) {
            while ($row = $query->fetch()) {
                $canteens[] = [
                    'canteen_id' => $row['canteen_id'],
                    'name' => $row['name'],
                    'campus_location' => $row['campus_location'],
                    'description' => $row['description'],
                    'opening_time' => $row['opening_time'],
                    'closing_time' => $row['closing_time'],
                    'status' => $row['status'],
                    'created_at' => $row['created_at']
                ];
            }
        }

        return $canteens;
    }

    function fetchCanteenById($canteen_id)
    {
        $sql = "SELECT * FROM canteens WHERE canteen_id = :canteen_id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':canteen_id', $canteen_id);
        $query->execute();
        return $query->fetch();
    }

    function editCanteen($canteen_id, $name, $campus_location)
    {
        $sql = "UPDATE canteens SET 
                name = :name, 
                campus_location = :campus_location,
                description = :description,
                opening_time = :opening_time,
                closing_time = :closing_time,
                status = :status 
                WHERE canteen_id = :canteen_id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':name', $name);
        $query->bindParam(':campus_location', $campus_location);
        $query->bindParam(':description', $this->description);
        $query->bindParam(':opening_time', $this->opening_time);
        $query->bindParam(':closing_time', $this->closing_time);
        $query->bindParam(':status', $this->status);
        $query->bindParam(':canteen_id', $canteen_id);
        return $query->execute();
    }

    function deleteCanteen($canteen_id)
    {
        $sql = "DELETE FROM canteens WHERE canteen_id = :canteen_id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':canteen_id', $canteen_id);
        return $query->execute();
    }

    function searchCanteens($keyword = '') {
        try {
            $sql = "SELECT * FROM canteens 
                    WHERE name LIKE :keyword 
                    OR campus_location LIKE :keyword 
                    OR description LIKE :keyword
                    ORDER BY canteen_id DESC";
            
            $query = $this->db->connect()->prepare($sql);
            $searchTerm = "%" . $keyword . "%";
            $query->bindParam(':keyword', $searchTerm);
            $query->execute();
            
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error searching canteens: " . $e->getMessage());
            throw $e;
        }
    }

    function searchCanteensAndProducts($keyword)
    {
        $sql = "SELECT c.canteen_id, c.name AS canteen_name, c.campus_location, p.product_id, p.name AS product_name, p.description, p.price
                FROM canteens c
                LEFT JOIN products p ON c.canteen_id = p.canteen_id
                WHERE c.name LIKE :keyword OR c.campus_location LIKE :keyword OR p.name LIKE :keyword OR p.description LIKE :keyword";
        
        $query = $this->db->connect()->prepare($sql);
        $searchKeyword = '%' . $keyword . '%';
        $query->bindParam(':keyword', $searchKeyword);
        $query->execute();
        
        return $query->fetchAll();
    }  

    function registerCanteen()
    {
        $db = $this->db->connect();

        $sql = "INSERT INTO canteens (name, campus_location, description, opening_time, closing_time, status) 
                VALUES (:name, :campus_location, :description, :opening_time, :closing_time, :status)";
        $query = $db->prepare($sql);

        $query->bindParam(':name', $this->name, PDO::PARAM_STR);
        $query->bindParam(':campus_location', $this->campus_location, PDO::PARAM_STR);
        $query->bindParam(':description', $this->description, PDO::PARAM_STR);
        $query->bindParam(':opening_time', $this->opening_time);
        $query->bindParam(':closing_time', $this->closing_time);
        $query->bindParam(':status', $this->status, PDO::PARAM_STR);

        if ($query->execute()) {
            return $db->lastInsertId();
        } else {
            return false;
        }
    }

    function getAllCanteens()
    {
        $sql = "SELECT * FROM canteens ORDER BY name ASC";
        $stmt = $this->db->connect()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    function isOpen($canteen_id) {
        try {
            $sql = "SELECT status, opening_time, closing_time 
                    FROM canteens 
                    WHERE canteen_id = :canteen_id";
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->execute(['canteen_id' => $canteen_id]);
            $canteen = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$canteen) {
                return false;
            }
            
            if ($canteen['status'] !== 'open') {
                return false;
            }
            
            $current_time = date('H:i:s');
            $opening_time = $canteen['opening_time'];
            $closing_time = $canteen['closing_time'];
            
            return ($current_time >= $opening_time && $current_time <= $closing_time);
        } catch (Exception $e) {
            error_log("Error checking canteen status: " . $e->getMessage());
            return false;
        }
    }
}
?>
