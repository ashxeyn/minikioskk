<?php
require_once '../classes/canteenClass.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $canteenObj = new Canteen();
    $canteen_id = $_POST['canteen_id'];
    $name = $_POST['name'];
    $campus_location = $_POST['campus_location'];

    $result = $canteenObj->editCanteen($canteen_id, $name, $campus_location);
    echo $result ? 'success' : 'failure';
}
?>
