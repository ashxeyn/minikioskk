<?php
require_once 'databaseClass.php';

class Employee {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function fetchCanteenEmployees($canteenId) {
        try {
            $sql = "SELECT u.user_id, CONCAT(u.last_name, ', ', u.given_name) as name, 
                           u.username, u.email
                    FROM users u 
                    JOIN managers m ON u.user_id = m.user_id 
                    WHERE m.canteen_id = :canteen_id 
                    AND u.role = 'manager'
                    AND u.user_id != :current_user
                    ORDER BY u.last_name, u.given_name";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([
                'canteen_id' => $canteenId,
                'current_user' => $_SESSION['user_id'] ?? 0
            ]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error fetching canteen employees: " . $e->getMessage());
            throw new Exception("Error fetching employees");
        }
    }

    public function addEmployee($data) {
        try {
            $db = $this->db->getConnection();
            $db->beginTransaction();

            // Check for existing email/username
            $sql = "SELECT COUNT(*) FROM users WHERE email = :email OR username = :username";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'email' => $data['email'],
                'username' => $data['username']
            ]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception("Email or username already exists");
            }

            // Insert into users table with accepted status
            $userSql = "INSERT INTO users (username, email, password, role, status, last_name, given_name, middle_name) 
                        VALUES (:username, :email, :password, 'manager', 'approved', :last_name, :given_name, :middle_name)";
            
            $userStmt = $db->prepare($userSql);
            $result = $userStmt->execute([
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => password_hash($data['password'], PASSWORD_DEFAULT),
                'last_name' => $data['last_name'],
                'given_name' => $data['given_name'],
                'middle_name' => $data['middle_name'] ?? null
            ]);

            if (!$result) {
                throw new Exception("Failed to create user account");
            }

            $userId = $db->lastInsertId();

            // Insert into managers table with accepted status
            $managerSql = "INSERT INTO managers (user_id, canteen_id, start_date, status) 
                          VALUES (:user_id, :canteen_id, CURDATE(), 'accepted')";
            
            $managerStmt = $db->prepare($managerSql);
            $result = $managerStmt->execute([
                'user_id' => $userId,
                'canteen_id' => $data['canteen_id']
            ]);

            if (!$result) {
                throw new Exception("Failed to create manager record");
            }

            $db->commit();
            return true;
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw new Exception($e->getMessage());
        }
    }

    public function getAllEmployees() {
        try {
            $sql = "SELECT u.user_id, CONCAT(u.last_name, ', ', u.given_name) as name, 
                           u.username, u.email
                    FROM users u
                    JOIN employees e ON u.user_id = e.user_id
                    WHERE u.role = 'employee'
                    ORDER BY u.last_name, u.given_name";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Error fetching employees: " . $e->getMessage());
        }
    }

    public function getEmployeeById($userId) {
        try {
            $sql = "SELECT u.user_id, u.username, u.email, u.last_name, u.given_name, u.middle_name,
                           e.employee_number, e.department_id, e.position
                    FROM users u
                    JOIN employees e ON u.user_id = e.user_id
                    WHERE u.user_id = :user_id AND u.role = 'employee'";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute(['user_id' => $userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Error fetching employee details: " . $e->getMessage());
        }
    }

    private function generateEmployeeNumber() {
        return 'EMP' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }

    public function updateEmployee($data) {
        try {
            $db = $this->db->getConnection();
            $db->beginTransaction();

            // Check for duplicate email/username
            $sql = "SELECT COUNT(*) FROM users 
                    WHERE (email = :email OR username = :username) 
                    AND user_id != :user_id";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'email' => $data['email'],
                'username' => $data['username'],
                'user_id' => $data['user_id']
            ]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception("Email or username already exists");
            }

            // Update users table
            $userSql = "UPDATE users 
                       SET username = :username,
                           email = :email,
                           last_name = :last_name,
                           given_name = :given_name,
                           middle_name = :middle_name
                       WHERE user_id = :user_id";
            
            $userStmt = $db->prepare($userSql);
            $result = $userStmt->execute([
                'username' => $data['username'],
                'email' => $data['email'],
                'last_name' => $data['last_name'],
                'given_name' => $data['given_name'],
                'middle_name' => $data['middle_name'] ?? null,
                'user_id' => $data['user_id']
            ]);

            if (!$result) {
                throw new Exception("Failed to update user details");
            }

            if (isset($data['department_id'])) {
                // Update employees table
                $empSql = "UPDATE employees 
                          SET department_id = :department_id,
                              position = :position
                          WHERE user_id = :user_id";
                
                $empStmt = $db->prepare($empSql);
                $result = $empStmt->execute([
                    'department_id' => $data['department_id'],
                    'position' => $data['position'] ?? 'Staff',
                    'user_id' => $data['user_id']
                ]);

                if (!$result) {
                    throw new Exception("Failed to update employee details");
                }
            }

            $db->commit();
            return true;
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw new Exception($e->getMessage());
        }
    }

    public function deleteEmployee($userId, $canteenId = null) {
        try {
            $db = $this->db->getConnection();
            $db->beginTransaction();

            if ($canteenId !== null) {
                // Check if employee belongs to the canteen
                $sql = "SELECT COUNT(*) FROM managers 
                        WHERE user_id = :user_id AND canteen_id = :canteen_id";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    'user_id' => $userId,
                    'canteen_id' => $canteenId
                ]);
                if ($stmt->fetchColumn() == 0) {
                    throw new Exception("Unauthorized deletion attempt");
                }

                // Delete from managers table
                $sql = "DELETE FROM managers WHERE user_id = :user_id";
                $stmt = $db->prepare($sql);
                $stmt->execute(['user_id' => $userId]);
            } else {
                // Delete from employees table
                $sql = "DELETE FROM employees WHERE user_id = :user_id";
                $stmt = $db->prepare($sql);
                $stmt->execute(['user_id' => $userId]);
            }

            // Delete from users table
            $sql = "DELETE FROM users WHERE user_id = :user_id";
            $stmt = $db->prepare($sql);
            $result = $stmt->execute(['user_id' => $userId]);

            if (!$result) {
                throw new Exception("Failed to delete user");
            }

            $db->commit();
            return true;
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw new Exception($e->getMessage());
        }
    }

    public function getTotalEmployeesCount($canteenId) {
        try {
            $sql = "SELECT COUNT(*) as count 
                    FROM users u 
                    JOIN managers m ON u.user_id = m.user_id 
                    WHERE m.canteen_id = :canteen_id AND u.role = 'manager'
                    AND u.user_id != :current_user";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([
                'canteen_id' => $canteenId,
                'current_user' => $_SESSION['user_id'] ?? 0
            ]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'];
        } catch (Exception $e) {
            error_log("Error getting total employees count: " . $e->getMessage());
            throw new Exception("Error getting total count");
        }
    }
}
?> 