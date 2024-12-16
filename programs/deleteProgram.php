<?php
session_start();
require_once '../classes/programClass.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $program_id = $_POST['program_id'] ?? '';

    if (empty($program_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Program ID is required']);
        exit;
    }

    try {
        $program = new Program();
        $result = $program->deleteProgram($program_id);

        if ($result) {
            echo json_encode(['status' => 'success', 'message' => 'Program deleted successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete program']);
        }
    } catch (Exception $e) {
        error_log("Error in deleteProgram: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'An error occurred while deleting the program']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
