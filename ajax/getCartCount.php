<?php
session_start();
require_once '../classes/orderClass.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['count' => 0]);
    exit;
}

try {
    $orderObj = new Order();
    $count = $orderObj->getCartItemCount($_SESSION['user_id']);
    echo json_encode(['count' => $count]);
} catch (Exception $e) {
    echo json_encode(['count' => 0]);
} 