<?php
require_once '../classes/accountClass.php';
require_once '../tools/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $account = new Account();
        
        // Get and clean form data
        $account->last_name = clean_input($_POST['last_name'] ?? '');
        $account->given_name = clean_input($_POST['given_name'] ?? '');
        $account->middle_name = clean_input($_POST['middle_name'] ?? '');
        $account->email = clean_input($_POST['email'] ?? '');
        $account->username = clean_input($_POST['username'] ?? '');
        $account->password = clean_input($_POST['password'] ?? '');
        $account->role = clean_input($_POST['role'] ?? '');
        
        // Validate required fields
        if (empty($account->last_name) || empty($account->given_name) || 
            empty($account->email) || empty($account->username) || 
            empty($account->password) || empty($account->role)) {
            throw new Exception('Missing required fields');
        }
        
        // Handle role-specific fields
        switch ($account->role) {
            case 'student':
                $account->program_id = clean_input($_POST['program_id'] ?? null);
                break;
            case 'employee':
                $account->department_id = clean_input($_POST['department_id'] ?? null);
                break;
            case 'manager':
                $account->canteen_id = clean_input($_POST['canteen_id'] ?? null);
                break;
        }
        
        if ($account->addUser()) {
            echo 'success';
        } else {
            throw new Exception('Failed to add user');
        }
    } catch (Exception $e) {
        error_log("Error adding user: " . $e->getMessage());
        echo $e->getMessage();
    }
} else {
    http_response_code(405);
    echo 'Method not allowed';
}
?> 