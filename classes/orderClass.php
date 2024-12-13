<?php
require_once '../classes/databaseClass.php';

class Order
{
    public $db;

    function __construct()
    {
        try {
            $this->db = new Database();
        } catch (Exception $e) {
            error_log("Database connection error in OrderClass: " . $e->getMessage());
            throw new Exception("Failed to connect to database");
        }
    }

    public function placeOrder($user_id, $cartItems, $total) {
        try {
            $db = $this->db->connect();
            
            if (!$db->inTransaction()) {
                $db->beginTransaction();
            }

            // First verify stock availability for all items
            $stocksObj = new Stocks();
            foreach ($cartItems as $item) {
                $stock = $stocksObj->fetchStockByProductId($item['product_id']);
                if (!$stock || $stock['quantity'] < $item['quantity']) {
                    throw new Exception("Insufficient stock for {$item['name']}");
                }
            }

            $canteen_id = $cartItems[0]['canteen_id'] ?? 1;

            // Create the order
            $sql = "INSERT INTO orders (user_id, canteen_id, total_amount, status, payment_status, created_at) 
                    VALUES (:user_id, :canteen_id, :total_amount, 'placed', 'unpaid', NOW())";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'user_id' => $user_id,
                'canteen_id' => $canteen_id,
                'total_amount' => $total
            ]);
            
            $order_id = $db->lastInsertId();

            // Insert order items and update stock
            $sql = "INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal) 
                    VALUES (:order_id, :product_id, :quantity, :unit_price, :subtotal)";
            
            $stmt = $db->prepare($sql);
            
            foreach ($cartItems as $item) {
                $subtotal = $item['quantity'] * $item['unit_price'];
                
                // Insert order item
                $stmt->execute([
                    'order_id' => $order_id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $subtotal
                ]);

                // Reduce stock
                if (!$stocksObj->updateStock($item['product_id'], -$item['quantity'])) {
                    throw new Exception("Failed to update stock for {$item['name']}");
                }
            }

