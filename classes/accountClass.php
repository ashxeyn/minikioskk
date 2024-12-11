<?php
require_once '../classes/databaseClass.php';

class Account
{
    public $user_id = '';
    public $last_name = '';
    public $given_name = '';
    public $middle_name = '';
    public $email = '';
    public $password = '';
    public $role = '';
    public $username = '';
    public $program_id = null;

    protected $db;

    function __construct()
    {
        $this->db = new Database();
    }

    function UserInfo() {
        $sql = "SELECT * FROM Users WHERE user_id = :user_id";
        $query = $this->db->connect()->prepare($sql);
    
        $query->bindParam(':user_id', $this->user_id);
    
        if ($query->execute()) {
            return $query->fetch();
        } else {
            return null; 
        }
    }
    

    function signup($last_name, $given_name, $middle_name, $email, $username, $password, $is_student = 0, $is_employee = 0, $is_guest = 0, $program_id = null) {
        try {
            $conn = $this->db->connect();
            
            // Start transaction
            $conn->beginTransaction();

            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Validate email domain for students and employees
            if (($is_student || $is_employee) && !preg_match('/@wmsu\.edu\.ph$/', $email)) {
                throw new Exception("WMSU email domain required for students and employees");
            }

            // For guests, email is optional
            if ($is_guest && empty($email)) {
                $email = null;
            }

            // Insert into users table
            $sql = "INSERT INTO users (last_name, given_name, middle_name, email, username, password, 
                    is_student, is_employee, is_guest, program_id) 
                    VALUES (:last_name, :given_name, :middle_name, :email, :username, :password,
                    :is_student, :is_employee, :is_guest, :program_id)";
            
            $query = $conn->prepare($sql);
            
            $params = [
                ':last_name' => $last_name,
                ':given_name' => $given_name,
                ':middle_name' => $middle_name,
                ':email' => $email,
                ':username' => $username,
                ':password' => $hashed_password,
                ':is_student' => $is_student,
                ':is_employee' => $is_employee,
                ':is_guest' => $is_guest,
                ':program_id' => $program_id
            ];

            if ($query->execute($params)) {
                $this->user_id = $conn->lastInsertId();
                $conn->commit();
                return true;
            }

            $conn->rollBack();
            return false;

        } catch (Exception $e) {
            if (isset($conn)) {
                $conn->rollBack();
            }
            error_log("Signup error: " . $e->getMessage());
            throw $e;
        }
    }
    

    function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) && preg_match('/@wmsu\.edu\.ph$/', $email);
    }

    public function emailExist($email) {
        $sql = "SELECT COUNT(*) FROM Users WHERE email = :email";
        $query = $this->db->connect()->prepare($sql);
    
        $query->bindParam(':email', $email);
    
        $query->execute();
        
        $count = $query->fetchColumn();
        
        return $count > 0; 
    }
    
    
    function fetchPrograms()
    {
        $sql = "SELECT program_id, program_name FROM Programs";
        $query = $this->db->connect()->query($sql);
        return $query->fetchAll();
    }

    function usernameExist($username, $excludeID=null)
    {
        $sql = "SELECT COUNT(*) FROM Users WHERE username = :username";
        if ($excludeID) {
            $sql .= " and id != :excludeID";
        }

        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':username', $username);

        if ($excludeID) {
            $query->bindParam(':excludeID', $excludeID);
        }

        $count = $query->execute() ? $query->fetchColumn() : 0;

        return $count > 0;
    }
    
    function login($username, $password)
    {
        $sql = "SELECT * FROM Users WHERE username = :username LIMIT 1;";
        $query = $this->db->connect()->prepare($sql);

        $query->bindParam('username', $username);

        if ($query->execute()) {
            $data = $query->fetch();
            if ($data && password_verify($password, $data['password'])) {
                return true;
            }
        }

        return false;
    }

    function fetch($username)
    {
        $sql = "SELECT * FROM Users WHERE username = :username LIMIT 1;";
        $query = $this->db->connect()->prepare($sql);

        $query->bindParam('username', $username);
        $data = null;
        if ($query->execute()) {
            $data = $query->fetch();
        }

        return $data;
    }

    function addManager($canteen_id) {
        $sql = "INSERT INTO Users (last_name, given_name, middle_name, email, username, password, is_manager, canteen_id) 
                VALUES (:last_name, :given_name, :middle_name, :email, :username, :password, 1, :canteen_id)";
        $query = $this->db->connect()->prepare($sql);
    
        $hashedPassword = password_hash($this->password, PASSWORD_DEFAULT);
        
        $query->bindParam(':last_name', $this->last_name);
        $query->bindParam(':given_name', $this->given_name);
        $query->bindParam(':middle_name', $this->middle_name);
        $query->bindParam(':email', $this->email);
        $query->bindParam(':username', $this->username);
        $query->bindParam(':password', $hashedPassword);
        $query->bindParam(':canteen_id', $canteen_id); 
    
        return $query->execute(); 
    }

    function fetchUserById($user_id)
    {
        $sql = "SELECT * FROM users WHERE user_id = :user_id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':user_id', $user_id);
        $query->execute();
        return $query->fetch();
    }

    function editUser($user_id, $email, $username, $last_name, $given_name, $middle_name, $role)
    {
        $sql = "UPDATE users 
                SET email = :email, 
                    username = :username, 
                    last_name = :last_name, 
                    given_name = :given_name, 
                    middle_name = :middle_name, 
                    role = :role 
                WHERE user_id = :user_id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':email', $email);
        $query->bindParam(':username', $username);
        $query->bindParam(':last_name', $last_name);
        $query->bindParam(':given_name', $given_name);
        $query->bindParam(':middle_name', $middle_name);
        $query->bindParam(':role', $role);
        $query->bindParam(':user_id', $user_id);
        return $query->execute();
    }

    function deleteUser($user_id)
    {
        $sql = "DELETE FROM users WHERE user_id = :user_id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':user_id', $user_id);
        return $query->execute();
    }

    function fetchUsers() {
        $sql = "SELECT * FROM Users";
        $query = $this->db->connect()->query($sql);
        return $query->fetchAll(PDO::FETCH_ASSOC); // Return all users as an associative array
    }
    
    function searchUsers($keyword = '', $role = null) {
        $sql = "SELECT * FROM users WHERE 1";
        if ($keyword) {
            $sql .= " AND (last_name LIKE :keyword 
                        OR given_name LIKE :keyword 
                        OR middle_name LIKE :keyword 
                        OR username LIKE :keyword 
                        OR email LIKE :keyword 
                        OR role LIKE :keyword)";
        }
        if ($role) {
            $sql .= " AND role = :role";
        }
        $query = $this->db->connect()->prepare($sql);
        if ($keyword) {
            $query->bindParam(':keyword', $keyword);
        }
        if ($role) {
            $query->bindParam(':role', $role);
        }
        $query->execute();
        return $query->fetchAll();
    }
    

    function getManagerCanteen($username)
    {
        $sql = "SELECT canteen_id FROM users WHERE username = :username AND is_manager = 1";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':username', $username);

        if ($query->execute()) {
            return $query->fetchColumn(); 
        }

        return null; 
    }

    public function getPendingManagers() {
        $sql = "SELECT u.user_id, u.last_name, u.given_name, u.middle_name,u.email, c.name AS canteen_name
            FROM Users u
            JOIN Canteens c ON u.canteen_id = c.canteen_id
            WHERE u.role = 'pending_manager'
        ";
        $query = $this->db->connect()->prepare($sql);
        $query->execute();
    
        return $query->fetchAll();
    }

    function approveManager($user_id) {
        $sql = "UPDATE Users SET is_manager = 1, role = 'manager' WHERE user_id = :user_id";
        
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':user_id', $user_id);
        
        return $query->execute();
    }
    
    function reject($user_id) {
        $sql = "SELECT canteen_id FROM Users WHERE user_id = :user_id AND role = 'pending_manager'";
        $query = $this->db->connect()->prepare($sql);
        
        $query->bindParam(':user_id', $user_id);
        $query->execute();
        
        $canteen = $query->fetch(PDO::FETCH_ASSOC);
    
        if ($canteen) {
            $canteen_id = $canteen['canteen_id'];
    
            $deleteCanteenSql = "DELETE FROM canteens WHERE canteen_id = :canteen_id";
            $deleteCanteenQuery = $this->db->connect()->prepare($deleteCanteenSql);
    
            $deleteCanteenQuery->bindParam(':canteen_id', $canteen_id);
            $deleteCanteenQuery->execute();
        }
    
        $deleteUserSql = "DELETE FROM Users WHERE user_id = :user_id AND role = 'pending_manager'";
        $deleteUserQuery = $this->db->connect()->prepare($deleteUserSql);
    
        $deleteUserQuery->bindParam(':user_id', $user_id);
        $deleteUserQuery->execute();
    
        return true;
    }
    



    function addPendingManager($canteen_id) {
        $sql = "INSERT INTO Users (last_name, given_name, middle_name, email, username, password, canteen_id, is_manager, role) 
                VALUES (:last_name, :given_name, :middle_name, :email, :username, :password, :canteen_id, 0, 'pending_manager')";
        
        $query = $this->db->connect()->prepare($sql);
    
        $hashedPassword = password_hash($this->password, PASSWORD_DEFAULT);
    
        $query->bindParam(':last_name', $this->last_name);
        $query->bindParam(':given_name', $this->given_name);
        $query->bindParam(':middle_name', $this->middle_name);
        $query->bindParam(':email', $this->email);
        $query->bindParam(':username', $this->username);
        $query->bindParam(':password', $hashedPassword);
        $query->bindParam(':canteen_id', $canteen_id);
    
        return $query->execute(); 
    }
    
    
    
    
}
?>