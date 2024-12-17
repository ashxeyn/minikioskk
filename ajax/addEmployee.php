<?php
session_start();
require_once '../classes/employeeClass.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'manager') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

try {
    if (!isset($_POST['username']) || !isset($_POST['email']) || !isset($_POST['password']) || 
        !isset($_POST['last_name']) || !isset($_POST['given_name']) || !isset($_POST['canteen_id'])) {
        throw new Exception("Missing required fields");
    }

    $employee = new Employee();
    $result = $employee->addEmployee([
        'username' => $_POST['username'],
        'email' => $_POST['email'],
        'password' => $_POST['password'],
        'last_name' => $_POST['last_name'],
        'given_name' => $_POST['given_name'],
        'middle_name' => $_POST['middle_name'] ?? null,
        'canteen_id' => $_POST['canteen_id']
    ]);

    echo json_encode(['success' => true, 'message' => 'Co-employee added successfully']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 