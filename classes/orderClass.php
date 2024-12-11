<?php
require_once '../classes/databaseClass.php';

class Order
{
    protected $db;

    function __construct()
    {
        $this->db = new Database();
    }

    function fetchOrdersByCanteen($canteenId)
    {
        $sql = "SELECT o.order_id, 
                       u.username, 
                       CONCAT(u.last_name, ', ', u.given_name, ' ', u.middle_name) AS customer_name,
                       c.name AS canteen_name,
                       GROUP_CONCAT(CONCAT(p.name, ' (', oi.quantity, ')')) AS product_names,
                       SUM(oi.quantity) AS total_quantity,
                       SUM(oi.total_price) AS total_price,
                       o.status,
                       o.queue_number,
                       o.created_at
                FROM orders o
                JOIN users u ON o.user_id = u.user_id
                JOIN canteens c ON o.canteen_id = c.canteen_id
                JOIN order_items oi ON o.order_id = oi.order_id
                JOIN products p ON oi.product_id = p.product_id
                WHERE o.canteen_id = :canteen_id
                GROUP BY o.order_id, u.username, u.last_name, u.given_name, u.middle_name, 
                         c.name, o.status, o.queue_number, o.created_at
                ORDER BY o.created_at DESC";
        
        $stmt = $this->db->connect()->prepare($sql);
        $stmt->bindParam(':canteen_id', $canteenId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    function fetchOrders()
    {
        $sql = "SELECT o.order_id, 
                           u.username, 
                           CONCAT(u.last_name, ' , ', u.given_name, ' ', u.middle_name) AS customer_name, 
                           c.name AS canteen_name, 
                        GROUP_CONCAT(CONCAT(p.name, ' (', oi.quantity, ')') ORDER BY p.name ASC) AS product_names,
                           SUM(oi.quantity) AS total_quantity, 
                           SUM(oi.total_price) AS total_price, 
                           o.status, 
                           o.queue_number
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.user_id
                LEFT JOIN canteens c ON o.canteen_id = c.canteen_id
                LEFT JOIN order_items oi ON o.order_id = oi.order_id
                LEFT JOIN products p ON oi.product_id = p.product_id
                WHERE c.name IS NOT NULL
                GROUP BY o.order_id
                ORDER BY o.created_at DESC";
    
    $query = $this->db->connect()->prepare($sql);
    $query->execute();
    
        return $query->fetchAll();
    }
    

    function fetchOrderById($order_id)
{
    $query = "SELECT o.order_id, o.status, o.queue_number, o.user_id, o.canteen_id, 
                     p.name AS product_names 
              FROM orders o
              JOIN order_items oi ON o.order_id = oi.order_id
              JOIN products p ON oi.product_id = p.product_id
              WHERE o.order_id = ?";
    
    $stmt = $this->db->connect()->prepare($query);
    $stmt->execute([$order_id]);

    $order = $stmt->fetch();
    
    return $order;
}


    
    function deleteOrder($order_id)
    {
            $sql = "DELETE FROM order_items WHERE order_id = :order_id";
            $query = $this->db->connect()->prepare($sql);
            $query->bindParam(':order_id', $order_id);
            $query->execute();

            $sql = "DELETE FROM orders WHERE order_id = :order_id";
            $query = $this->db->connect()->prepare($sql);
            $query->bindParam(':order_id', $order_id);
            $query->execute();

            return true;
    }

    function updateOrder($order_id, $status, $queue_number)
    {
            $sql = "UPDATE orders SET status = :status, queue_number = :queue_number WHERE order_id = :order_id";
            $query = $this->db->connect()->prepare($sql);
            $query->bindParam(':status', $status);
            $query->bindParam(':queue_number', $queue_number);
            $query->bindParam(':order_id', $order_id);
            $query->execute();

            return true;
    }

    function addOrder($user_id)
    {
            $sql = "SELECT order_id FROM orders WHERE user_id = :user_id AND status = 'pending' LIMIT 1";
            $query = $this->db->connect()->prepare($sql);
            $query->bindParam(':user_id', $user_id);
            $query->execute();
            $order = $query->fetch();
    
            if (!$order) {
                $sql = "INSERT INTO orders (user_id, status) VALUES (:user_id, 'pending')";
                $query = $this->db->connect()->prepare($sql);
                $query->bindParam(':user_id', $user_id);
                $query->execute();
                $order_id = $this->db->connect()->lastInsertId();
    
                if (!$order_id) {
                    throw new Exception("Failed to create a new order.");
                }
            } else {
                $order_id = $order['order_id'];
            }

            return $order_id; 
    }
        

    function getAvailableProductsByCanteen($canteen_id)
    {
        $sql = "SELECT * FROM products WHERE canteen_id = :canteen_id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':canteen_id', $canteen_id);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    function getOrCreatePendingOrder($user_id) {
        try {
            $conn = $this->db->connect();
            $conn->beginTransaction();

            // First check if there's an existing pending order
            $sql = "SELECT o.* FROM orders o 
                   WHERE o.user_id = :user_id 
                   AND o.status = 'pending' 
                   ORDER BY o.created_at DESC 
                   LIMIT 1";
            $query = $conn->prepare($sql);
            $query->bindParam(':user_id', $user_id);
            $query->execute();
            $order = $query->fetch();

            if (!$order) {
                // Get canteen_id from cart items
                $sql = "SELECT p.canteen_id 
                        FROM cart_items ci
                        JOIN products p ON ci.product_id = p.product_id
                        WHERE ci.user_id = :user_id
                        LIMIT 1";
                $query = $conn->prepare($sql);
                $query->bindParam(':user_id', $user_id);
                $query->execute();
                $canteen_id = $query->fetchColumn();

                if (!$canteen_id) {
                    throw new Exception("No canteen found for cart items");
                }

                // Create new order with canteen_id
                $sql = "INSERT INTO orders (user_id, canteen_id, status, created_at) 
                        VALUES (:user_id, :canteen_id, 'pending', NOW())";
                $query = $conn->prepare($sql);
                $query->bindParam(':user_id', $user_id);
                $query->bindParam(':canteen_id', $canteen_id);
                $query->execute();
                $order_id = $conn->lastInsertId();
    
                if (!$order_id) {
                    throw new Exception("Failed to create a new order.");
                }
            } else {
                $order_id = $order['order_id'];
            }

            $conn->commit();
            return $order_id;
        } catch (Exception $e) {
            if (isset($conn)) {
                $conn->rollBack();
            }
            error_log("Error in getOrCreatePendingOrder: " . $e->getMessage());
            throw $e;
        }
    }

    function addToCart($user_id, $product_id, $quantity) {
        try {
            $conn = $this->db->connect();
            $conn->beginTransaction();

            // Get the canteen_id for the product
            $sql = "SELECT p.canteen_id, p.price 
                    FROM products p 
                    WHERE p.product_id = :product_id";
            $stmt = $conn->prepare($sql);
            $stmt->execute(['product_id' => $product_id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$product) {
                throw new Exception("Product not found");
            }

            // Get or create pending order with canteen_id
            $sql = "SELECT o.* FROM orders o 
                   WHERE o.user_id = :user_id 
                   AND o.status = 'pending' 
                   AND o.canteen_id = :canteen_id
                   ORDER BY o.created_at DESC 
                   LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                'user_id' => $user_id,
                'canteen_id' => $product['canteen_id']
            ]);
            $order = $stmt->fetch();

            if (!$order) {
                // Create new order with canteen_id
                $sql = "INSERT INTO orders (user_id, canteen_id, status, created_at) 
                        VALUES (:user_id, :canteen_id, 'pending', NOW())";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    'user_id' => $user_id,
                    'canteen_id' => $product['canteen_id']
                ]);
                $order_id = $conn->lastInsertId();
            } else {
                $order_id = $order['order_id'];
            }

            // Get product price
            $sql = "SELECT price FROM products WHERE product_id = :product_id";
            $query = $conn->prepare($sql);
            $query->bindParam(':product_id', $product_id);
            $query->execute();
            $product = $query->fetch();

            if (!$product) {
                throw new Exception("Product not found");
            }

            $total_price = $product['price'] * $quantity;

            // Check if product already exists in order
            $sql = "SELECT order_item_id, quantity FROM order_items 
                    WHERE order_id = :order_id AND product_id = :product_id";
            $query = $conn->prepare($sql);
            $query->bindParam(':order_id', $order_id);
            $query->bindParam(':product_id', $product_id);
            $query->execute();
            $existing_item = $query->fetch();

            if ($existing_item) {
                // Update existing item
                $sql = "UPDATE order_items 
                        SET quantity = quantity + :quantity,
                            total_price = (quantity + :quantity) * :price 
                        WHERE order_id = :order_id AND product_id = :product_id";
                $query = $conn->prepare($sql);
                $query->bindParam(':quantity', $quantity);
                $query->bindParam(':price', $product['price']);
            } else {
                // Insert new item
                $sql = "INSERT INTO order_items (order_id, product_id, quantity, price, total_price) 
                        VALUES (:order_id, :product_id, :quantity, :price, :total_price)";
                $query = $conn->prepare($sql);
                $query->bindParam(':total_price', $total_price);
                $query->bindParam(':price', $product['price']);
            }

            $query->bindParam(':order_id', $order_id);
            $query->bindParam(':product_id', $product_id);
            $query->bindParam(':quantity', $quantity);
            $query->execute();

            $conn->commit();
            return true;

        } catch (Exception $e) {
            if (isset($conn)) {
                $conn->rollBack();
            }
            error_log("Error adding to cart: " . $e->getMessage());
            throw $e;
        }
    }

    

    
