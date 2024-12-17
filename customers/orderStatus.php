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
        <!-- Tab Navigation -->
        <ul class="nav nav-tabs mb-4" id="orderTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="active-tab" data-bs-toggle="tab" data-bs-target="#active" type="button" role="tab">
                    Active Orders
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="completed-tab" data-bs-toggle="tab" data-bs-target="#completed" type="button" role="tab">
                    Completed Orders
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="cancelled-tab" data-bs-toggle="tab" data-bs-target="#cancelled" type="button" role="tab">
                    Cancelled Orders
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="orderTabContent">
            <!-- Active Orders Tab -->
            <div class="tab-pane fade show active" id="active" role="tabpanel">
                <div class="orders-grid-container">
                    <?php 
                    $hasActiveOrders = false;
                    foreach ($orders as $order): 
                        if ($order['status'] === 'placed' || $order['status'] === 'preparing' || $order['status'] === 'ready'):
                            $hasActiveOrders = true;
                    ?>
                        <div class="order-card">
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
                                <p><?= htmlspecialchars($order['name'] ?? 'Not Available'); ?></p>
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
                            <?php if ($order['status'] === 'placed'): ?>
                                <div class="reorder-section">
                                    <button class="btn btn-danger cancel-btn" onclick="cancelOrder(<?= $order['order_id']; ?>)">
                                        <i class="bi bi-x-circle"></i> Cancel Order
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php 
                        endif;
                    endforeach; 
                    if (!$hasActiveOrders): ?>
                        <p class="no-orders">No active orders.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Completed Orders Tab -->
            <div class="tab-pane fade" id="completed" role="tabpanel">
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

            <!-- Cancelled Orders Tab -->
            <div class="tab-pane fade" id="cancelled" role="tabpanel">
                <div class="orders-grid-container">
                    <?php 
                    $hasCancelledOrders = false;
                    foreach ($orders as $order): 
                        if ($order['status'] === 'cancelled'):
                            $hasCancelledOrders = true;
                    ?>
                        <div class="order-card cancelled">
                            <div class="order-row">
                                <p><strong>Order ID:</strong></p>
                                <p><?= htmlspecialchars($order['order_id']); ?></p>
                            </div>
                            <div class="order-row">
                                <p><strong>Status:</strong></p>
                                <p class="status-cancelled">Cancelled</p>
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
                                <p><strong>Cancelled At:</strong></p>
                                <p><?= date('F j, Y, g:i a', strtotime($order['created_at'])); ?></p>
                            </div>
                        </div>
                    <?php 
                        endif;
                    endforeach; 
                    if (!$hasCancelledOrders): ?>
                        <p class="no-orders">No cancelled orders.</p>
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

<!-- Cancel Order Modal -->
<div class="modal fade" id="cancelOrderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancel Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to cancel this order?</p>
                <p class="text-danger">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No, Keep Order</button>
                <button type="button" class="btn btn-danger" id="confirmCancel">Yes, Cancel Order</button>
            </div>
        </div>
    </div>
</div>

<style>
/* Update tab styles */
.nav-tabs {
    border-bottom: 2px solid #dee2e6;
    margin-bottom: 20px;
}

.nav-tabs .nav-link {
    color: #495057;
    border: none;
    border-bottom: 2px solid transparent;
    padding: 10px 20px;
    margin-bottom: -2px;
    transition: all 0.3s ease;
}

.nav-tabs .nav-link:hover {
    border-color: transparent;
    color: #FF7F11;
}

.nav-tabs .nav-link.active {
    color: #FF7F11;
    border-bottom: 2px solid #FF7F11;
    font-weight: bold;
}

/* Update container styles */
.orders-grid-container {
    display: flex;
    flex-direction: column;
    gap: 20px;
    padding: 20px;
}

.order-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    width: 100%;
    max-width: 600px;
    margin: 0 auto;
}

/* Keep your existing status and button styles */
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

.cancel-btn {
    width: 100%;
    padding: 8px 15px;
    background-color: #dc3545;
    border-color: #dc3545;
    transition: all 0.3s ease;
    margin-top: 15px;
}

.cancel-btn:hover {
    background-color: #c82333;
    border-color: #bd2130;
    transform: translateY(-2px);
}

.cancel-btn i {
    margin-right: 5px;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../js/orderActions.js"></script>
</body>
</html>
