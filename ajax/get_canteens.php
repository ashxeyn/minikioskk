<?php
require_once '../classes/canteenClass.php';

$canteen = new Canteen();
$result = $canteen->fetchCanteens();
echo json_encode($result); 