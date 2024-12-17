<?php
require_once '../classes/accountClass.php';
require_once '../tools/functions.php';

// Prevent any output before headers
ob_start();

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

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
        throw new Exception('All required fields must be filled out');
    }

    $result = $account->addUser();
    
    if ($result === true) {
        ob_clean();
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'User added successfully'
        ]);
    } else {
        throw new Exception('Failed to add user');
    }

} catch (Exception $e) {
    ob_clean();
    error_log("Error adding user: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

// Ensure no additional output
ob_end_flush();
exit;
?> 