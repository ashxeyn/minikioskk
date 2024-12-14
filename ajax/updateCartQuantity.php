<?php
session_start();
require_once '../classes/cartClass.php';

header('Content-Type: application/json');

error_log("Update cart quantity request: " . print_r($_POST, true));

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to update cart']);
    exit;
}

try {
    $cart = new Cart();
    $user_id = $_SESSION['user_id'];
    $product_id = $_POST['product_id'] ?? null;
    $new_quantity = intval($_POST['new_quantity'] ?? 0);

    error_log("Processing update cart: user_id=$user_id, product_id=$product_id, new_quantity=$new_quantity");

    if (!$product_id || $new_quantity < 1) {
        throw new Exception("Invalid product or quantity");
    }

    $result = $cart->updateCartQuantity($user_id, $product_id, $new_quantity);
    
    if ($result) {
        // Get updated cart total
        $total = $cart->getCartTotal($user_id);
        echo json_encode([
            'success' => true, 
            'message' => 'Cart updated successfully',
            'total' => $total
        ]);
    } else {
        throw new Exception("Failed to update cart");
    }

} catch (Exception $e) {
    error_log("Error in updateCartQuantity.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 