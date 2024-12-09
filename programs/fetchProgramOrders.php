<?php
require_once '../classes/programClass.php';
require_once '../classes/orderClass.php';  // Assuming this is the file for the Order class

$programNames = [];  // Array to hold program names
$orderCounts = [];   // Array to hold corresponding order counts

$programObj = new Program();
$orderDataObj = new Order();  // Assuming OrderData is the class that handles orders

$programs = $programObj->fetchPrograms();  // Fetch all programs
foreach ($programs as $program) {
    $programNames[] = $program['program_name'];  // Get program names
    
    // Now, we need to get the order count for this program
    $orderCount = $programObj->getOrderCountByProgram($program['program_id']);  // Get order count for the program
    $orderCounts[] = $orderCount;  // Add the order count to the array
}
?>
