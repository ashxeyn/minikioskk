<?php
session_start();
require_once '../classes/orderClass.php';
$orderObj = new Order();

$cartItems = $orderObj->getCartItems($_SESSION['user_id']);
$total = 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['checkout'])) {
        $orderObj->placeOrder($_SESSION['user_id'], $cartItems);
        header('Location: order_status.php');
        exit;
    }

    if (isset($_POST['remove_item'])) {
        $product_id = $_POST['product_id'];
        $orderObj->removeFromCart($_SESSION['user_id'], $product_id);
        header('Location: cart.php');
        exit;
    }

    if (isset($_POST['update_quantity'])) {
        $product_id = $_POST['product_id'];
        $new_quantity = $_POST['new_quantity'];

        if ($new_quantity > 0) {
            try {
                $orderObj->updateCartQuantity($_SESSION['user_id'], $product_id, $new_quantity);
            } catch (Exception $e) {
                echo "Error: " . $e->getMessage();
            }
        }
        header('Location: cart.php');
        exit;
    }
}

require_once 'confirmDeleteModal.html';
require_once 'editQuantityModal.html';
require_once 'viewOrderItems.html';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Your Cart</title>
    <link rel="stylesheet" href="../css/customer-cart.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../js/customer.js"></script>
</head>
<body>


            <div id="contentArea" class="container mt-4">
                <h2>Your Cart</h2>
                <div class="cart-container">
                    <?php if (empty($cartItems)): ?>
                        <p>You have no items in your cart.</p>
                    <?php else: ?>
                    <?php foreach ($cartItems as $item): ?>
                        <div class="cart-item">
                            <div class="item-info">
                                <h4 class="item-name"><?= $item['name']; ?></h4>
                            </div>
                            <div class="description">
                                <div class="item-details">
                                    <p class="quantity">Quantity: <?= $item['quantity']; ?></p>
                                    <p class="total">Total: <?= number_format($item['total_price'], 2); ?></p>
                                </div>
                                <div class="description">
                                    <form method="post" action="">
                                    <button type="button" class="btn description" data-bs-toggle="modal" data-bs-target="#editQuantityModal" onclick="editProduct(<?= $item['product_id']; ?>, <?= $item['quantity']; ?>)">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    </form>
                                    <form method="post" action="" class="delete-button">
                                        <input type="hidden" name="product_id" value="<?= $item['product_id']; ?>">
                                        <button type="button" class="btn description" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal" onclick="deleteProduct(<?= $item['product_id']; ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php $total += $item['total_price']; ?>
                    <?php endforeach; ?>  
                    <?php endif ?>
                </div>
                <div class="cart-total">
                    <p>Total: <?= number_format($total, 2); ?></p>
                </div>
                <form method="post" action="">
                    <button type="submit" name="checkout" class="btn checkout">Proceed to Checkout</button>
                </form>
            </div>
        </div>
    </div>
</div>
</body>

</html>