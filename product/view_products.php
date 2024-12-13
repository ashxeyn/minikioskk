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
        $productTypes = $productTypeObj->fetchAllTypes();
    }
} catch (Exception $e) {
    error_log("Error fetching products: " . $e->getMessage());
}
?>

<div id="productTable">
    <!-- Search and Filters -->
    <div class="row mb-3">
        <!-- Search Box -->
        <div class="col-md-4">
            <div class="input-group">
                <input type="text" id="searchInput" class="form-control" placeholder="Search products...">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
            </div>
        </div>
        
        <!-- Category Filter -->
        <div class="col-md-4">
            <select id="categoryFilter" class="form-select">
                <option value="">All Categories</option>
                <option value="Food">Food</option>
                <option value="Beverages">Beverages</option>
                <option value="Utensils">Utensils</option>
                <option value="Toiletries">Toiletries</option>
                <option value="Others">Others</option>
            </select>
        </div>
        
        <!-- Status Filter -->
        <div class="col-md-4">
            <select id="statusFilter" class="form-select">
                <option value="">All Status</option>
                <option value="available">Available</option>
                <option value="unavailable">Unavailable</option>
            </select>
        </div>
    </div>

    <!-- Add Product Button -->
    <div class="mb-3">
        <button class="btn btn-primary" onclick="openAddProductModal()">
            <i class="bi bi-plus-circle"></i> Add New Product
        </button>
    </div>

    <?php if (!empty($products)): ?>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Product ID</th>
                    <th>Image</th>
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
                        <td>
                            <?php if ($product['image_url']): ?>
                                <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="Product" class="product-thumbnail">
                            <?php else: ?>
                                <span class="no-image">No Image</span>
                            <?php endif; ?>
                        </td>
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
                            <div class="btn-group">
                                <button class="btn btn-sm btn-primary" onclick="editProduct(<?= $product['product_id'] ?>)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-success" onclick="updateStock(<?= $product['product_id'] ?>)">
                                    <i class="bi bi-box"></i>
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
