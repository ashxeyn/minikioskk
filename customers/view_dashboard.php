<?php
require_once '../tools/functions.php';
require_once '../classes/canteenClass.php';
require_once '../classes/productClass.php';

$canteenObj = new Canteen();
$productObj = new Product();

$keyword = isset($_GET['search']) ? clean_input($_GET['search']) : '';

$searchResults = $canteenObj->searchCanteensAndProducts($keyword);

$canteens = [];
$menuItems = [];

foreach ($searchResults as $result) {
    if (isset($result['canteen_id'])) {
        $canteens[] = $result;
    }
    if (isset($result['product_id'])) {
        $menuItems[] = $result;
    }
}
?>

<head>
    <link rel="stylesheet" href="../css/customer.css">
</head>

<form autocomplete="off">
    <input type="search" id="search" placeholder="Search..." value="<?= $keyword ?>">
</form>

<h3>Canteens</h3>
<div class="canteen-container">
    <?php if (!empty($canteens)): ?>
        <?php foreach ($canteens as $canteen): ?>
            <div class="canteen" onclick="window.location.href='view_canteen.php?canteen_id=<?= $canteen['canteen_id'] ?>'">
                <img src="https://media.philstar.com/photos/2021/05/13/wmsu-campus_2021-05-13_15-03-21.jpg" alt="Canteen Image">
                <h4><?= clean_input($canteen['canteen_name']); ?></h4>
                <p class="location"><?= clean_input($canteen['campus_location']); ?></p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="no-result">No canteens found.</p>
    <?php endif; ?>
</div>

<h3>Menu Items</h3>
<div class="menu-container">
    <?php if (!empty($menuItems)): ?>
        <?php foreach ($menuItems as $menuItem): ?>
            <div class="menu-item" onclick="window.location.href='view_canteen.php?canteen_id=<?= $menuItem['canteen_id'] ?>'">
                <img src="https://www.eatingwell.com/thmb/YxkWBfh2AvNYrDKoHukRdmRvD5U=/750x0/filters:no_upscale():max_bytes(150000):strip_icc():format(webp)/article_291139_the-top-10-healthiest-foods-for-kids_-02-4b745e57928c4786a61b47d8ba920058.jpg" alt="Menu Image">
                <h4><?= clean_input($menuItem['product_name']); ?></h4>
                <p class="location"><?= clean_input($menuItem['canteen_name']); ?></p>
                <p class="description"><?= clean_input($menuItem['description']); ?></p>
                <p class="price">Price: <?= clean_input($menuItem['price'], 2); ?></p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="no-result">No menu items found.</p>
    <?php endif; ?>
</div>

<script src="../js/search.js"></script>
