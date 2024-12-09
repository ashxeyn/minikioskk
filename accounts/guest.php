<?php
session_start();

require_once '../tools/functions.php';
require_once '../classes/accountClass.php';

$accountObj = new Account();
$username = 'guest'; 
$program_id = null;
$is_student = 0;
$is_employee = 0;
$is_guest = 1;
$email = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($accountObj->usernameExist($username)) {
        $username .= rand(1000, 9999); 
    }

    $password = 'guest123'; 

    if ($accountObj->signup('', '', '', $email, $username, $password, $is_student, $is_employee, $is_guest, $program_id)) {
        $_SESSION['username'] = $username; 
        header('location: ../customers/customerDashboard.php'); 
        exit;
    } else {
        $signupErr = 'Error creating guest account. Please try again.';
    }
}
?>
