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
                WHERE o.canteen_id = :canteen_id
                GROUP BY o.order_id
                ORDER BY o.created_at DESC";
    
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':canteen_id', $canteenId);
        $query->execute();
    
        return $query->fetchAll();
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

    function addProductToOrder($order_id, $product_id, $quantity)
{
        $product = $this->getProductById($product_id);
        if (!$product) {
            throw new Exception("Product not found.");
        }

        $total_price = $product['price'] * $quantity;  

        $sql = "SELECT * FROM order_items WHERE order_id = :order_id AND product_id = :product_id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':order_id', $order_id);
        $query->bindParam(':product_id', $product_id);
        $query->execute();

        if ($query->rowCount() > 0) {
            $existingItem = $query->fetch(PDO::FETCH_ASSOC);
            $new_quantity = $existingItem['quantity'] + $quantity;  
            $new_total_price = $new_quantity * $product['price'];  

            $sql = "UPDATE order_items 
                    SET quantity = :quantity, 
                        total_price = :total_price 
                    WHERE order_id = :order_id AND product_id = :product_id";
            $query = $this->db->connect()->prepare($sql);
            $query->bindParam(':order_id', $order_id);
            $query->bindParam(':product_id', $product_id);
            $query->bindParam(':quantity', $new_quantity);
            $query->bindParam(':total_price', $new_total_price);
            $query->execute();
        } else {
            
            $sql = "INSERT INTO order_items (order_id, product_id, quantity, total_price) 
                    VALUES (:order_id, :product_id, :quantity, :total_price)";
            $query = $this->db->connect()->prepare($sql);
            $query->bindParam(':order_id', $order_id);
            $query->bindParam(':product_id', $product_id);
            $query->bindParam(':quantity', $quantity);
            $query->bindParam(':total_price', $total_price);
            $query->execute();
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
    
    function placeOrder($user_id, $cartItems)
    {

    $sql = "SELECT order_id FROM orders WHERE user_id = :user_id AND status = 'pending'";
    $query = $this->db->connect()->prepare($sql);
    $query->bindParam(':user_id', $user_id);
    $query->execute();
    $order = $query->fetch();

    if (!$order) {
        throw new Exception("No pending order found.");
    }

    $order_id = $order['order_id'];

    $sql = "UPDATE orders SET status = 'completed' WHERE order_id = :order_id";
    $query = $this->db->connect()->prepare($sql);
    $query->bindParam(':order_id', $order_id);
    $query->execute();

    $sql = "DELETE FROM order_items WHERE order_id = :order_id";
    $query = $this->db->connect()->prepare($sql);
    $query->bindParam(':order_id', $order_id);
    $query->execute();
    }

    function getOrderStatus($user_id)
    {
        $sql = "SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 1";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':user_id', $user_id);
        $query->execute();
        return $query->fetch();
    }
    
    function removeFromCart($user_id, $product_id)
    {
        $sql = "SELECT order_id FROM orders WHERE user_id = :user_id AND status = 'pending' LIMIT 1";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':user_id', $user_id);
        $query->execute();
        $order = $query->fetch();

        if (!$order) {
            throw new Exception("No pending order found for this user.");
        }

        $order_id = $order['order_id'];

        $sql = "DELETE FROM order_items WHERE order_id = :order_id AND product_id = :product_id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':order_id', $order_id);
        $query->bindParam(':product_id', $product_id);
        $query->execute();
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
    $sql = "SELECT oi.*, p.name, p.price FROM order_items oi
                 JOIN products p ON oi.product_id = p.product_id
                 WHERE oi.order_id IN (SELECT order_id FROM orders WHERE user_id = :user_id AND status = 'pending')";
         $query = $this->db->connect()->prepare($sql);
         $query->bindParam(':user_id', $user_id);
         $query->execute();
         return $query->fetchAll();
    }
}

?>