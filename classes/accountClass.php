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
    

    function signup($last_name, $given_name, $middle_name, $email, $username, $password, 
                  $is_student, $is_employee, $is_guest, $program_id = null, $department_id = null) {
        try {
            $conn = $this->db->connect();
            $conn->beginTransaction();

            // Validate program_id if student
            if ($is_student && $program_id) {
                $checkProgram = $conn->prepare("SELECT program_id FROM programs WHERE program_id = ?");
                $checkProgram->execute([$program_id]);
                if (!$checkProgram->fetch()) {
                    throw new Exception("Invalid program selected");
                }
            }

            // Validate department_id if employee
            if ($is_employee && $department_id) {
                $checkDept = $conn->prepare("SELECT department_id FROM departments WHERE department_id = ?");
                $checkDept->execute([$department_id]);
                if (!$checkDept->fetch()) {
                    throw new Exception("Invalid department selected");
                }
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert into users table with all fields
            $sql = "INSERT INTO users (
                        last_name, given_name, middle_name, email, username, password, 
                        role, status, program_id, department_id
                    ) VALUES (
                        :last_name, :given_name, :middle_name, :email, :username, :password,
                        CASE 
                            WHEN :is_student = 1 THEN 'student'
                            WHEN :is_employee = 1 THEN 'employee'
                            ELSE 'guest'
                        END,
                        CASE 
                            WHEN :is_guest = 1 THEN 'approved'
                            ELSE 'pending'
                        END,
                        NULLIF(:program_id, ''),
                        NULLIF(:department_id, '')
                    )";
            
            $query = $conn->prepare($sql);
            
            // Bind all parameters
            $params = [
                ':last_name' => $last_name,
                ':given_name' => $given_name,
                ':middle_name' => $middle_name,
                ':email' => $email,
                ':username' => $username,
                ':password' => $hashedPassword,
                ':is_student' => $is_student,
                ':is_employee' => $is_employee,
                ':is_guest' => $is_guest,
                ':program_id' => $is_student ? $program_id : null,
                ':department_id' => $is_employee ? $department_id : null
            ];
            
            $query->execute($params);
            
            $this->user_id = $conn->lastInsertId();
            $conn->commit();
            return true;
            
        } catch (Exception $e) {
            if (isset($conn)) {
                $conn->rollBack();
            }
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
    
    function login($username, $password) {
        try {
            $sql = "SELECT u.*, 
                        COALESCE(s.student_number, e.employee_number) as id_number,
                        CASE 
                            WHEN s.student_id IS NOT NULL THEN p.program_name
                            WHEN e.employee_id IS NOT NULL THEN d.department_name
                            ELSE NULL
                        END as department_program
                    FROM users u
                    LEFT JOIN students s ON u.user_id = s.user_id
                    LEFT JOIN employees e ON u.user_id = e.user_id
                    LEFT JOIN programs p ON u.program_id = p.program_id
                    LEFT JOIN departments d ON u.department_id = d.department_id
                    WHERE u.username = :username";
            
            $query = $this->db->connect()->prepare($sql);
            $query->bindParam(':username', $username);
            $query->execute();
            
            $user = $query->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                if ($user['status'] !== 'approved' && $user['role'] !== 'guest') {
                    throw new Exception("Account is pending approval");
                }
                
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['id_number'] = $user['id_number'];
                $_SESSION['department_program'] = $user['department_program'];
                
                return true;
            }
            return false;
            
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function fetch($username) {
        $sql = "SELECT user_id, username, password, role, status, canteen_id 
                FROM users 
                WHERE username = :username";
                
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':username', $username);
        $query->execute();
        
        return $query->fetch(PDO::FETCH_ASSOC);
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
    
    function searchUsers($keyword = '', $role = '') {
        try {
            $sql = "SELECT user_id, username, email, role, status, 
                           last_name, given_name, middle_name
                    FROM users 
                    WHERE 1=1";

            $params = [];
            
            if ($keyword) {
                $sql .= " AND (username LIKE :keyword 
                          OR email LIKE :keyword 
                          OR last_name LIKE :keyword 
                          OR given_name LIKE :keyword 
                          OR middle_name LIKE :keyword)";
                $params[':keyword'] = "%$keyword%";
            }
            
            if ($role) {
                $sql .= " AND role = :role";
                $params[':role'] = $role;
            }
            
            $sql .= " ORDER BY last_name ASC, given_name ASC";
            
            $query = $this->db->connect()->prepare($sql);
            $query->execute($params);
            
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error searching users: " . $e->getMessage());
            throw $e;
        }
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

    public function getPendingManagers()
    {
        $sql = "SELECT u.user_id, up.last_name, up.given_name, up.middle_name, 
                u.email, c.name AS canteen_name
                FROM users u
                JOIN managers m ON u.user_id = m.user_id
                JOIN user_profiles up ON u.user_id = up.user_id
                JOIN canteens c ON m.canteen_id = c.canteen_id
                WHERE m.status = 'pending'";
        
        $query = $this->db->connect()->prepare($sql);
        $query->execute();
        return $query->fetchAll();
    }

    function approveManager($user_id)
    {
        try {
            $this->db->connect()->beginTransaction();
            
            $sql = "UPDATE users SET status = 'approved' WHERE user_id = :user_id";
            $query = $this->db->connect()->prepare($sql);
            $query->bindParam(':user_id', $user_id);
            $query->execute();
            
            $sql = "UPDATE managers SET status = 'accepted' WHERE user_id = :user_id";
            $query = $this->db->connect()->prepare($sql);
            $query->bindParam(':user_id', $user_id);
            $query->execute();
            
            $this->db->connect()->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->connect()->rollBack();
            throw $e;
        }
    }
    
    function reject($user_id)
    {
        try {
            $this->db->connect()->beginTransaction();
            
            $sql = "UPDATE users SET status = 'rejected' WHERE user_id = :user_id";
            $query = $this->db->connect()->prepare($sql);
            $query->bindParam(':user_id', $user_id);
            $query->execute();
            
            $sql = "UPDATE managers SET status = 'rejected' WHERE user_id = :user_id";
            $query = $this->db->connect()->prepare($sql);
            $query->bindParam(':user_id', $user_id);
            $query->execute();
            
            $this->db->connect()->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->connect()->rollBack();
            throw $e;
        }
    }
    



    function addPendingManager($canteen_id) {
        try {
            $conn = $this->db->connect();
            $conn->beginTransaction();

            $hashedPassword = password_hash($this->password, PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO users (last_name, given_name, middle_name, email, username, password, is_manager, role, status, canteen_id) 
                    VALUES (:last_name, :given_name, :middle_name, :email, :username, :password, 0, 'pending_manager', 'pending', :canteen_id)";
            
            $stmt = $conn->prepare($sql);
            
            $params = [
                ':last_name' => $this->last_name,
                ':given_name' => $this->given_name,
                ':middle_name' => $this->middle_name,
                ':email' => $this->email,
                ':username' => $this->username,
                ':password' => $hashedPassword,
                ':canteen_id' => $canteen_id
            ];

            if ($stmt->execute($params)) {
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
    
    
    
    
    function checkStatus($user_id) {
        $sql = "SELECT status FROM users WHERE user_id = :user_id";
        $query = $this->db->connect()->prepare($sql);
        $query->execute(['user_id' => $user_id]);
        $result = $query->fetch();
        return $result['status'] ?? null;
    }

    public function fetchDepartments() {
        $sql = "SELECT d.department_id, d.department_name, c.abbreviation as college_abbreviation 
                FROM departments d
                JOIN colleges c ON d.college_id = c.college_id
                ORDER BY c.abbreviation, d.department_name";
        
        $query = $this->db->connect()->query($sql);
        return $query->fetchAll();
    }
}
?>