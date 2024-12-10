<?php
session_start();
require_once '../classes/orderClass.php';

$orderObj = new Order();

// Check if user_id is set in session
if (isset($_SESSION['user_id'])) {
    // Fetch the orders only if user_id is defined
    $orders = $orderObj->getAllOrders($_SESSION['user_id']);
} else {
    // Handle the case where user_id is not defined
    $orders = [];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Order Status</title>
    <link rel="stylesheet" href="../css/customer-order.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../js/customer.js"></script>
</head>
<body>
<div id="contentArea" class="container mt-4">
    <h2>Your Order Status</h2>
    <?php if ($orders && count($orders) > 0): ?>
        <div class="order-card latest-order">
            <div class="order-row">
                <p><strong>Order ID:</strong></p>
                <p><?= htmlspecialchars($orders[0]['order_id']); ?></p>
            </div>
            <div class="order-row">
                <p><strong>Status:</strong></p>
                <p><?= htmlspecialchars(ucfirst($orders[0]['status'])); ?></p>
            </div>
            <div class="order-row">
                <p><strong>Queue Number:</strong></p>
                <p><?= htmlspecialchars($orders[0]['queue_number']); ?></p>
            </div>
            <div class="order-row">
                <p><strong>Created At:</strong></p>
                <p class="date"><?= date('F j, Y, g:i a', strtotime($orders[0]['created_at'])); ?></p>
            </div>
            <div class="order-row">
                <p><strong>Updated At:</strong></p>
                <p class="date"><?= date('F j, Y, g:i a', strtotime($orders[0]['updated_at'])); ?></p>
            </div>
            <button class="view-items">View Items</button>
        </div>
        <h4>Previous Orders</h4>
        <div class="orders-container">
            <?php for ($i = 1; $i < count($orders); $i++): ?>
                <div class="order-card" data-order-id="<?= htmlspecialchars($orders[$i]['order_id']); ?>">
                    <div class="order-row">
                        <p><strong>Order ID:</strong></p>
                        <p><?= htmlspecialchars($orders[$i]['order_id']); ?></p>
                    </div>
                    <div class="order-row">
                        <p><strong>Status:</strong></p>
                        <p><?= htmlspecialchars(ucfirst($orders[$i]['status'])); ?></p>
                    </div>
                    <div class="order-row">
                        <p><strong>Queue Number:</strong></p>
                        <p><?= htmlspecialchars($orders[$i]['queue_number']); ?></p>
                    </div>
                    <div class="order-row">
                        <p><strong>Created At:</strong></p>
                        <p class="date"><?= date('F j, Y, g:i a', strtotime($orders[$i]['created_at'])); ?></p>
                    </div>
                    <div class="order-row">
                        <p><strong>Updated At:</strong></p>
                        <p class="date"><?= date('F j, Y, g:i a', strtotime($orders[$i]['updated_at'])); ?></p>
                    </div>
                    <button class="view-items">View Items</button>
                </div>
            <?php endfor; ?>
        </div>
    <?php else: ?>
        <p>You have no orders or are not logged in.</p>
    <?php endif; ?>
</div>
</body>
</html>
