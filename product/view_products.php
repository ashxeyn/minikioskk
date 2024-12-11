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
            <input type="search" id="searchProduct" placeholder="Search products..." 
                   onkeyup="searchProducts(this.value, document.getElementById('category').value)">
        </form>
        
        <div class="filter-group">
            <label for="category">Category</label>
            <select id="category" class="form-select w-auto" onchange="searchProducts(document.getElementById('searchProduct').value, this.value)">
                <option value="">All Categories</option>
                <option value="Snacks">Snacks</option>
                <option value="Drinks and Beverages">Drinks and Beverages</option>
                <option value="Meals">Meals</option>
                <option value="Fruits">Fruits</option>
            </select>
        </div>

        <div class="mb-3">
            <button type="button" class="btn btn-primary" onclick="openAddProductModal()">Add Product</button>
        </div>

        <table class="table table-bordered" id="productTable">
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
            <tbody id="productTableBody">
            </tbody>
        </table>
    </div>
</div>

<script>
function searchProducts(keyword, category) {
    let url = `../ajax/search_products.php?keyword=${encodeURIComponent(keyword)}`;
    if (category) {
        url += `&category=${encodeURIComponent(category)}`;
    }
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('productTableBody');
            tbody.innerHTML = '';
            
            if (data.length === 0) {
                const cols = <?php echo $_SESSION['role'] != 'manager' ? '9' : '8'; ?>;
                tbody.innerHTML = `
                    <tr>
                        <td colspan="${cols}" class="text-center">No products found.</td>
                    </tr>`;
                return;
            }
            
            data.forEach(product => {
                let row = '<tr>';
                <?php if ($_SESSION['role'] != 'manager'): ?>
                    row += `<td>${escapeHtml(product.canteen_name)}</td>`;
                <?php endif; ?>
                row += `
                    <td>${escapeHtml(product.product_id)}</td>
                    <td>${escapeHtml(product.name)}</td>
                    <td>${escapeHtml(product.description)}</td>
                    <td>${escapeHtml(product.category)}</td>
                    <td>${escapeHtml(product.price)}</td>
                    <td>${escapeHtml(product.quantity || 0)}</td>
                    <td>${product.quantity > 0 ? 'In Stock' : 'Out of Stock'}</td>
                    <td>
                        <button class="btn btn-warning btn-sm" onclick="openEditModal(${product.product_id})">Edit</button>
                        <button class="btn btn-danger btn-sm" onclick="openDeleteModal(${product.product_id})">Delete</button>
                        <button class="btn btn-info btn-sm" onclick="openStockModal(${product.product_id})">Stock In/Stock Out</button>
                    </td>
                </tr>`;
                tbody.innerHTML += row;
            });
        })
        .catch(error => console.error('Error:', error));
}

function escapeHtml(unsafe) {
    return unsafe
        ? unsafe.toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;")
        : '';
}

// Initial load
searchProducts('', '');
</script>
