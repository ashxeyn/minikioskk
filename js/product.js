function loadProductTable() {
    $.ajax({
        url: '../product/view_products.php',
        method: 'GET',
        success: function (response) {
            $('#productTable').html(response);
        },
        error: function () {
            alert('Error fetching products.');
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

function openDeleteModal(productId) {
    $('#deleteProductId').val(productId);
    $('#deleteProductModal').modal('show');
}

$('#deleteForm').submit(function (e) {
    e.preventDefault();

    const productId = $('#deleteProductId').val();

    $.ajax({
        url: '../product/deleteProduct.php',
        method: 'POST',
        data: { product_id: productId },
        success: function (response) {
            if (response === 'success') {
                $('#deleteProductModal').modal('hide');
                loadProductTable();
            } else {
                alert('Error deleting product');
            }
        },
        error: function () {
            alert('Error deleting product.');
        }
    });
});

function openAddProductModal() {
    $('#addProductModal').modal('show');
}

$('#addProductForm').submit(function (e) {
    e.preventDefault();
    const formData = $(this).serialize();

    $.ajax({
        url: '../product/addProduct.php',
        method: 'POST',
        data: formData,
        success: function (response) {
            if (response === 'success') {
                $('#addProductModal').modal('hide');
                loadProductTable();
            } else {
                alert('Failed to add product: ' + response);
            }
        },
        error: function () {
            alert('Error occurred while adding the product.');
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
        url: '../product/updateStock.php',
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

$(document).ready(function () {
    loadProductTable();
});
