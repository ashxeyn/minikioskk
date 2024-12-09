<?php
require_once '../classes/accountClass.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $accountObj = new Account();

    $user_id = $_POST['user_id'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $last_name = $_POST['last_name'];
    $given_name = $_POST['given_name'];
    $middle_name = $_POST['middle_name'];
    $role = $_POST['role'];

    $result = $accountObj->editUser($user_id, $email, $username, $last_name, $given_name, $middle_name, $role);

    echo $result ? 'success' : 'failure';
}
?>
