<?php
require_once '../classes/orderClass.php';

// Check if user is logged in as admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$orderObj = new Order();
$orders = $orderObj->fetchAllOrders();
?>

<div class="container-fluid">
    <h2>Order Management</h2>
    
    <!-- Search and Filters -->
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="input-group">
                <input type="text" id="searchInput" class="form-control" placeholder="Search orders...">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
            </div>
        </div>
        
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
        
        <div class="col-md-4">
            <select id="productFilter" class="form-select">
                <option value="">All Products</option>
                <?php 
                $products = $orderObj->getAllProducts();
                foreach($products as $product): 
                    $name = htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8');
                ?>
                    <option value="<?= $name ?>"><?= $name ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="table-responsive">
        <?php if (!empty($orders)): ?>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Canteen</th>
                        <th>Username</th>
                        <th>Customer Name</th>
                        <th>Products</th>
                        <th>Total Quantity</th>
                        <th>Total Price</th>
                        <th>Status</th>
                        <th>Queue Number</th>
                        <th>Order Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= htmlspecialchars($order['order_id']) ?></td>
                            <td><?= htmlspecialchars($order['canteen_name']) ?></td>
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
                            <td><?= date('Y-m-d H:i:s', strtotime($order['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-info">No orders found.</div>
        <?php endif; ?>
    </div>
</div>

<style>
.badge {
    padding: 0.5em 0.8em;
}

.table th {
    background-color: #f8f9fa;
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
                const canteen = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                const username = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                const customerName = row.querySelector('td:nth-child(4)').textContent.toLowerCase();
                
                showRow = orderId.includes(searchTerm) || 
                         canteen.includes(searchTerm) ||
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
                const products = row.querySelector('td:nth-child(5)').textContent.toLowerCase();
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