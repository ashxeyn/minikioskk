<?php
require_once '../classes/programClass.php';
require_once '../tools/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $program = new Program();
        
        $program_name = clean_input($_POST['program_name']);
        $department_id = clean_input($_POST['department_id']);
        $description = clean_input($_POST['description']);
        
        // Validate required fields
        if (empty($program_name) || empty($department_id)) {
            echo json_encode([
                'success' => false,
                'message' => 'Please fill in all required fields'
            ]);
            exit;
        }
        
        $program_id = $program->addProgram($program_name, $department_id, $description);
        
        echo json_encode([
            'success' => ($program_id !== false),
            'message' => ($program_id !== false) ? 'Program added successfully' : 'Failed to add program',
            'program_id' => $program_id
        ]);
    } catch (Exception $e) {
        error_log("Error adding program: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred while adding the program'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?>
