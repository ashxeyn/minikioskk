<?php
require_once '../tools/functions.php';
require_once '../classes/canteenClass.php';
require_once '../classes/productClass.php';

$canteenObj = new Canteen();
$canteens = $canteenObj->fetchCanteens(); // Fetch all canteens (update method name if needed)

if (!empty($canteens)): ?>
    <?php foreach ($canteens as $canteen): ?>
        <div class="canteen" onclick="loadCanteenDetails('viewCanteen.php?canteen_id=<?= htmlspecialchars($canteen['canteen_id']); ?>')">
            <img src="https://media.philstar.com/photos/2021/05/13/wmsu-campus_2021-05-13_15-03-21.jpg" alt="Canteen Image">
            <h4><?= htmlspecialchars($canteen['name']); ?></h4>
            <p class="location"><?= htmlspecialchars($canteen['campus_location']); ?></p>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p class="no-result">No canteens found.</p>
<?php endif; ?>