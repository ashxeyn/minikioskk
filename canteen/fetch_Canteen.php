<?php
require_once '../classes/canteenClass.php';

if (isset($_GET['canteen_id'])) {
    $canteenObj = new Canteen();
    $canteen = $canteenObj->fetchCanteenById($_GET['canteen_id']);
    echo json_encode($canteen);
}
?>
