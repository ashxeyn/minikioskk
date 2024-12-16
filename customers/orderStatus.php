<?php
session_start();
require_once '../classes/orderClass.php';

$orderObj = new Order();


if (isset($_SESSION['user_id'])) {
    try {
 
        $orders = $orderObj->getUserOrders($_SESSION['user_id']);
        error_log("Orders fetched for user " . $_SESSION['user_id'] . ": " . print_r($orders, true));
    } catch (Exception $e) {
        error_log("Error fetching orders: " . $e->getMessage());
        $orders = [];
    }
} else {
    $orders = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Order Status</title>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="../css/customer-order.css">
</head>
<body>
<div id="contentArea" class="container mt-4">
    <h2>Your Order Status</h2>
    
    <?php if (!empty($orders)): ?>
        <div class="orders-grid">
            <div class="orders-section active-section">
                <h3>Active Orders</h3>
                <div class="orders-grid-container">
                    <?php 
                    $hasActiveOrders = false;
                    foreach ($orders as $order): 
                        if ($order['status'] !== 'completed'):
                            $hasActiveOrders = true;
                    ?>
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
                    <?php 
                        endif;
                    endforeach; 
                    if (!$hasActiveOrders): ?>
                        <p class="no-orders">No active orders.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="orders-section completed-section">
                <h3>Completed Orders</h3>
                <div class="orders-grid-container">
                    <?php 
                    $hasCompletedOrders = false;
                    foreach ($orders as $order): 
                        if ($order['status'] === 'completed'):
                            $hasCompletedOrders = true;
                            $hasActiveReorder = $orderObj->hasActiveReorder($_SESSION['user_id'], $order['order_id']);
                    ?>
                        <div class="order-card completed">
                            <div class="order-row">
                                <p><strong>Order ID:</strong></p>
                                <p><?= htmlspecialchars($order['order_id']); ?></p>
                            </div>
                            <div class="order-row">
                                <p><strong>Status:</strong></p>
                                <p class="status-completed">Completed</p>
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
                                <p><strong>Completed At:</strong></p>
                                <p><?= date('F j, Y, g:i a', strtotime($order['created_at'])); ?></p>
                            </div>
                            
                            <div class="reorder-section">
                                <?php if ($hasActiveReorder): ?>
                                    <button class="btn btn-secondary reorder-btn" disabled title="This order has a pending reorder">
                                        <i class="bi bi-arrow-repeat"></i> Already Reordered
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-success reorder-btn" onclick="reorderItems(<?= $order['order_id']; ?>)">
                                        <i class="bi bi-arrow-repeat"></i> Reorder
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php 
                        endif;
                    endforeach; 
                    if (!$hasCompletedOrders): ?>
                        <p class="no-orders">No completed orders.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-info">No orders found.</div>
    <?php endif; ?>
</div>

<div class="modal fade" id="reorderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reorder Items</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to reorder these items?</p>
                <div id="reorderItems"></div>
                <hr>
                <p class="text-end fw-bold">Total: <span id="reorderTotal"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmReorder">Confirm Reorder</button>
            </div>
        </div>
    </div>
</div>

<!-- Response Modal -->
<div class="modal fade" id="responseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="responseMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<style>
/* Update grid layout */
.orders-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    padding: 20px;
}

.active-section {
    grid-column: span 2;
}

.completed-section {
    grid-column: span 2;
}

/* Update container to center cards */
.orders-grid-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 20px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 10px;
    min-height: 200px; /* Minimum height instead of fixed height */
    overflow-y: visible; /* Remove scrollbar */
}

/* Update order card styles */
.order-card {
    background: white;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    width: 90%; /* Fixed width for cards */
    max-width: 400px; /* Maximum width */
}

.orders-section {
    margin-bottom: 0;
    background: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.orders-section h3 {
    color: #333;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #eee;
    text-align: center;
}

/* Center no orders message */
.no-orders {
    color: #666;
    font-style: italic;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    text-align: center;
    width: 90%;
    max-width: 400px;
    margin: auto;
}

/* Responsive design */
@media (max-width: 1200px) {
    .orders-grid {
        grid-template-columns: 1fr;
    }

    .active-section,
    .completed-section {
        grid-column: span 1;
    }
}

/* Keep existing status styles */
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

/* Add these new styles */
.reorder-section {
    margin-top: 15px;
    text-align: center;
    padding-top: 15px;
    border-top: 1px solid #eee;
}

.reorder-btn {
    width: 100%;
    padding: 8px 15px;
    background-color: #28a745;
    border-color: #28a745;
    transition: all 0.3s ease;
}

.reorder-btn:hover {
    background-color: #218838;
    border-color: #1e7e34;
    transform: translateY(-2px);
}

.reorder-btn i {
    margin-right: 5px;
}

/* Add these styles for the modals */
.modal-content {
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
}

.modal-header {
    border-bottom: 1px solid #dee2e6;
    background-color: #f8f9fa;
    border-radius: 10px 10px 0 0;
}

.modal-footer {
    border-top: 1px solid #dee2e6;
    background-color: #f8f9fa;
    border-radius: 0 0 10px 10px;
}

#reorderItems {
    max-height: 300px;
    overflow-y: auto;
    padding: 10px;
    background-color: #f8f9fa;
    border-radius: 5px;
    margin: 10px 0;
}

.text-success {
    color: #28a745 !important;
}

.text-danger {
    color: #dc3545 !important;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../js/orderStatus.js" type="module"></script>
</body>
</html>