function getOrderProducts($order_id)
{
    $sql = "SELECT oi.*, p.name, p.price FROM order_items oi
            JOIN products p ON oi.product_id = p.product_id
            WHERE oi.order_id = :order_id";
    $query = $this->db->connect()->prepare($sql);
    $query->bindParam(':order_id', $order_id);
    $query->execute();
    return $query->fetchAll();
}


function addQuantityToProduct($order_id, $product_id, $quantity) {
        $product = $this->getProductById($product_id);
        if (!$product) {
            throw new Exception("Product not found");
        }

        $sql = "SELECT quantity, total_price FROM order_items 
                WHERE order_id = :order_id AND product_id = :product_id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':order_id', $order_id);
        $query->bindParam(':product_id', $product_id);
        $query->execute();
        $currentItem = $query->fetch(PDO::FETCH_ASSOC);

        if ($currentItem) {
            $newQuantity = $currentItem['quantity'] + $quantity;
            $newItemTotalPrice = $newQuantity * $product['price'];

            $sql = "UPDATE order_items 
                    SET quantity = :quantity, 
                        total_price = :total_price 
                    WHERE order_id = :order_id 
                    AND product_id = :product_id";
        } else {
            $newQuantity = $quantity;
            $newItemTotalPrice = $quantity * $product['price'];

            $sql = "INSERT INTO order_items (order_id, product_id, quantity, total_price) 
                    VALUES (:order_id, :product_id, :quantity, :total_price)";
        }

        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':order_id', $order_id);
        $query->bindParam(':product_id', $product_id);
        $query->bindParam(':quantity', $newQuantity);
        $query->bindParam(':total_price', $newItemTotalPrice);
        $result = $query->execute();

        if ($result) {
            $this->updateOrderTotalPrice($order_id);
        }

        return $result;
}