            $db->commit();
            return [
                'success' => true,
                'order_id' => $order_id
            ];

        } catch (Exception $e) {
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Error placing order: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getOrdersByUser($user_id) {
        try {
            $sql = "SELECT o.*, 
                    GROUP_CONCAT(CONCAT(oi.quantity, 'x ', p.name) SEPARATOR ', ') as items
                    FROM orders o
                    LEFT JOIN order_items oi ON o.order_id = oi.order_id
                    LEFT JOIN products p ON oi.product_id = p.product_id
                    WHERE o.user_id = :user_id
                    GROUP BY o.order_id
                    ORDER BY o.created_at DESC";
            
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->execute(['user_id' => $user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error getting user orders: " . $e->getMessage());
            return [];
        }
    }

    public function getOrderDetails($order_id) {
        try {
            $sql = "SELECT o.*, oi.*, p.name as product_name
                    FROM orders o
                    JOIN order_items oi ON o.order_id = oi.order_id
                    JOIN products p ON oi.product_id = p.product_id
                    WHERE o.order_id = :order_id";
            
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->execute(['order_id' => $order_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error getting order details: " . $e->getMessage());
            return [];
        }
    }

    public function cancelOrder($order_id) {
        $db = null;
        try {
            $db = $this->db->connect();
            $db->beginTransaction();

            // Get order items to restore stock
            $sql = "SELECT product_id, quantity FROM order_items WHERE order_id = :order_id";
            $stmt = $db->prepare($sql);
            $stmt->execute(['order_id' => $order_id]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Update order status
            $sql = "UPDATE orders 
                    SET status = 'cancelled', 
                        updated_at = NOW() 
                    WHERE order_id = :order_id";
            
            $stmt = $db->prepare($sql);
            $stmt->execute(['order_id' => $order_id]);

            // Restore stock quantities
            $stocksObj = new Stocks();
            foreach ($items as $item) {
                $stocksObj->updateStock(
                    $item['product_id'], 
                    $item['quantity']
                );
            }

            $db->commit();
            return true;

        } catch (Exception $e) {
            if ($db && $db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Error cancelling order: " . $e->getMessage());
            return false;
        }
    }

    public function getUserOrders($user_id) {
        return $this->getOrdersByUser($user_id);
    }

    public function fetchOrders($canteen_id = null) {
        try {
            $sql = "SELECT o.*, 
                    GROUP_CONCAT(DISTINCT p.name SEPARATOR ', ') as product_names,
                    SUM(oi.quantity) as total_quantity,
                    o.total_amount as total_price,
                    u.username,
                    CONCAT(u.given_name, ' ', u.last_name) as customer_name,
                    c.name as canteen_name,
                    CONCAT(DATE_FORMAT(o.created_at, '%Y%m%d'), '-', LPAD(o.order_id, 3, '0')) as queue_number
                    FROM orders o
                    LEFT JOIN order_items oi ON o.order_id = oi.order_id
                    LEFT JOIN products p ON oi.product_id = p.product_id
                    LEFT JOIN users u ON o.user_id = u.user_id
                    LEFT JOIN canteens c ON o.canteen_id = c.canteen_id";

            $params = [];
            
            if ($canteen_id) {
                $sql .= " WHERE o.canteen_id = :canteen_id";
                $params[':canteen_id'] = $canteen_id;
            }

            $sql .= " GROUP BY o.order_id 
                      ORDER BY CASE o.status
                          WHEN 'placed' THEN 1
                          WHEN 'accepted' THEN 2
                          WHEN 'preparing' THEN 3
                          WHEN 'ready' THEN 4
                          WHEN 'completed' THEN 5
                          WHEN 'cancelled' THEN 6
                          ELSE 7
                      END, o.created_at DESC";
            
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error fetching orders: " . $e->getMessage());
            return [];
        }
    }

    public function getUniqueProducts($canteen_id) {
        try {
            $sql = "SELECT DISTINCT p.product_id, 
                    TRIM(p.name) as name 
                    FROM products p 
                    JOIN order_items oi ON p.product_id = oi.product_id
                    JOIN orders o ON oi.order_id = o.order_id
                    WHERE o.canteen_id = :canteen_id
                    ORDER BY p.name";
                    
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->execute(['canteen_id' => $canteen_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error getting unique products: " . $e->getMessage());
            return [];
        }
    }

    public function getOrderById($orderId, $canteenId) {
        try {
            $sql = "SELECT o.*, u.username, u.name as customer_name 
                    FROM orders o 
                    JOIN users u ON o.user_id = u.user_id 
                    WHERE o.order_id = :order_id AND o.canteen_id = :canteen_id";
            
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->execute([
                ':order_id' => $orderId,
                ':canteen_id' => $canteenId
            ]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$result) {
                throw new Exception("Order not found");
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Error getting order by ID: " . $e->getMessage());
            throw $e;
        }
    }

    public function getOrderProducts($orderId) {
        try {
            $sql = "SELECT p.name, oi.quantity, oi.unit_price as price 
                    FROM order_items oi 
                    JOIN products p ON oi.product_id = p.product_id 
                    WHERE oi.order_id = :order_id";
            
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->execute([':order_id' => $orderId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting order products: " . $e->getMessage());
            throw $e;
        }
    }

    public function updateOrderStatus($orderId, $newStatus, $canteenId = null) {
        try {
            $db = $this->db->connect();
            
            // If canteenId is provided, verify the order belongs to the canteen
            $sql = "SELECT status FROM orders WHERE order_id = :order_id";
            $params = [':order_id' => $orderId];
            
            if ($canteenId !== null) {
                $sql .= " AND canteen_id = :canteen_id";
                $params[':canteen_id'] = $canteenId;
            }
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$order) {
                throw new Exception("Order not found or does not belong to this canteen");
            }
            
            // Validate status transition
            $validTransitions = [
                'placed' => ['accepted', 'cancelled'],
                'accepted' => ['preparing'],
                'preparing' => ['ready'],
                'ready' => ['completed']
            ];
            
            $currentStatus = $order['status'];
            if (!isset($validTransitions[$currentStatus]) || 
                !in_array($newStatus, $validTransitions[$currentStatus])) {
                throw new Exception("Invalid status transition from {$currentStatus} to {$newStatus}");
            }
            
            // Update the order status
            $sql = "UPDATE orders SET 
                    status = :status,
                    updated_at = NOW()
                    WHERE order_id = :order_id";
            
            $params = [
                ':status' => $newStatus,
                ':order_id' => $orderId
            ];
            
            if ($canteenId !== null) {
                $sql .= " AND canteen_id = :canteen_id";
                $params[':canteen_id'] = $canteenId;
            }
                    
            $stmt = $db->prepare($sql);
            $result = $stmt->execute($params);
            
            if (!$result) {
                throw new Exception("Failed to update order status");
            }
            
            // If order is cancelled, restore stock
            if ($newStatus === 'cancelled') {
                $this->restoreStockForCancelledOrder($orderId);
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Error updating order status: " . $e->getMessage());
            throw $e;
        }
    }

    private function restoreStockForCancelledOrder($orderId) {
        try {
            $db = $this->db->connect();
            
            // Get order items
            $sql = "SELECT product_id, quantity FROM order_items WHERE order_id = :order_id";
            $stmt = $db->prepare($sql);
            $stmt->execute([':order_id' => $orderId]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Restore stock for each item
            foreach ($items as $item) {
                $sql = "UPDATE stocks 
                        SET quantity = quantity + :restore_quantity 
                        WHERE product_id = :product_id";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':restore_quantity' => $item['quantity'],
                    ':product_id' => $item['product_id']
                ]);
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Error restoring stock: " . $e->getMessage());
            throw $e;
        }
    }

    public function generateQueueNumber($order_id) {
        try {
            $db = $this->db->connect();
            
            // Get the order date
            $sql = "SELECT DATE(created_at) as order_date 
                    FROM orders 
                    WHERE order_id = :order_id";
            $stmt = $db->prepare($sql);
            $stmt->execute(['order_id' => $order_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                throw new Exception("Order not found");
            }
            
            // Format: YYYYMMDD-XXX (where XXX is the order_id padded with zeros)
            $queueNumber = date('Ymd', strtotime($result['order_date'])) . '-' . 
                          str_pad($order_id, 3, '0', STR_PAD_LEFT);
            
            // Check if the orders table has the queue_number column
            $checkColumnSql = "SHOW COLUMNS FROM orders LIKE 'queue_number'";
            $checkStmt = $db->prepare($checkColumnSql);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() > 0) {
                // Update the order with the queue number
                $sql = "UPDATE orders 
                        SET queue_number = :queue_number 
                        WHERE order_id = :order_id";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    'queue_number' => $queueNumber,
                    'order_id' => $order_id
                ]);
            } else {
                // If column doesn't exist, log it but don't fail
                error_log("Queue number column does not exist in orders table");
            }
            
            return $queueNumber;
            
        } catch (Exception $e) {
            error_log("Error generating queue number: " . $e->getMessage());
            // Return a fallback queue number if there's an error
            return date('Ymd') . '-' . str_pad($order_id, 3, '0', STR_PAD_LEFT);
        }
    }

    public function getStatusBadgeClass($status) {
        $badgeClasses = [
            'placed' => 'badge-primary',
            'accepted' => 'badge-info',
            'preparing' => 'badge-warning',
            'ready' => 'badge-success',
            'completed' => 'badge-secondary',
            'cancelled' => 'badge-danger'
        ];
        
        return $badgeClasses[$status] ?? 'badge-secondary';
    }

    public function getAvailableActions($status) {
        $actions = [];
        
        switch ($status) {
            case 'placed':
                $actions[] = ['action' => 'accept', 'label' => 'Accept', 'class' => 'btn-success'];
                $actions[] = ['action' => 'cancel', 'label' => 'Cancel', 'class' => 'btn-danger'];
                break;
            case 'accepted':
                $actions[] = ['action' => 'prepare', 'label' => 'Start Preparing', 'class' => 'btn-primary'];
                break;
            case 'preparing':
                $actions[] = ['action' => 'ready', 'label' => 'Mark Ready', 'class' => 'btn-info'];
                break;
            case 'ready':
                $actions[] = ['action' => 'complete', 'label' => 'Complete', 'class' => 'btn-success'];
                break;
        }
        
        return $actions;
    }

    public function fetchAllOrders() {
        try {
            $sql = "SELECT o.*, 
                    GROUP_CONCAT(DISTINCT p.name SEPARATOR ', ') as product_names,
                    SUM(oi.quantity) as total_quantity,
                    o.total_amount as total_price,
                    u.username,
                    CONCAT(u.given_name, ' ', u.last_name) as customer_name,
                    c.name as canteen_name
                    FROM orders o
                    LEFT JOIN order_items oi ON o.order_id = oi.order_id
                    LEFT JOIN products p ON oi.product_id = p.product_id
                    LEFT JOIN users u ON o.user_id = u.user_id
                    LEFT JOIN canteens c ON o.canteen_id = c.canteen_id
                    GROUP BY o.order_id
                    ORDER BY o.created_at DESC";
            
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error fetching all orders: " . $e->getMessage());
            return [];
        }
    }

    public function getAllProducts() {
        try {
            $sql = "SELECT DISTINCT p.product_id, p.name 
                    FROM products p 
                    JOIN order_items oi ON p.product_id = oi.product_id
                    ORDER BY p.name";
            
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error getting all products: " . $e->getMessage());
            return [];
        }
    }

    public function getOrderItems($order_id) {
        try {
            $sql = "SELECT oi.product_id, oi.quantity 
                    FROM order_items oi 
                    WHERE oi.order_id = :order_id";
                    
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->execute(['order_id' => $order_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error getting order items: " . $e->getMessage());
            throw $e;
        }
    }
}

?>