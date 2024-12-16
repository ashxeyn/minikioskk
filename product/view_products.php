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

    <style>
        .dataTables_wrapper {
            --dt-row-selected: none;  
        }
        
        
        .table {
            background-color: transparent !important;
        }
        
        
        .table thead th {
            background-color: transparent !important;
        }
        
       
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: transparent !important;
        }
       
        .table-hover tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.02) !important;
        }
        
      
        .sidebar {
            background-color: #343a40 !important; 
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8) !important;  
        }
        
        .sidebar .nav-link:hover {
            color: #fff !important;  
        }
        
        .sidebar .nav-link.active {
            color: #fff !important; 
            background-color: rgba(255, 255, 255, 0.1) !important;  
        }
    </style>

    <!-- jQuery -->
    <script src="../assets/jquery/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap -->
    <link href="../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="../assets/datatables/js/jquery.dataTables.min.js"></script>
    <script src="../assets/datatables/js/dataTables.responsive.min.js"></script>
    <script src="../assets/datatables/js/dataTables.buttons.min.js"></script>

    <!-- DataTables CSS -->
    <link href="../assets/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="../assets/datatables/css/responsive.dataTables.min.css" rel="stylesheet">
    <link href="../assets/datatables/css/buttons.dataTables.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="../assets/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
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
<div class="modal fade" id="addProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Product</h5>
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
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="price" class="form-label">Price</label>
                        <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required>
                    </div>

                    <div class="mb-3">
                        <label for="type_id" class="form-label">Product Type</label>
                        <select class="form-control" id="type_id" name="type_id" required>
                            <option value="">Select Type</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="initial_stock" class="form-label">Initial Stock</label>
                        <input type="number" class="form-control" id="initial_stock" name="initial_stock" min="0" required>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
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
                        <small class="form-text text-muted">Enter the quantity to add to current stock</small>
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
$(document).ready(function() {
    // Initialize all modals
    var modals = [
        'addProductModal',
        'editProductModal',
        'responseModal',
        'confirmModal',
        'successModal',
        'errorModal'
    ];
    
    modals.forEach(function(modalId) {
        var modalElement = document.getElementById(modalId);
        if (modalElement) {
            new bootstrap.Modal(modalElement);
        }
    });

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
            { "orderable": false, "targets": [7] },
            { "orderable": false, "searchable": false, "targets": [0] }
        ]
    });

    // Add Product Form Submission
    $('#addProductForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        // Add canteen_id from session
        formData.append('canteen_id', '<?php echo $_SESSION['canteen_id']; ?>');
        
        // Get initial stock value
        const initialStock = $('#initial_stock').val();
        formData.append('initial_stock', initialStock);
        
        $.ajax({
            url: '../ajax/addProduct.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                const result = typeof response === 'string' ? JSON.parse(response) : response;
                if (result.success) {
                    $('#addProductModal').modal('hide');
                    showResponse('Product added successfully', true);
                    $('#addProductForm')[0].reset();
                    $('#productsTable').DataTable().ajax.reload();
                } else {
                    showResponse(result.message || 'Error adding product', false);
                }
            },
            error: function(xhr, status, error) {
                showResponse('Error submitting form: ' + error, false);
            }
        });
    });

    // Edit Product Form Submission
    $('#editProductForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        // Add canteen_id from session
        formData.append('canteen_id', '<?php echo $_SESSION['canteen_id']; ?>');
        
        // Get the quantity value for stock update
        const quantity = $('#editQuantity').val();
        if (quantity && quantity > 0) {
            formData.append('quantity', quantity);
        }
        
        $.ajax({
            url: '../ajax/updateProduct.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                const result = typeof response === 'string' ? JSON.parse(response) : response;
                if (result.success) {
                    $('#editProductModal').modal('hide');
                    showResponse('Product updated successfully', true);
                    $('#editProductForm')[0].reset();
                    $('#productsTable').DataTable().ajax.reload();
                } else {
                    showResponse(result.message || 'Error updating product', false);
                }
            },
            error: function(xhr, status, error) {
                showResponse('Error updating product: ' + error, false);
            }
        });
    });
});

// Function to open Add Product Modal
function openAddProductModal() {
    loadProductTypes();
    $('#addProductModal').modal('show');
}

// Function to load product types
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
                $('#editTypeId, #type_id').html(options);
            } catch (error) {
                console.error('Error parsing product types:', error);
                showResponse('Error loading product types', false);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading product types:', error);
            showResponse('Error loading product types', false);
        }
    });
}

// Function to edit product
function editProduct(productId) {
    $.ajax({
        url: `../ajax/getProduct.php?product_id=${productId}`,
        method: 'GET',
        success: function(response) {
            try {
                const data = typeof response === 'string' ? JSON.parse(response) : response;
                if (data.error) {
                    showResponse(data.error, false);
                    return;
                }
                
                $('#editProductId').val(data.product_id);
                $('#editName').val(data.name);
                $('#editDescription').val(data.description);
                $('#editPrice').val(data.price);
                $('#editCurrentStock').text(data.stock_quantity || 0);
                
                loadProductTypes(data.type_id);
                $('#editProductModal').modal('show');
            } catch (error) {
                console.error('Error parsing product data:', error);
                showResponse('Error loading product details', false);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
            showResponse('Error loading product details', false);
        }
    });
}

// Function to delete product
function deleteProduct(productId) {
    if (confirm('Are you sure you want to delete this product?')) {
        $.ajax({
            url: '../ajax/deleteProduct.php',
            type: 'POST',
            data: { product_id: productId },
            success: function(response) {
                const result = typeof response === 'string' ? JSON.parse(response) : response;
                if (result.success) {
                    showResponse('Product deleted successfully', true);
                    $('#productsTable').DataTable().ajax.reload();
                } else {
                    showResponse(result.message || 'Error deleting product', false);
                }
            },
            error: function(xhr, status, error) {
                showResponse('Error deleting product: ' + error, false);
            }
        });
    }
}

// Function to show response messages
function showResponse(message, success = true) {
    const responseMessage = document.getElementById('responseMessage');
    if (responseMessage) {
        responseMessage.textContent = message;
        responseMessage.className = success ? 'text-success' : 'text-danger';
        
        const responseModal = new bootstrap.Modal(document.getElementById('responseModal'));
        responseModal.show();
    }
}
</script>

<!-- Add this modal for showing responses -->
<div class="modal fade" id="responseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
