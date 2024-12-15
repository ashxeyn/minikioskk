<?php
require_once '../classes/programClass.php';
require_once '../tools/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $program = new Program();
    
    $college_name = clean_input($_POST['college_name']);
    $abbreviation = clean_input($_POST['abbreviation']);
    $description = clean_input($_POST['description']);
    
    $result = $program->addCollege($college_name, $abbreviation, $description);
    
    echo json_encode([
        'success' => $result,
        'message' => $result ? 'College added successfully' : 'Failed to add college'
    ]);
}
?> 