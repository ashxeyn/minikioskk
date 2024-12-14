<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Add session validation
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'manager') {
    header('Location: /minikioskk/accounts/login.php');
    exit();
}

require_once '../classes/accountClass.php';


require_once '../includes/_head.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/manager.js"></script>
    <title>Manager Dashboard</title>
</head>
<body>

<?php require_once '../includes/_topnav.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php require_once '../includes/_sidebar2.php'; ?>
            <div class="col py-3">
                <div id="contentArea" class="container mt-4">
                    <h3><?php echo "Welcome, {$_SESSION['username']}!"; ?></h3><br>
                    <?php require_once 'view_manager_analytics.php'; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
