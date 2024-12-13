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
    <!-- jQuery first -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Then Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Then your custom scripts -->
    <script src="../js/users.js"></script>
    <script src="../js/admin.js"></script> 
    <script src="../js/canteen.js"></script> 
</head>
<body>
    <?php require_once '../includes/_topnav.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php require_once '../includes/_sidebar.php'; ?>
            <div class="col py-3">
                <div id="contentArea" class="container mt-4">
                    <h3><?php echo "Welcome to Dashboard, {$_SESSION['username']}!"; ?><br></h3>
                    <?php 
                    if (isset($_GET['page'])) {
                        switch($_GET['page']) {
                            case 'accounts':
                                require_once '../users/accountsSection.php';
                                break;
                            case 'orders':
                                require_once 'view_orders.php';
                                break;
                            default:
                                require_once 'view_analytics.php';
                                break;
                        }
                    } else {
                        require_once 'view_analytics.php';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
