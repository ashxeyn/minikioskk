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
<head>
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        /* Override DataTables styles to prevent affecting sidebar */
        .dataTables_wrapper {
            --dt-row-selected: none;  /* Prevent DataTables selection color */
        }
        
        /* Ensure sidebar styles are preserved */
        .sidebar {
            background-color: #343a40 !important;  /* Force sidebar background */
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8) !important;  /* Force sidebar link color */
        }
        
        .sidebar .nav-link:hover {
            color: #fff !important;  /* Force sidebar hover color */
        }
        
        .sidebar .nav-link.active {
            color: #fff !important;  /* Force active link color */
            background-color: rgba(255, 255, 255, 0.1) !important;  /* Force active background */
        }
    </style>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>

    <h2>Orders</h2>
    <div id="orderTable">
   
    <?php if (!empty($orders)): ?>
    <div class="table-responsive">
        <table class="table table-hover" id="ordersTable">
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
.product-thumbnail {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 4px;
}

.no-image {
    color: #999;
    font-size: 0.8em;
}

.badge {
    padding: 0.5em 0.8em;
}

.btn-group {
    gap: 0.25rem;
}

.table th {
    background-color: #f8f9fa;
}

/* Modal backdrop transparency */
.modal-backdrop {
    opacity: 0.7 !important;
}

.modal-backdrop.show {
    opacity: 0.7 !important;
}

/* Additional styles specific to orders */
.action-buttons {
    display: flex;
    gap: 0.25rem;
}

.queue-number {
    font-weight: bold;
    padding: 0.3em 0.6em;
    background-color: #f8f9fa;
    border-radius: 4px;
}

/* Search and filter styles */
.input-group {
    margin-bottom: 0;
}

.form-select {
    cursor: pointer;
}

.btn-secondary {
    margin-bottom: 1rem;
}

/* Loading indicator styles */
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

.entries-wrapper {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.pagination {
    margin: 0;
}

.table-info {
    padding: 0.5rem 0;
}

.page-link {
    padding: 0.375rem 0.75rem;
}
</style>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#ordersTable').DataTable({
        "pageLength": 10,
        "responsive": true,
        "order": [[0, "desc"]], // Sort by Order ID descending by default
        "columnDefs": [
            { "orderable": false, "targets": -1 } // Disable sorting on Actions column
        ]
    });
});

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
    const entriesPerPage = document.getElementById('entriesPerPage');
    const tableBody = document.querySelector('tbody');
    const startEntry = document.getElementById('startEntry');
    const endEntry = document.getElementById('endEntry');
    const totalEntries = document.getElementById('totalEntries');
    const prevPage = document.getElementById('prevPage');
    const nextPage = document.getElementById('nextPage');
    
    let currentPage = 1;
    let rows = Array.from(tableBody.querySelectorAll('tr'));
    
    function updateTableInfo() {
        const total = rows.filter(row => row.style.display !== 'none').length;
        const start = (currentPage - 1) * parseInt(entriesPerPage.value) + 1;
        const end = Math.min(start + parseInt(entriesPerPage.value) - 1, total);
        
        startEntry.textContent = total === 0 ? 0 : start;
        endEntry.textContent = end;
        totalEntries.textContent = total;
        
        // Update pagination
        const totalPages = Math.ceil(total / parseInt(entriesPerPage.value));
        prevPage.classList.toggle('disabled', currentPage === 1);
        nextPage.classList.toggle('disabled', currentPage === totalPages || total === 0);
    }
    
    function filterAndPaginateTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const statusTerm = statusFilter.value.toLowerCase();
        const productTerm = productFilter.value.toLowerCase();
        
        rows.forEach(row => {
            // Your existing filter logic here
            let showRow = true;
            // ... existing filter conditions ...
            
            row.style.display = showRow ? '' : 'none';
        });
        
        // Apply pagination
        const pageSize = parseInt(entriesPerPage.value);
        const start = (currentPage - 1) * pageSize;
        const visibleRows = rows.filter(row => row.style.display !== 'none');
        
        visibleRows.forEach((row, index) => {
            row.style.display = (index >= start && index < start + pageSize) ? '' : 'none';
        });
        
        updateTableInfo();
    }
    
    // Event listeners
    entriesPerPage.addEventListener('change', () => {
        currentPage = 1;
        filterAndPaginateTable();
    });
    
    prevPage.addEventListener('click', (e) => {
        e.preventDefault();
        if (currentPage > 1) {
            currentPage--;
            filterAndPaginateTable();
        }
    });
    
    nextPage.addEventListener('click', (e) => {
        e.preventDefault();
        const visibleRows = rows.filter(row => row.style.display !== 'none');
        const totalPages = Math.ceil(visibleRows.length / parseInt(entriesPerPage.value));
        if (currentPage < totalPages) {
            currentPage++;
            filterAndPaginateTable();
        }
    });
    
    // Initialize table
    filterAndPaginateTable();
    
    // Add to your existing filter event listeners
    searchInput.addEventListener('input', () => {
        currentPage = 1;
        filterAndPaginateTable();
    });
    statusFilter.addEventListener('change', () => {
        currentPage = 1;
        filterAndPaginateTable();
    });
    productFilter.addEventListener('change', () => {
        currentPage = 1;
        filterAndPaginateTable();
    });
});
</script>
