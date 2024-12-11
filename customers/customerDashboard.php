<?php
session_start();
require_once '../classes/accountClass.php';
require_once '../classes/orderClass.php';
require_once '../classes/productClass.php';
require_once '../classes/canteenClass.php';
require_once '../tools/functions.php';

// Initialize objects
$account = new Account();
$orderObj = new Order();
$productObj = new Product();
$canteenObj = new Canteen();

// Get user info
$account->user_id = $_SESSION['user_id'] ?? null;
$userInfo = $account->UserInfo();

// Get user's orders
$userOrders = [];
if ($account->user_id) {
    $userOrders = $orderObj->getUserOrders($account->user_id);
}

// Get available canteens
$canteens = $canteenObj->getAllCanteens();

// Get featured products
$featuredProducts = $productObj->getFeaturedProducts();

$topNavFile = '../includes/_topnav2.php';
require_once '../includes/_head.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Amaranth">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/customer-cart.css">
    <link rel="stylesheet" href="../css/customer-order.css">
    <link rel="stylesheet" href="../css/customer.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../customers/dashboard_content.js"></script>
</head>
<body>

<?php require_once $topNavFile; ?>

<div class="container-fluid">
    <div class="row">
        <?php require_once '../includes/_sidebar3.php'; ?>
        <div class="col py-3">
            <div id="contentArea" class="container mt-4">
                <!-- Welcome Section -->
                <div class="welcome-section mb-4">
                    <h3>
                        <?php 
                        if ($userInfo) {
                            echo "Welcome back, " . clean_input($userInfo['name']) . "!";
                        } else {
                            echo "Welcome to our dashboard!";
                        }
                        ?>
                    </h3>
                </div>

                <!-- Quick Stats -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Your Orders</h5>
                                <p class="card-text"><?= count($userOrders) ?> orders</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Available Canteens</h5>
                                <p class="card-text"><?= count($canteens) ?> canteens</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Featured Items</h5>
                                <p class="card-text"><?= count($featuredProducts) ?> items</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="recent-orders mb-4">
                    <h4>Recent Orders</h4>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Canteen</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($userOrders, 0, 5) as $order): ?>
                                <tr>
                                    <td>#<?= clean_input($order['order_id']) ?></td>
                                    <td><?= clean_input($order['canteen_name']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= getStatusColor($order['status']) ?>">
                                            <?= clean_input($order['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" 
                                                onclick="viewOrderDetails(<?= $order['order_id'] ?>)">
                                            View
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Featured Products -->
                <div class="featured-products">
                    <h4>Featured Products</h4>
                    <div class="row">
                        <?php foreach (array_slice($featuredProducts, 0, 4) as $product): ?>
                        <div class="col-md-3 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title"><?= clean_input($product['name']) ?></h5>
                                    <p class="card-text">₱<?= number_format($product['price'], 2) ?></p>
                                    <button class="btn btn-primary btn-sm" 
                                            onclick="addToCart(<?= $product['product_id'] ?>)">
                                        Add to Cart
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function viewOrderDetails(orderId) {
    window.location.href = `orderDetails.php?id=${orderId}`;
}

function addToCart(productId) {
    $.post('../ajax/addToCart.php', {
        product_id: productId
    }, function(response) {
        if (response.success) {
            alert('Product added to cart!');
            updateCartCount();
        } else {
            alert('Failed to add product to cart.');
        }
    }, 'json');
}

function updateCartCount() {
    $.get('../ajax/getCartCount.php', function(response) {
        $('#cartCount').text(response.count);
    }, 'json');
}

function getStatusColor(status) {
    switch(status) {
        case 'pending': return 'warning';
        case 'accepted': return 'info';
        case 'completed': return 'success';
        case 'cancelled': return 'danger';
        default: return 'secondary';
    }
}
</script>

</body>
</html>
