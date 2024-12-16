<?php
session_start();
require_once '../classes/cartClass.php';
require_once '../classes/accountClass.php';
require_once '../classes/stocksClass.php';
require_once '../classes/orderClass.php';
require_once '../classes/databaseClass.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$cartObj = new Cart();
$account = new Account();
$stocksObj = new Stocks();
$orderObj = new Order();

$user_id = $_SESSION['user_id'] ?? null;
$userInfo = $account->UserInfo($user_id);

// Debug log
error_log("User ID: " . print_r($user_id, true));

$cartItems = $cartObj->getCartItems($user_id);
// Debug log cart items
error_log("Cart Items: " . print_r($cartItems, true));

$canCheckout = true;
$errorMessages = [];
$total = $cartObj->getCartTotal($user_id);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        if (isset($_POST['checkout']) && $canCheckout) {
            error_log("Checkout process started");
            
            // Debug log
            error_log("Total amount: " . $total);
            error_log("Cart items before checkout: " . print_r($cartItems, true));
            
            $db = new Database();
            $conn = $db->connect();
            $conn->beginTransaction();
            
            try {
                // Verify stock availability before placing order
                foreach ($cartItems as $item) {
                    $stock = $stocksObj->fetchStockByProductId($item['product_id']);
                    error_log("Checking stock for product {$item['product_id']}: " . print_r($stock, true));
                    
                    if (!$stock || $stock['quantity'] < $item['quantity']) {
                        throw new Exception("Insufficient stock for {$item['name']} (Available: {$stock['quantity']}, Requested: {$item['quantity']})");
                    }
                }
                
                // Place the order
                $orderData = [
                    'user_id' => $user_id,
                    'total_amount' => $total,
                    'status' => 'pending',
                    'payment_status' => 'unpaid',
                    'canteen_id' => $cartItems[0]['canteen_id'] ?? null
                ];
                
                error_log("Order data before creation: " . print_r($orderData, true));
                
                $order_id = $orderObj->createOrder($orderData);
                error_log("Created order ID: " . $order_id);
                
                if (!$order_id) {
                    throw new Exception("Failed to create order");
                }
                
                // Add order items and update stock
                foreach ($cartItems as $item) {
                    $orderItemData = [
                        'order_id' => $order_id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price']
                    ];
                    error_log("Adding order item: " . print_r($orderItemData, true));
                    
                    // Add order item
                    $orderObj->addOrderItem($orderItemData);
                    
                    // Update stock
                    error_log("Updating stock for product {$item['product_id']}, reducing by {$item['quantity']}");
                    if (!$stocksObj->updateStock($item['product_id'], -$item['quantity'])) {
                        throw new Exception("Failed to update stock for {$item['name']}");
                    }
                }
                
                // Clear the cart
                error_log("Clearing cart for user: " . $user_id);
                $cartObj->clearCart($user_id);
                
                // Set session data for order status
                $_SESSION['last_order'] = [
                    'order_id' => $order_id,
                    'total_amount' => $total,
                    'status' => 'pending',
                    'payment_status' => 'unpaid'
                ];
                error_log("Set session last_order: " . print_r($_SESSION['last_order'], true));
                
                $conn->commit();
                error_log("Transaction committed successfully");
                header('Location: orderStatus.php');
                exit;
                
            } catch (Exception $e) {
                $conn->rollBack();
                error_log("Error during checkout process: " . $e->getMessage());
                error_log("Stack trace: " . $e->getTraceAsString());
                $errorMessages[] = $e->getMessage();
            }
        }

        if (isset($_POST['remove_item'])) {
            $product_id = $_POST['product_id'];
            $orderObj->removeFromCart($user_id, $product_id);
            header('Location: myCart.php');
            exit;
        }

        if (isset($_POST['update_quantity'])) {
            $product_id = $_POST['product_id'];
            $new_quantity = $_POST['new_quantity'];
            

            $stock = $stocksObj->fetchStockByProductId($product_id);
            if ($stock && $stock['quantity'] >= $new_quantity) {
                $orderObj->updateCartQuantity($user_id, $product_id, $new_quantity);
            } else {
                throw new Exception("Insufficient stock available");
            }
            header('Location: myCart.php');
            exit;
        }
    } catch (Exception $e) {
        $errorMessages[] = $e->getMessage();
    }
}

    
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Cart</title>
  
    

    <style>
        .modal {
            display: none;
        }
        
        .modal.show {
            display: block;
        }
        
        .modal-dialog {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) !important;
            margin: 0;
            width: 90%;
            max-width: 500px;
        }
        
        .modal-content {
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        
        .modal.fade .modal-dialog {
            transform: translate(-50%, -70%) !important;
            transition: transform 0.3s ease-out;
        }
        
        .modal.show .modal-dialog {
            transform: translate(-50%, -50%) !important;
        }
        
  
        .modal-backdrop.show {
            opacity: 0.7;
        }
        

        @media (max-width: 576px) {
            .modal-dialog {
                width: 95%;
                margin: 0;
            }
        }
   
        #responseModal .modal-content {
            background-color: white;
            color: #333;
        }
        
        #responseModal.success .modal-content {
            border-left: 5px solid #28a745;
        }
        
        #responseModal.error .modal-content {
            border-left: 5px solid #dc3545;
        }
        
        #responseModal .modal-header {
            border-bottom: 1px solid #dee2e6;
            background-color: white;
        }
        
        #responseModal .modal-footer {
            border-top: 1px solid #dee2e6;
            background-color: white;
        }
        
        #responseMessage {
            padding: 10px;
            font-size: 16px;
            font-weight: 500;
        }
        
        #responseModal.success #responseMessage {
            color: #198754;
            font-weight: bold;
        }
        
        #responseModal.error #responseMessage {
            color: #dc3545;
            font-weight: bold;
        }
        
        #responseModal .modal-title {
            color: #333;
            font-weight: bold;
        }
        
        #responseModal .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        
        #responseModal .btn-primary:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
        }
    </style>
