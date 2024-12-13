<?php
require_once '../classes/programClass.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $programObj = new Program();

    $program_id = $_POST['program_id'];
    $program_name = $_POST['program_name'];
    $department_id = $_POST['department_id'];
    $description = $_POST['description'];

    $result = $programObj->updateProgram($program_id, $program_name, $department_id, $description);

    echo $result ? 'success' : 'failure';
}
?>
