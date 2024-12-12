<?php
require_once '../tools/functions.php';
require_once '../classes/canteenClass.php';
require_once '../classes/productClass.php';
require_once '../classes/accountClass.php';
require_once '../classes/orderClass.php';

// Start session
session_start();

// Initialize objects
$canteenObj = new Canteen();
$productObj = new Product();
$orderObj = new Order();

// Fetch canteen and product information
$canteen_id = isset($_GET['canteen_id']) ? $_GET['canteen_id'] : null;

if (!$canteen_id) {
    echo '<p class="text-danger">Invalid request.</p>';
    exit;
}

$canteen = $canteenObj->fetchCanteenById($canteen_id);
$products = $productObj->fetchProductsByCanteen($canteen_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Canteen</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
</head>
<body>

<div class="canteen-imgcover">
    <img src="https://media.philstar.com/photos/2021/05/13/wmsu-campus_2021-05-13_15-03-21.jpg" alt="">
</div>
<div class="container mt-4">
    <h2 class="menu-title">Menu for <?= htmlspecialchars($canteen['name']); ?></h2>
    <div class="product-container">
        <?php foreach ($products as $product): ?>
            <div class="menu-item">
                <h4><?= htmlspecialchars($product['name']); ?></h4>
                <p class="description"><?= htmlspecialchars($product['description']); ?></p>
                <p class="price">Price: ₱<?= number_format($product['price'], 2); ?></p>
                <button class="btn btn-primary" 
                        onclick="openOrderModal(<?= $product['product_id'] ?>, '<?= htmlspecialchars(addslashes($product['name'])) ?>', <?= $product['price'] ?>)">
                    Order
                </button>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Order Modal -->
<div class="modal fade" id="orderModal" tabindex="-1" role="dialog" aria-labelledby="orderModalLabel">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderModalLabel">Place Your Order</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <form id="orderForm">
                    <input type="hidden" id="product_id" name="product_id">
                    <div class="order-details">
                        <div class="detail-row">
                            <div class="label-group">
                                <span class="detail-label">Item Name</span>
                                <span id="modal_product_name" class="detail-value"></span>
                            </div>
                        </div>
                        <div class="detail-row">
                            <div class="label-group">
                                <span class="detail-label">Unit Price</span>
                                <span id="modal_product_price" class="detail-value"></span>
                            </div>
                        </div>
                        <div class="detail-row">
                            <div class="label-group">
                                <span class="detail-label">Quantity</span>
                                <div class="quantity-controls">
                                    <button type="button" class="btn btn-quantity" onclick="decrementQuantity()">
                                        <i class="bi bi-dash"></i>
                                    </button>
                                    <input type="number" id="quantity" name="quantity" min="1" value="1">
                                    <button type="button" class="btn btn-quantity" onclick="incrementQuantity()">
                                        <i class="bi bi-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="detail-row total-row">
                            <div class="label-group">
                                <span class="detail-label">Total Amount</span>
                                <span id="modal_total_price" class="detail-value total-price"></span>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="addToCart()">Add to Cart</button>
            </div>
        </div>
    </div>
</div>

<!-- Add this modal HTML after your existing order modal -->
<div class="modal fade" id="responseModal" tabindex="-1" role="dialog" aria-labelledby="responseModalLabel">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="responseModalLabel">Order Status</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="order-details">
                    <div class="detail-row">
                        <div class="label-group">
                            <span id="responseMessage"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<style>
/* Modal Styles */
.modal-content {
    border: none;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    max-width: 400px;
    margin: 0 auto;
}

.modal-header {
    background: #FF7F11;
    color: white;
    padding: 15px 20px;
    border-top-left-radius: 12px;
    border-top-right-radius: 12px;
    border-bottom: none;
}

.modal-title {
    font-size: 1.2rem;
    font-weight: 600;
}

.order-details {
    padding: 15px;
}

.detail-row {
    padding: 10px 0;
    border-bottom: 1px solid #eef0f3;
}

.detail-row:last-child {
    border-bottom: none;
}

.total-row {
    margin-top: 10px;
    padding-top: 15px;
    border-top: 2px solid #eef0f3;
}

.label-group {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 5px;
}

.detail-label {
    color: #6c757d;
    font-size: 0.95rem;
    font-weight: 500;
}

.detail-value {
    color: #212529;
    font-weight: 500;
}

.total-price {
    color: #FF7F11;
    font-size: 1.1rem;
    font-weight: 600;
}

.quantity-controls {
    display: flex;  
    align-items: center;
    gap: 10px;
    background: #f8f9fa;
    border-radius: 8px;
    padding: 5px 10px;
}

.btn-quantity {
    width: 32px;
    height: 32px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    background: white;
    border: 1px solid #dee2e6;
    color: #FF7F11;
}

.btn-quantity:hover {
    background: #FF7F11;
    color: white;
    border-color: #FF7F11;
}

#quantity {
    width: 50px;
    text-align: center;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 4px;
    font-size: 0.95rem;
}

