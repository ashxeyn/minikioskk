<?php
require_once '../tools/functions.php';
require_once '../classes/canteenClass.php';

$canteenObj = new Canteen();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the form data
    $canteenObj->name = clean_input($_POST['name']);
    $canteenObj->campus_location = clean_input($_POST['location']);

    if (empty($canteenObj->name) || empty($canteenObj->campus_location)) {
        echo 'failure';  
        exit;
    }

    $result = $canteenObj->addCanteen();
    echo $result ? 'success' : 'failure';
}
?>
