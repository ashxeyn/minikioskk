<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Description</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
                <?php 
                $stock = $stockObj->fetchStockByProductId($product['product_id']);
                $stockQuantity = $stock ? $stock['quantity'] : 0;
                $stockStatus = $stockQuantity > 0 ? 'In Stock' : 'Out of Stock';
                $statusClass = $stockQuantity > 0 ? 'success' : 'danger';
                ?>
                <tr>
                    <td><?= htmlspecialchars($product['name']) ?></td>
                    <td><?= htmlspecialchars($product['description']) ?></td>
                    <td>₱<?= number_format($product['price'], 2) ?></td>
                    <td><?= $stockQuantity ?></td>
                    <td><span class="badge bg-<?= $statusClass ?>"><?= $stockStatus ?></span></td>
                    <td>
                        <button class="btn btn-primary btn-sm" onclick="openEditModal(<?= $product['product_id'] ?>)">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-success btn-sm" onclick="openStockModal(<?= $product['product_id'] ?>)">
                            <i class="bi bi-plus-circle"></i>
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="deleteProduct(<?= $product['product_id'] ?>)">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
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
                    <div class="mb-3">
                        <label for="productName" class="form-label">Product Name</label>
                        <input type="text" class="form-control" id="productName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="productDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="productDescription" name="description"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="productPrice" class="form-label">Price</label>
                        <input type="number" class="form-control" id="productPrice" name="price" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label for="initialStock" class="form-label">Initial Stock</label>
                        <input type="number" class="form-control" id="initialStock" name="stock" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="saveProduct()">Save Product</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1">
    <!-- Similar structure to add modal but with id="editProductForm" -->
</div>

<!-- Stock Update Modal -->
<div class="modal fade" id="stockModal" tabindex="-1">
    <!-- Stock update form -->
</div> 