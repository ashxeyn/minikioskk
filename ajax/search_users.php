<?php
require_once '../classes/accountClass.php';

header('Content-Type: application/json');

$accountObj = new Account();
$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
$role = isset($_GET['role']) ? $_GET['role'] : '';

try {
    $users = $accountObj->searchUsers($keyword, $role);
    echo json_encode($users);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} 