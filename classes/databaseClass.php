<?php 
class Database{

    private $host = "localhost";
    private $database = "minikiosk1";
    private $username = "root";
    private $password = "";
    private $conn = null;

    public function connect() {
        try {
            if ($this->conn === null) {
                $this->conn = new PDO(
                    "mysql:host=" . $this->host . ";dbname=" . $this->database,
                    $this->username,
                    $this->password,
                    array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
                );
            }
            return $this->conn;
        } catch(PDOException $e) {
            error_log("Connection Error: " . $e->getMessage());
            return false;
        }
    }

    public function prepare($sql) {
        $conn = $this->connect();
        if ($conn === false) {
            throw new Exception("Database connection failed");
        }
        return $conn->prepare($sql);
    }

    public function getConnection() {
        $conn = $this->connect();
        if ($conn === false) {
            throw new Exception("Database connection failed");
        }
        return $conn;
    }
}
//$objdb = new Database;
//$objdb->connect();
?>