.modal-footer {
    padding: 15px;
    border-top: 1px solid #eef0f3;
    justify-content: space-between;
}

.btn-primary {
    background: #FF7F11;
    border-color: #FF7F11;
    padding: 8px 20px;
    font-weight: 500;
}

.btn-primary:hover {
    background: #e67100;
    border-color: #e67100;
}

.btn-outline-secondary {
    color: #6c757d;
    border-color: #dee2e6;
    padding: 8px 20px;
    font-weight: 500;
}

.btn-outline-secondary:hover {
    background: #f8f9fa;
    color: #6c757d;
    border-color: #dee2e6;
}

@media (max-width: 576px) {
    .modal-dialog {
        margin: 1rem;
    }
    
    .order-details {
        padding: 10px;
    }
    
    .modal-footer {
        padding: 10px;
    }
}

#responseMessage {
    font-size: 1.1rem;
    padding: 20px;
    text-align: center;
    display: block;
}

.text-success {
    color: #28a745;
}

.text-danger {
    color: #dc3545;
}
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

<script>
let currentPrice = 0;

function openOrderModal(productId, productName, price) {
    currentPrice = price;
    $('#product_id').val(productId);
    $('#modal_product_name').text(productName);
    $('#modal_product_price').text('₱' + price.toFixed(2));
    updateTotalPrice();
    
    const orderModal = new bootstrap.Modal(document.getElementById('orderModal'));
    orderModal.show();
}

function updateTotalPrice() {
    const quantity = $('#quantity').val();
    const totalPrice = currentPrice * quantity;
    $('#modal_total_price').text('₱' + totalPrice.toFixed(2));
}

function showResponseModal(message, success = true) {
    const orderModal = bootstrap.Modal.getInstance(document.getElementById('orderModal'));
    if (orderModal) {
        orderModal.hide();
    }
    
    $('#responseMessage').text(message);
    if (success) {
        $('#responseMessage').removeClass('text-danger').addClass('text-success');
    } else {
        $('#responseMessage').removeClass('text-success').addClass('text-danger');
    }
    
    const responseModal = new bootstrap.Modal(document.getElementById('responseModal'));
    responseModal.show();
}

function addToCart() {
    const productId = $('#product_id').val();
    const quantity = $('#quantity').val();

    $.ajax({
        url: '../ajax/addToCart.php',
        type: 'POST',
        dataType: 'json',
        data: {
            product_id: productId,
            quantity: quantity
        },
        success: function(response) {
            if (response.success) {
                showResponseModal('Product added to cart successfully!', true);
                updateCartCount();
            } else {
                showResponseModal(response.message || 'Failed to add product to cart.', false);
            }
        },
        error: function(xhr, status, error) {
            console.error('Cart Error:', error);
            showResponseModal('Error occurred while adding to cart. Please try again.', false);
        }
    });
}

function updateCartCount() {
    $.ajax({
        url: '../ajax/getCartCount.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.count !== undefined) {
                $('#cartCount').text(response.count);
            }
        }
    });
}

function incrementQuantity() {
    const quantityInput = document.getElementById('quantity');
    quantityInput.value = parseInt(quantityInput.value) + 1;
    updateTotalPrice();
}

function decrementQuantity() {
    const quantityInput = document.getElementById('quantity');
    if (parseInt(quantityInput.value) > 1) {
        quantityInput.value = parseInt(quantityInput.value) - 1;
        updateTotalPrice();
    }
}

// Update total price when quantity changes
$('#quantity').on('input', updateTotalPrice);
</script>

</body>
</html>
