<?php
session_start();
require_once '../classes/orderClass.php';

$orderObj = new Order();

// Check if user_id is set in session
if (isset($_SESSION['user_id'])) {
    // Fetch orders only if user_id is defined
    $orders = $orderObj->getUserOrders($_SESSION['user_id']);
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
                <p><?= isset($orders[0]['queue_number']) ? htmlspecialchars($orders[0]['queue_number']) : 'Not Assigned'; ?></p>
            </div>
            <div class="order-row">
                <p><strong>Canteen:</strong></p>
                <p><?= htmlspecialchars($orders[0]['canteen_name'] ?? 'Not Available'); ?></p>
            </div>
            <div class="order-row">
                <p><strong>Created At:</strong></p>
                <p><?= date('F j, Y, g:i a', strtotime($orders[0]['created_at'])); ?></p>
            </div>
            <div class="order-row">
                <p><strong>Updated At:</strong></p>
                <p><?= date('F j, Y, g:i a', strtotime($orders[0]['updated_at'])); ?></p>
            </div>
        </div>

        <!-- Add refresh button -->
        <div class="text-center mt-3">
            <button type="button" class="btn btn-primary" onclick="refreshOrderStatus()">
                Refresh Status
            </button>
        </div>
    <?php else: ?>
        <div class="alert alert-info">No orders found.</div>
    <?php endif; ?>
</div>

<!-- Add response modal for status updates -->
<div class="modal fade" id="responseModal" tabindex="-1" role="dialog" aria-labelledby="responseModalLabel">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="responseModalLabel">Order Status Update</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="order-details">
                    <div class="detail-row">
                        <div class="label-group">
                            <span id="responseMessage"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<script>
// Update the refresh function to use modal
function refreshOrderStatus() {
    $.ajax({
        url: window.location.href,
        type: 'GET',
        success: function(response) {
            $('#contentArea').html($(response).find('#contentArea').html());
            showResponseModal('Order status updated successfully!', true);
        },
        error: function() {
            showResponseModal('Failed to update order status', false);
        }
    });
}

function showResponseModal(message, success = true) {
    $('#responseMessage').text(message);
    if (success) {
        $('#responseMessage').removeClass('text-danger').addClass('text-success');
    } else {
        $('#responseMessage').removeClass('text-success').addClass('text-danger');
    }
    
    const responseModal = new bootstrap.Modal(document.getElementById('responseModal'));
    responseModal.show();
}

// Auto refresh every 30 seconds
setInterval(function() {
    refreshOrderStatus();
}, 30000);
</script>

</body>
</html>
