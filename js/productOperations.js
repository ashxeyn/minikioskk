function openAddProductModal() {
    // Reset form and show modal
    $('#addProductForm')[0].reset();
    $('#addProductModal').modal('show');
    
    // Load categories for the select dropdown
    $.ajax({
        url: '../product/getCategories.php',
        method: 'GET',
        success: function(response) {
            try {
                const categories = JSON.parse(response);
                let options = '<option value="">Select Category</option>';
                categories.forEach(category => {
                    options += `<option value="${category.type_id}">${category.name}</option>`;
                });
                $('#type_id').html(options);
            } catch (e) {
                console.error('Error parsing categories:', e);
                showAlert('Error loading categories', 'danger');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error fetching categories:', error);
            showAlert('Error loading categories', 'danger');
        }
    });
}

// Handle add product form submission
$('#addProductForm').submit(function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    // Add canteen_id from session
    const canteenId = $('#canteen_id').val();
    formData.append('canteen_id', canteenId);

    $.ajax({
        url: '../product/addProduct.php',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            try {
                const result = JSON.parse(response);
                if (result.status === 'success') {
                    $('#addProductModal').modal('hide');
                    showAlert('Product added successfully!', 'success');
                    // Reload the product table
                    loadProductTable();
                } else {
                    showAlert('Error: ' + result.message, 'danger');
                }
            } catch (e) {
                console.error('Error parsing response:', e);
                showAlert('Error adding product', 'danger');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
            showAlert('Error adding product: ' + error, 'danger');
        }
    });
});

// Helper function to show alerts
function showAlert(message, type) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    // Remove any existing alerts
    $('.alert').remove();
    
    // Add the new alert before the product table
    $('#productTable').before(alertHtml);
    
    // Auto dismiss after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow', function() {
            $(this).remove();
        });
    }, 5000);
}

// Function to reload the product table
function loadProductTable() {
    $.ajax({
        url: 'view_products.php',
        method: 'GET',
        success: function(response) {
            $('#productTableContent').html(response);
        },
        error: function(xhr, status, error) {
            showAlert('Error loading product table: ' + error, 'danger');
        }
    });
} 