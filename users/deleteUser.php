<?php
require_once '../classes/accountClass.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $accountObj = new Account();

    $user_id = $_POST['user_id'];

    $result = $accountObj->deleteUser($user_id);

    echo $result ? 'success' : 'failure';
}
?>
