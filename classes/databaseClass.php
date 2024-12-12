<?php 
class Database{

    private $host = 'localhost';
    private $username = 'root';
    private $password = '';
    private $database = 'minikiosk1';
    private $connection = null;

    public function connect() {
        try {
            if ($this->connection === null) {
                $this->connection = new PDO(
                    "mysql:host=$this->host;dbname=$this->database",
                    $this->username,
                    $this->password
                );
                $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
            return $this->connection;
        } catch(PDOException $e) {
            echo "Connection error: " . $e->getMessage();
            exit;
        }
    }
}
//$objdb = new Database;
//$objdb->connect();
?>