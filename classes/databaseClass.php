<?php 
class Database{

    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $database = "minikiosk2";
    private $conn = null;

    public function connect() {
        try {
            $conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->database,
                $this->username,
                $this->password
            );
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conn;
        } catch(PDOException $e) {
            error_log("Connection Error: " . $e->getMessage());
            return false;
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