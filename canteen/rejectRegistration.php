<?php
require_once '../classes/accountClass.php';

$accountObj = new Account();

if (isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];

    $isDeleted = $accountObj->reject($user_id);

    if ($isDeleted) {
        echo 'success';
    } else {
        echo 'failure';
    }
}
?>
