<?php
session_start();
require_once '../classes/productClass.php';
require_once '../classes/stocksClass.php';
require_once '../tools/functions.php';

try {
    $productObj = new Product();
    $stockObj = new Stocks();

    // Debug output
    error_log("Session role: " . ($_SESSION['role'] ?? 'not set'));
    error_log("Session canteen_id: " . ($_SESSION['canteen_id'] ?? 'not set'));

    $products = [];
    if (isset($_SESSION['role']) && $_SESSION['role'] == 'manager') {
        if (!isset($_SESSION['canteen_id'])) {
            throw new Exception("Canteen ID not set for manager");
        }
        $canteen_id = $_SESSION['canteen_id'];
        $products = $productObj->fetchProductsByCanteen($canteen_id);
    } else {
        $products = $productObj->searchProducts();
    }

    // Debug output
    error_log("Products fetched: " . json_encode($products));

    if (empty($products)) {
        echo "<div class='alert alert-info'>No products found.</div>";
    }
    
    // Continue with the rest of the table output...
    include 'productTable.php'; // Move the table HTML to a separate file
    
} catch (Exception $e) {
    error_log("Error in view_products.php: " . $e->getMessage());
    echo "<div class='alert alert-danger'>Error loading products: " . htmlspecialchars($e->getMessage()) . "</div>";
}
?>
<div class="row mb-4">
    <div class="col-md-4">
        <input type="text" id="searchProduct" class="form-control" placeholder="Search products...">
    </div>
    <div class="col-md-4">
        <select id="categoryFilter" class="form-select">
            <option value="">All Categories</option>
            <?php
            try {
                $categories = $productObj->getCategories();
                foreach ($categories as $category) {
                    echo "<option value='" . htmlspecialchars($category['type_id']) . "'>" . 
                         htmlspecialchars($category['name']) . "</option>";
                }
            } catch (Exception $e) {
                error_log("Error loading categories: " . $e->getMessage());
            }
            ?>
        </select>
    </div>
    <div class="col-md-4">
        <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#addProductModal">
            <i class="bi bi-plus-circle"></i> Add Product
        </button>
        <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#categoryModal">
            <i class="bi bi-tags"></i> Manage Categories
        </button>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover" id="productTable">
        <thead>
            <tr>
                <?php if (!isset($canteen_id)): ?>
                    <th>Canteen</th>
                <?php endif; ?>
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
        <tbody>
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $product): 
                    $stock = $stockObj->fetchStockByProductId($product['product_id']);
                    $stockQuantity = $stock ? $stock['quantity'] : 0;
                    $status = $stockQuantity > 0 ? 'In Stock' : 'Out of Stock';
                ?>
                    <tr>
                        <?php if (!isset($canteen_id)): ?>
                            <td><?= htmlspecialchars($product['canteen_name'] ?? '') ?></td>
                        <?php endif; ?>
                        <td><?= htmlspecialchars($product['name']) ?></td>
                        <td><?= htmlspecialchars($productObj->getCategoryName($product['type_id'])) ?></td>
                        <td><?= htmlspecialchars($product['description']) ?></td>
                        <td>₱<?= number_format($product['price'], 2) ?></td>
                        <td><?= $stockQuantity ?></td>
                        <td><?= $status ?></td>
                        <td><?= $stock ? date('Y-m-d H:i', strtotime($stock['updated_at'])) : 'Never' ?></td>
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
            <?php endif; ?>
        </tbody>
    </table>
</div> 
