<?php
require_once '../classes/orderClass.php';

$orderObj = new Order();
$orders = $orderObj->fetchAllOrders();
?>

<!-- Add jQuery first -->
<script src="../assets/jquery/jquery.min.js"></script>
<script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>

<!-- Add DataTables CSS -->
<link rel="stylesheet" href="../assets/datatables/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="../assets/datatables/css/responsive.bootstrap5.min.css">

<!-- Add DataTables JS -->
<script src="../assets/datatables/js/jquery.dataTables.min.js"></script>
<script src="../assets/datatables/js/dataTables.bootstrap5.min.js"></script>
<script src="../assets/datatables/js/dataTables.responsive.min.js"></script>
<script src="../assets/datatables/js/responsive.bootstrap5.min.js"></script>

<div class="container-fluid">
    <h2>Order Management</h2>
    
    <div class="row mb-3">
        <div class="col-md-6">
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
        
        <div class="col-md-6">
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

    <!-- Add some spacing between filters and table -->
    <div class="mb-4"></div>

    <div class="table-responsive">
        <?php if (!empty($orders)): ?>
            <table class="table table-hover display" id="ordersTable">
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

td .btn-sm {
    display: inline-flex;
    margin: 0 2px;  
    vertical-align: middle;
}

.table td:last-child {
    white-space: nowrap;
    width: 1%;  
}

.table td:last-child {
    text-align: center;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    line-height: 1;
}

.btn-sm i {
    font-size: 1rem;
    display: inline-block;
    vertical-align: middle;
}

/* Filter styles */
.form-select {
    height: 38px;
    border-radius: 4px;
    border: 1px solid #ced4da;
    padding: 0.375rem 2.25rem 0.375rem 0.75rem;
    background-color: #fff;
    margin-bottom: 10px;
}

.form-select:focus {
    border-color: #86b7fe;
    outline: 0;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

/* Spacing for mobile responsiveness */
@media (max-width: 768px) {
    .col-md-6 {
        margin-bottom: 1rem;
    }
}
</style>

<script>
$(document).ready(function() {
    // Destroy existing DataTable if it exists
    if ($.fn.DataTable.isDataTable('#ordersTable')) {
        $('#ordersTable').DataTable().destroy();
    }

    // Initialize DataTable
    const table = $('#ordersTable').DataTable({
        "responsive": true,
        "pageLength": 10,
        "order": [[0, "desc"]], // Order by ID descending
        "columnDefs": [
            {
                "targets": [10], // Actions column
                "orderable": false
            }
        ],
        "initComplete": function() {
            // Setup - add a text input to each header cell
            $('#searchInput').on('keyup', function() {
                table.search(this.value).draw();
            });

            $('#statusFilter').on('change', function() {
                table.column(7).search(this.value).draw();
            });

            $('#productFilter').on('change', function() {
                table.column(4).search(this.value).draw();
            });
        }
    });

    // Initialize Bootstrap modals
    const editStatusModal = new bootstrap.Modal(document.getElementById('editStatusModal'));
    const deleteOrderModal = new bootstrap.Modal(document.getElementById('deleteOrderModal'));

    // Edit status modal handler
    window.openEditStatusModal = function(orderId, currentStatus) {
        $('#editOrderId').val(orderId);
        $('#orderStatus').val(currentStatus);
        editStatusModal.show();
    };

    // Delete modal handler
    window.openDeleteOrderModal = function(orderId) {
        $('#deleteOrderId').val(orderId);
        deleteOrderModal.show();
    };

    // Update status handler
    window.updateOrderStatus = function() {
        const orderId = $('#editOrderId').val();
        const status = $('#orderStatus').val();
        
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
                table.ajax.reload();
            } else {
                alert(data.message || 'Error updating order status');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to update order status. Please try again.');
        });
    };

    // Delete order handler
    window.deleteOrder = function() {
        const orderId = $('#deleteOrderId').val();
        
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
                table.ajax.reload();
            } else {
                alert(data.message || 'Error deleting order');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to delete order. Please try again.');
        });
    };
});
</script> 