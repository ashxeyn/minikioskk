<?php
require_once '../classes/programClass.php';

header('Content-Type: application/json');

$programObj = new Program();
$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';

try {
    $programs = $programObj->searchPrograms($keyword);
    echo json_encode($programs);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} 