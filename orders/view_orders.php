<?php
session_start();
require_once '../classes/orderClass.php';

$orderObj = new Order();
$orders = [];

try {
    if (isset($_SESSION['role']) && $_SESSION['role'] == 'manager') {
        $canteenId = $_SESSION['canteen_id'] ?? null;
        if (!$canteenId) {
            throw new Exception("Canteen ID not found in session");
        }
        $orders = $orderObj->fetchOrders($canteenId);
    }
} catch (Exception $e) {
    error_log("Error fetching orders: " . $e->getMessage());
}
?>

<div id="orderTable">
    <!-- Search and Filters -->
    <div class="row mb-3">
        <!-- Search Box -->
        <div class="col-md-4">
            <div class="input-group">
                <input type="text" id="searchInput" class="form-control" placeholder="Search orders...">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
            </div>
        </div>
        
        
        <!-- Status Filter -->
        <div class="col-md-4">
            <select id="statusFilter" class="form-select">
                <option value="">All Statuses</option>
                <option value="placed">Placed</option>
                <option value="accepted">Accepted</option>
                <option value="preparing">Preparing</option>
                <option value="ready">Ready</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>
        
        <!-- Product Filter -->
        <div class="col-md-4">
            <select id="productFilter" class="form-select">
                <option value="">All Products</option>
                <?php 
                $products = $orderObj->getUniqueProducts($canteenId);
                foreach($products as $product): 
                    $name = htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8');
                ?>
                    <option value="<?= $name ?>"><?= $name ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <?php if (!empty($orders)): ?>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Username</th>
                    <th>Customer Name</th>
                    <th>Products</th>
                    <th>Total Quantity</th>
                    <th>Total Price</th>
                    <th>Status</th>
                    <th>Queue Number</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?= htmlspecialchars($order['order_id']) ?></td>
                        <td><?= htmlspecialchars($order['username']) ?></td>
                        <td><?= htmlspecialchars($order['customer_name']) ?></td>
                        <td><?= htmlspecialchars($order['product_names']) ?></td>
                        <td><?= htmlspecialchars($order['total_quantity']) ?></td>
                        <td>₱<?= number_format($order['total_price'], 2) ?></td>
                        <td>
                            <span class="badge bg-<?= getStatusBadgeClass($order['status']) ?>">
                                <?= htmlspecialchars($order['status']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($order['queue_number']) ?></td>
                        <td>
                            <?php if ($order['status'] === 'placed'): ?>
                                <button class="btn btn-success btn-sm" onclick="updateOrderStatus(<?= $order['order_id'] ?>, 'accepted')">
                                    Accept
                                </button>
                                <button class="btn btn-danger btn-sm" onclick="updateOrderStatus(<?= $order['order_id'] ?>, 'cancelled')">
                                    Reject
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <div class="alert alert-info">No orders found.</div>
    <?php endif; ?>
</div>

<style>
.badge {
    padding: 0.5em 0.8em;
}

.table th {
    background-color: #f8f9fa;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const productFilter = document.getElementById('productFilter');
    
    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const statusTerm = statusFilter.value.toLowerCase();
        const productTerm = productFilter.value.toLowerCase();
        
        const rows = document.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const status = row.querySelector('.badge').textContent.toLowerCase();
            const products = row.children[3].textContent.toLowerCase();
            
            const matchesSearch = text.includes(searchTerm);
            const matchesStatus = !statusTerm || status === statusTerm;
            const matchesProduct = !productTerm || products.includes(productTerm);
            
            row.style.display = (matchesSearch && matchesStatus && matchesProduct) ? '' : 'none';
        });
    }
    
    searchInput.addEventListener('input', filterTable);
    statusFilter.addEventListener('change', filterTable);
    productFilter.addEventListener('change', filterTable);
});

function updateOrderStatus(orderId, status) {
    if (!confirm(`Are you sure you want to ${status} this order?`)) return;
    
    fetch('../ajax/updateOrderStatus.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            order_id: orderId,
            status: status
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to update order status');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating order status');
    });
}

function getStatusBadgeClass(status) {
    switch(status.toLowerCase()) {
        case 'placed': return 'warning';
        case 'accepted': return 'info';
        case 'preparing': return 'primary';
        case 'ready': return 'success';
        case 'completed': return 'success';
        case 'cancelled': return 'danger';
        default: return 'secondary';
    }
}
</script>
