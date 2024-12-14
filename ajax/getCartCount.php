<?php
session_start();
require_once '../classes/cartClass.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['count' => 0]);
    exit;
}

try {
    $cart = new Cart();
    $items = $cart->getCartItems($_SESSION['user_id']);
    $count = count($items);
    echo json_encode(['count' => $count]);
} catch (Exception $e) {
    echo json_encode(['count' => 0]);
} 