function removeQuantityFromProduct($order_id, $product_id, $quantity) {
        $sql = "SELECT oi.quantity, oi.total_price, p.price 
                FROM order_items oi
                JOIN products p ON p.product_id = oi.product_id
                WHERE oi.order_id = :order_id AND oi.product_id = :product_id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':order_id', $order_id);
        $query->bindParam(':product_id', $product_id);
        $query->execute();
        $item = $query->fetch(PDO::FETCH_ASSOC);

        if (!$item) {
            throw new Exception("Product not found in order");
        }

        $newQuantity = $item['quantity'] - $quantity;
        
        if ($newQuantity > 0) {
            $newTotalPrice = $newQuantity * $item['price'];
            $sql = "UPDATE order_items 
                    SET quantity = :quantity, total_price = :total_price 
                    WHERE order_id = :order_id AND product_id = :product_id";
            $query = $this->db->connect()->prepare($sql);
            $query->bindParam(':quantity', $newQuantity);
            $query->bindParam(':total_price', $newTotalPrice);
            $query->bindParam(':order_id', $order_id);
            $query->bindParam(':product_id', $product_id);
            $result = $query->execute();
        } else {
            $sql = "DELETE FROM order_items 
                    WHERE order_id = :order_id AND product_id = :product_id";
            $query = $this->db->connect()->prepare($sql);
            $query->bindParam(':order_id', $order_id);
            $query->bindParam(':product_id', $product_id);
            $result = $query->execute();
        }

        if ($result) {
            $this->updateOrderTotalPrice($order_id);
        }

        return $result;
}

function updateOrderTotalPrice($order_id)
{
    try {
        $sql = "UPDATE orders o 
                SET total_price = (
                    SELECT COALESCE(SUM(total_price), 0)
                    FROM order_items 
                    WHERE order_id = :order_id
                )
                WHERE o.order_id = :order_id";
        
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':order_id', $order_id);
        return $query->execute();
    } catch (Exception $e) {
        error_log("Error updating order total price: " . $e->getMessage());
        return false;
    }
}

