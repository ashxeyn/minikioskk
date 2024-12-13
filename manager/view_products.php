<?php
session_start();
require_once '../classes/productClass.php';
require_once '../classes/stocksClass.php';
require_once '../tools/functions.php';

// Check if user is manager and has canteen_id
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'manager' || !isset($_SESSION['canteen_id'])) {
    header('Location: ../login.php');
    exit();
}

try {
    $productObj = new Product();
    $stockObj = new Stocks();
    $canteen_id = $_SESSION['canteen_id'];
?>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col">
            <input type="text" class="form-control" id="searchProducts" placeholder="Search products...">
        </div>
        <div class="col-auto">
            <button class="btn btn-primary" onclick="openAddProductModal()">
                <i class="bi bi-plus-circle"></i> Add Product
            </button>
        </div>
    </div>

    <div id="productTableContent">
        <!-- Alert messages will be inserted here -->
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Last Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="productTableBody">
                    <!-- Products will be loaded dynamically -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addProductForm">
                    <input type="hidden" name="canteen_id" value="<?php echo $canteen_id; ?>">
                    <div class="mb-3">
                        <label>Product Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label>Category</label>
                        <select class="form-control" name="type_id" required>
                            <!-- Categories will be loaded dynamically -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Description</label>
                        <textarea class="form-control" name="description"></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Price</label>
                        <input type="number" class="form-control" name="price" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label>Initial Stock</label>
                        <input type="number" class="form-control" name="initial_stock" min="0" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Product</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Stock Modal -->
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
                        <label>Quantity Change</label>
                        <input type="number" class="form-control" name="quantity_change" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Stock</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
} catch (Exception $e) {
    error_log("Error in manager view_products: " . $e->getMessage());
    echo '<div class="alert alert-danger">Error loading products. Please try again.</div>';
}
?> 