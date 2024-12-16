<?php
session_start();

require_once '../classes/stocksClass.php';
require_once '../tools/functions.php';
require_once '../classes/adminProductTypeClass.php';
require_once '../classes/adminProductClass.php';

// Remove the session check since we want to allow access
// try {
//     if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
//         throw new Exception("Unauthorized access");
//     }
// } catch (Exception $e) {
//     error_log($e->getMessage());
//     header('Location: ../accounts/login.php');
//     exit;
// }

try {
    $adminProduct = new AdminProduct();
    $products = $adminProduct->getAllProducts();
    $canteens = $adminProduct->getCanteens();
} catch (Exception $e) {
    error_log("Error in admin view_products: " . $e->getMessage());
    echo "<div class='alert alert-danger'>Error loading products.</div>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - View All Products</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>All Products</h2>
            
        </div>

        <div class="table-responsive">
            <table class="table table-hover" id="productsTable">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Canteen</th>
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
                                <td><?= htmlspecialchars($product['canteen_name']) ?></td>
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
                                        
                                        <button type="button" class="btn btn-sm btn-danger" onclick="deleteProduct(<?= $product['product_id'] ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center">No products found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php 
    include '../product/editProductModal.html';
    include '../product/deleteProductModal.html';
    ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

    <script>
    // Define deleteProduct in global scope
    function deleteProduct(productId) {
        $('#deleteProductId').val(productId);
        $('#deleteProductModal').modal('show');
    }

    $(document).ready(function() {
        // Initialize DataTable
        const table = $('#productsTable').DataTable({
            "responsive": true,
            "pageLength": 10,
            "order": [[0, "asc"]],
            "language": {
                "emptyTable": "No products found"
            }
        });

        // Function to load product types
        function loadProductTypes(selectedType = '') {
            return $.ajax({
                url: '../ajax/getAdminProductTypes.php',
                method: 'GET',
                success: function(response) {
                    console.log('Product types response:', response);
                    let options = '<option value="">Select Category</option>';
                    response.forEach(type => {
                        options += `<option value="${type.type_id}" ${selectedType == type.type_id ? 'selected' : ''}>
                            ${type.name} (${type.category_name})
                        </option>`;
                    });
                    $('#type_id, #editTypeId').html(options);
                },
                error: function(xhr, status, error) {
                    console.error('Error loading product types:', error);
                    showResponse('Error loading product categories', false);
                }
            });
        }

        // Load product types when page loads
        loadProductTypes();

        // Load product types when modals open
        $('#addProductModal').on('show.bs.modal', function() {
            loadProductTypes();
        });

        $('#editProductModal').on('show.bs.modal', function() {
            const selectedType = $('#editTypeId').val();
            loadProductTypes(selectedType);
        });

        // Edit Product
        window.openEditModal = function(productId) {
            $.ajax({
                url: '../ajax/getProduct.php',
                method: 'GET',
                data: { product_id: productId },
                success: function(response) {
                    console.log('Edit product response:', response);
                    
                    if (response.error) {
                        showResponse(response.error, false);
                        return;
                    }

                    // Fill the edit modal with product data
                    $('#editProductId').val(response.product_id);
                    $('#editName').val(response.name);
                    $('#editDescription').val(response.description);
                    $('#editPrice').val(response.price);
                    $('#editTypeId').val(response.type_id);
                    
                    // Update current stock display
                    $('#editCurrentStock').text(response.stock_quantity || 0);
                    
                    // Show the modal
                    $('#editProductModal').modal('show');
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching product:', error);
                    showResponse('Error loading product details', false);
                }
            });
        };

        // Update Product
        $('#updateProductBtn').click(function() {
            const formData = new FormData($('#editProductForm')[0]);
            
            // Add current status
            formData.append('status', 'available');
            
            $.ajax({
                url: '../ajax/updateProduct.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    try {
                        const result = typeof response === 'string' ? JSON.parse(response) : response;
                        if (result.success) {
                            $('#editProductModal').modal('hide');
                            showResponse('Product updated successfully', true);
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            showResponse(result.message || 'Failed to update product', false);
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                        showResponse('Error updating product', false);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    showResponse('Error updating product', false);
                }
            });
        });

        // Move delete confirmation handler here
        $('#confirmDeleteBtn').click(function() {
            const productId = $('#deleteProductId').val();
            
            if (!productId) {
                showResponse('Invalid product ID', false);
                return;
            }
            
            $.ajax({
                url: '../ajax/deleteProduct.php',
                method: 'POST',
                data: { product_id: productId },
                success: function(response) {
                    try {
                        const result = typeof response === 'string' ? JSON.parse(response) : response;
                        if (result.success) {
                            $('#deleteProductModal').modal('hide');
                            showResponse('Product deleted successfully', true);
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            showResponse(result.message || 'Failed to delete product', false);
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                        showResponse('Error deleting product', false);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    showResponse('Error deleting product', false);
                }
            });
        });

        // Add Product
        $('#saveProductBtn').click(function() {
            const formData = new FormData($('#addProductForm')[0]);
            formData.append('status', 'available');
            
            $.ajax({
                url: '../ajax/addProduct.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    try {
                        const result = typeof response === 'string' ? JSON.parse(response) : response;
                        if (result.success) {
                            $('#addProductModal').modal('hide');
                            showResponse('Product added successfully', true);
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            showResponse(result.message || 'Failed to add product', false);
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                        showResponse('Error adding product', false);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    showResponse('Error adding product', false);
                }
            });
        });
    });

    // Helper function to show response messages (keep this outside document.ready)
    function showResponse(message, success = true) {
        const alertDiv = $('<div>')
            .addClass(`alert alert-${success ? 'success' : 'danger'} alert-dismissible fade show`)
            .attr('role', 'alert')
            .html(`
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `);
        
        // Remove any existing alerts
        $('.alert').remove();
        
        // Add the new alert at the top of the container
        $('.container').prepend(alertDiv);
        
        // Auto dismiss after 3 seconds
        setTimeout(() => {
            alertDiv.alert('close');
        }, 3000);
    }
    </script>
</body>
</html> 