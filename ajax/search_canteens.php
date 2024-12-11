<?php
require_once '../classes/canteenClass.php';

header('Content-Type: application/json');

$canteenObj = new Canteen();
$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';

try {
    $canteens = $canteenObj->searchCanteens($keyword);
    echo json_encode($canteens);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} 