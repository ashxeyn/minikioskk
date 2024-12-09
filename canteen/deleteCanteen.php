<?php
require_once '../classes/canteenClass.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $canteenObj = new Canteen();
    $canteen_id = $_POST['canteen_id'];
    $result = $canteenObj->deleteCanteen($canteen_id);
    echo $result ? 'success' : 'failure';
}
?>
