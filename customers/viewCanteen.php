<?php
require_once '../tools/functions.php';
require_once '../classes/canteenClass.php';
require_once '../classes/productClass.php';
require_once '../classes/accountClass.php';
require_once '../classes/orderClass.php';

// Start session to handle user authentication
session_start();

// Handle user authentication check
$account = new Account();
$isLoggedIn = isset($_SESSION['user_id']);
if ($isLoggedIn) {
    $account->user_id = $_SESSION['user_id'];
    $userInfo = $account->UserInfo();
}

$canteenObj = new Canteen();
$productObj = new Product();
$orderObj = new Order();

// Fetch canteen and product information
$canteen_id = isset($_GET['canteen_id']) ? $_GET['canteen_id'] : null;

if (!$canteen_id) {
    echo '<p class="text-danger">Invalid request.</p>';
    exit;
}

$canteen = $canteenObj->fetchCanteenById($canteen_id);
$products = $productObj->fetchProductsByCanteen($canteen_id);

// Fetch user's orders if logged in
try {
    $orders = $isLoggedIn ? $orderObj->getAllOrders($_SESSION['user_id']) : [];
} catch (Exception $e) {
    error_log("Error fetching user orders: " . $e->getMessage());
    $orders = [];
}
?>

<div class="canteen-imgcover">
    <img src="https://media.philstar.com/photos/2021/05/13/wmsu-campus_2021-05-13_15-03-21.jpg" alt="">
</div>
<div class="container mt-4">
    <h2 class="menu-title">Menu for <?= htmlspecialchars($canteen['name']); ?></h2>
    <div class="product-container">
        <?php foreach ($products as $product): ?>
            <div class="menu-item">
                <h4><?= htmlspecialchars($product['name']); ?></h4>
                <p class="description"><?= htmlspecialchars($product['description']); ?></p>
                <p class="price">Price: <?= htmlspecialchars($product['price']); ?></p>
                <button onclick="loadAddToCartModal()" class="btn btn-primary">Open Add to Cart Modal</button>
            </div>
        <?php endforeach; ?>
    </div>
</div>

