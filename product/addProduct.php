<?php
require_once '../tools/functions.php';
require_once '../classes/productClass.php';

$productObj = new Product();
$name = $price = '';
$canteen_id = null; 

session_start();

if (isset($_SESSION['role']) && $_SESSION['role'] == 'manager') {
    $canteen_id = $_SESSION['canteen_id'];  
} elseif (isset($_POST['canteen_id']) && $_SESSION['role'] == 'admin') {
    $canteen_id = clean_input($_POST['canteen_id']);  
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = clean_input($_POST['name']);
    $description = clean_input($_POST['description']);
    $price = clean_input($_POST['price']);
    $category = clean_input($_POST['category']);

    if (empty($canteen_id) || empty($name) || empty($description) || empty($price) || empty($category)) {
        echo 'failure';
        exit;
    }

    $result = $productObj->addProduct($canteen_id, $name, $category, $description, $price);

    echo $result ? 'success' : 'failure';
}
?>
