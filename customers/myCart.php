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
$total = 0;
$canCheckout = true;
$errorMessages = [];

// Check stock availability for each item
foreach ($cartItems as $item) {
    $stock = $stocksObj->fetchStockByProductId($item['product_id']);
    if (!$stock || $stock['quantity'] < $item['quantity']) {
        $canCheckout = false;
        $errorMessages[] = "Insufficient stock for {$item['name']}";
    }
    $total += $item['total_price'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        if (isset($_POST['checkout']) && $canCheckout) {
            // Start transaction
            $db = $orderObj->db->connect();
            $db->beginTransaction();
            
            try {
                // Place the order
                $orderObj->placeOrder($user_id, $cartItems);
                
                // Update stock quantities
                foreach ($cartItems as $item) {
                    $stocksObj->updateStock($item['product_id'], $item['quantity'], 'Out of Stock');
                }
                
                // Clear the cart after successful order
                $orderObj->clearCart($user_id);
                
                // Commit transaction
                $db->commit();
                
                header('Location: orderStatus.php');
                exit;
            } catch (Exception $e) {
                $db->rollBack();
                throw $e;
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
                            <button type="button" class="btn btn-danger clear-all" onclick="clearCart()">
                                Clear All Items
                            </button>
                        </div>
                        
                        <?php foreach ($cartItems as $item): ?>
                            <div class="cart-item" data-product-id="<?= htmlspecialchars($item['product_id']); ?>">
                                <div class="item-info">
                                    <h4 class="item-name"><?= htmlspecialchars($item['name']); ?></h4>
                                    <div class="item-details">
                                        <p class="price">Price: ₱<?= number_format($item['price'], 2); ?></p>
                                        <p class="quantity">Quantity: <?= htmlspecialchars($item['quantity']); ?></p>
                                        <p class="total">₱<?= number_format($item['total_price'], 2); ?></p>
                                    </div>
                                </div>
                                <div class="item-actions">
                                    <button type="button" 
                                            class="btn btn-danger remove-item" 
                                            onclick="removeFromCart(<?= htmlspecialchars($item['product_id']); ?>)">
                                        Remove
                                    </button>
                                </div>
                            </div>
                            <?php $total += $item['total_price']; ?>
                        <?php endforeach; ?>
                        
                        <div class="cart-total">
                            <p>Total: ₱<?= number_format($total, 2); ?></p>
                        </div>
                        
                        <!-- Checkout button -->
                        <button type="button" class="btn checkout" onclick="openCheckoutModal()">Proceed to Checkout</button>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    function removeFromCart(productId) {
        if (confirm('Are you sure you want to remove this item?')) {
            console.log('Attempting to remove product:', productId);
            
            $.ajax({
                url: '../ajax/removeFromCart.php',
                type: 'POST',
                data: {
                    product_id: productId
                },
                dataType: 'json',
                success: function(response) {
                    console.log('Server response:', response);
                    
                    if (response.success) {
                        // Remove the item from DOM
                        const $cartItem = $(`.cart-item[data-product-id="${productId}"]`);
                        $cartItem.fadeOut(300, function() {
                            $(this).remove();
                            updateCartCount();
                            updateCartTotal();
                            
                            // Check if cart is empty
                            if ($('.cart-item').length === 0) {
                                $('.cart-container').html('<p>You have no items in your cart.</p>');
                                $('.cart-total').hide();
                                $('.checkout').hide();
                            }
                        });
                    } else {
                        console.error('Remove failed:', response.message);
                        alert(response.message || 'Failed to remove item from cart');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', {
                        status: status,
                        error: error,
                        response: xhr.responseText
                    });
                    try {
                        const response = JSON.parse(xhr.responseText);
                        alert(response.message || 'Error occurred while removing item from cart');
                    } catch(e) {
                        alert('Error occurred while removing item from cart');
                    }
                }
            });
        }
    }

    // Add this function to update the cart total
    function updateCartTotal() {
        let total = 0;
        $('.cart-item').each(function() {
            const itemTotal = parseFloat($(this).find('.total').text().replace('₱', '').replace(',', ''));
            total += itemTotal;
        });
        $('.cart-total p').text('Total: ₱' + total.toFixed(2));
        
        // If cart is empty, show empty message
        if ($('.cart-item').length === 0) {
            $('.cart-container').html('<p>You have no items in your cart.</p>');
            $('.cart-total').hide();
            $('.checkout').hide();
        }
    }

    // Add this to update cart count after removal
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

    // Add this after your existing JavaScript functions
    function clearCart() {
        if (confirm('Are you sure you want to remove all items from your cart?')) {
            $.ajax({
                url: '../ajax/clearCart.php',
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Remove all items with animation
                        $('.cart-item').fadeOut(300, function() {
                            $('.cart-container').html('<p>Your cart is empty.</p>');
                            $('.cart-total').hide();
                            $('.checkout').hide();
                            updateCartCount();
                        });
                    } else {
                        alert(response.message || 'Failed to clear cart');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', {
                        status: status,
                        error: error,
                        response: xhr.responseText
                    });
                    alert('Error occurred while clearing cart');
                }
            });
        }
    }

    function openCheckoutModal() {
        // Populate modal with cart items
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
            
            // Add to total
            total += parseFloat(price.replace('₱', '').replace(',', ''));
        });

        $('#modalCartItems').html(modalCartItems);
        $('#modalTotalAmount').text('₱' + total.toFixed(2));

        // Show modal
        var checkoutModal = new bootstrap.Modal(document.getElementById('checkoutModal'));
        checkoutModal.show();
    }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Add this modal HTML before the closing </body> tag -->
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
                    <form method="post" action="">
                        <button type="submit" name="checkout" class="btn btn-primary">Place Order</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
