<?php
session_start();
require_once '../classes/productClass.php';
require_once '../classes/stocksClass.php';
require_once '../tools/functions.php';

$productObj = new Product();
$stocksObj = new Stocks();

if (!isset($_SESSION['user_id'])) {
    // Handle guest cart
    if (!isset($_SESSION['guest_cart'])) {
        $_SESSION['guest_cart'] = [];
    }
    
    $product_id = clean_input($_POST['product_id']);
    $quantity = clean_input($_POST['quantity']);
    
    // Get product details
    $product = $productObj->getProduct($product_id);
    if (!$product) {
        throw new Exception("Product not found");
    }
    
    // Check stock
    $stock = $stocksObj->fetchStockByProductId($product_id);
    if (!$stock || $stock['quantity'] < $quantity) {
        throw new Exception("Insufficient stock");
    }
    
    // Add to guest cart
    $found = false;
    foreach ($_SESSION['guest_cart'] as &$item) {
        if ($item['product_id'] == $product_id) {
            $new_quantity = $item['quantity'] + $quantity;
            if ($stock['quantity'] >= $new_quantity) {
                $item['quantity'] = $new_quantity;
                $item['subtotal'] = $item['unit_price'] * $new_quantity;
                $found = true;
            } else {
                throw new Exception("Insufficient stock");
            }
            break;
        }
    }
    
    if (!$found) {
        $_SESSION['guest_cart'][] = [
            'product_id' => $product_id,
            'quantity' => $quantity,
            'unit_price' => $product['price'],
            'subtotal' => $product['price'] * $quantity
        ];
    }
    
    echo json_encode(['status' => 'success']);
    exit;
} 