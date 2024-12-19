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
    public $department_id = null;
    public $canteen_id = null;

    protected $db;

    function __construct()
    {
        $this->db = new Database();
    }

    function UserInfo() {
        try {
            $sql = "SELECT u.*, 
                    m.status as manager_status,
                    m.canteen_id,
                    COALESCE(u.rejection_reason, m.rejection_reason) as rejection_reason
                    FROM users u 
                    LEFT JOIN managers m ON u.user_id = m.user_id 
                    WHERE u.user_id = :user_id";
            
            $query = $this->db->connect()->prepare($sql);
            $query->bindParam(':user_id', $this->user_id);
            
            if ($query->execute()) {
                $result = $query->fetch(PDO::FETCH_ASSOC);
                if ($result) {
                    // Debug log
                    error_log("UserInfo result: " . print_r($result, true));
                    
                    // Set default values if not set
                    $result['status'] = $result['status'] ?? 'pending';
                    $result['manager_status'] = $result['manager_status'] ?? $result['status'];
                    $result['rejection_reason'] = $result['rejection_reason'] ?? null;
                    
                    return $result;
                }
            }
            
            error_log("No user found for ID: " . $this->user_id);
            return false;
            
        } catch (Exception $e) {
            error_log("Error in UserInfo: " . $e->getMessage());
            return false;
        }
    }
    

    function signup($last_name, $given_name, $middle_name, $email, $username, $password, 
                  $is_student, $is_employee, $is_guest, $program_id = null, $department_id = null) {
        try {
            $conn = $this->db->connect();
            $conn->beginTransaction();
            if ($is_student && $program_id) {
                $checkProgram = $conn->prepare("SELECT program_id FROM programs WHERE program_id = ?");
                $checkProgram->execute([$program_id]);
                if (!$checkProgram->fetch()) {
                    throw new Exception("Invalid program selected");
                }
            }

          
            if ($is_employee && $department_id) {
                $checkDept = $conn->prepare("SELECT department_id FROM departments WHERE department_id = ?");
                $checkDept->execute([$department_id]);
                if (!$checkDept->fetch()) {
                    throw new Exception("Invalid department selected");
                }
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
          
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
        try {
            $sql = "SELECT u.*, m.status as manager_status, m.canteen_id 
                    FROM users u 
                    LEFT JOIN managers m ON u.user_id = m.user_id 
                    WHERE u.username = :username";
            
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->execute(['username' => $username]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            error_log("DEBUG: fetch result for $username: " . print_r($result, true));
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error in fetch: " . $e->getMessage());
            return null;
        }
    }

    function addManager($canteen_id) {
        try {
            $conn = $this->db->connect();
            $conn->beginTransaction();

           
            $hashedPassword = password_hash($this->password, PASSWORD_DEFAULT);
            
           
            $sql = "INSERT INTO users (last_name, given_name, middle_name, email, username, 
                    password, role, status) 
                    VALUES (:last_name, :given_name, :middle_name, :email, :username, 
                    :password, 'manager', 'pending')";
            
            $stmt = $conn->prepare($sql);
            $params = [
                ':last_name' => $this->last_name,
                ':given_name' => $this->given_name,
                ':middle_name' => $this->middle_name,
                ':email' => $this->email,
                ':username' => $this->username,
                ':password' => $hashedPassword
            ];
            
            if ($stmt->execute($params)) {
                $this->user_id = $conn->lastInsertId();
                
              
                $sql2 = "INSERT INTO managers (user_id, canteen_id, status) 
                         VALUES (:user_id, :canteen_id, 'pending')";
                
                $stmt2 = $conn->prepare($sql2);
                $params2 = [
                    ':user_id' => $this->user_id,
                    ':canteen_id' => $canteen_id
                ];
                
                if ($stmt2->execute($params2)) {
                   
                    $sql3 = "INSERT INTO user_profiles (user_id, last_name, given_name, middle_name) 
                             VALUES (:user_id, :last_name, :given_name, :middle_name)";
                    
                    $stmt3 = $conn->prepare($sql3);
                    $params3 = [
                        ':user_id' => $this->user_id,
                        ':last_name' => $this->last_name,
                        ':given_name' => $this->given_name,
                        ':middle_name' => $this->middle_name
                    ];
                    
                    if ($stmt3->execute($params3)) {
                        $conn->commit();
                        return true;
                    }
                }
            }
            
            $conn->rollBack();
            return false;
            
        } catch (PDOException $e) {
            if ($conn) {
                $conn->rollBack();
            }
            error_log("Error adding pending manager: " . $e->getMessage());
            throw $e;
        }
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
        return $query->fetchAll(PDO::FETCH_ASSOC); 
    }
    
    function searchUsers($search = '', $role = '') {
        try {
            $sql = "SELECT user_id, last_name, given_name, middle_name, email, username, role, 
                    DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') as created_at 
                    FROM users 
                    WHERE 1=1";
            
            $params = [];
            
            if (!empty($search)) {
                $sql .= " AND (last_name LIKE ? OR given_name LIKE ? OR email LIKE ? OR username LIKE ?)";
                $searchTerm = "%$search%";
                $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
            }
            
            if (!empty($role)) {
                $sql .= " AND role = ?";
                $params[] = $role;
            }
            
            $sql .= " ORDER BY last_name, given_name";
            
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in searchUsers: " . $e->getMessage());
            throw new Exception("Failed to search users");
        }
    }
    

    function getManagerCanteen($username)
    {
        try {
            $sql = "SELECT m.canteen_id 
                    FROM managers m 
                    JOIN users u ON m.user_id = u.user_id 
                    WHERE u.username = :username 
                    AND m.status = 'accepted'";
                
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->execute(['username' => $username]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            error_log("DEBUG: getManagerCanteen result for $username: " . print_r($result, true));
            
            return $result ? $result['canteen_id'] : null;
        } catch (PDOException $e) {
            error_log("Error in getManagerCanteen: " . $e->getMessage());
            return null;
        }
    }

    public function getPendingManagers() {
        try {
            $sql = "SELECT u.user_id, u.email, u.username, 
                    u.last_name, u.given_name, u.middle_name,
                    m.canteen_id, c.name as canteen_name
                    FROM users u
                    JOIN managers m ON u.user_id = m.user_id
                    JOIN canteens c ON m.canteen_id = c.canteen_id
                    WHERE u.role = 'manager' 
                    AND m.status = 'pending'";

            $query = $this->db->connect()->prepare($sql);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getPendingManagers: " . $e->getMessage());
            return false;
        }
    }

    function approveManager($user_id)
    {
        try {
            $db = $this->db->connect();
            $db->beginTransaction();
            
            $sql = "UPDATE users 
                    SET status = 'approved' 
                    WHERE user_id = :user_id";
            $stmt = $db->prepare($sql);
            $stmt->execute(['user_id' => $user_id]);
            
            $sql2 = "UPDATE managers 
                     SET status = 'accepted' 
                     WHERE user_id = :user_id";
            $stmt2 = $db->prepare($sql2);
            $stmt2->execute(['user_id' => $user_id]);
            
            $db->commit();
            return true;
            
        } catch (Exception $e) {
            if ($db && $db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Error approving manager: " . $e->getMessage());
            return false;
        }
    }
    
    function reject($user_id, $reason)
    {
        try {
            $conn = $this->db->connect();
            $conn->beginTransaction();
            
            // Debug log
            error_log("Rejecting user ID: $user_id with reason: $reason");
            
            // Update users table
            $sql = "UPDATE users 
                    SET status = 'rejected', 
                        rejection_reason = :reason 
                    WHERE user_id = :user_id";
            $stmt = $conn->prepare($sql);
            $result1 = $stmt->execute([
                ':user_id' => $user_id,
                ':reason' => $reason
            ]);
            
            // Debug log
            error_log("Users table update result: " . ($result1 ? 'success' : 'failure'));
            
            // Update managers table
            $sql = "UPDATE managers 
                    SET status = 'rejected',
                        rejection_reason = :reason 
                    WHERE user_id = :user_id";
            $stmt = $conn->prepare($sql);
            $result2 = $stmt->execute([
                ':user_id' => $user_id,
                ':reason' => $reason
            ]);
            
            // Debug log
            error_log("Managers table update result: " . ($result2 ? 'success' : 'failure'));
            
            if ($result1 && $result2) {
                $conn->commit();
                return true;
            } else {
                throw new Exception("Failed to update one or more tables");
            }
            
        } catch (Exception $e) {
            if ($conn && $conn->inTransaction()) {
                $conn->rollBack();
            }
            error_log("Error rejecting manager: " . $e->getMessage());
            return false;
        }
    }
    



    function addPendingManager($canteen_id) {
        try {
            $conn = $this->db->connect();
            $conn->beginTransaction();

            $hashedPassword = password_hash($this->password, PASSWORD_DEFAULT);
            
            // First, insert into users table with pending status
            $sql = "INSERT INTO users (last_name, given_name, middle_name, email, username, password, role, status) 
                    VALUES (:last_name, :given_name, :middle_name, :email, :username, :password, 'manager', 'pending')";
            
            $stmt = $conn->prepare($sql);
            
            $params = [
                ':last_name' => $this->last_name,
                ':given_name' => $this->given_name,
                ':middle_name' => $this->middle_name,
                ':email' => $this->email,
                ':username' => $this->username,
                ':password' => $hashedPassword
            ];
            
            if ($stmt->execute($params)) {
                $this->user_id = $conn->lastInsertId(); // Save the user_id
                
                // Then insert into managers table
                $sql2 = "INSERT INTO managers (user_id, canteen_id, status) 
                         VALUES (:user_id, :canteen_id, 'pending')";
                
                $stmt2 = $conn->prepare($sql2);
                $params2 = [
                    ':user_id' => $this->user_id,
                    ':canteen_id' => $canteen_id
                ];
                
                if ($stmt2->execute($params2)) {
                    // Insert into user_profiles table
                    $sql3 = "INSERT INTO user_profiles (user_id, last_name, given_name, middle_name) 
                             VALUES (:user_id, :last_name, :given_name, :middle_name)";
                    
                    $stmt3 = $conn->prepare($sql3);
                    $params3 = [
                        ':user_id' => $this->user_id,
                        ':last_name' => $this->last_name,
                        ':given_name' => $this->given_name,
                        ':middle_name' => $this->middle_name
                    ];
                    
                    if ($stmt3->execute($params3)) {
                        $conn->commit();
                        return true;
                    }
                }
            }
            
            $conn->rollBack();
            return false;
            
        } catch (PDOException $e) {
            if ($conn) {
                $conn->rollBack();
            }
            error_log("Error adding pending manager: " . $e->getMessage());
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

    public function addUser() {
        try {
            $conn = $this->db->connect();
            $conn->beginTransaction();

            // Check if username or email already exists
            $checkSql = "SELECT username, email FROM users WHERE username = ? OR email = ?";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->execute([$this->username, $this->email]);
            $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing) {
                if ($existing['username'] === $this->username) {
                    throw new Exception("Username already exists");
                }
                if ($existing['email'] === $this->email) {
                    throw new Exception("Email already exists");
                }
            }

            // Hash the password
            $hashedPassword = password_hash($this->password, PASSWORD_DEFAULT);

            // Insert into users table
            $sql = "INSERT INTO users (last_name, given_name, middle_name, email, username, password, role) 
                    VALUES (:last_name, :given_name, :middle_name, :email, :username, :password, :role)";
            
            $stmt = $conn->prepare($sql);
            $result = $stmt->execute([
                ':last_name' => $this->last_name,
                ':given_name' => $this->given_name,
                ':middle_name' => $this->middle_name,
                ':email' => $this->email,
                ':username' => $this->username,
                ':password' => $hashedPassword,
                ':role' => $this->role
            ]);

            if ($result) {
                $conn->commit();
                return true;
            }

            $conn->rollBack();
            throw new Exception("Failed to insert user record");

        } catch (PDOException $e) {
            if (isset($conn) && $conn->inTransaction()) {
                $conn->rollBack();
            }
            error_log("Error in addUser: " . $e->getMessage());
            throw new Exception("Database error occurred while adding user");
        }
    }

    function addAdmin() {
        $this->role = 'admin';
        return $this->addUser();
    }

    public function getUserById($userId) {
        try {
            $sql = "SELECT user_id, username, role, email 
                    FROM users 
                    WHERE user_id = :user_id";
            
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->execute(['user_id' => $userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching user: " . $e->getMessage());
            return false;
        }
    }
}
?>