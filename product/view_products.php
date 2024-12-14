<?php
session_start();
require_once '../classes/productClass.php';
require_once '../classes/productTypeClass.php';

$productObj = new Product();
    
    $productTypeObj = new ProductType();
    $products = [];
    $productTypes = [];
    
    
    try {
        if (isset($_SESSION['role']) && $_SESSION['role'] == 'manager') {
            $canteenId = $_SESSION['canteen_id'] ?? null;
            if (!$canteenId) {
                throw new Exception("Canteen ID not found in session");
            }
            $products = $productObj->fetchProducts($canteenId);
            $productTypes = $productTypeObj->getAllProductTypes();
        }
    } catch (Exception $e) {
        error_log("Error fetching products: " . $e->getMessage());
    }   

    // Fetch all products with necessary information
    $sql = "SELECT p.*, pt.name as type_name, c.name as canteen_name,
            s.quantity as stock_quantity, s.updated_at as last_stock_update 
            FROM products p 
            LEFT JOIN product_types pt ON p.type_id = pt.type_id 
            LEFT JOIN canteens c ON p.canteen_id = c.canteen_id
            LEFT JOIN stocks s ON p.product_id = s.product_id 
            ORDER BY c.name, p.name";
            
    $products = $productObj->getProducts(); 
    
   

    if (empty($products)) {
        echo "<div class='alert alert-info'>No products found.</div>";
    }
    
    
    
$productObj = new Product();
$productTypeObj = new ProductType();
$products = [];
$productTypes = [];

try {
    if (isset($_SESSION['role']) && $_SESSION['role'] == 'manager') {
        $canteenId = $_SESSION['canteen_id'] ?? null;
        if (!$canteenId) {
            throw new Exception("Canteen ID not found in session");
        }
        $products = $productObj->fetchProducts($canteenId);
        $productTypes = $productTypeObj->getAllProductTypes();
    }
} catch (Exception $e) {
    error_log("Error fetching products: " . $e->getMessage());
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
        
        /* Remove table background */
        .table {
            background-color: transparent !important;
        }
        
        /* Remove header background */
        .table thead th {
            background-color: transparent !important;
        }
        
        /* Remove striping if any */
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: transparent !important;
        }
        
        /* Remove hover background if needed */
        .table-hover tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.02) !important;
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


    <div id="productTable">
        <div class="row mb-4 align-items-center">
            <div class="col-md-6">
                <h2 class="mb-0">Products</h2>
            </div>
            <div class="col-md-6 text-end">
                <button class="btn btn-primary" onclick="openAddProductModal()">
                    <i class="bi bi-plus-circle"></i> Add New Product
                </button>
            </div>
        </div>

    <?php if (!empty($products)): ?>
    <div class="table-responsive">
        <table class="table table-hover" id="productsTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?= htmlspecialchars($product['product_id']) ?></td>
                        <td><?= htmlspecialchars($product['name']) ?></td>
                        <td><?= htmlspecialchars($product['description']) ?></td>
                        <td><?= htmlspecialchars($product['type']) ?> (<?= htmlspecialchars($product['type_category']) ?>)</td>
                        <td>₱<?= number_format($product['price'], 2) ?></td>
                        <td>
                            <span class="badge <?= $product['status'] === 'available' ? 'bg-success' : 'bg-danger' ?>">
                                <?= ucfirst($product['status']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($product['stock_quantity']) ?></td>
                        <td>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-primary" onclick="editProduct(<?= $product['product_id'] ?>)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteProduct(<?= $product['product_id'] ?>)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <div class="alert alert-info">No products found.</div>
    <?php endif; ?>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addProductModalLabel">Add New Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addProductForm">
                    <input type="hidden" id="canteen_id" name="canteen_id" value="<?php echo $_SESSION['canteen_id'] ?? ''; ?>">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Product Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>

                    <div class="mb-3">
                        <label for="type_id" class="form-label">Category</label>
                        <select class="form-control" id="type_id" name="type_id" required>
                            <option value="">Select Category</option>
                            <?php foreach ($productTypes as $type): ?>
                                <option value="<?= htmlspecialchars($type['type_id']) ?>">
                                    <?= htmlspecialchars($type['name']) ?> (<?= htmlspecialchars($type['type']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="price" class="form-label">Price</label>
                        <input type="number" class="form-control" id="price" name="price" step="0.01" required>
                    </div>

                    <div class="mb-3">
                        <label for="initial_stock" class="form-label">Initial Stock</label>
                        <input type="number" class="form-control" id="initial_stock" name="initial_stock" required>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editProductForm">
                    <input type="hidden" id="editProductId" name="product_id">
                    <div class="mb-3">
                        <label for="editName" class="form-label">Product Name</label>
                        <input type="text" class="form-control" id="editName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="editDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="editDescription" name="description"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="editTypeId" class="form-label">Category</label>
                        <select class="form-control" id="editTypeId" name="type_id" required>
                            <!-- Categories will be loaded dynamically -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="editPrice" class="form-label">Price</label>
                        <input type="number" class="form-control" id="editPrice" name="price" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Current Stock: <span id="editCurrentStock">0</span></label>
                    </div>
                    <div class="mb-3">
                        <label for="editQuantity" class="form-label">Add Stock</label>
                        <input type="number" class="form-control" id="editQuantity" name="quantity" min="0">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
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

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="confirmMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmAction">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Success</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="successMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Error Modal -->
<div class="modal fade" id="errorModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Error</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="errorMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
</style>

<script>
// Define loadProductTypes globally
function loadProductTypes(selectedType = '') {
    $.ajax({
        url: '../ajax/getProductTypes.php',
        method: 'GET',
        success: function(response) {
            try {
                const types = JSON.parse(response);
                let options = '<option value="">Select Category</option>';
                types.forEach(type => {
                    options += `<option value="${type.type_id}" ${selectedType == type.type_id ? 'selected' : ''}>
                        ${type.name} (${type.type})
                    </option>`;
                });
                $('#editTypeId, #type_id').html(options); // Update both modals' select elements
            } catch (error) {
                console.error('Error parsing product types:', error);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading product types:', error);
        }
    });
}

function openAddProductModal() {
    // Reset form
    $('#addProductForm')[0].reset();
    
    // Load product types
    loadProductTypes();
    
    // Show modal
    new bootstrap.Modal(document.getElementById('addProductModal')).show();
}

// Rest of your document.ready function and other functions...
$(document).ready(function() {
    // Initialize DataTable
    $('#productsTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "../ajax/getProducts.php",
            "type": "POST"
        },
        "pageLength": 10,
        "responsive": true,
        "order": [[1, "asc"]], // Order by name column
        "columns": [
            { 
                "data": null,
                "render": function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            { "data": "name" },
            { "data": "type" },
            { "data": "description" },
            { "data": "price" },
            { "data": "stock" },
            { "data": "status" },
            { "data": "actions" }
        ],
        "columnDefs": [
            { "orderable": false, "targets": [7] }, // Actions column not sortable
            { "orderable": false, "searchable": false, "targets": [0] } // Counter column not sortable or searchable
        ],
        "language": {
            "processing": "Loading...",
            "search": "Search:",
            "lengthMenu": "Show _MENU_ entries",
            "info": "Showing _START_ to _END_ of _TOTAL_ entries",
            "infoEmpty": "Showing 0 to 0 of 0 entries",
            "infoFiltered": "(filtered from _MAX_ total entries)",
            "emptyTable": "No products found",
            "zeroRecords": "No matching products found"
        }
    });
    
    // Rest of your document.ready code...
    var editModal = new bootstrap.Modal(document.getElementById('editProductModal'));
    var stockModal = new bootstrap.Modal(document.getElementById('stockModal'));
    var addModal = new bootstrap.Modal(document.getElementById('addProductModal'));
});

// Your other existing functions...

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const categoryFilter = document.getElementById('categoryFilter');
    const statusFilter = document.getElementById('statusFilter');
    
    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const categoryTerm = categoryFilter.value.toLowerCase();
        const statusTerm = statusFilter.value.toLowerCase();
        
        const rows = document.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const name = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
            const type = row.querySelector('td:nth-child(5)').textContent.toLowerCase();
            const status = row.querySelector('.badge').textContent.toLowerCase();
            
            const matchesSearch = name.includes(searchTerm);
            const matchesCategory = !categoryTerm || type.includes(categoryTerm);
            const matchesStatus = !statusTerm || status === statusTerm;
            
            row.style.display = (matchesSearch && matchesCategory && matchesStatus) ? '' : 'none';
        });
    }
    
    searchInput.addEventListener('input', filterTable);
    categoryFilter.addEventListener('change', filterTable);
    statusFilter.addEventListener('change', filterTable);
});

