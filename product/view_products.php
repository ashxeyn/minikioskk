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
                    <th>Product ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Type</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?= htmlspecialchars($product['product_id']) ?></td>
                        <td><?= htmlspecialchars($product['name']) ?></td>
                        <td><?= htmlspecialchars($product['description']) ?></td>
                        <td><?= htmlspecialchars($product['type']) ?></td>
                        <td>₱<?= number_format($product['price'], 2) ?></td>
                        <td>
                            <span class="badge <?= $product['status'] === 'available' ? 'bg-success' : 'bg-danger' ?>">
                                <?= ucfirst($product['status']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($product['quantity'] ?? 0) ?></td>
                        <td>
                            <button class="btn btn-warning btn-sm" onclick="openEditModal(<?= $product['product_id'] ?>)">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="openDeleteModal(<?= $product['product_id'] ?>)">
                                <i class="bi bi-trash"></i>
                            </button>
                            <button class="btn btn-info btn-sm" onclick="openStockModal(<?= $product['product_id'] ?>)">
                                <i class="bi bi-box-seam"></i>
                            </button>
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
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Stock Update Modal -->
<div class="modal fade" id="stockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="stockForm">
                    <input type="hidden" id="stockProductId" name="product_id">
                    <div class="mb-3">
                        <label class="form-label">Current Stock: <span id="currentStock">0</span></label>
                    </div>
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Add Stock</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" required min="1">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update Stock</button>
                    </div>
                </form>
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
        "pageLength": 10,
        "responsive": true
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

function editProduct(productId) {
    // Implement edit functionality
}

function updateStock(productId) {
    // Implement stock update functionality
}

function deleteProduct(productId) {
    if (confirm('Are you sure you want to delete this product?')) {
        // Implement delete functionality
    }
}
</script>
