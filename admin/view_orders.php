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
                        <th>#</th>
                        <th>Canteen</th>
                        <th>Username</th>
                        <th>Customer Name</th>
                        <th>Products</th>
                        <th>Total Quantity</th>
                        <th>Total Price</th>
                        <th>Status</th>
                        <th>Queue Number</th>
                        <th>Order Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $counter = 1;
                    foreach ($orders as $order): 
                    ?>
                        <tr>
                            <td><?= $counter++ ?></td>
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
                                <?php 
                                    $queueNumber = date('Ymd', strtotime($order['created_at'])) . 
                                                 str_pad($order['order_id'], 4, '0', STR_PAD_LEFT);
                                    echo htmlspecialchars($queueNumber);
                                ?>
                            </td>
                            <td><?= date('Y-m-d H:i:s', strtotime($order['created_at'])) ?></td>
                            <td>
                                <button class="btn btn-warning btn-sm" onclick="openEditStatusModal(<?= $order['order_id'] ?>, '<?= $order['status'] ?>')" title="Edit Status">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button class="btn btn-danger btn-sm" onclick="openDeleteOrderModal(<?= $order['order_id'] ?>)" title="Delete Order">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-info">No orders found.</div>
        <?php endif; ?>
    </div>
</div>

<!-- Edit Status Modal -->
<div class="modal fade" id="editStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Order Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editStatusForm">
                    <input type="hidden" id="editOrderId" name="order_id">
                    <div class="mb-3">
                        <label for="orderStatus" class="form-label">Status</label>
                        <select class="form-select" id="orderStatus" name="status" required>
                            <option value="placed">Placed</option>
                            <option value="accepted">Accepted</option>
                            <option value="preparing">Preparing</option>
                            <option value="ready">Ready</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="updateOrderStatus()">Save changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Order Modal -->
<div class="modal fade" id="deleteOrderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this order? This action cannot be undone.</p>
                <input type="hidden" id="deleteOrderId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="deleteOrder()">Delete</button>
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

/* Add these styles for horizontal button alignment */
td .btn-sm {
    display: inline-flex;
    margin: 0 2px;  /* Add some space between buttons */
    vertical-align: middle;
}

/* Optional: Make the actions column width fixed to prevent wrapping */
.table td:last-child {
    white-space: nowrap;
    width: 1%;  /* This makes the column as narrow as possible */
}

/* Center the buttons in the cell */
.table td:last-child {
    text-align: center;
}

/* Ensure consistent button sizing */
.btn-sm {
    padding: 0.25rem 0.5rem;
    line-height: 1;
}

/* Ensure icons are centered in buttons */
.btn-sm i {
    font-size: 1rem;
    display: inline-block;
    vertical-align: middle;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const productFilter = document.getElementById('productFilter');
    
    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        const statusTerm = statusFilter.value.toLowerCase().trim();
        const productTerm = productFilter.value.toLowerCase().trim();
        
        const rows = document.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            let showRow = true;
            
            // Search filter
            if (searchTerm) {
                const canteen = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                const username = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                const customerName = row.querySelector('td:nth-child(4)').textContent.toLowerCase();
                
                showRow = canteen.includes(searchTerm) ||
                         username.includes(searchTerm) || 
                         customerName.includes(searchTerm);
            }
            
            // Status filter
            if (showRow && statusTerm) {
                const status = row.querySelector('.badge').textContent.toLowerCase().trim();
                showRow = status === statusTerm;
            }
            
            // Product filter
            if (showRow && productTerm) {
                const products = row.querySelector('td:nth-child(5)').textContent.toLowerCase();
                showRow = products.includes(productTerm);
            }
            
            // Show/hide row and update counter
            if (showRow) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
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
    
    // Initialize filters
    filterTable();
});

const editStatusModal = new bootstrap.Modal(document.getElementById('editStatusModal'));
const deleteOrderModal = new bootstrap.Modal(document.getElementById('deleteOrderModal'));

function openEditStatusModal(orderId, currentStatus) {
    document.getElementById('editOrderId').value = orderId;
    document.getElementById('orderStatus').value = currentStatus;
    editStatusModal.show();
}

function openDeleteOrderModal(orderId) {
    document.getElementById('deleteOrderId').value = orderId;
    deleteOrderModal.show();
}

function updateOrderStatus() {
    const orderId = document.getElementById('editOrderId').value;
    const status = document.getElementById('orderStatus').value;
    
    fetch('../ajax/update_order_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ order_id: orderId, status: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            editStatusModal.hide();
            location.reload();
        } else {
            alert(data.message || 'Error updating order status');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to update order status. Please try again.');
    });
}

function deleteOrder() {
    const orderId = document.getElementById('deleteOrderId').value;
    
    fetch('../ajax/delete_order.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ order_id: orderId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            deleteOrderModal.hide();
            location.reload();
        } else {
            alert(data.message || 'Error deleting order');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to delete order. Please try again.');
    });
}
</script> 