function fetchOrders() {
    $.ajax({
        url: '../orders/view_orders.php',
        method: 'GET',
        success: function(response) {
            $('#orderTable').html(response);
        },
        error: function() {
            alert('Failed to fetch orders. Please try again.');
        }
    });
}

function openEditModal(order_id) {
    $.ajax({
        url: '../orders/fetchOrderDetails.php',
        type: 'GET',
        data: { order_id: order_id },
        success: function(response) {
            try {
                const order = JSON.parse(response);
                $('#editOrderId').val(order.order_id);
                $('#edit_status').val(order.status);
                $('#edit_queue_number').val(order.queue_number);

             
                $.ajax({
                    url: '../orders/fetchOrderProducts.php',
                    type: 'GET',
                    data: { order_id: order.order_id },
                    success: function(productsResponse) {
                        const products = JSON.parse(productsResponse);
                        let productListHtml = '';
                        products.forEach(product => {
                            productListHtml += `
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <strong>${product.name}</strong>
                                        <span>Current Quantity: ${product.quantity}</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="input-group" style="max-width: 200px;">
                                            <input type="number" 
                                                class="form-control form-control-sm quantity-change" 
                                                min="1" 
                                                value="1" 
                                                data-product-id="${product.product_id}">
                                        </div>
                                        <button type="button" 
                                            class="btn btn-success btn-sm add-quantity" 
                                            data-product-id="${product.product_id}">
                                            Add
                                        </button>
                                        <button type="button" 
                                            class="btn btn-warning btn-sm remove-quantity" 
                                            data-product-id="${product.product_id}">
                                            Remove
                                        </button>
                                    </div>
                                </div>`;
                        });
                        $('#current-products').html(productListHtml);
                    }
                });

                $.ajax({
                    url: '../orders/fetchProductsByCanteen.php',
                    type: 'GET',
                    data: { canteen_id: order.canteen_id },
                    success: function(productsResponse) {
                        const products = JSON.parse(productsResponse);
                        let productOptions = '<option value="">Select a product</option>';
                        products.forEach(product => {
                            productOptions += `<option value="${product.product_id}">${product.name} - ${product.price}</option>`;
                        });
                        $('.product-select').html(productOptions);
                    }
                });

                $('#editOrderModal').modal('show');
            } catch (error) {
                console.error('Error parsing response:', error);
            }
        }
    });
}

$('#add-more-products').click(function() {
    const newRow = `
        <div class="row mb-2">
            <div class="col-md-6">
                <select class="form-select product-select" name="new_products[]">
                    ${$('.product-select').first().html()}
                </select>
            </div>
            <div class="col-md-4">
                <input type="number" class="form-control quantity-input" name="new_quantities[]" min="1" value="1" placeholder="Quantity">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-danger btn-sm remove-product-row">Remove</button>
            </div>
        </div>`;
    $('#new-products-container').append(newRow);
});

$(document).on('click', '.remove-product-row', function() {
    $(this).closest('.row').remove();
});

$(document).on('click', '.add-quantity', function() {
    const productId = $(this).data('product-id');
    const orderId = $('#editOrderId').val();
    const quantityToAdd = $(this).closest('.list-group-item').find('.quantity-change').val();
    
    $.ajax({
        url: '../orders/updateProductInOrder.php',
        type: 'POST',
        data: {
            order_id: orderId,
            product_id: productId,
            quantity: quantityToAdd,
            action: 'add'
        },
        success: function(response) {
            if (response === 'success') {
                // Refresh the products list
                openEditModal(orderId);
            } else {
                alert('Failed to update quantity');
            }
        }
    });
});

$(document).on('click', '.remove-quantity', function() {
    const productId = $(this).data('product-id');
    const orderId = $('#editOrderId').val();
    const quantityToRemove = $(this).closest('.list-group-item').find('.quantity-change').val();
    
    $.ajax({
        url: '../orders/updateProductInOrder.php',
        type: 'POST',
        data: {
            order_id: orderId,
            product_id: productId,
            quantity: quantityToRemove,
            action: 'remove'
        },
        success: function(response) {
            if (response === 'success') {
                // Refresh the products list
                openEditModal(orderId);
            } else {
                alert('Failed to update quantity');
            }
        }
    });
});

$(document).on('click', '.remove-product', function() {
    if (confirm('Are you sure you want to remove this product from the order?')) {
        const productId = $(this).data('product-id');
        const orderId = $('#editOrderId').val();
        
        $.ajax({
            url: '../orders/updateProductInOrder.php',
            type: 'POST',
            data: {
                order_id: orderId,
                product_id: productId,
                quantity: 0,
                action: 'delete'
            },
            success: function(response) {
                if (response === 'success') {
                    // Refresh the products list
                    openEditModal(orderId);
                } else {
                    alert('Failed to remove product');
                }
            }
        });
    }
});

function openDeleteModal(orderId) {
    $('#deleteOrderId').val(orderId);
    $('#deleteOrderModal').modal('show');
}

$('#editOrderForm').on('submit', function(event) {
    event.preventDefault();

    var formData = $(this).serialize();

    $.ajax({
        url: '../orders/editOrder.php',
        method: 'POST',
        data: formData,
        success: function(response) {
            if (response === 'success') {
                $('#editOrderModal').modal('hide');
                fetchOrders(); 
            } 
        },
        error: function() {
            alert('Error updating order. Please try again.');
        }
    });
});

$('#deleteOrderForm').on('submit', function(event) {
    event.preventDefault();

    var formData = $(this).serialize();

    $.ajax({
        url: '../orders/deleteOrder.php',
        method: 'POST',
        data: formData,
        success: function(response) {
            if (response === 'success') {
                $('#deleteOrderModal').modal('hide');
                fetchOrders(); 
            } else {
                alert('Failed to delete order. Please try again.');
            }
        },
        error: function() {
            alert('Error deleting order. Please try again.');
        }
    });
});

$(document).ready(function() {
    fetchOrders();
});

$('#editOrderModal').on('hidden.bs.modal', function () {
    $('#new-products-container').html(`
        <div class="row mb-2">
            <div class="col-md-6">
                <select class="form-select product-select" name="new_products[]">
                    <option value="">Select a product</option>
                </select>
            </div>
            <div class="col-md-4">
                <input type="number" class="form-control quantity-input" name="new_quantities[]" min="1" value="1" placeholder="Quantity">
            </div>
        </div>
    `);
});


