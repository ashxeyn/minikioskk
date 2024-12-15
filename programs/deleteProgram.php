<?php
require_once '../classes/programClass.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['program_id'])) {
    try {
        $program = new Program();
        $result = $program->deleteProgram($_POST['program_id']);
        
        echo json_encode([
            'success' => $result,
            'message' => $result ? 'Program deleted successfully' : 'Failed to delete program'
        ]);
    } catch (Exception $e) {
        error_log("Error deleting program: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Error occurred while deleting program'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request'
    ]);
}
?>
