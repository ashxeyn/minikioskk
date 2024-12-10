<?php
session_start();
require_once '../classes/orderClass.php';

$orderObj = new Order();

// Fetch the orders for the logged-in user
$orders = $orderObj->getAllOrders($_SESSION['user_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $orderId = $_POST['order_id'];
    $items = $orderObj->viewItems($orderId);
    echo $items;
    exit;
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
                        <?php if ($orders): ?>
                        <div class="order-card latest-order">
                            <div class="order-row">
                                <p><strong>Order ID:</strong></p>
                                <p><?= $orders[0]['order_id']; ?></p>
                            </div>
                        <div class="order-row">
                            <p><strong>Status:</strong></p>
                            <p><?= ucfirst($orders[0]['status']); ?></p>
                        </div>
                        <div class="order-row">
                            <p><strong>Queue Number:</strong></p>
                            <p><?= $orders[0]['queue_number']; ?></p>
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
                    <div class="order-card" data-order-id="<?= $orders[$i]['order_id']; ?>">
                        <div class="order-row">
                            <p><strong>Order ID:</strong></p>
                            <p><?= $orders[$i]['order_id']; ?></p>
                        </div>
                        <div class="order-row">
                            <p><strong>Status:</strong></p>
                            <p><?= ucfirst($orders[$i]['status']); ?></p>
                        </div>
                        <div class="order-row">
                            <p><strong>Queue Number:</strong></p>
                            <p><?= $orders[$i]['queue_number']; ?></p>
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
                    <?php else: ?>
                    <p>You have no orders.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>