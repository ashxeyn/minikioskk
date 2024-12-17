<?php
require_once '../tools/functions.php';
require_once '../classes/canteenClass.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $canteenObj = new Canteen();
    $canteen_id = clean_input($_POST['canteen_id']);
    $name = clean_input($_POST['name']);
    $campus_location = clean_input($_POST['location']);

    if (empty($canteen_id) || empty($name) || empty($campus_location)) {
        echo 'failure';
        exit;
    }

    $result = $canteenObj->editCanteen($canteen_id, $name, $campus_location);
    echo $result ? 'success' : 'failure';
}
?>