function showConfirmation(message, callback) {
    const responseMessage = document.getElementById('responseMessage');
    responseMessage.textContent = message;
    responseMessage.className = 'text-primary';
    
    const modalElement = document.getElementById('responseModal');
    const modalFooter = modalElement.querySelector('.modal-footer');
    modalFooter.innerHTML = `
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="confirmAction">Confirm</button>
    `;
    
    document.getElementById('confirmAction').onclick = callback;
    
    const responseModal = new bootstrap.Modal(modalElement);
    responseModal.show();
}

function showResponse(message, success = true) {
    const responseMessage = document.getElementById('responseMessage');
    responseMessage.textContent = message;
    responseMessage.className = success ? 'text-success' : 'text-danger';
    
    const modalElement = document.getElementById('responseModal');
    const modalFooter = modalElement.querySelector('.modal-footer');
    modalFooter.innerHTML = `
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
    `;
    
    const responseModal = new bootstrap.Modal(modalElement);
    responseModal.show();
    
    if (success) {
        modalElement.addEventListener('hidden.bs.modal', function() {
            $('#productsTable').DataTable().ajax.reload(null, false);
        }, { once: true });
    }
}

function deleteProduct(productId) {
    showConfirmation('Are you sure you want to delete this product?', function() {
        const responseModal = bootstrap.Modal.getInstance(document.getElementById('responseModal'));
        responseModal.hide();
        
        const formData = new FormData();
        formData.append('product_id', productId);
        
        fetch('../ajax/deleteProduct.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            showResponse(
                data.success ? 'Product deleted successfully' : (data.message || 'Error deleting product'),
                data.success
            );
            
            if (data.success) {
                $('#productsTable').DataTable().ajax.reload(null, false);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showResponse('Error deleting product', false);
        });
    });
}

function editProduct(productId) {
    fetch(`../ajax/getProduct.php?product_id=${productId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                showResponse(data.error, false);
                return;
            }
            // Fill the edit modal with product data
            document.getElementById('editProductId').value = data.product_id;
            document.getElementById('editName').value = data.name;
            document.getElementById('editDescription').value = data.description;
            document.getElementById('editPrice').value = data.price;
            document.getElementById('editTypeId').value = data.type_id;
            document.getElementById('editCurrentStock').textContent = data.stock_quantity || 0;
            
            loadProductTypes(data.type_id);
            
            const editModal = new bootstrap.Modal(document.getElementById('editProductModal'));
            editModal.show();
        })
        .catch(error => {
            console.error('Error:', error);
            showResponse('Error loading product details', false);
        });
}

function deleteProduct(productId) {
    if (confirm('Are you sure you want to delete this product?')) {
        // Implement delete functionality
    }
}
</script>
