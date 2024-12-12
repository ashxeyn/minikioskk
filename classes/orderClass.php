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
            
            // Start transaction
            if (!$db->inTransaction()) {
                $db->beginTransaction();
            }

            // Get canteen_id from the first item (assuming all items are from same canteen)
            $canteen_id = $cartItems[0]['canteen_id'] ?? 1;  // Default to 1 if not found

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

            // Insert order items
            $sql = "INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal) 
                    VALUES (:order_id, :product_id, :quantity, :unit_price, :subtotal)";
            
            $stmt = $db->prepare($sql);
            
            foreach ($cartItems as $item) {
                $subtotal = $item['quantity'] * $item['unit_price'];
                $stmt->execute([
                    'order_id' => $order_id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $subtotal
                ]);
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

    private function generateQueueNumber() {
        try {
            $db = $this->db->connect();
            
            // Get today's date in Y-m-d format
            $today = date('Y-m-d');
            
            // Get the current highest queue number for today
            $sql = "SELECT MAX(CAST(SUBSTRING(queue_number, -3) AS UNSIGNED)) as max_num 
                    FROM orders 
                    WHERE DATE(created_at) = :today";
            
            $stmt = $db->prepare($sql);
            $stmt->execute(['today' => $today]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $nextNum = ($result['max_num'] ?? 0) + 1;
            
            // Format: YYYYMMDD-XXX (where XXX is a sequential number)
            return date('Ymd') . '-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
            
        } catch (Exception $e) {
            error_log("Error generating queue number: " . $e->getMessage());
            throw $e;
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

    public function updateOrderStatus($order_id, $status) {
        try {
            $sql = "UPDATE orders 
                    SET status = :status, 
                        updated_at = NOW() 
                    WHERE order_id = :order_id";
            
            $stmt = $this->db->connect()->prepare($sql);
            return $stmt->execute([
                'order_id' => $order_id,
                'status' => $status
            ]);
            
        } catch (Exception $e) {
            error_log("Error updating order status: " . $e->getMessage());
            return false;
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
}

?>