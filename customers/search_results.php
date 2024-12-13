<?php
session_start();
require_once '../tools/functions.php';
require_once '../classes/canteenClass.php';
require_once '../classes/productClass.php';

$canteenObj = new Canteen();
$keyword = isset($_GET['search']) ? clean_input($_GET['search']) : '';
$search_type = isset($_GET['search_type']) ? clean_input($_GET['search_type']) : 'all';
$searchResults = $canteenObj->searchCanteensAndProducts($keyword, $search_type);
$canteens = ($search_type === 'menu') ? [] : $searchResults['canteens'];
$menuItems = ($search_type === 'canteens') ? [] : $searchResults['products'];
?>

<!-- Only return the results section -->
<div id="searchResultsContent">
    <!-- Canteens Section -->
    <h3>Canteens</h3>
    <div class="canteen-container">
        <?php if (!empty($canteens)): ?>
            <?php foreach ($canteens as $canteen): ?>
                <div class="canteen" onclick="loadCanteenDetails(<?= $canteen['canteen_id'] ?>)">
                    <img src="https://media.philstar.com/photos/2021/05/13/wmsu-campus_2021-05-13_15-03-21.jpg" alt="Canteen Image">
                    <h4><?= htmlspecialchars($canteen['name']); ?></h4>
                    <p class="location"><?= htmlspecialchars($canteen['campus_location']); ?></p>
                    <p class="description"><?= htmlspecialchars($canteen['description'] ?? ''); ?></p>
                    
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-result">No canteens found.</p>
        <?php endif; ?>
    </div>

    <!-- Menu Items Section -->
    <h3>Menu Items</h3>
    <div class="menu-container">
        <?php if (!empty($menuItems)): ?>
            <?php foreach ($menuItems as $menuItem): ?>
                <div class="menu-item" onclick="loadCanteenDetails(<?= $menuItem['canteen_id'] ?>)">
                    <img src="https://www.eatingwell.com/thmb/YxkWBfh2AvNYrDKoHukRdmRvD5U=/750x0/filters:no_upscale():max_bytes(150000):strip_icc():format(webp)/article_291139_the-top-10-healthiest-foods-for-kids_-02-4b745e57928c4786a61b47d8ba920058.jpg" alt="Menu Image">
                    <h4><?= htmlspecialchars($menuItem['name'] ?? ''); ?></h4>
                    <p class="location"><?= htmlspecialchars($menuItem['canteen_name'] ?? ''); ?></p>
                    <p class="description"><?= htmlspecialchars($menuItem['description'] ?? ''); ?></p>
                    <p class="price">Price: ₱<?= number_format(floatval($menuItem['price'] ?? 0), 2); ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-result">No menu items found.</p>
        <?php endif; ?>
    </div>
</div> 