<?php
require_once '../classes/programClass.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $programObj = new Program();

    $program_id = $_POST['program_id'];
    $program_name = $_POST['program_name'];
    $department = $_POST['department'];
    $college = $_POST['college']; 

    $result = $programObj->updateProgram($program_id, $program_name, $department, $college);

    echo $result ? 'success' : 'failure';
}
?>
