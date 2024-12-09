<?php
require_once '../tools/functions.php';
require_once '../classes/canteenClass.php';
require_once '../classes/accountClass.php';

$accountObj = new Account();
$canteenObj = new Canteen();
$canteens = $canteenObj->fetchCanteens();

$last_name = $given_name = $middle_name = $email = $password = $username = '';
$canteen_id = '';
$signupErr = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $last_name = clean_input($_POST['last_name']);
    $given_name = clean_input($_POST['given_name']);
    $middle_name = clean_input($_POST['middle_name']);
    $email = clean_input($_POST['email']);
    $username = clean_input($_POST['username']);
    $password = clean_input($_POST['password']);
    $canteen_id = clean_input($_POST['canteen_id']);

    if (empty($last_name) || empty($given_name) || empty($email) || empty($username) || empty($password) || empty($canteen_id)) {
        echo 'failure';  
        exit;
    }

    $accountObj->last_name = $last_name;
    $accountObj->given_name = $given_name;
    $accountObj->middle_name = $middle_name;
    $accountObj->email = $email;
    $accountObj->username = $username;
    $accountObj->password = $password;

    $result = $accountObj->addManager($canteen_id);

    echo $result ? 'success' : 'failure';
}
?>
