function showAlert(message, type) {
    // Remove any existing alerts
    $('.alert').remove();
    
    // Create the alert HTML
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    // Insert the alert before the product table
    $('#productTableContent').prepend(alertHtml);
    
    // Auto dismiss after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow', function() {
            $(this).remove();
        });
    }, 5000);
}

function loadProductTable() {
    const userRole = document.body.dataset.userRole;
    const url = userRole === 'admin' ? '../admin/getProducts.php' : '../manager_product/getProducts.php';

    $.ajax({
        url: url,
        method: 'GET',
        success: function(response) {
            if (typeof response === 'string') {
                response = JSON.parse(response);
            }

            if (response.status === 'success') {
                const products = response.data;
                let tableBody = '';
                
                products.forEach(product => {
                    const stockStatus = product.quantity > 0 ? 'In Stock' : 'Out of Stock';
                    const stockClass = product.quantity > 0 ? 'text-success' : 'text-danger';
                    
                    tableBody += `
                        <tr>
                            <td>${product.name}</td>
                            <td>${product.category_name || 'N/A'}</td>
                            <td>${product.description}</td>
                            <td>₱${parseFloat(product.price).toFixed(2)}</td>
                            <td>${product.quantity || 0}</td>
                            <td class="${stockClass}">${stockStatus}</td>
                            <td>${product.updated_at || 'N/A'}</td>
                            <td>
                                <button class="btn btn-warning btn-sm" onclick="openEditModal(${product.product_id})">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-danger btn-sm" onclick="openDeleteModal(${product.product_id})">
                                    <i class="bi bi-trash"></i>
                                </button>
                                <button class="btn btn-info btn-sm" onclick="openStockModal(${product.product_id})">
                                    <i class="bi bi-box"></i>
                                </button>
                            </td>
                        </tr>`;
                });
                
                $('#productTableBody').html(tableBody);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading products:', error);
            showAlert('Error loading products. Please try again.', 'danger');
        }
    });
}

function initializeDataTable() {
    const table = $('#productTable').DataTable({
        dom: 'lrtip',
        pageLength: 10,
        order: [[0, 'asc']]
    });

    // Search functionality
    $('#searchProduct').on('keyup', function() {
        table.search(this.value).draw();
    });

    // Category filter
    $('#categoryFilter').on('change', function() {
        const categoryId = $(this).val();
        if (categoryId) {
            table.column(1) // Category column
                .search($(this).find('option:selected').text())
                .draw();
        } else {
            table.column(1).search('').draw();
        }
    });
}

function openEditModal(productId) {
    $.ajax({
        url: '../product/fetchProductDetails.php',
        method: 'GET',
        data: { product_id: productId },
        success: function (response) {
            const product = JSON.parse(response);
            $('#editProductId').val(product.product_id);
            $('#edit_name').val(product.name);
            $('#edit_description').val(product.description);
            $('#edit_category').val(product.category);
            $('#edit_price').val(product.price);
            $('#edit_availability').val(product.availability);
            $('#editProductModal').modal('show');
        },
        error: function () {
            alert('Error fetching product details.');
        }
    });
}

$('#editProductForm').submit(function (e) {
    e.preventDefault();

    const productId = $('#editProductId').val();
    const name = $('#edit_name').val();
    const description = $('#edit_description').val();
    const category = $('#edit_category').val();
    const price = $('#edit_price').val();
    const availability = $('#edit_availability').val();

    $.ajax({
        url: '../product/editProduct.php',
        method: 'POST',
        data: {
            product_id: productId,
            name: name,
            description: description,
            category: category,
            price: price,
            availability: availability
        },
        success: function (response) {
            if (response === 'success') {
                $('#editProductModal').modal('hide');
                loadProductTable();
            } else {
                alert('Error updating product');
            }
        },
        error: function () {
            alert('Error updating product.');
        }
    });
});

function openDeleteModal(product_id) {
    $('#deleteProductId').val(product_id);
    $('#deleteProductModal').modal('show');
}

$('#deleteProductForm').submit(function(e) {
    e.preventDefault();
    const product_id = $('#deleteProductId').val();
    
    $.ajax({
        url: '../product/deleteProduct.php',
        method: 'POST',
        data: { product_id: product_id },
        dataType: 'json',
        success: function(response) {
            $('#deleteProductModal').modal('hide');
            
            if (response.status === 'success') {
                showAlert('Product deleted successfully!', 'success');
                // Reload the product table
                loadProductTable();
            } else {
                showAlert('Error: ' + (response.message || 'Failed to delete product'), 'danger');
            }
        },
        error: function(xhr, status, error) {
            $('#deleteProductModal').modal('hide');
            showAlert('Error: ' + error, 'danger');
        }
    });
});

function openAddProductModal() {
    $('#addProductModal').modal('show');
}

$('#addProductForm').submit(function(e) {
    e.preventDefault();
    const formData = $(this).serialize();
    
    $.ajax({
        url: '../manager_product/addProduct.php',
        method: 'POST',
        data: formData,
        success: function(response) {
            if (response.startsWith('success')) {
                $('#addProductModal').modal('hide');
                showAlert('Product added successfully!', 'success');
                loadProductTable();
            } else {
                const errorMsg = response.replace('failure:', '');
                showAlert('Error adding product: ' + errorMsg, 'danger');
            }
        },
        error: function(xhr, status, error) {
            showAlert('Error adding product: ' + error, 'danger');
        }
    });
});

function openStockModal(product_id) {
    $('#stockProductId').val(product_id);
    $('#stockModal').modal('show');
}

$('#stockForm').submit(function (e) {
    e.preventDefault();
    const formData = new FormData(this);

    $.ajax({
        url: '../manager_product/updateStock.php',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (response) {
            if (response.status === 'success') {
                $('#stockForm')[0].reset();
                $('#stockModal').modal('hide');
                loadProductTable();
            } else {
                alert('Failed to update stock.');
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX Error:', status, error);
            alert('An error occurred while updating the stock.');
        }
    });
});

function openCategoryModal() {
    loadCategories();
    $('#categoryModal').modal('show');
}

function loadCategories() {
    $.ajax({
        url: '../product/getCategories.php',
        method: 'GET',
        success: function(response) {
            const categories = JSON.parse(response);
            let options = '<option value="">Select Category</option>';
            categories.forEach(category => {
                options += `<option value="${category.type_id}">${category.name}</option>`;
            });
            $('#type_id').html(options);
        },
        error: function(xhr, status, error) {
            console.error('Error loading categories:', error);
        }
    });
}

$('#addCategoryForm').on('submit', function(e) {
    e.preventDefault();
    const categoryName = $('#newCategoryName').val();
    
    $.ajax({
        url: '../product/addCategory.php',
        method: 'POST',
        data: { name: categoryName },
        success: function(response) {
            if (response === 'success') {
                $('#newCategoryName').val('');
                loadCategories();
                showAlert('Category added successfully!', 'success');
            } else {
                showAlert('Error adding category', 'danger');
            }
        }
    });
});

function editCategory(typeId, name) {
    const newName = prompt('Enter new category name:', name);
    if (newName && newName !== name) {
        $.ajax({
            url: '../product/editCategory.php',
            method: 'POST',
            data: { type_id: typeId, name: newName },
            success: function(response) {
                if (response === 'success') {
                    loadCategories();
                    showAlert('Category updated successfully!', 'success');
                } else {
                    showAlert('Error updating category', 'danger');
                }
            }
        });
    }
}

function deleteCategory(typeId) {
    if (confirm('Are you sure you want to delete this category?')) {
        $.ajax({
            url: '../product/deleteCategory.php',
            method: 'POST',
            data: { type_id: typeId },
            success: function(response) {
                if (response === 'success') {
                    loadCategories();
                    showAlert('Category deleted successfully!', 'success');
                } else {
                    showAlert('Error deleting category', 'danger');
                }
            }
        });
    }
}

function loadCanteens() {
    console.log('Loading canteens...'); // Debug log
    $.ajax({
        url: '../product/getCanteens.php',
        method: 'GET',
        dataType: 'json', // Specify expected data type
        success: function(response) {
            console.log('Canteens response:', response); // Debug log
            
            if (response.error) {
                console.error('Server error:', response.error);
                showAlert('Error loading canteens: ' + response.error, 'danger');
                return;
            }
            
            let options = '<option value="">Select Canteen</option>';
            response.forEach(canteen => {
                options += `<option value="${canteen.canteen_id}">${canteen.name} - ${canteen.campus_location}</option>`;
            });
            $('#canteen_id').html(options);
        },
        error: function(xhr, status, error) {
            console.error('Ajax error:', error); // Debug log
            console.error('Status:', status);
            console.error('Response:', xhr.responseText);
            showAlert('Error loading canteens. Please try again.', 'danger');
        }
    });
}

$(document).ready(function () {
    const userRole = document.body.dataset.userRole;
    
    // Hide category management and canteen selection for managers
    if (userRole === 'admin') {
        $('.category-management').show();
        $('#canteen_select').show();
    } else {
        $('.category-management').hide();
        $('#canteen_select').hide();
        // Remove category management button if it exists
        $('.btn-manage-categories').remove();
    }
    
    loadProductTable();
    
    // Load only categories for managers when adding products
    $('#addProductModal').on('show.bs.modal', function() {
        loadCategories();
        // Remove canteen loading for managers
        $('#addProductForm')[0].reset();
    });
    
    // Update search functionality for managers
    $('#searchProducts').on('input', function() {
        const keyword = $(this).val();
        const userRole = document.body.dataset.userRole;
        
        $.ajax({
            url: '../manager_product/search_products.php',
            method: 'GET',
            data: { 
                keyword: keyword,
                canteen_id: userRole === 'manager' ? document.body.dataset.canteenId : null
            },
            success: function(response) {
                // Refresh table with search results
                loadProductTable();
            }
        });
    });
});
