<?php
session_start();
require_once '../classes/orderClass.php';
require_once '../classes/accountClass.php';

$orderObj = new Order();
$account = new Account();

// Check if the user is logged in
$user_id = $_SESSION['user_id'] ?? null;
$userInfo = $user_id ? $account->UserInfo($user_id) : null;

// Handle cart items based on login status
$cartItems = $user_id ? $orderObj->getCartItems($user_id) : [];
$total = 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['checkout'])) {
        if ($user_id) {
            $orderObj->placeOrder($user_id, $cartItems);
            header('Location: order_status.php');
            exit;
        } else {
            header('Location: login.php');
            exit;
        }
    }

    if (isset($_POST['remove_item'])) {
        if ($user_id) {
            $product_id = $_POST['product_id'];
            $orderObj->removeFromCart($user_id, $product_id);
            header('Location: cart.php');
            exit;
        } else {
            header('Location: login.php');
            exit;
        }
    }

    if (isset($_POST['update_quantity'])) {
        if ($user_id) {
            $product_id = $_POST['product_id'];
            $new_quantity = $_POST['new_quantity'];

            if ($new_quantity > 0) {
                try {
                    $orderObj->updateCartQuantity($user_id, $product_id, $new_quantity);
                } catch (Exception $e) {
                    echo "Error: " . $e->getMessage();
                }
            }
            header('Location: cart.php');
            exit;
        } else {
            header('Location: login.php');
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Your Cart</title>
    <link rel="stylesheet" href="../css/customer-cart.css">
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
                            <h4 class="item-name"><?= htmlspecialchars($item['name']); ?></h4>
                        </div>
                        <div class="description">
                            <div class="item-details">
                                <p class="quantity">Quantity: <?= htmlspecialchars($item['quantity']); ?></p>
                                <p class="total">Total: <?= number_format($item['total_price'], 2); ?></p>
                            </div>
                            <div class="description">
                                <form method="post" action="">
                                    <input type="hidden" name="product_id" value="<?= htmlspecialchars($item['product_id']); ?>">
                                    <button type="submit" name="remove_item" class="btn description">Remove</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php $total += $item['total_price']; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="cart-total">
            <p>Total: <?= number_format($total, 2); ?></p>
        </div>
        <?php if ($user_id): ?>
            <form method="post" action="">
                <button type="submit" name="checkout" class="btn checkout">Proceed to Checkout</button>
            </form>
        <?php else: ?>
            <p>Please <a href="login.php">log in</a> to proceed with checkout.</p>
        <?php endif; ?>
    </div>
</body>
</html>
