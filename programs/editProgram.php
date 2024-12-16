<?php
session_start();
require_once '../classes/programClass.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $program_id = $_POST['program_id'] ?? '';
    $program_name = $_POST['program_name'] ?? '';
    $department_id = $_POST['department_id'] ?? '';
    $description = $_POST['description'] ?? '';

    if (empty($program_id) || empty($program_name) || empty($department_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Required fields are missing']);
        exit;
    }

    try {
        $program = new Program();
        $result = $program->updateProgram($program_id, $program_name, $department_id, $description);

        if ($result) {
            echo json_encode(['status' => 'success', 'message' => 'Program updated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update program']);
        }
    } catch (Exception $e) {
        error_log("Error updating program: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'An error occurred while updating the program']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
