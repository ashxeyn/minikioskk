<?php
require_once '../classes/canteenClass.php';

header('Content-Type: application/json');

try {
    $canteen = new Canteen();
    $canteens = $canteen->getCanteens();
    
    if ($canteens === false) {
        throw new Exception("Failed to fetch canteens");
    }
    
    if (empty($canteens)) {
        echo json_encode([]);
    } else {
        echo json_encode($canteens);
    }
    
} catch (Exception $e) {
    error_log("Error in getCanteens.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to load canteens']);
}
?> 