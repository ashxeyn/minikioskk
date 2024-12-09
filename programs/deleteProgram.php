<?php
require_once '../classes/programClass.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $programObj = new Program();
    $program_id = $_POST['program_id'];
    $result = $programObj->deleteProgram($program_id);
    echo $result ? 'success' : 'failure';
}
?>
