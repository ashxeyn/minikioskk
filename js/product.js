function showAlert(message, type) {

    $('.alert').remove();
    
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    

    $('#productTableContent').prepend(alertHtml);
    

    setTimeout(function() {
        $('.alert').fadeOut('slow', function() {
            $(this).remove();
        });
    }, 5000);
}

function loadProductTable() {
    $.ajax({
        url: 'view_products.php',
        method: 'GET',
        success: function(response) {
            $('#productTableContent').html(response);

            
            $('#productTable').DataTable({
                dom: 'lrtip',
                pageLength: 10,
                order: [[0, 'asc']]
            });
        },
        error: function(xhr, status, error) {
            showAlert('Error loading product table: ' + error, 'danger');
        }
    });
}

function initializeDataTable() {
    const table = $('#productTable').DataTable({
        dom: 'lrtip',
        pageLength: 10,
        order: [[0, 'asc']]
    });


    $('#searchProduct').on('keyup', function() {
        table.search(this.value).draw();
    });

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
        url: '../product/addProduct.php',
        method: 'POST',
        data: formData,
        success: function(response) {
            if (response.startsWith('success')) {
                $('#addProductModal').modal('hide');
                showAlert('Product added successfully!', 'success');
                loadProductTable(); // Refresh the product table
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

function openStockModal(productId) {
    // Debug logging
    console.log('Raw product ID:', productId);
    
    // Ensure productId is a valid integer
    productId = parseInt(productId, 10);
    
    // Debug logging
    console.log('Parsed product ID:', productId);
    
    if (!productId || isNaN(productId) || productId <= 0) {
        console.error('Invalid product ID:', productId);
        alert('Invalid product ID');
        return;
    }

    // Set the product ID in the hidden input
    $('#stockProductId').val(productId);

    // Then fetch current stock
    $.ajax({
        url: '../product/fetchStockByProductId.php',
        method: 'GET',
        data: { product_id: productId },
        dataType: 'json',
        success: function(response) {
            console.log('Stock fetch response:', response);
            if (response.success) {
                $('#currentStock').text(response.quantity || 0);
            } else {
                alert('Error: ' + (response.message || 'Failed to fetch stock'));
                const stockModal = bootstrap.Modal.getInstance(document.getElementById('stockModal'));
                if (stockModal) {
                    stockModal.hide();
                }
            }
        },
        error: function(xhr, status, error) {
            console.error('Error fetching stock:', error);
            console.error('Response:', xhr.responseText);
            alert('Error fetching stock details');
        }
    });
}

// Stock form submission
$('#stockForm').on('submit', function(e) {
    e.preventDefault();
    
    const productId = $('#stockProductId').val();
    const quantity = $('#quantity').val();
    
    // Debug logging
    console.log('Form submission data:', {
        productId: productId,
        quantity: quantity
    });

    $.ajax({
        url: '../product/updateStock.php',
        method: 'POST',
        data: {
            product_id: productId,
            quantity: quantity
        },
        dataType: 'json',
        success: function(response) {
            console.log('Stock update response:', response);
            if (response.success) {
                const stockModal = bootstrap.Modal.getInstance(document.getElementById('stockModal'));
                if (stockModal) {
                    stockModal.hide();
                }
                alert('Stock updated successfully!');
                location.reload();
            } else {
                alert('Error: ' + (response.message || 'Failed to update stock'));
            }
        },
        error: function(xhr, status, error) {
            console.error('Stock update error:', error);
            console.error('Response:', xhr.responseText);
            alert('Error updating stock');
        }
    });
});

$(document).ready(function() {
    // Initialize DataTable if it exists and DataTable function is available
    if (typeof $.fn.DataTable !== 'undefined' && $('#productTable').length) {
        try {
            // Destroy existing DataTable if it exists
            if ($.fn.DataTable.isDataTable('#productTable')) {
                $('#productTable').DataTable().destroy();
            }
            
            // Initialize new DataTable
            const table = $('#productTable').DataTable({
                responsive: true,
                dom: 'lrtip',
                pageLength: 10,
                order: [[0, 'asc']],
                language: {
                    emptyTable: "No products found"
                }
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
        } catch (error) {
            console.error('Error initializing DataTable:', error);
        }
    } else {
        console.warn('DataTable plugin not loaded or table not found');
    }

    // Stock form submission
    $('#stockForm').on('submit', function(e) {
        e.preventDefault();
        
        const productId = $('#stockProductId').val();
        const quantity = $('#quantity').val();
        
        // Debug logging
        console.log('Form submission data:', {
            productId: productId,
            quantity: quantity
        });

        // Validate inputs
        if (!productId || isNaN(parseInt(productId)) || parseInt(productId) <= 0) {
            alert('Invalid product ID');
            return;
        }

        if (!quantity || isNaN(parseInt(quantity)) || parseInt(quantity) <= 0) {
            alert('Please enter a valid quantity');
            return;
        }

        $.ajax({
            url: '../product/updateStock.php',
            method: 'POST',
            data: {
                product_id: productId,
                quantity: quantity
            },
            dataType: 'json',
            success: function(response) {
                console.log('Stock update response:', response);
                if (response.success) {
                    $('#stockModal').modal('hide');
                    alert('Stock updated successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + (response.message || 'Failed to update stock'));
                }
            },
            error: function(xhr, status, error) {
                console.error('Stock update error:', error);
                console.error('Response:', xhr.responseText);
                alert('Error updating stock');
            }
        });
    });

    // Edit Product Form Submission
    $('#editProductForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        $.ajax({
            url: '../product/editProduct.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#editProductModal').modal('hide');
                    showSuccessModal('Product updated successfully');
                    loadProductTable();
                } else {
                    showErrorModal(response.message || 'Failed to update product');
                }
            },
            error: function() {
                showErrorModal('Error updating product');
            }
        });
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
    loadProductTable();
    
    // Load categories and canteens when add product modal opens
    $('#addProductModal').on('show.bs.modal', function() {
        console.log('Modal opening - loading canteens and categories');
        loadCanteens();
        loadCategories();
        $('#addProductForm')[0].reset();
    });
});
