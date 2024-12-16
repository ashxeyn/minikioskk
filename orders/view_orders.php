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
    <!-- jQuery -->
    <script src="../assets/jquery/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="../assets/datatables/js/jquery.dataTables.min.js"></script>
    <script src="../assets/datatables/js/dataTables.responsive.min.js"></script>
    <script src="../assets/datatables/js/dataTables.buttons.min.js"></script>
    <!-- DataTables CSS -->
    <link href="../assets/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="../assets/datatables/css/responsive.dataTables.min.css" rel="stylesheet">
    <link href="../assets/datatables/css/buttons.dataTables.min.css" rel="stylesheet">
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
        
        /* Enhanced Status Badge Styles */
        .badge {
            padding: 0.5em 0.8em;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: inline-block;
            min-width: 90px;
            text-align: center;
        }
        
        /* Status-specific colors */
        .badge-pending {
            background-color: #ffc107 !important;
            color: #000 !important;
        }
        
        .badge-accepted {
            background-color: #17a2b8 !important;
            color: #fff !important;
        }
        
        .badge-preparing {
            background-color: #fd7e14 !important;
            color: #fff !important;
        }
        
        .badge-ready {
            background-color: #20c997 !important;
            color: #fff !important;
        }
        
        .badge-completed {
            background-color: #198754 !important;
            color: #fff !important;
        }
        
        .badge-cancelled {
            background-color: #dc3545 !important;
            color: #fff !important;
        }
        
        /* DataTables Pagination and Length Menu Styles */
        .dataTables_length {
            margin-bottom: 1rem;
            float: left;
        }
        
        .dataTables_length select {
            padding: 0.375rem 1.75rem 0.375rem 0.75rem;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            margin: 0 0.5rem;
        }
        
        .dataTables_paginate {
            margin-top: 1rem;
            float: right;
        }
        
        .dataTables_paginate .paginate_button {
            padding: 0.375rem 0.75rem;
            margin-left: 2px;
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            cursor: pointer;
        }
        
        .dataTables_paginate .paginate_button.current {
            background: #0d6efd;
            color: white !important;
            border-color: #0d6efd;
        }
        
        .dataTables_paginate .paginate_button:hover:not(.current) {
            background: #e9ecef;
            color: #0a58ca !important;
        }
        
        .dataTables_info {
            padding-top: 0.5rem;
            float: left;
        }
        
        /* Clear floats */
        .dataTables_wrapper::after {
            content: "";
            display: table;
            clear: both;
        }
    </style>
</head>

    <h2>Orders</h2>
    <div id="orderTable">
   
    <?php if (!empty($orders)): ?>
    <div class="table-responsive">
        <table class="table table-hover" id="ordersTable">
            <thead>
                <tr>
                    <th>#</th>
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

<!-- Response Modal -->
<div class="modal fade" id="responseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Action Status</h5>
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

.page-link {
    padding: 0.375rem 0.75rem;
}
</style>

<script>
$(document).ready(function() {
    // Initialize DataTable with AJAX
    $('#ordersTable').DataTable({
        "processing": true,
        "serverSide": true,
        "dom": '<"top"lf>rt<"bottom"ip><"clear">',
        "ajax": {
            "url": "../ajax/getOrders.php",
            "type": "POST"
        },
        "pageLength": 10,
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
        "responsive": true,
        "order": [[0, "desc"]],
        "columns": [
            { 
                "data": null,
                "render": function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            { "data": "username" },
            { "data": "customer_name" },
            { "data": "product_names" },
            { "data": "total_quantity" },
            { "data": "total_price" },
            { "data": "status" },
            { "data": "queue_number" },
            { "data": "actions" }
        ],
        "columnDefs": [
            { "orderable": false, "targets": [8] }, // Actions column not sortable
            { "orderable": false, "searchable": false, "targets": [0] } // Counter column not sortable or searchable
        ],
        "language": {
            "processing": "Loading...",
            "search": "Search:",
            "lengthMenu": "Show _MENU_ entries",
            "info": "Showing _START_ to _END_ of _TOTAL_ entries",
            "infoEmpty": "Showing 0 to 0 of 0 entries",
            "infoFiltered": "(filtered from _MAX_ total entries)",
            "emptyTable": "No orders found",
            "zeroRecords": "No matching orders found"
        }
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
    
    // Show confirmation in modal
    const responseMessage = document.getElementById('responseMessage');
    responseMessage.textContent = confirmMessage;
    responseMessage.className = 'text-primary';
    
    const responseModal = new bootstrap.Modal(document.getElementById('responseModal'));
    const modalElement = document.getElementById('responseModal');
    
    // Change the modal footer to include Confirm and Cancel buttons
    const modalFooter = modalElement.querySelector('.modal-footer');
    modalFooter.innerHTML = `
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="confirmAction">Confirm</button>
    `;
    
    // Add click handler for confirm button
    document.getElementById('confirmAction').onclick = function() {
        // Hide the confirmation modal
        responseModal.hide();
        
        // Show loading message
        responseMessage.textContent = 'Processing...';
        responseMessage.className = 'text-info';
        modalFooter.innerHTML = `
            <button type="button" class="btn btn-primary" disabled>Please wait...</button>
        `;
        responseModal.show();
        
        // Make the API call
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
                responseMessage.textContent = 'Order status updated successfully!';
                responseMessage.className = 'text-success';
                modalFooter.innerHTML = `
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                `;
                
                // Refresh the table without page reload
                $('#ordersTable').DataTable().ajax.reload(null, false);
                responseModal.hide();
            } else {
                responseMessage.textContent = data.message || 'Failed to update order status';
                responseMessage.className = 'text-danger';
                modalFooter.innerHTML = `
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            responseMessage.textContent = 'Error updating order status';
            responseMessage.className = 'text-danger';
            modalFooter.innerHTML = `
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            `;
        });
    };
    
    responseModal.show();
}
</script>
