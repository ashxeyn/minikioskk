<?php
session_start();
require_once '../classes/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'manager') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();
    
    $sql = "SELECT department_id, name FROM departments ORDER BY name";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($departments);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
} 