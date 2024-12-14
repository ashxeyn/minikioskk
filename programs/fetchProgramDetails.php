<?php
require_once '../classes/programClass.php';

if (isset($_POST['program_id']) || isset($_GET['program_id'])) {
    $programId = isset($_POST['program_id']) ? $_POST['program_id'] : $_GET['program_id'];
    
    $programObj = new Program();
    $program = $programObj->fetchProgramById($programId);
    
    if ($program) {
        echo json_encode($program);
    } else {
        echo json_encode(['error' => 'Program not found']);
    }
} else {
    echo json_encode(['error' => 'No program ID provided']);
}
?>
