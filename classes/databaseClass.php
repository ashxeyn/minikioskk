<?php 
class Database{

    private $host = 'localhost';
    private $dbname = 'minikiosk';
    private $username = 'root';
    private $password = '';
    private $conn = null;

    public function connect() {
        try {
            if ($this->conn === null) {
                $this->conn = new PDO(
                    "mysql:host=$this->host;dbname=$this->dbname",
                    $this->username,
                    $this->password
                );
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
            return $this->conn;
        } catch(PDOException $e) {
            error_log("Connection Error: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }
    }

    public function prepare($sql) {
        return $this->connect()->prepare($sql);
    }
}
//$objdb = new Database;
//$objdb->connect();
?>