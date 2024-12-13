<?php
require_once '../classes/databaseClass.php';

class RegisterAccount {
    // Properties
    public $user_id;
    public $last_name;
    public $given_name;
    public $middle_name;
    public $email;
    public $username;
    public $password;
    protected $db;

    // Constructor
    public function __construct() {
        $this->db = new Database();
    }

    // Add user to database and return user_id
    public function addUser() {
        try {
            // First check if username already exists
            if ($this->usernameExist($this->username)) {
                throw new Exception("Username already exists");
            }

            // Check if email already exists
            if ($this->emailExist($this->email)) {
                throw new Exception("Email already exists");
            }

            $conn = $this->db->connect();
            $conn->beginTransaction();

            $hashedPassword = password_hash($this->password, PASSWORD_DEFAULT);
            
            // Insert into users table with all required fields
            $sql = "INSERT INTO users (last_name, given_name, middle_name, email, username, 
                    password, role, status) 
                    VALUES (:last_name, :given_name, :middle_name, :email, :username, 
                    :password, :role, :status)";
            
            $stmt = $conn->prepare($sql);
            $params = [
                ':last_name' => $this->last_name,
                ':given_name' => $this->given_name,
                ':middle_name' => $this->middle_name,
                ':email' => $this->email,
                ':username' => $this->username,
                ':password' => $hashedPassword,
                ':role' => 'manager',
                ':status' => 'pending'
            ];
            
            if ($stmt->execute($params)) {
                $this->user_id = $conn->lastInsertId();
                $conn->commit();
                return $this->user_id;
            }
            
            $conn->rollBack();
            return false;

        } catch (PDOException $e) {
            if (isset($conn)) {
                $conn->rollBack();
            }
            error_log("Error registering user: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            return false;
        }
    }

    // Add pending manager record
    public function addPendingManager($canteen_id) {
        try {
            $conn = $this->db->connect();
            
            // First check if this user_id already exists in managers table
            $checkSql = "SELECT COUNT(*) FROM managers WHERE user_id = :user_id";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->execute([':user_id' => $this->user_id]);
            
            if ($checkStmt->fetchColumn() > 0) {
                throw new Exception("Manager record already exists for this user");
            }
            
            // Get current date for start_date
            $currentDate = date('Y-m-d');
            
            $sql = "INSERT INTO managers (user_id, canteen_id, start_date, status) 
                    VALUES (:user_id, :canteen_id, :start_date, 'pending')";
            
            $stmt = $conn->prepare($sql);
            $params = [
                ':user_id' => $this->user_id,
                ':canteen_id' => $canteen_id,
                ':start_date' => $currentDate
            ];
            
            if ($stmt->execute($params)) {
                return true;
            }
            return false;

        } catch (PDOException $e) {
            error_log("Error adding manager record: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            error_log("Manager record error: " . $e->getMessage());
            return false;
        }
    }

    // Check if email exists
    public function emailExist($email) {
        try {
            $sql = "SELECT COUNT(*) FROM users WHERE email = :email";
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error checking email: " . $e->getMessage());
            return false;
        }
    }

    // Check if username exists
    public function usernameExist($username) {
        try {
            $sql = "SELECT COUNT(*) FROM users WHERE username = :username";
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error checking username: " . $e->getMessage());
            return false;
        }
    }
}
?> 