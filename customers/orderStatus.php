<?php
session_start();
require_once '../classes/orderClass.php';

$orderObj = new Order();

// Check if user_id is set in session
if (isset($_SESSION['user_id'])) {
    try {
        // Fetch orders only if user_id is defined
        $orders = $orderObj->getUserOrders($_SESSION['user_id']);
        error_log("Orders fetched for user " . $_SESSION['user_id'] . ": " . print_r($orders, true));
    } catch (Exception $e) {
        error_log("Error fetching orders: " . $e->getMessage());
        $orders = [];
    }
} else {
    // Handle the case where user_id is not defined
    $orders = [];
}

// Add debugging
error_log("Session data in orderStatus.php: " . print_r($_SESSION, true));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Order Status</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/customer-order.css">
</head>
<body>
<div id="contentArea" class="container mt-4">
    <h2>Your Order Status</h2>
    <?php if (!empty($orders)): ?>
        <?php foreach ($orders as $order): ?>
            <div class="order-card <?= $order['order_id'] === ($_SESSION['last_order']['order_id'] ?? null) ? 'latest-order' : '' ?>">
                <div class="order-row">
                    <p><strong>Order ID:</strong></p>
                    <p><?= htmlspecialchars($order['order_id']); ?></p>
                </div>
                <div class="order-row">
                    <p><strong>Status:</strong></p>
                    <p class="status-<?= strtolower($order['status']) ?>">
                        <?= htmlspecialchars(ucfirst($order['status'])); ?>
                    </p>
                </div>
                <div class="order-row">
                    <p><strong>Queue Number:</strong></p>
                    <p><?= date('Ymd', strtotime($order['created_at'])) . str_pad($order['order_id'], 4, '0', STR_PAD_LEFT); ?></p>
                </div>
                <div class="order-row">
                    <p><strong>Canteen:</strong></p>
                    <p><?= htmlspecialchars($order['canteen_name'] ?? 'Not Available'); ?></p>
                </div>
                <div class="order-row">
                    <p><strong>Items:</strong></p>
                    <p><?= htmlspecialchars($order['items'] ?? 'No items'); ?></p>
                </div>
                <div class="order-row">
                    <p><strong>Total Amount:</strong></p>
                    <p>₱<?= number_format($order['total_amount'], 2); ?></p>
                </div>
                <div class="order-row">
                    <p><strong>Created At:</strong></p>
                    <p><?= date('F j, Y, g:i a', strtotime($order['created_at'])); ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="alert alert-info">No orders found.</div>
    <?php endif; ?>
</div>

<style>
    .order-card {
        background: white;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .order-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        padding: 5px 0;
        border-bottom: 1px solid #eee;
    }
    
    .status-pending {
        color: #ffc107;
        font-weight: bold;
    }
    
    .status-completed {
        color: #28a745;
        font-weight: bold;
    }
    
    .status-cancelled {
        color: #dc3545;
        font-weight: bold;
    }
    
    .latest-order {
        border: 2px solid #007bff;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
