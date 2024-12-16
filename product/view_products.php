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

<!DOCTYPE html>
<html>
<head>
    <!-- Existing CSS files -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.dataTables.min.css">
    
    <!-- jQuery first, then DataTables -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    
    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
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
                            <?php foreach ($productTypes as $type): ?>
                                <option value="<?php echo htmlspecialchars($type['type_id']); ?>">
                                    <?php echo htmlspecialchars($type['name']); ?>
                                </option>
                            <?php endforeach; ?>
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
    fetch('../ajax/getAdminProductTypes.php')
        .then(response => response.json())
        .then(types => {
            let options = '<option value="">Select Category</option>';
            types.forEach(type => {
                options += `<option value="${type.type_id}" ${selectedType == type.type_id ? 'selected' : ''}>
                    ${type.name} (${type.category_name})
                </option>`;
            });
            $('#editTypeId, #type_id').html(options);
        })
        .catch(error => {
            console.error('Error loading product types:', error);
            showResponse('Error loading product categories', false);
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
    // Make sure DataTable is available before initializing
    if ($.fn.DataTable) {
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
    } else {
        console.error('DataTables plugin not loaded');
    }
    
    // Rest of your document.ready code...
    var editModal = new bootstrap.Modal(document.getElementById('editProductModal'));
    var stockModal = new bootstrap.Modal(document.getElementById('stockModal'));
    var addModal = new bootstrap.Modal(document.getElementById('addProductModal'));
    
    // Load product types when page loads
    loadProductTypes();
    
    // Load product types when add modal opens
    $('#addProductModal').on('show.bs.modal', function() {
        loadProductTypes();
    });
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
    // Show confirmation modal
    $('#confirmMessage').text('Are you sure you want to delete this product?');
    $('#confirmModal').modal('show');
    
    // Set up the confirm action
    $('#confirmAction').off('click').on('click', function() {
        const formData = new FormData();
        formData.append('product_id', productId);
        
        fetch('../ajax/deleteProduct.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // Hide confirmation modal
            $('#confirmModal').modal('hide');
            
            // Show response message
            if (data.success) {
                showResponse('Product deleted successfully', true);
                // Reload DataTable
                const dataTable = $('#productsTable').DataTable();
                if (dataTable) {
                    dataTable.ajax.reload();
                } else {
                    location.reload();
                }
            } else {
                showResponse(data.message || 'Error deleting product', false);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            $('#confirmModal').modal('hide');
            showResponse('Error deleting product', false);
        });
    });
}

function editProduct(productId) {
    // Show confirmation modal
    $('#editProductId').val(productId);
    
    // Fetch product details
    fetch(`../ajax/getProduct.php?product_id=${productId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                showResponse(data.error, false);
                return;
            }
            
            // Fill the edit modal with product data
            $('#editName').val(data.name);
            $('#editDescription').val(data.description);
            $('#editPrice').val(data.price);
            $('#editTypeId').val(data.type_id);
            $('#editCurrentStock').text(data.stock_quantity || 0);
            
            // Load product types and set selected
            loadProductTypes(data.type_id);
            
            // Show the modal
            $('#editProductModal').modal('show');
        })
        .catch(error => {
            console.error('Error:', error);
            showResponse('Error loading product details', false);
        });
}

// Modify the edit product form submission handler
$('#editProductForm').submit(function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('canteen_id', '<?php echo $_SESSION['canteen_id']; ?>');
    
    // Get the quantity value for stock update
    const quantity = $('#editQuantity').val();
    const productId = $('#editProductId').val();
    
    console.log('Updating product:', Object.fromEntries(formData));
    
    // First update the product details
    $.ajax({
        url: '../ajax/updateProduct.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            console.log('Update product response:', response);
            
            try {
                const result = typeof response === 'string' ? JSON.parse(response) : response;
                
                if (result.success) {
                    console.log('Product updated successfully:', result);
                    
                    // If quantity was provided, update stock
                    if (quantity && quantity > 0) {
                        const stockFormData = new FormData();
                        stockFormData.append('product_id', productId);
                        stockFormData.append('quantity', quantity);
                        
                        // Make a second AJAX call to update the stock
                        $.ajax({
                            url: '../ajax/addStock.php',
                            type: 'POST',
                            data: stockFormData,
                            processData: false,
                            contentType: false,
                            success: function(stockResponse) {
                                console.log('Stock update response:', stockResponse);
                                $('#editProductModal').modal('hide');
                                showResponse('Product and stock updated successfully', true);
                                $('#editProductForm')[0].reset();
                                $('#productsTable').DataTable().ajax.reload();
                            },
                            error: function(xhr, status, error) {
                                console.error('Error updating stock:', error);
                                showResponse('Product updated but error updating stock', false);
                            }
                        });
                    } else {
                        // If no quantity provided, just close modal and show success
                        $('#editProductModal').modal('hide');
                        showResponse('Product updated successfully', true);
                        $('#editProductForm')[0].reset();
                        $('#productsTable').DataTable().ajax.reload();
                    }
                } else {
                    console.error('Error updating product:', result.message);
                    showResponse(result.message || 'Error updating product', false);
                }
            } catch (error) {
                console.error('Error parsing update product response:', error);
                showResponse('Error processing server response', false);
            }
        },
        error: function(xhr, status, error) {
            console.error('Update product ajax error:', {xhr, status, error});
            showResponse('Error submitting form: ' + error, false);
        }
    });
});

$(document).ready(function() {
    // Initialize DataTable code here...

    // Add Product Form Submission
    $('#addProductForm').submit(function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('canteen_id', '<?php echo $_SESSION['canteen_id']; ?>');
        
        // Log the form data being sent
        console.log('Adding new product:', Object.fromEntries(formData));
        
        $.ajax({
            url: '../ajax/addProduct.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('Add product response:', response);
                
                try {
                    const result = typeof response === 'string' ? JSON.parse(response) : response;
                    
                    if (result.success) {
                        console.log('Product added successfully:', result);
                        
                        // Add initial stock for the new product
                        const stockFormData = new FormData();
                        stockFormData.append('product_id', result.product_id);
                        stockFormData.append('quantity', formData.get('initial_stock'));
                        
                        // Make a second AJAX call to add the initial stock
                        $.ajax({
                            url: '../ajax/addInitialStock.php',
                            type: 'POST',
                            data: stockFormData,
                            processData: false,
                            contentType: false,
                            success: function(stockResponse) {
                                console.log('Stock added response:', stockResponse);
                                $('#addProductModal').modal('hide');
                                showResponse('Product and initial stock added successfully', true);
                                $('#addProductForm')[0].reset();
                                $('#productsTable').DataTable().ajax.reload();
                            },
                            error: function(xhr, status, error) {
                                console.error('Error adding stock:', error);
                                showResponse('Product added but error setting initial stock', false);
                            }
                        });
                    } else {
                        console.error('Error adding product:', result.message);
                        showResponse(result.message || 'Error adding product', false);
                    }
                } catch (error) {
                    console.error('Error parsing add product response:', error);
                    showResponse('Error processing server response', false);
                }
            },
            error: function(xhr, status, error) {
                console.error('Add product ajax error:', {xhr, status, error});
                showResponse('Error submitting form: ' + error, false);
            }
        });
    });

    // Add validation for price and stock inputs
    $('#price, #initial_stock').on('input', function() {
        this.value = this.value.replace(/[^0-9.]/g, '');
    });
});

// Make sure the showResponse function is defined
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

// Add this to your existing JavaScript
$('#editProductForm').submit(function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('canteen_id', '<?php echo $_SESSION['canteen_id']; ?>');
    
    // Get the quantity value
    const quantity = $('#editQuantity').val();
    if (quantity && quantity > 0) {
        formData.append('quantity', quantity);
    }
    
    // Add console log before submission
    console.log('Updating product:', Object.fromEntries(formData));
    
    $.ajax({
        url: '../ajax/updateProduct.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            // Add console log for response
            console.log('Update product response:', response);
            
            try {
                const result = typeof response === 'string' ? JSON.parse(response) : response;
                
                if (result.success) {
                    console.log('Product updated successfully:', result);
                    // Close the modal
                    $('#editProductModal').modal('hide');
                    
                    // Show success message
                    showResponse('Product updated successfully', true);
                    
                    // Reset the form
                    $('#editProductForm')[0].reset();
                    
                    // Reload the DataTable
                    $('#productsTable').DataTable().ajax.reload();
                } else {
                    console.error('Error updating product:', result.message);
                    showResponse(result.message || 'Error updating product', false);
                }
            } catch (error) {
                console.error('Error parsing update product response:', error);
                showResponse('Error processing server response', false);
            }
        },
        error: function(xhr, status, error) {
            console.error('Update product ajax error:', {xhr, status, error});
            showResponse('Error submitting form: ' + error, false);
        }
    });
});
</script>

<!-- Add this modal for showing responses -->
