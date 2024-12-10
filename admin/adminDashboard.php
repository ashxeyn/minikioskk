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

require_once '../includes/_head.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../js/admin.js"></script> 
    <script src="../js/canteen.js"></script> 
    <script src="../js/search.js"></script> 
</head>
<body>
    <?php require_once '../includes/_topnav.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php require_once '../includes/_sidebar.php'; ?>
            <div class="col py-3">
                <div id="contentArea" class="container mt-4">
                    <h3><?php echo "Welcome to Dashboard, {$_SESSION['username']}!"; ?><br></h3>
                    <?php require_once 'view_analytics.php' ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
