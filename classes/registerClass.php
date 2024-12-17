<?php
require_once '../classes/databaseClass.php';

class RegisterAccount {
   
    public $user_id;
    public $last_name;
    public $given_name;
    public $middle_name;
    public $email;
    public $username;
    public $password;
    protected $db;
    public function __construct() {
        $this->db = new Database();
    }
    public function addUser() {
        try {
            $conn = $this->db->connect();
            $conn->beginTransaction();

            if ($this->emailExist($this->email)) {
                $_SESSION['error'] = "Email already exists";
                return false;
            }
            if ($this->usernameExist($this->username)) {
                $_SESSION['error'] = "Username already exists";
                return false;
            }

            $hashedPassword = password_hash($this->password, PASSWORD_DEFAULT);
         
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
            
            $stmt->execute($params);
            $userId = $conn->lastInsertId();
            
            if ($userId) {
                $conn->commit();
                $this->user_id = $userId;
                return $userId;
            }
            
            $conn->rollBack();
            return false;

        } catch (PDOException $e) {
            if (isset($conn)) {
                $conn->rollBack();
            }
            error_log("Error registering user: " . $e->getMessage());
            $_SESSION['error'] = "Database error occurred: " . $e->getMessage();
            return false;
        }
    }

  
    public function addPendingManager($canteen_id) {
        try {
            $conn = $this->db->connect();
            
         
            $checkSql = "SELECT COUNT(*) FROM managers WHERE user_id = :user_id";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->execute([':user_id' => $this->user_id]);
            
            if ($checkStmt->fetchColumn() > 0) {
                throw new Exception("Manager record already exists for this user");
            }
          
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