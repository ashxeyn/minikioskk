<?php
session_start();
require_once '../tools/functions.php';
require_once '../classes/accountClass.php';
require_once '../classes/orderClass.php';
require_once '../classes/productClass.php';
require_once '../classes/canteenClass.php';

$account = new Account();
$orderObj = new Order();
$productObj = new Product();
$canteenObj = new Canteen();

$account->user_id = $_SESSION['user_id'] ?? null;
$userInfo = $account->UserInfo();

$userOrders = [];
if ($account->user_id) {
    $userOrders = $orderObj->getOrdersByUser($account->user_id);
}

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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/customer-cart.css">
    <link rel="stylesheet" href="../css/customer-order.css">
    <link rel="stylesheet" href="../css/customer.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<?php require_once '../includes/_topnav2.php'; ?>

<div class="container-fluid">

    <div class="row">
        <?php require_once '../includes/_sidebar3.php'; ?>

            <div class="col py-3">
        

            <div id="searchSection" class="search-section" style="display: none;">
                <form autocomplete="off" class="search-form" id="searchForm">
                    <div class="search-container">
                        <input type="search" id="search" name="search" placeholder="Search canteens or menu items...">
                    </div>
                    <div class="search-options">
                        <label><input type="radio" name="search_type" value="all" checked> All</label>
                        <label><input type="radio" name="search_type" value="canteens"> Canteens Only</label>
                        <label><input type="radio" name="search_type" value="menu"> Menu Items Only</label>
                    </div>
                </form>
            </div>
            
           
            
            <div id="contentArea" class="container mt-4">
                <!-- Content will be loaded here dynamically -->
            </div>
        </div>
    </div>
</div>

<script src="../js/dashboard_content.js"></script>
</body>
</html>
