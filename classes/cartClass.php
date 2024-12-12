<?php
require_once '../classes/databaseClass.php';

class Cart {
    protected $db;

    function __construct() {
        try {
            $this->db = new Database();
            $conn = $this->db->connect();
            error_log("Database connection established successfully in CartClass");
        } catch (Exception $e) {
            error_log("Database connection error in CartClass: " . $e->getMessage());
            throw new Exception("Failed to connect to database");
        }
    }

    public function addToCart($user_id, $product_id, $quantity = 1) {
        $db = null;
        try {
            $db = $this->db->connect();
            $db->beginTransaction();

            // Debug log
            error_log("Starting addToCart - User ID: $user_id, Product ID: $product_id, Quantity: $quantity");

            // First verify the product exists and get its details
            $sql = "SELECT p.*, c.canteen_id 
                    FROM products p 
                    JOIN canteens c ON p.canteen_id = c.canteen_id 
                    WHERE p.product_id = :product_id";
            $stmt = $db->prepare($sql);
            $stmt->execute(['product_id' => $product_id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$product) {
                throw new Exception("Product not found");
            }

            // Get existing cart or create new one
            $cart_id = $this->getOrCreateCart($user_id, $db);

            // Check if this product is already in the cart
            $existingItem = $this->getCartItem($cart_id, $product_id, $db);

            if ($existingItem) {
                $this->updateCartItem($existingItem, $quantity, $product['price'], $db);
            } else {
                $this->addCartItem($cart_id, $product_id, $quantity, $product['price'], $db);
            }

            $db->commit();
            return true;

        } catch (Exception $e) {
            if ($db && $db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Error adding to cart: " . $e->getMessage());
            throw $e;
        }
    }

    public function getCartItems($user_id) {
        try {
            $sql = "SELECT ci.*, p.name, p.price as current_price, p.canteen_id, c.name as canteen_name
                    FROM carts ca
                    JOIN cart_items ci ON ca.cart_id = ci.cart_id
                    JOIN products p ON ci.product_id = p.product_id
                    JOIN canteens c ON p.canteen_id = c.canteen_id
                    WHERE ca.user_id = :user_id
                    ORDER BY ci.created_at DESC";
            
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->execute(['user_id' => $user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting cart items: " . $e->getMessage());
            return [];
        }
    }

    public function clearCart($user_id) {
        try {
            $db = $this->db->connect();
            
            // Start transaction
            if (!$db->inTransaction()) {
                $db->beginTransaction();
            }

            // Get the cart ID first
            $sql = "SELECT cart_id FROM carts WHERE user_id = :user_id";
            $stmt = $db->prepare($sql);
            $stmt->execute(['user_id' => $user_id]);
            $cart = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($cart) {
                // Delete cart items first
                $sql = "DELETE FROM cart_items WHERE cart_id = :cart_id";
                $stmt = $db->prepare($sql);
                $stmt->execute(['cart_id' => $cart['cart_id']]);

                // Then delete the cart
                $sql = "DELETE FROM carts WHERE cart_id = :cart_id";
                $stmt = $db->prepare($sql);
                $stmt->execute(['cart_id' => $cart['cart_id']]);
            }

            $db->commit();
            return true;
            
        } catch (Exception $e) {
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Error clearing cart: " . $e->getMessage());
            return false;
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
            return 0;
        }
    }

    private function getOrCreateCart($user_id, $db) {
        $sql = "SELECT cart_id FROM carts WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute(['user_id' => $user_id]);
        $cart = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$cart) {
            $sql = "INSERT INTO carts (user_id) VALUES (:user_id)";
            $stmt = $db->prepare($sql);
            $stmt->execute(['user_id' => $user_id]);
            return $db->lastInsertId();
        }

        return $cart['cart_id'];
    }

    private function getCartItem($cart_id, $product_id, $db) {
        $sql = "SELECT cart_item_id, quantity 
                FROM cart_items 
                WHERE cart_id = :cart_id 
                AND product_id = :product_id";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            'cart_id' => $cart_id,
            'product_id' => $product_id
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function updateCartItem($existingItem, $quantity, $price, $db) {
        $newQuantity = $existingItem['quantity'] + $quantity;
        $subtotal = $newQuantity * $price;
        
        $sql = "UPDATE cart_items 
                SET quantity = :quantity, 
                    subtotal = :subtotal 
                WHERE cart_item_id = :cart_item_id";
        
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            'quantity' => $newQuantity,
            'subtotal' => $subtotal,
            'cart_item_id' => $existingItem['cart_item_id']
        ]);
    }

    private function addCartItem($cart_id, $product_id, $quantity, $price, $db) {
        $subtotal = $quantity * $price;
        
        $sql = "INSERT INTO cart_items 
                (cart_id, product_id, quantity, unit_price, subtotal) 
                VALUES 
                (:cart_id, :product_id, :quantity, :unit_price, :subtotal)";
        
        $stmt = $db->prepare($sql);
        $result = $stmt->execute([
            'cart_id' => $cart_id,
            'product_id' => $product_id,
            'quantity' => $quantity,
            'unit_price' => $price,
            'subtotal' => $subtotal
        ]);

        if (!$result) {
            throw new Exception("Failed to add item to cart");
        }
    }

    public function updateCartQuantity($user_id, $product_id, $new_quantity) {
        $db = null;
        try {
            if ($new_quantity < 1) {
                throw new Exception("Quantity must be at least 1");
            }

            $db = $this->db->connect();
            $db->beginTransaction();

            // Get the cart
            $sql = "SELECT c.cart_id FROM carts c WHERE c.user_id = :user_id";
            $stmt = $db->prepare($sql);
            $stmt->execute(['user_id' => $user_id]);
            $cart = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$cart) {
                throw new Exception("Cart not found");
            }

            // Get product price
            $sql = "SELECT price FROM products WHERE product_id = :product_id";
            $stmt = $db->prepare($sql);
            $stmt->execute(['product_id' => $product_id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$product) {
                throw new Exception("Product not found");
            }

            $subtotal = $product['price'] * $new_quantity;

            // Update cart item
            $sql = "UPDATE cart_items 
                    SET quantity = :quantity, 
                        subtotal = :subtotal 
                    WHERE cart_id = :cart_id 
                    AND product_id = :product_id";

            $stmt = $db->prepare($sql);
            $stmt->execute([
                'quantity' => $new_quantity,
                'subtotal' => $subtotal,
                'cart_id' => $cart['cart_id'],
                'product_id' => $product_id
            ]);

            $db->commit();
            return true;

        } catch (Exception $e) {
            if ($db && $db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Error updating cart quantity: " . $e->getMessage());
            throw $e;
        }
    }
}
?> 