<?php
require_once '../classes/programClass.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['department_id'])) {
    try {
        $program = new Program();
        $result = $program->deleteDepartment($_POST['department_id']);
        
        echo json_encode([
            'success' => $result,
            'message' => $result ? 'Department deleted successfully' : 'Failed to delete department'
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error deleting department: ' . $e->getMessage()
        ]);
    }
} 