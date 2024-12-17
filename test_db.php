<?php
require_once 'classes/canteenClass.php';
require_once 'classes/databaseClass.php';

try {
    $canteenObj = new Canteen();
    $canteens = $canteenObj->getAllCanteens();
    echo "<pre>";
    print_r($canteens);
    echo "</pre>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} 