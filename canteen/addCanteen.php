<?php
require_once '../tools/functions.php';
require_once '../classes/canteenClass.php';

$canteenObj = new Canteen();
$canteen_name = $campus_location = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $canteen_name = clean_input($_POST['canteen_name']);
    $campus_location = clean_input($_POST['campus_location']);

    if (empty($canteen_name) || empty($campus_location)) {
        echo 'failure';  // Return failure if fields are empty
        exit;
    }

    $canteenObj->name = $canteen_name;
    $canteenObj->campus_location = $campus_location;

    $result = $canteenObj->addCanteen();

    echo $result ? 'success' : 'failure';
}
?>
