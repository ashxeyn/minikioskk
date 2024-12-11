<?php
require_once '../classes/databaseClass.php';

class Canteen
{
    public $name = '';
    public $campus_location = '';

    protected $db;

    function __construct()
    {
        $this->db = new Database();
    }

    function addCanteen()
    {
        $sql = "INSERT INTO Canteens (name, campus_location) VALUES (:name, :campus_location)";
        $query = $this->db->connect()->prepare($sql);

        $query->bindParam(':name', $this->name);
        $query->bindParam(':campus_location', $this->campus_location);

        return $query->execute();
    }

    function fetchCanteens()
    {
        $sql = "SELECT * FROM Canteens";
        $query = $this->db->connect()->query($sql);

        $canteens = [];
        if ($query) {
            while ($row = $query->fetch()) {
                $canteens[] = [
                    'canteen_id' => $row['canteen_id'],
                    'name' => $row['name'],
                    'campus_location' => $row['campus_location']
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
        $sql = "UPDATE Canteens SET name = :name, campus_location = :campus_location WHERE canteen_id = :canteen_id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':name', $name);
        $query->bindParam(':campus_location', $campus_location);
        $query->bindParam(':canteen_id', $canteen_id);
        return $query->execute();
    }

    function deleteCanteen($canteen_id)
    {
        $sql = "DELETE FROM Canteens WHERE canteen_id = :canteen_id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':canteen_id', $canteen_id);
        return $query->execute();
    }

    function searchCanteens($keyword = '') {
        try {
            $sql = "SELECT * FROM canteens 
                    WHERE name LIKE :keyword 
                    OR campus_location LIKE :keyword 
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

    $sql = "INSERT INTO Canteens (name, campus_location) VALUES (:name, :campus_location)";
    $query = $db->prepare($sql);

    $query->bindParam(':name', $this->name, PDO::PARAM_STR);
    $query->bindParam(':campus_location', $this->campus_location, PDO::PARAM_STR);

    if ($query->execute()) {
        return $db->lastInsertId(); // Ensure we use the same $db connection
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

}
?>
