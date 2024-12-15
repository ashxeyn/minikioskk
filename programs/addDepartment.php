<?php
require_once '../classes/programClass.php';
require_once '../tools/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $program = new Program();
    
    $college_id = clean_input($_POST['college_id']);
    $department_name = clean_input($_POST['department_name']);
    $description = clean_input($_POST['description']);
    
    $result = $program->addDepartment($college_id, $department_name, $description);
    
    echo json_encode([
        'success' => $result,
        'message' => $result ? 'Department added successfully' : 'Failed to add department'
    ]);
}
?> 