<?php
require_once 'databaseClass.php';

class Employee {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    public function fetchCanteenEmployees($canteenId) {
        try {
            $sql = "SELECT u.user_id, u.username, u.email, u.last_name, u.given_name, 
                           u.middle_name, u.status, m.manager_id 
                    FROM users u 
                    JOIN managers m ON u.user_id = m.user_id 
                    WHERE m.canteen_id = ? AND u.role = 'manager'
                    AND u.user_id != ?"; 
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$canteenId, $_SESSION['user_id']]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching canteen employees: " . $e->getMessage());
            throw new Exception("Error fetching employees");
        }
    }

    public function addEmployee($data) {
        try {
            $this->conn->beginTransaction();

          
            $sql = "SELECT COUNT(*) FROM users WHERE email = ? OR username = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$data['email'], $data['username']]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception("Email or username already exists");
            }

         
            $sql = "INSERT INTO users (email, username, password, role, status, 
                                     last_name, given_name, middle_name, canteen_id) 
                    VALUES (?, ?, ?, 'manager', 'pending', ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($sql);
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            
            $stmt->execute([
                $data['email'],
                $data['username'],
                $hashedPassword,
                $data['last_name'],
                $data['given_name'],
                $data['middle_name'] ?? null,
                $data['canteen_id']
            ]);

            $userId = $this->conn->lastInsertId();

          
            $sql = "INSERT INTO managers (user_id, canteen_id, start_date, status) 
                    VALUES (?, ?, CURDATE(), 'pending')";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$userId, $data['canteen_id']]);

            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Error adding employee: " . $e->getMessage());
            throw new Exception("Error adding employee");
        }
    }

    public function deleteEmployee($userId, $canteenId) {
        try {
            $sql = "SELECT COUNT(*) FROM managers 
                    WHERE user_id = ? AND canteen_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$userId, $canteenId]);
            if ($stmt->fetchColumn() == 0) {
                throw new Exception("Unauthorized deletion attempt");
            }

            $this->conn->beginTransaction();

          
            $sql = "DELETE FROM managers WHERE user_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$userId]);

        
            $sql = "DELETE FROM users WHERE user_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$userId]);

            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Error deleting employee: " . $e->getMessage());
            throw new Exception("Error deleting employee");
        }
    }

    public function getEmployeeDetails($userId, $canteenId) {
        try {
            $sql = "SELECT u.*, m.manager_id 
                    FROM users u 
                    JOIN managers m ON u.user_id = m.user_id 
                    WHERE u.user_id = ? AND m.canteen_id = ?";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$userId, $canteenId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting employee details: " . $e->getMessage());
            throw new Exception("Error getting employee details");
        }
    }

    public function updateEmployee($data) {
        try {
            $this->conn->beginTransaction();

          
            $sql = "SELECT COUNT(*) FROM users 
                    WHERE (email = ? OR username = ?) 
                    AND user_id != ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$data['email'], $data['username'], $data['user_id']]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception("Email or username already exists");
            }

            $sql = "UPDATE users 
                    SET email = ?, username = ?, last_name = ?, 
                        given_name = ?, middle_name = ?
                    WHERE user_id = ?";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                $data['email'],
                $data['username'],
                $data['last_name'],
                $data['given_name'],
                $data['middle_name'] ?? null,
                $data['user_id']
            ]);

        
            if (!empty($data['password'])) {
                $sql = "UPDATE users SET password = ? WHERE user_id = ?";
                $stmt = $this->conn->prepare($sql);
                $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
                $stmt->execute([$hashedPassword, $data['user_id']]);
            }

            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Error updating employee: " . $e->getMessage());
            throw new Exception("Error updating employee");
        }
    }

    public function getTotalEmployeesCount($canteenId) {
        try {
            $sql = "SELECT COUNT(*) as count 
                    FROM users u 
                    JOIN managers m ON u.user_id = m.user_id 
                    WHERE m.canteen_id = ? AND u.role = 'manager'
                    AND u.user_id != ?";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$canteenId, $_SESSION['user_id']]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'];
        } catch (PDOException $e) {
            error_log("Error getting total employees count: " . $e->getMessage());
            throw new Exception("Error getting total count");
        }
    }

    public function getEmployeesForDataTable($canteenId, $search, $start, $length, $orderBy, $orderDir) {
        try {
            // Base query for total count
            $countSql = "SELECT COUNT(*) as total
                        FROM users u 
                        JOIN managers m ON u.user_id = m.user_id 
                        WHERE m.canteen_id = :canteen_id 
                        AND u.role = 'manager'
                        AND u.user_id != :current_user";
            
            $countStmt = $this->conn->prepare($countSql);
            $countStmt->execute([
                ':canteen_id' => $canteenId,
                ':current_user' => $_SESSION['user_id']
            ]);
            $totalRecords = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
           
            $sql = "SELECT u.user_id, u.username, u.email, u.last_name, u.given_name, 
                           u.middle_name, u.status, m.status as manager_status
                    FROM users u 
                    JOIN managers m ON u.user_id = m.user_id 
                    WHERE m.canteen_id = :canteen_id 
                    AND u.role = 'manager'
                    AND u.user_id != :current_user";
            
            $params = [
                ':canteen_id' => $canteenId,
                ':current_user' => $_SESSION['user_id']
            ];
            
         
            if (!empty($search)) {
                $sql .= " AND (u.username LIKE :search 
                          OR u.email LIKE :search 
                          OR u.last_name LIKE :search 
                          OR u.given_name LIKE :search 
                          OR u.middle_name LIKE :search)";
                $params[':search'] = "%$search%";
            }
            
            
            $filteredStmt = $this->conn->prepare($sql);
            $filteredStmt->execute($params);
            $filteredCount = $filteredStmt->rowCount();
            
          
            $sql .= " ORDER BY " . $this->sanitizeOrderBy($orderBy) . " " . ($orderDir === 'asc' ? 'ASC' : 'DESC');
            
         
            $sql .= " LIMIT :start, :length";
            $params[':start'] = (int)$start;
            $params[':length'] = (int)$length;
            
            $stmt = $this->conn->prepare($sql);
           
            foreach ($params as $key => $value) {
                if (in_array($key, [':start', ':length'])) {
                    $stmt->bindValue($key, $value, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($key, $value);
                }
            }
            
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'total_count' => $totalRecords,
                'filtered_count' => $filteredCount,
                'data' => $data
            ];
        } catch (PDOException $e) {
            error_log("Error getting employees for DataTable: " . $e->getMessage());
            throw new Exception("Error fetching employees: " . $e->getMessage());
        }
    }
    
    private function sanitizeOrderBy($column) {
        $allowedColumns = [
            'user_id' => 'u.user_id',
            'name' => 'u.last_name',
            'username' => 'u.username',
            'email' => 'u.email',
            'status' => 'm.status'
        ];
        return $allowedColumns[$column] ?? 'u.last_name';
    }
}
?> 