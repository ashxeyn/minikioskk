<?php
session_start();
require_once '../classes/accountClass.php';

if (!isset($_SESSION['user_id'])) {
header("Location: ../accounts/login.php");
exit();
}

$account = new Account();
$account->user_id = $_SESSION['user_id'];
$userInfo = $account->UserInfo();
$isLoggedIn = isset($_SESSION['user_id']);

if ($isLoggedIn) {
    $account->user_id = $_SESSION['user_id'];
    $userInfo = $account->UserInfo();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Amaranth">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/customer-cart.css">
    <link rel="stylesheet" href="../css/customer-order.css">
    <link rel="stylesheet" href="../css/customer.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../customers/dashboard_content.js"></script>
</head>
<body>

<?php
if ($isLoggedIn) {
    require_once '../includes/_topnav.php';
} else {
    require_once '../includes/_topnav2.php';
}
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once '../includes/_sidebar3.php' ?>
        <div class="col py-3">
            <div id="contentArea" class="container mt-4">
            </div>
        </div>
    </div>
</div>

</body>
</html>