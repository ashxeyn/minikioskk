<?php
require_once '../classes/accountClass.php';
require_once '../classes/canteenClass.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $account = new Account();
    
    // Get form data
    $account->last_name = $_POST['last_name'] ?? '';
    $account->given_name = $_POST['given_name'] ?? '';
    $account->middle_name = $_POST['middle_name'] ?? '';
    $account->email = $_POST['email'] ?? '';
    $account->username = $_POST['username'] ?? '';
    $account->role = $_POST['role'] ?? '';
    
    // Handle role-specific fields
    if ($account->role === 'manager') {
        // Create new canteen first
        $canteen = new Canteen();
        $canteen_data = [
            'name' => $_POST['canteen_name'] ?? '',
            'campus_location' => $_POST['campus_location'] ?? ''
        ];
        $canteen_id = $canteen->addCanteen($canteen_data);
        if ($canteen_id) {
            $account->canteen_id = $canteen_id;
        } else {
            echo 'error_canteen';
            exit;
        }
    } else {
        $account->program_id = $_POST['program_id'] ?? null;
        $account->department_id = $_POST['department_id'] ?? null;
    }
    
    if ($account->addUser()) {
        echo 'success';
    } else {
        echo 'error';
    }
} else {
    http_response_code(405);
    echo 'Method not allowed';
}
?> 