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
        try {
            $conn = $this->db->connect();
            
            $checkSql = "SELECT canteen_id FROM canteens WHERE name = :name";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->execute(['name' => $this->name]);
            
            if ($checkStmt->fetch()) {
                return false; // Canteen name already exists
            }
            
            $sql = "INSERT INTO canteens (name, campus_location) VALUES (:name, :campus_location)";
            $stmt = $conn->prepare($sql);
            
            $result = $stmt->execute([
                'name' => $this->name,
                'campus_location' => $this->campus_location
            ]);

            return $result ? $conn->lastInsertId() : false;
            
        } catch (PDOException $e) {
            error_log("Error in addCanteen: " . $e->getMessage());
            return false;
        }
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
                    
                    'created_at' => $row['created_at']
                ];
            }
        }

        return $canteens;
    }

    public function fetchCanteenById($canteen_id)
    {
        try {
            $sql = "SELECT * FROM canteens WHERE canteen_id = :canteen_id";
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->execute(['canteen_id' => $canteen_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching canteen: " . $e->getMessage());
            return false;
        }
    }

    public function editCanteen($canteen_id, $name, $campus_location)
    {
        try {
            $sql = "UPDATE canteens 
                    SET name = :name, 
                        campus_location = :campus_location 
                    WHERE canteen_id = :canteen_id";
                    
            $stmt = $this->db->connect()->prepare($sql);
            $result = $stmt->execute([
                'canteen_id' => $canteen_id,
                'name' => $name,
                'campus_location' => $campus_location
            ]);
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error updating canteen: " . $e->getMessage());
            return false;
        }
    }

    public function deleteCanteen($canteen_id)
    {
        try {
            $sql = "DELETE FROM canteens WHERE canteen_id = :canteen_id";
            $stmt = $this->db->connect()->prepare($sql);
            return $stmt->execute(['canteen_id' => $canteen_id]);
        } catch (PDOException $e) {
            error_log("Error deleting canteen: " . $e->getMessage());
            return false;
        }
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

    public function searchCanteensAndProducts($keyword = '', $search_type = 'all') {
        try {
            $db = $this->db->connect();
            $searchTerm = "%" . $keyword . "%";
            $canteens = [];
            $products = [];
            
            if ($search_type === 'all' || $search_type === 'canteens') {
                $sql = "SELECT canteen_id, name, campus_location, description, 
                               opening_time, closing_time, status, created_at 
                        FROM canteens 
                        WHERE (name LIKE :keyword 
                        OR campus_location LIKE :keyword 
                        OR description LIKE :keyword)
                        AND status != 'maintenance'
                        ORDER BY name ASC";

                $stmt = $db->prepare($sql);
                $stmt->bindParam(':keyword', $searchTerm);
                $stmt->execute();
                $canteens = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            
            if ($search_type === 'all' || $search_type === 'menu') {
                $sql = "SELECT p.*, c.name as canteen_name, c.status as canteen_status
                        FROM products p 
                        JOIN canteens c ON p.canteen_id = c.canteen_id 
                        WHERE (p.name LIKE :keyword 
                        OR p.description LIKE :keyword)
                        AND c.status != 'maintenance'
                        ORDER BY p.name ASC";

                $stmt = $db->prepare($sql);
                $stmt->bindParam(':keyword', $searchTerm);
                $stmt->execute();
                $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            return [
                'canteens' => $canteens,
                'products' => $products
            ];

        } catch (PDOException $e) {
            error_log("Error in searchCanteensAndProducts: " . $e->getMessage());
            return ['canteens' => [], 'products' => []];
        }
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

    public function getAllCanteens() {
        try {
            $sql = "SELECT canteen_id, name, campus_location, status, 
                    DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') as created_at 
                    FROM canteens 
                    ORDER BY created_at DESC";
            
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getAllCanteens: " . $e->getMessage());
            return [];
        }
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

    public function getCanteens() {
        try {
            $sql = "SELECT canteen_id, name, campus_location FROM canteens";
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if ($result === false) {
                error_log("No results found in getCanteens()");
                return [];
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error in getCanteens(): " . $e->getMessage());
            return false;
        }
    }
}
?>
