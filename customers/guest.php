<?php
session_start();
require_once '../classes/productClass.php';
require_once '../classes/stocksClass.php';
require_once '../tools/functions.php';

// Initialize guest cart if not exists
if (!isset($_SESSION['guest_cart'])) {
    $_SESSION['guest_cart'] = [];
}

$productObj = new Product();
$stocksObj = new Stocks();
$errorMessages = [];
$total = 0;

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['remove_item'])) {
            $product_id = $_POST['product_id'];
            // Remove item from guest cart
            foreach ($_SESSION['guest_cart'] as $key => $item) {
                if ($item['product_id'] == $product_id) {
                    unset($_SESSION['guest_cart'][$key]);
                    break;
                }
            }
            $_SESSION['guest_cart'] = array_values($_SESSION['guest_cart']); // Reindex array
            header('Location: guest.php');
            exit;
        }

        if (isset($_POST['update_quantity'])) {
            $product_id = $_POST['product_id'];
            $new_quantity = $_POST['new_quantity'];
            
            // Validate stock before updating
            $stock = $stocksObj->fetchStockByProductId($product_id);
            if ($stock && $stock['quantity'] >= $new_quantity) {
                foreach ($_SESSION['guest_cart'] as &$item) {
                    if ($item['product_id'] == $product_id) {
                        $item['quantity'] = $new_quantity;
                        $item['subtotal'] = $item['unit_price'] * $new_quantity;
                        break;
                    }
                }
            } else {
                $errorMessages[] = "Insufficient stock available";
            }
            header('Location: guest.php');
            exit;
        }
    }

    // Calculate total and get product details
    $cartItems = [];
    foreach ($_SESSION['guest_cart'] as $item) {
        $product = $productObj->getProduct($item['product_id']);
        if ($product) {
            $stock = $stocksObj->fetchStockByProductId($item['product_id']);
            $cartItem = [
                'product_id' => $item['product_id'],
                'name' => $product['name'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'subtotal' => $item['subtotal'],
                'stock_quantity' => $stock ? $stock['quantity'] : 0
            ];
            $cartItems[] = $cartItem;
            $total += $item['subtotal'];
        }
    }

} catch (Exception $e) {
    $errorMessages[] = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/customer-cart.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3>Guest Cart</h3>
                        <a href="login.php" class="btn btn-primary">Login to Checkout</a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($errorMessages)): ?>
                            <div class="alert alert-danger">
                                <?php foreach($errorMessages as $error): ?>
                                    <p><?= $error ?></p>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (empty($cartItems)): ?>
                            <div class="text-center">
                                <p>Your cart is empty</p>
                                <a href="viewCanteen.php" class="btn btn-primary">Continue Shopping</a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <th>Subtotal</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($cartItems as $item): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($item['name']) ?></td>
                                                <td>₱<?= number_format($item['unit_price'], 2) ?></td>
                                                <td>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                                        <input type="number" 
                                                               name="new_quantity" 
                                                               value="<?= $item['quantity'] ?>" 
                                                               min="1" 
                                                               max="<?= $item['stock_quantity'] ?>" 
                                                               class="form-control quantity-input"
                                                               style="width: 80px;">
                                                        <button type="submit" 
                                                                name="update_quantity" 
                                                                class="btn btn-sm btn-outline-primary">
                                                            Update
                                                        </button>
                                                    </form>
                                                </td>
                                                <td>₱<?= number_format($item['subtotal'], 2) ?></td>
                                                <td>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                                        <button type="submit" 
                                                                name="remove_item" 
                                                                class="btn btn-sm btn-danger">
                                                            Remove
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                            <td><strong>₱<?= number_format($total, 2) ?></strong></td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div class="text-center mt-3">
                                <a href="viewCanteen.php" class="btn btn-secondary">Continue Shopping</a>
                                <a href="login.php" class="btn btn-primary">Login to Checkout</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 