<?php
require_once '../classes/programClass.php';
require_once '../classes/orderClass.php';

$programNames = [];
$orderCounts = [];

$programObj = new Program();
$orderDataObj = new Order();

$programs = $programObj->fetchPrograms();
foreach ($programs as $program) {
    $programNames[] = $program['program_name'];
    $orderCount = $programObj->getOrderCountByProgram($program['program_id']);
    $orderCounts[] = $orderCount;
}
?>
