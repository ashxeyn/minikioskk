<?php
session_start();
require_once '../classes/orderClass.php';

$response = ['success' => false, 'message' => ''];

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not logged in');
    }

    $orderObj = new Order();
    if ($orderObj->clearCart($_SESSION['user_id'])) {
        $response['success'] = true;
        $response['message'] = 'Cart cleared successfully';
    } else {
        throw new Exception('Failed to clear cart');
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response); 