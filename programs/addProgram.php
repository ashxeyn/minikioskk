<?php
require_once '../classes/programClass.php';
require_once '../tools/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $program = new Program();
    
    $program_name = clean_input($_POST['program_name']);
    $department = clean_input($_POST['department']);
    $college = clean_input($_POST['college']);
    $description = clean_input($_POST['description']);
    
    $department_id = $program->getOrCreateDepartment($department, $college);
    
    if ($department_id) {
        $result = $program->addProgram($program_name, $department_id, $description);
        echo $result ? 'success' : 'failure';
    } else {
        echo 'failure';
    }
}
?>
