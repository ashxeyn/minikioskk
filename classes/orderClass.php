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
        $db = null;
        try {
            $db = $this->db->connect();
            $db->beginTransaction();

            error_log("Starting placeOrder process for user: $user_id");
            error_log("Cart items: " . print_r($cartItems, true));

            // Get canteen_id from the first cart item
            $canteen_id = $cartItems[0]['canteen_id'] ?? null;
            if (!$canteen_id) {
                throw new Exception("Invalid canteen information");
            }

            // Create the order
            $sql = "INSERT INTO orders (user_id, canteen_id, total_amount, status, payment_status, created_at) 
                    VALUES (:user_id, :canteen_id, :total_amount, 'placed', 'unpaid', NOW())";
            
            $stmt = $db->prepare($sql);
            $result = $stmt->execute([
                'user_id' => $user_id,
                'canteen_id' => $canteen_id,
                'total_amount' => $total
            ]);

            if (!$result) {
                throw new Exception("Failed to create order");
            }

            $order_id = $db->lastInsertId();
            error_log("Created order with ID: $order_id");

            // Insert order items and update stock
            foreach ($cartItems as $item) {
                // Verify stock availability
                $stockSql = "SELECT quantity FROM stocks WHERE product_id = :product_id FOR UPDATE";
                $stockStmt = $db->prepare($stockSql);
                $stockStmt->execute(['product_id' => $item['product_id']]);
                $stock = $stockStmt->fetch(PDO::FETCH_ASSOC);

                if (!$stock || $stock['quantity'] < $item['quantity']) {
                    throw new Exception("Insufficient stock for product: {$item['name']}");
                }

                // Add order item
                $itemSql = "INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal) 
                           VALUES (:order_id, :product_id, :quantity, :unit_price, :subtotal)";
                
                $itemStmt = $db->prepare($itemSql);
                $itemResult = $itemStmt->execute([
                    'order_id' => $order_id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['quantity'] * $item['unit_price']
                ]);

                if (!$itemResult) {
                    throw new Exception("Failed to add order item");
                }

                // Update stock
                $updateStockSql = "UPDATE stocks 
                                  SET quantity = quantity - :quantity 
                                  WHERE product_id = :product_id";
                
                $updateStockStmt = $db->prepare($updateStockSql);
                $stockResult = $updateStockStmt->execute([
                    'quantity' => $item['quantity'],
                    'product_id' => $item['product_id']
                ]);

                if (!$stockResult) {
                    throw new Exception("Failed to update stock");
                }
            }

            $db->commit();
            error_log("Order placed successfully");
            
            return [
                'success' => true,
                'order_id' => $order_id,
                'message' => 'Order placed successfully'
            ];

        } catch (Exception $e) {
            error_log("Error in placeOrder: " . $e->getMessage());
            if ($db && $db->inTransaction()) {
                $db->rollBack();
            }
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
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

           
            $sql = "SELECT product_id, quantity FROM order_items WHERE order_id = :order_id";
            $stmt = $db->prepare($sql);
            $stmt->execute(['order_id' => $order_id]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

            
            $sql = "UPDATE orders 
                    SET status = 'cancelled', 
                        updated_at = NOW() 
                    WHERE order_id = :order_id";
            
            $stmt = $db->prepare($sql);
            $stmt->execute(['order_id' => $order_id]);

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
            $sql = "SELECT o.*, u.username, 
                    CONCAT(u.given_name, ' ', u.last_name) as customer_name 
                    FROM orders o 
                    JOIN users u ON o.user_id = u.user_id 
                    WHERE o.order_id = :order_id";
            
            $params = [':order_id' => $orderId];
            
            // Add canteen check if provided
            if ($canteenId !== null) {
                $sql .= " AND o.canteen_id = :canteen_id";
                $params[':canteen_id'] = $canteenId;
            }
            
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->execute($params);
            
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

    public function updateOrderStatus($orderId, $status) {
        try {
            $db = $this->db->connect();
            $sql = "UPDATE orders SET status = ?, updated_at = NOW() WHERE order_id = ?";
            $stmt = $db->prepare($sql);
            return $stmt->execute([$status, $orderId]);
        } catch (Exception $e) {
            error_log("Error updating order status: " . $e->getMessage());
            return false;
        }
    }

    private function restoreStockForCancelledOrder($orderId) {
        try {
            $db = $this->db->connect();
            
           
            $sql = "SELECT product_id, quantity FROM order_items WHERE order_id = :order_id";
            $stmt = $db->prepare($sql);
            $stmt->execute([':order_id' => $orderId]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
          
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
            
           
            $sql = "SELECT DATE(created_at) as order_date 
                    FROM orders 
                    WHERE order_id = :order_id";
            $stmt = $db->prepare($sql);
            $stmt->execute(['order_id' => $order_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                throw new Exception("Order not found");
            }
            
       
            $queueNumber = date('Ymd', strtotime($result['order_date'])) . '-' . 
                          str_pad($order_id, 3, '0', STR_PAD_LEFT);
            
          
            $checkColumnSql = "SHOW COLUMNS FROM orders LIKE 'queue_number'";
            $checkStmt = $db->prepare($checkColumnSql);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() > 0) {
               
                $sql = "UPDATE orders 
                        SET queue_number = :queue_number 
                        WHERE order_id = :order_id";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    'queue_number' => $queueNumber,
                    'order_id' => $order_id
                ]);
            } else {
               
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
            $sql = "SELECT oi.product_id, oi.quantity, oi.unit_price, p.name 
                    FROM order_items oi 
                    JOIN products p ON oi.product_id = p.product_id 
                    WHERE oi.order_id = :order_id";
                    
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->execute(['order_id' => $order_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error getting order items: " . $e->getMessage());
            throw $e;
        }
    }

    public function deleteOrder($orderId) {
        try {
            $db = $this->db->connect();
            $db->beginTransaction();

            // First delete related order_items due to foreign key constraint
            $sql1 = "DELETE FROM order_items WHERE order_id = ?";
            $stmt1 = $db->prepare($sql1);
            $stmt1->execute([$orderId]);
            
            // Then delete the order
            $sql2 = "DELETE FROM orders WHERE order_id = ?";
            $stmt2 = $db->prepare($sql2);
            $result = $stmt2->execute([$orderId]);

            $db->commit();
            return $result;
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Error deleting order: " . $e->getMessage());
            return false;
        }
    }

    public function createOrder($orderData) {
        try {
            $sql = "INSERT INTO orders (user_id, total_amount, status, payment_status, canteen_id, created_at) 
                    VALUES (:user_id, :total_amount, :status, :payment_status, :canteen_id, NOW())";
            
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->execute([
                ':user_id' => $orderData['user_id'],
                ':total_amount' => $orderData['total_amount'],
                ':status' => $orderData['status'],
                ':payment_status' => $orderData['payment_status'],
                ':canteen_id' => $orderData['canteen_id']
            ]);
            
            return $this->db->connect()->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error creating order: " . $e->getMessage());
            throw new Exception("Failed to create order");
        }
    }

    public function addOrderItem($itemData) {
        try {
            $sql = "INSERT INTO order_items (order_id, product_id, quantity, unit_price) 
                    VALUES (:order_id, :product_id, :quantity, :unit_price)";
            
            $stmt = $this->db->connect()->prepare($sql);
            return $stmt->execute([
                ':order_id' => $itemData['order_id'],
                ':product_id' => $itemData['product_id'],
                ':quantity' => $itemData['quantity'],
                ':unit_price' => $itemData['unit_price']
            ]);
        } catch (PDOException $e) {
            error_log("Error adding order item: " . $e->getMessage());
            throw new Exception("Failed to add order item");
        }
    }

    public function hasActiveReorder($userId, $originalOrderId) {
        try {
            // Get items from the original order
            $originalItems = $this->getOrderItems($originalOrderId);
            if (empty($originalItems)) {
                return false;
            }

            // Get all active orders for the user
            $sql = "SELECT o.order_id 
                    FROM orders o 
                    WHERE o.user_id = :user_id 
                    AND o.status != 'completed' 
                    AND o.status != 'cancelled'";
            
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->execute([':user_id' => $userId]);
            $activeOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // For each active order, check if items match
            foreach ($activeOrders as $order) {
                $activeItems = $this->getOrderItems($order['order_id']);
                
                // Skip the original order itself
                if ($order['order_id'] == $originalOrderId) {
                    continue;
                }

                // Compare items
                if ($this->compareOrderItems($originalItems, $activeItems)) {
                    return true; // Found matching active order
                }
            }

            return false;

        } catch (Exception $e) {
            error_log("Error checking active reorders: " . $e->getMessage());
            return false;
        }
    }

    private function compareOrderItems($items1, $items2) {
        if (count($items1) !== count($items2)) {
            return false;
        }

        // Sort both arrays by product_id to ensure consistent comparison
        usort($items1, function($a, $b) {
            return $a['product_id'] - $b['product_id'];
        });
        usort($items2, function($a, $b) {
            return $a['product_id'] - $b['product_id'];
        });

        // Compare each item
        for ($i = 0; $i < count($items1); $i++) {
            if ($items1[$i]['product_id'] != $items2[$i]['product_id'] ||
                $items1[$i]['quantity'] != $items2[$i]['quantity']) {
                return false;
            }
        }

        return true;
    }
}

?>