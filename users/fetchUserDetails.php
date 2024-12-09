<?php
require_once '../classes/accountClass.php';

if (isset($_GET['user_id'])) {
    $accountObj = new Account();
    $user = $accountObj->fetchUserById($_GET['user_id']);
    echo json_encode($user);
}
?>
