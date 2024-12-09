<?php
session_start();
require_once '../classes/productClass.php';
require_once '../classes/stocksClass.php';
require_once '../tools/functions.php';

$productObj = new Product();
$stockObj = new Stocks();

$keyword = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';

$products = [];
$products = $productObj->searchProducts($keyword, $category);

if (isset($_SESSION['role']) && $_SESSION['role'] == 'manager') {
    $canteen_id = $_SESSION['canteen_id'];
    $products = $productObj->fetchProductsByCanteen($canteen_id); 
}
?>

<head>
    <link rel="stylesheet" href="../css/table.css">
</head>

<div class='center'>
    <div class='table'>
        <form autocomplete='off'>
            <input type="search" id="search" placeholder="Search products...">
        </form>
        
        <div class="filter-group">
            <label for="category">Category</label>
            <select id="category" class="form-select w-auto" onchange="filterProductCategory()">
                <option value="">All Categories</option>
                <option value="Snacks" <?= $category === 'Snacks' ? 'selected' : '' ?>>Snacks</option>
                <option value="Drinks and Beverages" <?= $category === 'Drinks and Beverages' ? 'selected' : '' ?>>Drinks and Beverages</option>
                <option value="Meals" <?= $category === 'Meals' ? 'selected' : '' ?>>Meals</option>
                <option value="Fruits" <?= $category === 'Fruits' ? 'selected' : '' ?>>Fruits</option>
            </select>
        </div>

        <div class="mb-3">
            <button type="button" class="btn btn-primary" onclick="openAddProductModal()">Add Product</button>
        </div>

        <table class="table table-bordered" id="table">
            <thead>
                <tr>
                    <?php if ($_SESSION['role'] != 'manager'): ?>
                        <th>Canteen Name</th>
                    <?php endif; ?>
                    <th>Product ID</th>
                    <th>Product Name</th>
                    <th>Description</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock Quantity</th>
                    <th>Stock Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $product): ?>
                        <?php
                        $stock = $stockObj->fetchStockByProductId($product['product_id']);
                        $quantity = $stock ? $stock['quantity'] : 0;
                        $status = $stock && $quantity > 0 ? 'In Stock' : 'Out of Stock';
                        ?>
                        <tr id="product-<?= $product['product_id'] ?>" class="dataRow">
                            <?php if ($_SESSION['role'] != 'manager'): ?>
                                <td><?= clean_input($product['canteen_name']) ?></td>
                            <?php endif; ?>
                            <td><?= $product['product_id'] ?></td>
                            <td><?= clean_input($product['name']) ?></td>
                            <td><?= clean_input($product['description']) ?></td>
                            <td><?= clean_input($product['category']) ?></td>
                            <td><?= $product['price'] ?></td>
                            <td><?= $quantity ?></td>
                            <td><?= $status ?></td>
                            <td>
                                <button class="btn btn-warning btn-sm" onclick="openEditModal(<?= $product['product_id'] ?>)">Edit</button>
                                <button class="btn btn-danger btn-sm" onclick="openDeleteModal(<?= $product['product_id'] ?>)">Delete</button>
                                <button class="btn btn-info btn-sm" onclick="openStockModal(<?= $product['product_id'] ?>)">Stock In/Stock Out</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="text-center">No products found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="../js/search.js"></script>
