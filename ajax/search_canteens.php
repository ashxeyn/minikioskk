<?php
require_once '../classes/canteenClass.php';
require_once '../classes/databaseClass.php';

header('Content-Type: application/json');

try {
    $canteenObj = new Canteen();
    $canteens = $canteenObj->getAllCanteens();

    // Debug log
    error_log("Canteens data: " . print_r($canteens, true));

    // Ensure we have an array
    if (!is_array($canteens)) {
        $canteens = [];
    }

    // Format data for DataTables
    $data = array_map(function($canteen) {
        return [
            'canteen_id' => intval($canteen['canteen_id']),
            'name' => htmlspecialchars($canteen['name']),
            'campus_location' => htmlspecialchars($canteen['campus_location']),
            'created_at' => $canteen['created_at'],
            'status' => $canteen['status']
        ];
    }, $canteens);

    // Format response for DataTables
    $response = [
        'draw' => isset($_GET['draw']) ? intval($_GET['draw']) : 1,
        'recordsTotal' => count($data),
        'recordsFiltered' => count($data),
        'data' => $data
    ];

    // Debug log
    error_log("Response: " . json_encode($response));

    echo json_encode($response);

} catch (Exception $e) {
    error_log("Error in search_canteens.php: " . $e->getMessage());
    echo json_encode([
        'draw' => isset($_GET['draw']) ? intval($_GET['draw']) : 1,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'error' => $e->getMessage()
    ]);
}
?> 