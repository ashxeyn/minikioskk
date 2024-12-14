<?php
session_start();
require_once '../classes/employeeClass.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'manager') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

if (!isset($_GET['user_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'User ID is required']);
    exit;
}

try {
    $employee = new Employee();
    $userData = $employee->getEmployeeById($_GET['user_id']);
    
    if ($userData) {
        echo json_encode($userData);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Employee not found']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
} 