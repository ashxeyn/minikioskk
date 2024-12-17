<?php
require_once '../classes/accountClass.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        throw new Exception('Invalid request method');
    }

    $accountObj = new Account();
    
    $user_id = $_POST['user_id'] ?? '';
    $email = $_POST['email'] ?? '';
    $username = $_POST['username'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $given_name = $_POST['given_name'] ?? '';
    $middle_name = $_POST['middle_name'] ?? '';
    $role = $_POST['role'] ?? '';

    if (empty($user_id) || empty($email) || empty($username) || 
        empty($last_name) || empty($given_name) || empty($role)) {
        throw new Exception('Missing required fields');
    }

    $result = $accountObj->editUser(
        $user_id, 
        $email, 
        $username, 
        $last_name, 
        $given_name, 
        $middle_name, 
        $role
    );

    echo json_encode([
        'success' => $result,
        'message' => $result ? 'User updated successfully' : 'Failed to update user'
    ]);

} catch (Exception $e) {
    error_log("Error in editUser.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
