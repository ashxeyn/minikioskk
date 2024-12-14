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