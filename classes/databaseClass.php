<?php 
class Database{

    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $database = "minikiosk2";
    private $conn = null;

    public function connect() {
        try {
            if ($this->conn === null) {
                $this->conn = new PDO(
                    "mysql:host=" . $this->host . ";dbname=" . $this->database,
                    $this->username,
                    $this->password
                );
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
            return $this->conn;
        } catch(PDOException $e) {
            error_log("Connection error: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }
    }

    public function prepare($sql) {
        return $this->connect()->prepare($sql);
    }

    public function getConnection() {
        return $this->connect();
    }
}
//$objdb = new Database;
//$objdb->connect();
?>