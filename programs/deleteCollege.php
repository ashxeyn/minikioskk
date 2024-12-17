<?php
require_once '../classes/programClass.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['college_id'])) {
    try {
        $program = new Program();
        $result = $program->deleteCollege($_POST['college_id']);
        
        echo json_encode([
            'success' => $result,
            'message' => $result ? 'College deleted successfully' : 'Failed to delete college'
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error deleting college: ' . $e->getMessage()
        ]);
    }
} 