</head>
<body>
    
    <div class="container mt-4">
        <h2 class="mb-4">My Cart</h2>
        
        <?php if (!empty($cartItems)): ?>
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-end mb-3">
                        <button type="button" class="btn btn-danger" onclick="clearCart()">
                            <i class="bi bi-trash"></i> Clear All Items
                        </button>
                    </div>

                    <?php foreach ($cartItems as $item): ?>
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <h5 class="item-name"><?= htmlspecialchars($item['name']); ?></h5>
                                        <p class="text-muted mb-0">Unit Price: ₱<?= number_format($item['unit_price'], 2); ?></p>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="quantity d-flex align-items-center">
                                            <span class="me-2">Qty:</span>
                                            <div class="input-group input-group-sm" style="width: 120px;">
                                                <button type="button" class="btn btn-outline-secondary" onclick="updateQuantity(<?= $item['product_id']; ?>, 'decrease')">-</button>
                                                <input type="number" class="form-control text-center" value="<?= htmlspecialchars($item['quantity']); ?>" 
                                                       id="qty_<?= $item['product_id']; ?>" min="1" readonly>
                                                <button type="button" class="btn btn-outline-secondary" onclick="updateQuantity(<?= $item['product_id']; ?>, 'increase')">+</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <p class="mb-0 text-end">₱<?= number_format($item['subtotal'], 2); ?></p>
                                    </div>
                                    <div class="col-md-2 text-end">
                                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeFromCart(<?= $item['product_id']; ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-info">Your cart is empty</div>
        <?php endif; ?>

        <!-- Total and Checkout Section -->
        <?php if (!empty($cartItems)): ?>
            <div class="card mt-3">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <?php if (!empty($errorMessages)): ?>
                                <div class="alert alert-warning">
                                    <?php foreach ($errorMessages as $error): ?>
                                        <p class="mb-0"><?= htmlspecialchars($error); ?></p>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <div class="text-end">
                                <h4 class="mb-3" id="cartTotal">Total: ₱<?= number_format($total, 2); ?></h4>
                                <button type="button" class="btn btn-primary btn-lg" onclick="openCheckoutModal()">
                                    <i class="bi bi-cart-check"></i> Proceed to Checkout
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>


    <!-- Clear Cart Modal -->
    <div class="modal fade" id="clearCartModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Clear Cart</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to clear your cart?</p>
                    <div id="modalClearCartItems"></div>
                    <hr>
                    <p class="text-end fw-bold">Total: <span id="modalClearCartAmount"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="confirmClearCart()">Clear Cart</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Checkout Modal -->
    <div class="modal fade" id="checkoutModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="modalCartItems"></div>
                    <hr>
                    <p class="text-end fw-bold">Total Amount: <span id="modalTotalAmount"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="placeOrder()">Place Order</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Response Modal -->
    <div class="modal fade" id="responseModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="responseMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Remove Item Modal -->
    <div class="modal fade" id="removeItemModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Remove Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to remove this item from your cart?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmRemove">Remove</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function clearCart() {
            let modalCartItems = '';
            let total = 0;

            // Correctly select cart items from the card elements
            $('.card-body .card').each(function() {
                const name = $(this).find('.item-name').text();
                const quantity = $(this).find('.quantity span:last').text();
                const price = $(this).find('.mb-0.text-end').text().replace('₱', '').replace(',', '');
                
                modalCartItems += `<div class="d-flex justify-content-between mb-2">
                    <span>${name} x ${quantity}</span>
                    <span>₱${price}</span>
                </div>`;
                
                total += parseFloat(price);
            });

            $('#modalClearCartItems').html(modalCartItems);
            $('#modalClearCartAmount').text('₱' + total.toFixed(2));

            const clearCartModal = new bootstrap.Modal(document.getElementById('clearCartModal'));
            clearCartModal.show();
        }

        function confirmClearCart() {
            $.ajax({
                url: '../ajax/clearCart.php',
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    const clearCartModal = bootstrap.Modal.getInstance(document.getElementById('clearCartModal'));
                    clearCartModal.hide();
                    
                    if (response.success) {
                        showResponseModal('Cart cleared successfully!', true);
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        showResponseModal(response.message || 'Failed to clear cart', false);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Clear cart error:', error);
                    showResponseModal('Error occurred while clearing cart. Please try again.', false);
                }
            });
        }

        function openCheckoutModal() {
            let modalCartItems = '';
            let total = 0;

            // Correctly select cart items from the card elements
            $('.card-body .card').each(function() {
                const name = $(this).find('.item-name').text();
                const quantity = $(this).find('.quantity span:last').text();
                const price = $(this).find('.mb-0.text-end').text().replace('₱', '').replace(',', '');
                
                modalCartItems += `<div class="d-flex justify-content-between mb-2">
                    <span>${name} x ${quantity}</span>
                    <span>₱${price}</span>
                </div>`;
                
                total += parseFloat(price);
            });

            $('#modalCartItems').html(modalCartItems);
            $('#modalTotalAmount').text('₱' + total.toFixed(2));

            const checkoutModal = new bootstrap.Modal(document.getElementById('checkoutModal'));
            checkoutModal.show();
        }

        function placeOrder() {
            $.ajax({
                url: '../ajax/placeOrder.php',
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Show success message
                        showResponseModal('Order placed successfully! Order ID: ' + response.order_id, true);
                        
                        // Clear the cart display
                        $('.cart-items').empty();
                        $('.cart-total').text('₱0.00');
                        
                        // Update cart count
                        updateCartCount();
                        loadCartSection();
                        // Redirect to order status page after a short delay
                        setTimeout(function() {
                          
                        }, 2000);
                    } else {
                        showResponseModal(response.message || 'Failed to place order', false);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Order Error:', error);
                    console.error('Response:', xhr.responseText);
                    showResponseModal('Error occurred while placing order. Please try again.', false);
                }
            });
        }

        function showResponseModal(message, success) {
            $('#responseMessage').text(message);
            if (success) {
                $('#responseMessage').removeClass('text-danger').addClass('text-success');
            } else {
                $('#responseMessage').removeClass('text-success').addClass('text-danger');
            }
            
            const responseModal = new bootstrap.Modal(document.getElementById('responseModal'));
            responseModal.show();
        }

        function updateQuantity(productId, action) {
            const qtyInput = document.getElementById(`qty_${productId}`);
            let currentQty = parseInt(qtyInput.value);
            let newQty = action === 'increase' ? currentQty + 1 : currentQty - 1;
            
            if (newQty < 1) return; // Prevent negative quantities
            
            $.ajax({
                url: '../ajax/updateCartQuantity.php',
                type: 'POST',
                data: {
                    product_id: productId,
                    new_quantity: newQty
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Update quantity input
                        qtyInput.value = newQty;
                        
                        // Update subtotal
                        const unitPrice = parseFloat($(qtyInput)
                            .closest('.card-body')
                            .find('.text-muted.mb-0')
                            .text()
                            .replace('Unit Price: ₱', '')
                            .replace(',', ''));
                        
                        const newSubtotal = (unitPrice * newQty).toFixed(2);
                        $(qtyInput)
                            .closest('.row')
                            .find('.mb-0.text-end')
                            .text('₱' + numberWithCommas(newSubtotal));
                        
                        // Update cart total
                        updateCartTotal();
                    } else {
                        showResponseModal(response.message || 'Failed to update quantity', false);
                    }
                },
                error: function() {
                    showResponseModal('Error occurred while updating quantity', false);
                }
            });
        }

        // Helper function to format numbers with commas
        function numberWithCommas(x) {
            return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }

        // Function to update cart total
        function updateCartTotal() {
            let total = 0;
            $('.card-body .card').each(function() {
                const subtotal = parseFloat($(this)
                    .find('.mb-0.text-end')
                    .text()
                    .replace('₱', '')
                    .replace(',', ''));
                total += subtotal;
            });
            
            // Update the total display
            $('#cartTotal').text('₱' + numberWithCommas(total.toFixed(2)));
        }

        function removeFromCart(productId) {
            const removeItemModal = new bootstrap.Modal(document.getElementById('removeItemModal'));
            
            document.getElementById('confirmRemove').onclick = function() {
                $.ajax({
                    url: '../ajax/removeFromCart.php',
                    type: 'POST',
                    data: {
                        product_id: productId
                    },
                    dataType: 'json',
                    success: function(response) {
                        removeItemModal.hide();
                        if (response.success) {
                            const itemCard = $(`button[onclick="removeFromCart(${productId})"]`).closest('.card');
                            itemCard.fadeOut(300, function() {
                                $(this).remove();
                                updateCartTotal();
                                updateCartCount();
                                
                                if ($('.card-body .card').length === 0) {
                                    $('.card-body').html('<div class="alert alert-info">Your cart is empty</div>');
                                    $('.checkout-section').hide();
                                }
                            });
                            showResponseModal('Item removed successfully!', true);
                        } else {
                            showResponseModal(response.message || 'Failed to remove item from cart', false);
                        }
                    },
                    error: function(xhr, status, error) {
                        removeItemModal.hide();
                        console.error('Remove from cart error:', error);
                        console.error('Response:', xhr.responseText);
                        showResponseModal('Error occurred while removing item from cart. Please try again.', false);
                    }
                });
            };

            removeItemModal.show();
        }

        // Make sure this function exists to update the cart count in the header
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
    </script>
</body>
</html>
