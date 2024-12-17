<?php
require_once '../classes/programClass.php';
require_once '../tools/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $program = new Program();
        
        $department_id = clean_input($_POST['department_id']);
        $department_name = clean_input($_POST['department_name']);
        $college_id = clean_input($_POST['college_id']);
        $description = clean_input($_POST['description']);
        
        $result = $program->updateDepartment($department_id, $department_name, $college_id, $description);
        
        echo json_encode([
            'success' => $result,
            'message' => $result ? 'Department updated successfully' : 'Failed to update department'
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error updating department: ' . $e->getMessage()
        ]);
    }
} 