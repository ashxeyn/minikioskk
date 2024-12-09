<?php
require_once '../tools/functions.php';
require_once '../classes/programClass.php';

$programObj = new Program();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $program_name = clean_input($_POST['program_name']);
    $department = clean_input($_POST['department']);
    $college = clean_input($_POST['college']);

    if (empty($program_name) || empty($department) || empty($college)) {
        echo 'failure';  
        exit;
    }

    $programObj->program_name = $program_name;
    $programObj->department = $department;
    $programObj->college = $college;

    $result = $programObj->addProgram($program_name, $department, $college);

    echo $result ? 'success' : 'failure';
}
?>
