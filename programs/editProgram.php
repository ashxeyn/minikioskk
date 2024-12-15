<?php
require_once '../classes/programClass.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $programObj = new Program();

        $program_id = $_POST['program_id'];
        $program_name = $_POST['program_name'];
        $department_id = $_POST['department_id'];
        $description = $_POST['description'];

        $result = $programObj->updateProgram($program_id, $program_name, $department_id, $description);
        
        echo json_encode([
            'success' => $result,
            'message' => $result ? 'Program updated successfully' : 'Failed to update program'
        ]);
    } catch (Exception $e) {
        error_log("Error updating program: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Error occurred while updating program'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?>
