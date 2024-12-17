<?php
require_once '../tools/functions.php';
require_once '../classes/canteenClass.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $canteenObj = new Canteen();
    $canteen_id = clean_input($_POST['canteen_id']);
    
    if (empty($canteen_id)) {
        echo 'failure';
        exit;
    }

    $result = $canteenObj->deleteCanteen($canteen_id);
    echo $result ? 'success' : 'failure';
}
?>
