<?php
require_once '../classes/productClass.php';
require_once '../tools/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $productObj = new Product();
        
        // Clean and validate inputs
        $canteen_id = clean_input($_POST['canteen_id']);
        $name = clean_input($_POST['name']);
        $type_id = clean_input($_POST['type_id']);
        $description = clean_input($_POST['description']);
        $price = clean_input($_POST['price']);
        
        // Validate required fields
        if (empty($canteen_id) || empty($name) || empty($type_id) || empty($description) || empty($price)) {
            echo 'failure: All fields are required';
            exit;
        }

        // Validate numeric fields
        if (!is_numeric($price) || $price <= 0) {
            echo 'failure: Invalid price';
            exit;
        }

        if (!is_numeric($canteen_id) || !is_numeric($type_id)) {
            echo 'failure: Invalid selection';
            exit;
        }

        $result = $productObj->addProduct($name, $type_id, $description, $price, $canteen_id);
        echo $result ? 'success' : 'failure: Could not add product';
        
    } catch (Exception $e) {
        error_log("Error in addProduct.php: " . $e->getMessage());
        echo 'failure: ' . $e->getMessage();
    }
}
?>
