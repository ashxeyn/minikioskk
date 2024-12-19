<?php
require_once '../classes/accountClass.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $accountObj = new Account();
        
        if (!isset($_POST['user_id']) || !isset($_POST['reason'])) {
            throw new Exception('Missing required parameters');
        }
        
        $user_id = $_POST['user_id'];
        $reason = $_POST['reason'];
        
        // Debug log
        error_log("Attempting to reject user ID: $user_id with reason: $reason");
        
        if (empty($reason)) {
            throw new Exception('Rejection reason is required');
        }
        
        $isRejected = $accountObj->reject($user_id, $reason);
        
        if ($isRejected) {
            echo 'success';
        } else {
            throw new Exception('Failed to reject registration');
        }
        
    } catch (Exception $e) {
        error_log("Error in rejectRegistration.php: " . $e->getMessage());
        echo 'failure';
    }
} else {
    echo 'failure';
}
?>
