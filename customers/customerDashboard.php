<?php
session_start();
require_once '../classes/accountClass.php';
require_once '../tools/functions.php';

$account = new Account();

$account->user_id = $_SESSION['user_id'] ?? null;
$userInfo = $account->UserInfo();

$topNavFile = '../includes/_topnav2.php';
require_once '../includes/_head.php';
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

<!-- Dynamically include the correct top navigation bar -->
<?php require_once $topNavFile; ?>

<div class="container-fluid">
    <div class="row">
        <?php require_once '../includes/_sidebar3.php'; ?>
        <div class="col py-3">
            <div id="contentArea" class="container mt-4">
                <p>
                    <?php 
                    if ($userInfo) {
                        echo "Welcome back, " . clean_input($userInfo['name']) . "!";
                    } else {
                        echo "Welcome to our dashboard!";
                    }
                    ?>
                </p>
            </div>
        </div>
    </div>
</div>

</body>
</html>