function removeProductFromOrder($order_id, $product_id) {
    try {
        $sql = "DELETE FROM order_items 
                WHERE order_id = :order_id AND product_id = :product_id";
        
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':order_id', $order_id);
        $query->bindParam(':product_id', $product_id);
        
        return $query->execute();
    } catch (PDOException $e) {
        throw new Exception("Error removing product: " . $e->getMessage());
    }
}

function getCurrentQuantity($order_id, $product_id) {
    $sql = "SELECT quantity FROM order_items 
            WHERE order_id = :order_id AND product_id = :product_id";
    
    $query = $this->db->connect()->prepare($sql);
    $query->bindParam(':order_id', $order_id);
    $query->bindParam(':product_id', $product_id);
    $query->execute();
    
    $result = $query->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['quantity'] : 0;
}


    function getProductById($product_id)
    {
        $sql = "SELECT * FROM products WHERE product_id = :product_id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':product_id', $product_id);
        $query->execute();
        return $query->fetch();
    }
    
    function getAllOrders($user_id)
    {
    $sql = "SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC";
    $query = $this->db->connect()->prepare($sql);
    $query->bindParam(':user_id', $user_id);
    $query->execute();
    return $query->fetchAll();
    }
    
    function placeOrder($user_id, $cartItems) {
        try {
            $conn = $this->db->connect();
            $conn->beginTransaction();

            // Get the canteen_id and verify all items are from same canteen
            $sql = "SELECT DISTINCT p.canteen_id, c.name as canteen_name 
                    FROM cart_items ci
                    JOIN products p ON ci.product_id = p.product_id
                    JOIN canteens c ON p.canteen_id = c.canteen_id
                    WHERE ci.user_id = :user_id";
            $stmt = $conn->prepare($sql);
            $stmt->execute(['user_id' => $user_id]);
            $canteens = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($canteens) === 0) {
                throw new Exception("No items in cart");
            }
            if (count($canteens) > 1) {
                throw new Exception("Items from multiple canteens found in cart");
            }

            $canteen = $canteens[0];
            $queue_number = $this->getNextQueueNumber($canteen['canteen_id']);

            // Create new order
            $sql = "INSERT INTO orders (user_id, canteen_id, status, queue_number, created_at) 
                    VALUES (:user_id, :canteen_id, 'pending', :queue_number, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                'user_id' => $user_id,
                'canteen_id' => $canteen['canteen_id'],
                'queue_number' => $queue_number
            ]);
            
            $order_id = $conn->lastInsertId();

            // Insert order items
            $sql = "INSERT INTO order_items (order_id, product_id, canteen_id, quantity, total_price) 
                    VALUES (:order_id, :product_id, :canteen_id, :quantity, :total_price)";
            $stmt = $conn->prepare($sql);

            foreach ($cartItems as $item) {
                $stmt->execute([
                    'order_id' => $order_id,
                    'product_id' => $item['product_id'],
                    'canteen_id' => $canteen['canteen_id'],
                    'quantity' => $item['quantity'],
                    'total_price' => $item['total_price']
                ]);
            }

            $conn->commit();
            return [
                'success' => true,
                'order_id' => $order_id,
                'queue_number' => $queue_number,
                'canteen_name' => $canteen['canteen_name']
            ];
        } catch (Exception $e) {
            if (isset($conn)) {
                $conn->rollBack();
            }
            error_log("Error placing order: " . $e->getMessage());
            throw $e;
        }
    }

    private function getNextQueueNumber($canteen_id) {
        $sql = "SELECT COUNT(*) + 1 
                FROM orders 
                WHERE canteen_id = :canteen_id 
                AND DATE(created_at) = CURDATE()";
        $stmt = $this->db->connect()->prepare($sql);
        $stmt->execute(['canteen_id' => $canteen_id]);
        $number = $stmt->fetchColumn();
        return sprintf("%03d", $number); // Format as 001, 002, etc.
    }

    function getOrderStatus($user_id)
    {
        $sql = "SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 1";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':user_id', $user_id);
        $query->execute();
        return $query->fetch();
    }
    
    function removeFromCart($user_id, $product_id) {
        try {
            $conn = $this->db->connect();
            
            // First get the pending order id for this user
            $sql = "SELECT order_id FROM orders 
                    WHERE user_id = :user_id AND status = 'pending' 
                    LIMIT 1";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute(['user_id' => $user_id]);
            $order = $stmt->fetch();

            if (!$order) {
                throw new Exception("No pending order found");
            }

            // Start transaction
            $conn->beginTransaction();

            // Remove the item from order_items
            $sql = "DELETE FROM order_items 
                    WHERE order_id = :order_id AND product_id = :product_id";
            
            $stmt = $conn->prepare($sql);
            $result = $stmt->execute([
                'order_id' => $order['order_id'],
                'product_id' => $product_id
            ]);

            if (!$result) {
                throw new Exception("Failed to remove item from cart");
            }

            // Check if this was the last item in the order
            $sql = "SELECT COUNT(*) as count FROM order_items WHERE order_id = :order_id";
            $stmt = $conn->prepare($sql);
            $stmt->execute(['order_id' => $order['order_id']]);
            $itemCount = $stmt->fetch(PDO::FETCH_ASSOC);

            // If this was the last item, remove the order too
            if ($itemCount['count'] == 0) {
                $sql = "DELETE FROM orders WHERE order_id = :order_id";
                $stmt = $conn->prepare($sql);
                $stmt->execute(['order_id' => $order['order_id']]);
            } else {
                // Update the total price of the order
                $sql = "UPDATE orders o 
                       SET o.total_amount = (
                           SELECT SUM(oi.total_price) 
                           FROM order_items oi 
                           WHERE oi.order_id = :order_id
                       )
                       WHERE o.order_id = :order_id";
                $stmt = $conn->prepare($sql);
                $stmt->execute(['order_id' => $order['order_id']]);
            }

            // Commit transaction
            $conn->commit();
            return true;

        } catch (Exception $e) {
            // Rollback transaction on error
            if (isset($conn) && $conn->inTransaction()) {
                $conn->rollBack();
            }
            error_log("Error removing item from cart: " . $e->getMessage());
            throw $e;
        }
    }
    
    function updateCartQuantity($user_id, $product_id, $new_quantity)
    {
        $sql = "SELECT order_id FROM orders WHERE user_id = :user_id AND status = 'pending' LIMIT 1";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':user_id', $user_id);
        $query->execute();
        $order = $query->fetch();

        if (!$order) {
            throw new Exception("No pending order found for the user.");
        }

        $order_id = $order['order_id'];

        $product = $this->getProductById($product_id);
        if (!$product) {
            throw new Exception("Product not found.");
        }

        $total_price = $product['price'] * $new_quantity;

        error_log("Updating order: order_id = $order_id, product_id = $product_id, quantity = $new_quantity, total_price = $total_price");

        $sql = "UPDATE order_items 
                SET quantity = :quantity, total_price = :total_price 
                WHERE order_id = :order_id AND product_id = :product_id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':quantity', $new_quantity);
        $query->bindParam(':total_price', $total_price);
        $query->bindParam(':order_id', $order_id);
        $query->bindParam(':product_id', $product_id);
        $query->execute();
    }
    
    function getCartItems($user_id)
    {
        $sql = "SELECT oi.*, p.name, p.price as unit_price 
                FROM orders o 
                JOIN order_items oi ON o.order_id = oi.order_id 
                JOIN products p ON oi.product_id = p.product_id 
                WHERE o.user_id = :user_id AND o.status = 'pending'";
        
        $stmt = $this->db->connect()->prepare($sql);
        $stmt->execute(['user_id' => $user_id]);
        return $stmt->fetchAll();
    }

    function getUserOrders($user_id)
    {
        $sql = "SELECT o.*, c.name as canteen_name 
                FROM orders o 
                LEFT JOIN canteens c ON o.canteen_id = c.canteen_id 
                WHERE o.user_id = :user_id 
                ORDER BY o.created_at DESC";
                
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':user_id', $user_id);
        $query->execute();
        
        return $query->fetchAll();
    }

    public function clearCart($user_id) {
        try {
            $conn = $this->db->connect();
            
            // Get all pending orders for the user
            $sql = "SELECT order_id FROM orders 
                    WHERE user_id = :user_id AND status = 'pending'";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute(['user_id' => $user_id]);
            $orders = $stmt->fetchAll();

            if (empty($orders)) {
                return true; // No pending orders means cart is already empty
            }

            // Start transaction
            $conn->beginTransaction();

            try {
                foreach ($orders as $order) {
                    // Delete all items from the order
                    $sql = "DELETE FROM order_items WHERE order_id = :order_id";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute(['order_id' => $order['order_id']]);

                    // Delete the order itself
                    $sql = "DELETE FROM orders WHERE order_id = :order_id";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute(['order_id' => $order['order_id']]);
                }

                // Commit transaction
                $conn->commit();
                return true;
            } catch (Exception $e) {
                $conn->rollBack();
                error_log("Error clearing cart: " . $e->getMessage());
                return false;
            }
        } catch (Exception $e) {
            error_log("Error clearing cart: " . $e->getMessage());
            return false;
        }
    }
}

?>