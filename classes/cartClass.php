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

    private function getOrCreateCart($user_id, $db) {
        // First check if user has a cart
        $sql = "SELECT cart_id FROM carts WHERE user_id = :user_id";
        $stmt = $db->prepare($sql);
        $stmt->execute(['user_id' => $user_id]);
        $cart = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($cart) {
            return $cart['cart_id'];
        }

        // If no cart exists, create one
        $sql = "INSERT INTO carts (user_id, created_at) VALUES (:user_id, NOW())";
        $stmt = $db->prepare($sql);
        $stmt->execute(['user_id' => $user_id]);
        
        return $db->lastInsertId();
    }

    public function addToCart($user_id, $product_id, $quantity = 1) {
        $db = null;
        try {
            $db = $this->db->connect();
            $db->beginTransaction();

            // Get product details with stock information
            $sql = "SELECT p.*, s.quantity as stock_quantity, p.price 
                   FROM products p 
                   LEFT JOIN stocks s ON p.product_id = s.product_id 
                   WHERE p.product_id = :product_id";
            $stmt = $db->prepare($sql);
            $stmt->execute(['product_id' => $product_id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$product) {
                throw new Exception("Product not found");
            }

            // Check stock
            if ($product['stock_quantity'] < $quantity) {
                throw new Exception("Insufficient stock. Only " . $product['stock_quantity'] . " available.");
            }

            $cart_id = $this->getOrCreateCart($user_id, $db);

            // Check if product already in cart
            $sql = "SELECT * FROM cart_items WHERE cart_id = :cart_id AND product_id = :product_id";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'cart_id' => $cart_id,
                'product_id' => $product_id
            ]);
            $existingItem = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existingItem) {
                // Update existing item
                $new_quantity = $existingItem['quantity'] + $quantity;
                
                // Check if new quantity exceeds stock
                if ($new_quantity > $product['stock_quantity']) {
                    throw new Exception("Cannot add more items than available in stock");
                }
                
                $new_subtotal = $new_quantity * $product['price'];
                $sql = "UPDATE cart_items 
                       SET quantity = :new_quantity,
                           subtotal = :new_subtotal,
                           unit_price = :unit_price
                       WHERE cart_id = :cart_id 
                       AND product_id = :product_id";
                
                $stmt = $db->prepare($sql);
                $result = $stmt->execute([
                    'new_quantity' => $new_quantity,
                    'new_subtotal' => $new_subtotal,
                    'unit_price' => $product['price'],
                    'cart_id' => $cart_id,
                    'product_id' => $product_id
                ]);
            } else {
                // Insert new item
                $sql = "INSERT INTO cart_items (cart_id, product_id, quantity, unit_price, subtotal) 
                       VALUES (:cart_id, :product_id, :quantity, :unit_price, :subtotal)";
                
                $stmt = $db->prepare($sql);
                $result = $stmt->execute([
                    'cart_id' => $cart_id,
                    'product_id' => $product_id,
                    'quantity' => $quantity,
                    'unit_price' => $product['price'],
                    'subtotal' => $quantity * $product['price']
                ]);
            }

            if (!$result) {
                throw new Exception("Failed to update cart");
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
            $sql = "SELECT 
                    ci.cart_item_id,
                    ci.product_id,
                    ci.quantity,
                    ci.unit_price,
                    ci.subtotal,
                    p.name,
                    p.price,
                    p.canteen_id,
                    c.name as canteen_name,
                    s.quantity as stock_quantity
                    FROM carts cart
                    JOIN cart_items ci ON cart.cart_id = ci.cart_id 
                    JOIN products p ON ci.product_id = p.product_id 
                    JOIN canteens c ON p.canteen_id = c.canteen_id
                    LEFT JOIN stocks s ON p.product_id = s.product_id
                    WHERE cart.user_id = :user_id
                    GROUP BY ci.cart_item_id
                    ORDER BY ci.cart_item_id DESC";
            
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->execute(['user_id' => $user_id]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("Cart items found for user $user_id: " . print_r($items, true));
            return $items;
        } catch (Exception $e) {
            error_log("Error getting cart items: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return [];
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

    public function clearCart($user_id) {
        try {
            $db = $this->db->connect();
            $db->beginTransaction();

            // Get cart ID
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
            if ($db && $db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Error clearing cart: " . $e->getMessage());
            throw $e;
        }
    }

    public function updateCartQuantity($user_id, $product_id, $new_quantity) {
        try {
            $db = $this->db->connect();
            $db->beginTransaction();

            // Get cart
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

            // Update cart item
            $subtotal = $new_quantity * $product['price'];
            $sql = "UPDATE cart_items 
                   SET quantity = :quantity, 
                       subtotal = :subtotal 
                   WHERE cart_id = :cart_id 
                   AND product_id = :product_id";

            $stmt = $db->prepare($sql);
            $result = $stmt->execute([
                'quantity' => $new_quantity,
                'subtotal' => $subtotal,
                'cart_id' => $cart['cart_id'],
                'product_id' => $product_id
            ]);

            $db->commit();
            return $result;
        } catch (Exception $e) {
            if ($db && $db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Error updating cart quantity: " . $e->getMessage());
            throw $e;
        }
    }

    public function hasActiveCart($user_id) {
        try {
            $sql = "SELECT COUNT(*) as count FROM carts WHERE user_id = :user_id";
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->execute(['user_id' => $user_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] > 0;
        } catch (Exception $e) {
            error_log("Error checking active cart: " . $e->getMessage());
            return false;
        }
    }

    public function removeFromCart($user_id, $product_id) {
        $db = null;
        try {
            $db = $this->db->connect();
            $db->beginTransaction();

            // Get the cart ID
            $sql = "SELECT cart_id FROM carts WHERE user_id = :user_id";
            $stmt = $db->prepare($sql);
            $stmt->execute(['user_id' => $user_id]);
            $cart = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$cart) {
                throw new Exception("Cart not found");
            }

            // Remove the item
            $sql = "DELETE FROM cart_items 
                    WHERE cart_id = :cart_id 
                    AND product_id = :product_id";
            
            $stmt = $db->prepare($sql);
            $result = $stmt->execute([
                'cart_id' => $cart['cart_id'],
                'product_id' => $product_id
            ]);

            if (!$result) {
                throw new Exception("Failed to remove item from cart");
            }

            $db->commit();
            return true;

        } catch (Exception $e) {
            error_log("Error removing from cart: " . $e->getMessage());
            if ($db && $db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }
}
?> 