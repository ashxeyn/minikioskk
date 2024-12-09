<?php
require_once '../classes/programClass.php';

if (isset($_GET['program_id'])) {
    $programObj = new Program();
    $program = $programObj->fetchProgramById($_GET['program_id']);
    echo json_encode($program);
}
?>
