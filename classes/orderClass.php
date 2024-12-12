<?php
require_once '../classes/databaseClass.php';

class Order
{
    protected $db;

    function __construct()
    {
        try {
            $this->db = new Database();
            // Test the connection
            $conn = $this->db->connect();
            error_log("Database connection established successfully");
        } catch (Exception $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw new Exception("Failed to connect to database");
        }
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

    public function addToCart($user_id, $product_id, $quantity = 1)
    {
        try {
            $db = $this->db->connect();
            $db->beginTransaction();

            // First verify the user exists
            $sql = "SELECT user_id FROM users WHERE user_id = :user_id";
            $stmt = $db->prepare($sql);
            $stmt->execute(['user_id' => $user_id]);
            if (!$stmt->fetch()) {
                throw new Exception("Invalid user");
            }

            // Get product details
            $product = $this->getProductById($product_id);
            if (!$product) {
                throw new Exception("Product not found");
            }

            // Calculate subtotal
            $subtotal = $quantity * $product['price'];

            // Check if user has an active cart
            $sql = "SELECT cart_id FROM carts WHERE user_id = :user_id";
            $stmt = $db->prepare($sql);
            $stmt->execute(['user_id' => $user_id]);
            $cart = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$cart) {
                // Create new cart
                $sql = "INSERT INTO carts (user_id) VALUES (:user_id)";
                $stmt = $db->prepare($sql);
                $stmt->execute(['user_id' => $user_id]);
                $cart_id = $db->lastInsertId();
            } else {
                $cart_id = $cart['cart_id'];
            }

            // Check if product already exists in cart
            $sql = "SELECT cart_item_id, quantity FROM cart_items 
                    WHERE cart_id = :cart_id AND product_id = :product_id";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'cart_id' => $cart_id,
                'product_id' => $product_id
            ]);
            $existingItem = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existingItem) {
                // Update existing item quantity and subtotal
                $newQuantity = $existingItem['quantity'] + $quantity;
                $newSubtotal = $newQuantity * $product['price'];
                
                $sql = "UPDATE cart_items 
                        SET quantity = :quantity, subtotal = :subtotal 
                        WHERE cart_item_id = :cart_item_id";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    'quantity' => $newQuantity,
                    'subtotal' => $newSubtotal,
                    'cart_item_id' => $existingItem['cart_item_id']
                ]);
            } else {
                // Add new item to cart_items
                $sql = "INSERT INTO cart_items (cart_id, product_id, quantity, unit_price, subtotal) 
                        VALUES (:cart_id, :product_id, :quantity, :unit_price, :subtotal)";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    'cart_id' => $cart_id,
                    'product_id' => $product_id,
                    'quantity' => $quantity,
                    'unit_price' => $product['price'],
                    'subtotal' => $subtotal
                ]);
            }

            $db->commit();
            return true;

        } catch (Exception $e) {
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
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
                SET total_amount = (
                    SELECT COALESCE(SUM(subtotal), 0) 
                    FROM order_items 
                    WHERE order_id = :order_id
                )
                WHERE order_id = :order_id";
        
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':order_id', $order_id);
        return $query->execute();
    } catch (PDOException $e) {
        error_log($e->getMessage());
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
    
    public function placeOrder($user_id, $cartItems, $total_amount) {
        try {
            $db = $this->db->connect();
            $db->beginTransaction();

            // Get canteen_id from the first cart item's product
            $sql = "SELECT p.canteen_id 
                    FROM products p 
                    WHERE p.product_id = :product_id";
            $stmt = $db->prepare($sql);
            $stmt->execute(['product_id' => $cartItems[0]['product_id']]);
            $canteen = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$canteen) {
                throw new Exception("Canteen not found for the product");
            }

            // Calculate total amount
            $total_amount = 0;
            foreach ($cartItems as $item) {
                $total_amount += ($item['unit_price'] * $item['quantity']);
            }

            // Generate queue number (format: YYYYMMDD-XXX)
            $date = date('Ymd');
            $sql = "SELECT COUNT(*) + 1 as next_num FROM orders WHERE DATE(created_at) = CURDATE()";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $queue_number = $date . '-' . str_pad($result['next_num'], 3, '0', STR_PAD_LEFT);

            // Create new order
            $sql = "INSERT INTO orders (user_id, canteen_id, total_amount, status, payment_status, queue_number, created_at) 
                    VALUES (:user_id, :canteen_id, :total_amount, 'pending', 'unpaid', :queue_number, NOW())";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'user_id' => $user_id,
                'canteen_id' => $canteen['canteen_id'],
                'total_amount' => $total_amount,
                'queue_number' => $queue_number
            ]);

            $order_id = $db->lastInsertId();

            // Transfer cart items to order_items
            foreach ($cartItems as $item) {
                $sql = "INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal) 
                        VALUES (:order_id, :product_id, :quantity, :unit_price, :subtotal)";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    'order_id' => $order_id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => ($item['unit_price'] * $item['quantity'])
                ]);
            }

            // Clear the cart after successful order placement
            $this->clearCart($user_id);

            $db->commit();
            return [
                'success' => true,
                'order_id' => $order_id,
                'queue_number' => $queue_number,
                'canteen_name' => $this->getCanteenName($canteen['canteen_id'])
            ];

        } catch (Exception $e) {
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Error placing order: " . $e->getMessage());
            throw $e;
        }
    }

    private function getCanteenName($canteen_id) {
        try {
            $sql = "SELECT name FROM canteens WHERE canteen_id = :canteen_id";
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->execute(['canteen_id' => $canteen_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['name'] ?? '';
        } catch (Exception $e) {
            error_log("Error getting canteen name: " . $e->getMessage());
            return '';
        }
    }

    public function clearCart($user_id)
    {
        try {
            $conn = $this->db->connect();
            $conn->beginTransaction();

            // Get user's cart
            $sql = "SELECT cart_id FROM carts WHERE user_id = :user_id";
            $stmt = $conn->prepare($sql);
            $stmt->execute(['user_id' => $user_id]);
            $cart = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($cart) {
                // Delete all items from cart_items
                $sql = "DELETE FROM cart_items WHERE cart_id = :cart_id";
                $stmt = $conn->prepare($sql);
                $stmt->execute(['cart_id' => $cart['cart_id']]);

                // Optionally delete the cart itself
                $sql = "DELETE FROM carts WHERE cart_id = :cart_id";
                $stmt = $conn->prepare($sql);
                $stmt->execute(['cart_id' => $cart['cart_id']]);
            }

            $conn->commit();
            return true;

        } catch (Exception $e) {
            if (isset($conn) && $conn->inTransaction()) {
                $conn->rollBack();
            }
            error_log("Error clearing cart: " . $e->getMessage());
            throw $e;
        }
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
                           SELECT SUM(oi.subtotal) 
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

        $subtotal = $product['price'] * $new_quantity;

        $sql = "UPDATE order_items 
                SET quantity = :quantity, subtotal = :subtotal 
                WHERE order_id = :order_id AND product_id = :product_id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':quantity', $new_quantity);
        $query->bindParam(':subtotal', $subtotal);
        $query->bindParam(':order_id', $order_id);
        $query->bindParam(':product_id', $product_id);
        $query->execute();
    }
    
    function getCartItems($user_id) {
        try {
            $sql = "SELECT ci.*, p.name, p.description, p.price 
                    FROM carts c 
                    JOIN cart_items ci ON c.cart_id = ci.cart_id 
                    JOIN products p ON ci.product_id = p.product_id 
                    WHERE c.user_id = :user_id";
                    
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->execute(['user_id' => $user_id]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("Found " . count($items) . " items in cart for user " . $user_id);
            return $items;
        } catch (Exception $e) {
            error_log("Error getting cart items: " . $e->getMessage());
            throw $e;
        }
    }

    function getUserOrders($user_id)
    {
        try {
            $sql = "SELECT 
                    o.order_id,
                    o.user_id,
                    o.canteen_id,
                    o.total_amount,
                    o.status,
                    o.payment_status,
                    o.payment_method,
                    o.created_at,
                    c.name as canteen_name,
                    GROUP_CONCAT(
                        CONCAT(p.name, ' (', oi.quantity, ')')
                        SEPARATOR ', '
                    ) as items
                FROM orders o 
                INNER JOIN canteens c ON o.canteen_id = c.canteen_id 
                INNER JOIN order_items oi ON o.order_id = oi.order_id
                INNER JOIN products p ON oi.product_id = p.product_id
                WHERE o.user_id = :user_id 
                GROUP BY 
                    o.order_id,
                    o.user_id,
                    o.canteen_id,
                    o.total_amount,
                    o.status,
                    o.payment_status,
                    o.payment_method,
                    o.created_at,
                    c.name
                ORDER BY o.created_at DESC";
                
            $query = $this->db->connect()->prepare($sql);
            $query->bindParam(':user_id', $user_id);
            $query->execute();
            
            error_log("Fetching orders for user_id: " . $user_id);
            $orders = $query->fetchAll(PDO::FETCH_ASSOC);
            error_log("Found " . count($orders) . " orders");
            
            return $orders;
        } catch (Exception $e) {
            error_log("Error in getUserOrders: " . $e->getMessage());
            throw $e;
        }
    }

    function addOrderItem($order_id, $product_id, $quantity) {
        try {
            // Get product price
            $product = $this->getProductById($product_id);
            if (!$product) {
                throw new Exception("Product not found");
            }

            $subtotal = $quantity * $product['price'];
            
            $sql = "INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal) 
                    VALUES (:order_id, :product_id, :quantity, :unit_price, :subtotal)";
            
            $query = $this->db->connect()->prepare($sql);
            $query->bindParam(':order_id', $order_id);
            $query->bindParam(':product_id', $product_id);
            $query->bindParam(':quantity', $quantity);
            $query->bindParam(':unit_price', $product['price']);
            $query->bindParam(':subtotal', $subtotal);
            
            return $query->execute();
        } catch (Exception $e) {
            error_log("Error adding order item: " . $e->getMessage());
            throw $e;
        }
    }

    public function getCartTotal($user_id) {
        try {
            $sql = "SELECT SUM(ci.subtotal) as total 
                    FROM carts c 
                    JOIN cart_items ci ON c.cart_id = ci.cart_id 
                    WHERE c.user_id = :user_id";
                    
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->execute(['user_id' => $user_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (Exception $e) {
            error_log("Error getting cart total: " . $e->getMessage());
            throw $e;
        }
    }

    private function userExists($user_id) {
        $sql = "SELECT COUNT(*) FROM users WHERE user_id = :user_id";
        $stmt = $this->db->connect()->prepare($sql);
        $stmt->execute(['user_id' => $user_id]);
        return (bool)$stmt->fetchColumn();
    }

   

    private function getCanteenIdFromProduct($product_id) {
        $sql = "SELECT canteen_id FROM products WHERE product_id = ?";
        $stmt = $this->db->connect()->prepare($sql);
        $stmt->execute([$product_id]);
        return $stmt->fetchColumn();
    }

  
}

?>