<?php
require_once '../tools/functions.php';
require_once '../classes/accountClass.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $accountObj = new Account();
        
        // Debug log
        error_log("POST data received: " . print_r($_POST, true));
        
        // Clean and set all fields
        $accountObj->last_name = isset($_POST['last_name']) ? clean_input($_POST['last_name']) : '';
        $accountObj->given_name = isset($_POST['given_name']) ? clean_input($_POST['given_name']) : '';
        $accountObj->middle_name = isset($_POST['middle_name']) ? clean_input($_POST['middle_name']) : '';
        $accountObj->email = isset($_POST['email']) ? clean_input($_POST['email']) : '';
        $accountObj->username = isset($_POST['username']) ? clean_input($_POST['username']) : '';
        $accountObj->password = isset($_POST['password']) ? clean_input($_POST['password']) : '';
        $accountObj->role = isset($_POST['role']) ? clean_input($_POST['role']) : '';

        // Validate required fields
        if (empty($accountObj->last_name) || empty($accountObj->given_name) || 
            empty($accountObj->email) || empty($accountObj->username) || 
            empty($accountObj->password) || empty($accountObj->role)) {
            throw new Exception('Missing required fields');
        }

        // Handle role-specific fields
        $result = false;
        switch($accountObj->role) {
            case 'student':
            case 'employee':
                $program_id = isset($_POST['program_id']) ? clean_input($_POST['program_id']) : '';
                if (empty($program_id)) {
                    throw new Exception('Program ID is required for students and employees');
                }
                $result = $accountObj->addUser($program_id);
                break;
                
            case 'manager':
                $canteen_id = isset($_POST['canteen_id']) ? clean_input($_POST['canteen_id']) : '';
                if (empty($canteen_id)) {
                    throw new Exception('Canteen ID is required for managers');
                }
                $result = $accountObj->addManager($canteen_id);
                break;
                
            case 'guest':
                $result = $accountObj->addUser();
                break;
                
            default:
                throw new Exception('Invalid role specified');
        }

        echo $result ? 'success' : 'failure';
        
    } catch (Exception $e) {
        error_log("Error in addUser.php: " . $e->getMessage());
        echo $e->getMessage();
    }
} else {
    echo 'Invalid request method';
}
?> 