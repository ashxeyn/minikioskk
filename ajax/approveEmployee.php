<?php
session_start();
require_once '../classes/accountClass.php';

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'manager') {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

if (!isset($_POST['user_id']) || empty($_POST['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'User ID is required'
    ]);
    exit;
}

try {
    $account = new Account();
    $result = $account->approveManager($_POST['user_id']);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Employee approved successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to approve employee'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error approving employee: ' . $e->getMessage()
    ]);
}
?> 