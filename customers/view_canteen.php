<?php
session_start();
require_once '../tools/functions.php';
require_once '../classes/canteenClass.php';
require_once '../classes/productClass.php';
require_once '../classes/orderClass.php';

$productObj = new Product();
$orderObj = new Order();
$canteenObj = new Canteen();

$canteen_id = isset($_GET['canteen_id']) ? $_GET['canteen_id'] : null;

if (!$canteen_id) {
    die("Canteen not found.");
}

$canteen = $canteenObj->fetchCanteenById($canteen_id);

$products = $productObj->fetchProductsByCanteen($canteen_id);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $orderObj->addToCart($_SESSION['user_id'], $product_id, $quantity);
    header('Location: cart.php');
    exit;
}

require_once '../includes/_head.php';
require_once '../includes/_footer.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Menu for <?= clean_input($canteen['name']) ?></title>
    <link rel="stylesheet" href="../css/customer-order.css">
</head>
<body>

<?php require_once '../includes/_topnav.php' ?>
<div class="container-fluid">
    <div class="row">
    <?php require_once '../includes/_sidebar3.php' ?>
        <div class="col py-3">
            <div class="canteen-imgcover">
            <img src="https://media.philstar.com/photos/2021/05/13/wmsu-campus_2021-05-13_15-03-21.jpg" alt="">
            </div>
            <div id="contentArea" class="container mt-4">
                <h2 class="menu-title">Menu for <?= clean_input($canteen['name']) ?></h2>
                    <div class="product-container">
                        <?php foreach ($products as $product): ?>
                            <div class="menu-item">
                                <img src="https://images.immediate.co.uk/production/volatile/sites/30/2013/05/spaghetti-carbonara-382837d.jpg?quality=90&webp=true&resize=300,272" alt="Product Image">
                                <h4><?= clean_input($product['name']); ?></h4>
                                <p class="description"><?= clean_input($product['description']); ?></p>
                                <p class="price">Price: <?= clean_input($product['price'], 2); ?></p>
                                    <div class="form-container">
                                        <form method="post" action="" class="row-form">
                                            <input type="hidden" name="product_id" value="<?= $product['product_id']; ?>">
                                            <label for="quantity" class="quantity-label">Quantity:</label>
                                            <input id="quantity" type="number" name="quantity" value="0" min="1" required>
                                    </div>
                                            <button type="submit" name="add_to_cart" class="btn add-to-cart">Add to Cart</button>
                                        </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
