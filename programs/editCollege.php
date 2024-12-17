<?php
require_once '../classes/programClass.php';
require_once '../tools/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $program = new Program();
        
        $college_id = clean_input($_POST['college_id']);
        $college_name = clean_input($_POST['college_name']);
        $abbreviation = clean_input($_POST['abbreviation']);
        $description = clean_input($_POST['description']);
        
        $result = $program->updateCollege($college_id, $college_name, $abbreviation, $description);
        
        echo json_encode([
            'success' => $result,
            'message' => $result ? 'College updated successfully' : 'Failed to update college'
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error updating college: ' . $e->getMessage()
        ]);
    }
} 