<?php
require_once '../classes/accountClass.php';

$accountObj = new Account();

$response = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];

    if (!empty($user_id)) {
        $isApproved = $accountObj->approveManager($user_id);
        $response = $isApproved ? 'success' : 'failure';
    } else {
        $response = 'failure';
    }

    echo $response;
    exit;
}
?>
