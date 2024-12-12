<?php
session_start();
require_once '../classes/orderClass.php';
require_once '../classes/accountClass.php';
require_once '../classes/stocksClass.php';

$orderObj = new Order();
$account = new Account();
$stocksObj = new Stocks();

// Fetch user ID and user-related data
$user_id = $_SESSION['user_id'] ?? null;
$userInfo = $account->UserInfo($user_id);

// Fetch cart items
$cartItems = $orderObj->getCartItems($user_id);
$canCheckout = true;
$errorMessages = [];
$total = 0;

// Check stock availability for each item and calculate total
foreach ($cartItems as &$item) {
    $stock = $stocksObj->fetchStockByProductId($item['product_id']);
    if (!$stock || $stock['quantity'] < $item['quantity']) {
        $canCheckout = false;
        $errorMessages[] = "Insufficient stock for {$item['name']}";
    }
    // Calculate subtotal for each item
    $item['subtotal'] = $item['unit_price'] * $item['quantity'];
    $total += $item['subtotal'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        if (isset($_POST['checkout']) && $canCheckout) {
            // Start transaction
            $db = $orderObj->db->connect();
            $db->beginTransaction();
            
            try {
                // Place the order with total amount
                $orderResult = $orderObj->placeOrder($user_id, $cartItems, $total);
                
                if (!$orderResult['success']) {
                    throw new Exception("Failed to place order");
                }
                
                // Update stock quantities
                foreach ($cartItems as $item) {
                    if (!$stocksObj->updateStock($item['product_id'], -$item['quantity'])) {
                        throw new Exception("Failed to update stock for {$item['name']}");
                    }
                }
                
                // Store only the necessary order info in session
                $_SESSION['last_order'] = [
                    'order_id' => $orderResult['order_id'],
                    'total_amount' => $total,
                    'status' => 'pending',
                    'payment_status' => 'unpaid'
                ];
                
                $db->commit();
                header('Location: orderStatus.php');
                exit;
                
            } catch (Exception $e) {
                $db->rollBack();
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
            
            // Validate stock before updating
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/customer-cart.css">
    
    <!-- Add these modal styles -->
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
        
        /* Optional: Add some animation */
        .modal.fade .modal-dialog {
            transform: translate(-50%, -70%) !important;
            transition: transform 0.3s ease-out;
        }
        
        .modal.show .modal-dialog {
            transform: translate(-50%, -50%) !important;
        }
        
        /* Make modal backdrop darker */
        .modal-backdrop.show {
            opacity: 0.7;
        }
        
        /* Responsive adjustments */
        @media (max-width: 576px) {
            .modal-dialog {
                width: 95%;
                margin: 0;
            }
        }
        
        /* Response Modal Styles */
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
       
        
        <?php if (!empty($errorMessages)): ?>
            <div class="alert alert-danger">
                <?php foreach($errorMessages as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (empty($cartItems)): ?>
            <div class="alert alert-info">Your cart is empty.</div>
        <?php else: ?>
            <div id="contentArea" class="container mt-4">
                <h2>Your Cart</h2>
                <?php if (!empty($cartItems)): ?>
                    <div class="cart-container">
                        <div class="cart-actions">
                            <button type="button" class="btn btn-danger clear-all">
                                Clear All Items
                            </button>
                        </div>
                        
                        <?php foreach ($cartItems as $item): ?>
                            <div class="cart-item" data-product-id="<?= htmlspecialchars($item['product_id']); ?>">
                                <div class="item-info">
                                    <h4 class="item-name"><?= htmlspecialchars($item['name']); ?></h4>
                                    <div class="item-details">
                                        <p class="price">Price: ₱<?= number_format($item['unit_price'], 2); ?></p>
                                        <div class="quantity">
                                            <p>Quantity: <?= htmlspecialchars($item['quantity']); ?></p>
                                        </div>
                                        <p class="total">₱<?= number_format($item['subtotal'], 2); ?></p>
                                    </div>
                                </div>
                                <div class="item-actions">
                                    <button type="button" class="btn description" onclick="editQuantity(<?= $item['product_id'] ?>)">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn description" onclick="removeFromCart(<?= $item['product_id'] ?>)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <div class="cart-total">
                            <p>Total: ₱<?= number_format($total, 2); ?></p>
                        </div>
                        
                        <!-- Checkout button -->
                        <button type="button" class="btn checkout">Proceed to Checkout</button>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Load jQuery and Bootstrap JS first -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Clear Cart Modal -->
    <div class="modal fade" id="clearCartModal" tabindex="-1" aria-labelledby="clearCartModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="clearCartModalLabel">Clear Cart Confirmation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to remove all items from your cart?</p>
                    <div class="order-summary">
                        <h6>Items to be removed:</h6>
                        <div id="modalClearCartItems"></div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <strong>Total Amount to Clear:</strong>
                            <span id="modalClearCartAmount"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="confirmClearCart()">Clear Cart</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Checkout Modal -->
    <div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="checkoutModalLabel">Order Confirmation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="order-summary">
                        <h6>Order Summary:</h6>
                        <div id="modalCartItems"></div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <strong>Total Amount:</strong>
                            <span id="modalTotalAmount"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="post" action="../ajax/placeOrder.php">
                        <button type="submit" class="btn btn-primary">Place Order</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Response Modal -->
    <div class="modal fade" id="responseModal" tabindex="-1" role="dialog" aria-labelledby="responseModalLabel">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="responseModalLabel">Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="responseMessage"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Initialize Bootstrap modals
        document.addEventListener('DOMContentLoaded', function() {
            // Make sure clearCart and openCheckoutModal are available
            if (typeof window.clearCart === 'undefined') {
                window.clearCart = function() {
                    let modalCartItems = '';
                    let total = 0;

                    $('.cart-item').each(function() {
                        const name = $(this).find('.item-name').text();
                        const quantity = $(this).find('.quantity').text().replace('Quantity: ', '');
                        const price = $(this).find('.total').text();
                        
                        modalCartItems += `<div class="d-flex justify-content-between mb-2">
                            <span>${name} x ${quantity}</span>
                            <span>${price}</span>
                        </div>`;
                        
                        total += parseFloat(price.replace('₱', '').replace(',', ''));
                    });

                    $('#modalClearCartItems').html(modalCartItems);
                    $('#modalClearCartAmount').text('₱' + total.toFixed(2));

                    const clearCartModal = new bootstrap.Modal(document.getElementById('clearCartModal'));
                    clearCartModal.show();
                };
            }

            if (typeof window.openCheckoutModal === 'undefined') {
                window.openCheckoutModal = function() {
                    let modalCartItems = '';
                    let total = 0;

                    $('.cart-item').each(function() {
                        const name = $(this).find('.item-name').text();
                        const quantity = $(this).find('.quantity').text().replace('Quantity: ', '');
                        const price = $(this).find('.total').text();
                        
                        modalCartItems += `<div class="d-flex justify-content-between mb-2">
                            <span>${name} x ${quantity}</span>
                            <span>${price}</span>
                        </div>`;
                        
                        total += parseFloat(price.replace('₱', '').replace(',', ''));
                    });

                    $('#modalCartItems').html(modalCartItems);
                    $('#modalTotalAmount').text('₱' + total.toFixed(2));

                    const checkoutModal = new bootstrap.Modal(document.getElementById('checkoutModal'));
                    checkoutModal.show();
                };
            }

            // Add click handlers
            $('.clear-all').on('click', function() {
                clearCart();
            });

            $('.checkout').on('click', function() {
                openCheckoutModal();
            });
        });

        function confirmClearCart() {
            $.ajax({
                url: '../ajax/clearCart.php',
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Close the clear cart modal
                        const clearCartModal = bootstrap.Modal.getInstance(document.getElementById('clearCartModal'));
                        clearCartModal.hide();
                        
                        // Update the cart display to show empty cart
                        $('.cart-container').html('<div class="alert alert-info">Your cart is empty.</div>');
                        
                        // Show success message
                        showResponseModal('Cart cleared successfully!', true);
                        
                        // Update cart count to 0
                        if (typeof updateCartCount === 'function') {
                            updateCartCount(0);
                        }
                        
                        // Refresh the page after a short delay
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        showResponseModal(response.message || 'Failed to clear cart', false);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    showResponseModal('Error occurred while clearing cart', false);
                }
            });
        }

        // Update the checkout form submission
        $('#checkoutModal form').on('submit', function(e) {
            e.preventDefault();
            
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: {
                    checkout: true
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Close checkout modal
                        const checkoutModal = bootstrap.Modal.getInstance(document.getElementById('checkoutModal'));
                        checkoutModal.hide();
                        
                        // Show success message before redirecting
                        showResponseModal('Order placed successfully!', true);
                        
                        // Refresh the current page after a short delay
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        showResponseModal(response.message || 'Failed to place order', false);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    let errorMessage = 'Error occurred while placing order';
                    
                    // Try to parse response text
                    try {
                        const response = JSON.parse(xhr.responseText);
                        errorMessage = response.message || errorMessage;
                    } catch(e) {
                        // If response isn't JSON, check if it's a PHP error
                        if (xhr.responseText.includes('</b>')) {
                            // Strip HTML tags from PHP error
                            errorMessage = xhr.responseText.replace(/<[^>]*>/g, '');
                        }
                    }
                    
                    showResponseModal(errorMessage, false);
                }
            });
        });

        function showResponseModal(message, success) {
            // Update modal content
            $('#responseMessage').text(message);
            
            // Update modal class based on success/failure
            const responseModal = $('#responseModal');
            responseModal.removeClass('success error')
                        .addClass(success ? 'success' : 'error');
            
            // Show the modal
            const modal = new bootstrap.Modal(document.getElementById('responseModal'));
            modal.show();
            
            // If this is a success message for clearing cart, reload the page after modal is closed
            if (success && message === 'Cart cleared successfully!') {
                $('#responseModal').on('hidden.bs.modal', function () {
                    location.reload();
                });
            }
        }
    </script>
</body>
</html>
