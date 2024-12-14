<?php
session_start();
require_once '../classes/productClass.php';
require_once '../classes/stocksClass.php';
require_once '../tools/functions.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'manager') {
    header('Location: ../accounts/login.php');
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();
    
    $sql = "SELECT m.canteen_id 
            FROM managers m 
            JOIN users u ON m.user_id = u.user_id 
            WHERE m.user_id = :user_id 
            AND m.status = 'accepted'";
            
    $stmt = $conn->prepare($sql);
    $stmt->execute(['user_id' => $_SESSION['user_id']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$result) {
        header('Location: ../accounts/login.php');
        exit;
    }
    
    $_SESSION['canteen_id'] = $result['canteen_id'];
    
    $sql = "SELECT p.*, pt.name as type_name, s.quantity as stock_quantity, 
                   s.updated_at as last_stock_update 
            FROM products p 
            LEFT JOIN product_types pt ON p.type_id = pt.type_id 
            LEFT JOIN stocks s ON p.product_id = s.product_id 
            WHERE p.canteen_id = :canteen_id 
            ORDER BY p.name";
            
    $stmt = $conn->prepare($sql);
    $stmt->execute(['canteen_id' => $_SESSION['canteen_id']]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    error_log("Error in manager view_products: " . $e->getMessage());
    echo "<div class='alert alert-danger'>Error loading products.</div>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Manage Products</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                <i class="bi bi-plus-circle"></i> Add New Product
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-hover">
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
                <tbody>
                    <?php if (!empty($products)): ?>
                        <?php foreach ($products as $product): ?>
                            <?php 
                            $stockQuantity = $product['stock_quantity'] ?? 0;
                            $stockStatus = $stockQuantity > 0 ? 'In Stock' : 'Out of Stock';
                            $statusClass = $stockQuantity > 0 ? 'success' : 'danger';
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($product['name']) ?></td>
                                <td><?= htmlspecialchars($product['type_name']) ?></td>
                                <td><?= htmlspecialchars($product['description']) ?></td>
                                <td>₱<?= number_format($product['price'], 2) ?></td>
                                <td><?= $stockQuantity ?></td>
                                <td><span class="badge bg-<?= $statusClass ?>"><?= $stockStatus ?></span></td>
                                <td><?= $product['last_stock_update'] ? date('M d, Y H:i', strtotime($product['last_stock_update'])) : 'Never' ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-primary" onclick="openEditModal(<?= $product['product_id'] ?>)">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-success" onclick="openStockModal(<?= $product['product_id'] ?>)">
                                            <i class="bi bi-plus-circle"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="deleteProduct(<?= $product['product_id'] ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">No products found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php 
    include '../product/addProductModal.html';
    include '../product/editProductModal.html';
    include '../product/stockModal.html';
    ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

    <script>
    $(document).ready(function() {
        try {
            let table = $('.table').DataTable({
                "responsive": true,
                "pageLength": 10,
                "order": [[2, "asc"]],
                "columnDefs": [
                    {
                        "targets": [1], 
                        "orderable": false
                    },
                    {
                        "targets": [8],
                        "orderable": false,
                        "searchable": false
                    }
                ],
                "language": {
                    "emptyTable": "No products found",
                    "zeroRecords": "No matching products found"
                },
                "initComplete": function() {
                    console.log('DataTable initialization complete');
                }
            });

           
            console.log('DataTable initialized successfully');
        } catch (error) {
            console.error('Error initializing DataTable:', error);
        }

 
        $('#addProductForm').submit(function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('canteen_id', '<?php echo $_SESSION['canteen_id']; ?>');

            console.log('Submitting form with data:');
            for (let pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }

            $.ajax({
                url: '../product/addProduct.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    console.log('Raw response:', response);
                    try {
                        const result = typeof response === 'string' ? JSON.parse(response) : response;
                        if (result.status === 'success') {
                            $('#addProductModal').modal('hide');
                            location.reload();
                        } else {
                            alert('Error: ' + (result.message || 'Unknown error occurred'));
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                        alert('Error adding product: Invalid response from server');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', xhr.responseText);
                    alert('Error adding product: ' + error);
                }
            });
        });

      
        $('#addProductModal').on('show.bs.modal', function () {
            console.log('Modal opening - making AJAX call to get product types');
            
           
            $('#type_id').html('<option value="">Loading categories...</option>');
            $('#debug-info').text('Loading categories...');

            $.ajax({
                url: '../product/getProductTypes.php',
                method: 'GET',
                dataType: 'json',
                success: function(types) {
                    console.log('Received product types:', types);
                    let options = '<option value="">Select Category</option>';
                    if (Array.isArray(types)) {
                        types.forEach(type => {
                            console.log('Adding type:', type);
                            options += `<option value="${type.type_id}">${type.name} (${type.type})</option>`;
                        });
                        console.log('Final options HTML:', options);
                        $('#type_id').html(options);
                        $('#debug-info').text('Categories loaded successfully');
                    } else {
                        console.error('Received types is not an array:', types);
                        $('#debug-info').text('Error: Invalid data received');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading product types:');
                    console.error('Status:', status);
                    console.error('Error:', error);
                    console.error('Response:', xhr.responseText);
                    $('#debug-info').text('Error loading categories');
                    alert('Error loading categories. Please try again.');
                }
            });
        });


        $('#stockForm').submit(function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            // Debug log
            console.log('Stock form data:');
            for (let pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }

            $.ajax({
                url: '../product/updateStock.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    console.log('Raw stock response:', response);
                    try {
                        const result = typeof response === 'string' ? JSON.parse(response) : response;
                        if (result.status === 'success') {
                            $('#stockModal').modal('hide');
                            location.reload();
                        } else {
                            alert('Error: ' + (result.message || 'Failed to update stock'));
                        }
                    } catch (e) {
                        console.error('Error parsing stock response:', e);
                        alert('Error updating stock');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Stock update error:', error);
                    alert('Error updating stock');
                }
            });
        });
    });

    function openEditModal(productId) {
        $.ajax({
            url: '../product/fetchProductDetails.php',
            method: 'GET',
            data: { product_id: productId },
            success: function(response) {
                try {
                    const product = JSON.parse(response);
                    $('#editProductId').val(product.product_id);
                    $('#edit_name').val(product.name);
                    $('#edit_description').val(product.description);
                    $('#edit_category').val(product.type_id);
                    $('#edit_price').val(product.price);
                    $('#editProductModal').modal('show');
                } catch (e) {
                    console.error('Error parsing product details:', e);
                    alert('Error loading product details');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching product details:', error);
                alert('Error loading product details');
            }
        });
    }

    function openStockModal(productId) {
        $('#stockProductId').val(productId);
        $('#stockModal').modal('show');
    }

    function deleteProduct(productId) {
        if (confirm('Are you sure you want to delete this product?')) {
            $.ajax({
                url: '../product/deleteProduct.php',
                method: 'POST',
                data: { product_id: productId },
                success: function(response) {
                    try {
                        const result = JSON.parse(response);
                        if (result.status === 'success') {
                            location.reload();
                        } else {
                            alert('Error: ' + (result.message || 'Failed to delete product'));
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                        alert('Error deleting product');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error deleting product:', error);
                    alert('Error deleting product');
                }
            });
        }
    }
    </script>
</body>
</html> 