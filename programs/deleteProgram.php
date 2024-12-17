<?php
require_once '../classes/programClass.php';
require_once '../tools/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $program = new Program();
        $program_id = clean_input($_POST['program_id']);
        
        if (empty($program_id)) {
            throw new Exception('Program ID is required');
        }
        
        $result = $program->deleteProgram($program_id);
        
        echo json_encode([
            'success' => $result,
            'message' => $result ? 'Program deleted successfully' : 'Failed to delete program'
        ]);
    } catch (Exception $e) {
        error_log("Error deleting program: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred while deleting the program'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?>
