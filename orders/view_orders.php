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
                            <span class="badge <?php echo $orderObj->getStatusBadgeClass($order['status']); ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($order['queue_number']): ?>
                                <span class="queue-number"><?php echo htmlspecialchars($order['queue_number']); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <?php foreach ($orderObj->getAvailableActions($order['status']) as $action): ?>
                                    <button 
                                        onclick="handleOrderAction(<?php echo $order['order_id']; ?>, '<?php echo $action['action']; ?>')"
                                        class="btn <?php echo $action['class']; ?> btn-sm">
                                        <?php echo $action['label']; ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
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

<!-- Order Details Modal -->
<div class="modal fade" id="orderDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="orderDetailsContent"></div>
            </div>
        </div>
    </div>
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

.input-group {
    margin-bottom: 0;
}

.form-select {
    cursor: pointer;
}

.btn-secondary {
    margin-bottom: 1rem;
}

/* Add loading indicator styles */
.loading {
    opacity: 0.5;
    pointer-events: none;
}

.loading::after {
    content: "Loading...";
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(255, 255, 255, 0.8);
    padding: 1rem;
    border-radius: 4px;
}
</style>

<script>
// Define handleOrderAction in global scope
function handleOrderAction(orderId, action) {
    let newStatus;
    let confirmMessage;
    
    switch(action) {
        case 'accept':
            newStatus = 'accepted';
            confirmMessage = 'Are you sure you want to accept this order?';
            break;
        case 'prepare':
            newStatus = 'preparing';
            confirmMessage = 'Are you sure you want to start preparing this order?';
            break;
        case 'ready':
            newStatus = 'ready';
            confirmMessage = 'Are you sure you want to mark this order as ready?';
            break;
        case 'complete':
            newStatus = 'completed';
            confirmMessage = 'Are you sure you want to complete this order?';
            break;
        case 'cancel':
            newStatus = 'cancelled';
            confirmMessage = 'Are you sure you want to cancel this order?';
            break;
        default:
            console.error('Invalid action:', action);
            return;
    }
    
    if (!confirm(confirmMessage)) return;
    
    fetch('../ajax/updateOrderStatus.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            order_id: orderId,
            status: newStatus
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Create and show success toast
            const toastDiv = document.createElement('div');
            toastDiv.className = 'toast position-fixed top-0 end-0 m-3';
            toastDiv.innerHTML = `
                <div class="toast-header bg-success text-white">
                    <strong class="me-auto">Success</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    Order status updated successfully!
                </div>
            `;
            document.body.appendChild(toastDiv);
            
            const toast = new bootstrap.Toast(toastDiv);
            toast.show();
            
            // Reload the page after a short delay
            setTimeout(() => location.reload(), 1500);
        } else {
            alert(data.message || 'Failed to update order status');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating order status');
    });
}

// Document ready event listener for other functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const productFilter = document.getElementById('productFilter');
    
    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        const statusTerm = statusFilter.value.toLowerCase();
        const productTerm = productFilter.value.toLowerCase();
        
        const rows = document.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            let showRow = true;
            
            // Search filter
            if (searchTerm) {
                const orderId = row.querySelector('td:nth-child(1)').textContent.toLowerCase();
                const username = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                const customerName = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                
                showRow = orderId.includes(searchTerm) || 
                         username.includes(searchTerm) || 
                         customerName.includes(searchTerm);
            }
            
            // Status filter
            if (showRow && statusTerm) {
                const status = row.querySelector('.badge').textContent.toLowerCase();
                showRow = status === statusTerm;
            }
            
            // Product filter
            if (showRow && productTerm) {
                const products = row.querySelector('td:nth-child(4)').textContent.toLowerCase();
                showRow = products.includes(productTerm);
            }
            
            // Show/hide row
            row.style.display = showRow ? '' : 'none';
        });
        
        // Show/hide "No orders found" message
        const visibleRows = document.querySelectorAll('tbody tr[style=""]').length;
        const noOrdersMessage = document.querySelector('.alert-info');
        if (noOrdersMessage) {
            noOrdersMessage.style.display = visibleRows === 0 ? '' : 'none';
        }
    }
    
    // Add event listeners
    searchInput.addEventListener('input', filterTable);
    statusFilter.addEventListener('change', filterTable);
    productFilter.addEventListener('change', filterTable);
    
    // Add clear filters button
    const clearFiltersBtn = document.createElement('button');
    clearFiltersBtn.className = 'btn btn-secondary mt-2';
    clearFiltersBtn.textContent = 'Clear Filters';
    clearFiltersBtn.onclick = function() {
        searchInput.value = '';
        statusFilter.value = '';
        productFilter.value = '';
        filterTable();
    };
    
    // Add the clear filters button after the filters row
    const filtersRow = document.querySelector('.row.mb-3');
    filtersRow.insertAdjacentElement('afterend', clearFiltersBtn);
    
    // Initialize filters
    filterTable();
});
</script>
