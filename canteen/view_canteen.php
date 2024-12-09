<?php
require_once '../classes/canteenClass.php';

$canteenObj = new Canteen();
$keyword = isset($_GET['search']) ? $_GET['search'] : '';  

$canteens = $canteenObj->searchCanteens($keyword); 

?>
<head>
    <link rel="stylesheet" href="../css/table.css">
</head>

<div class='center'>
    <div class='table'>
        <form autocomplete='off'>
            <input type="search" name="" id="search" placeholder="Search canteens...">
        </form>
        <div class="mb-3">
        <button type="button" class="btn btn-primary" onclick="openAddCanteenModal()">Add Canteen</button>
        </div>
        <table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Location</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($canteens as $canteen): ?>
            <tr>
                <td><?= $canteen['canteen_id'] ?></td>
                <td><?= $canteen['name'] ?></td>
                <td><?= $canteen['campus_location'] ?></td>
                <td>
                    <button class="btn btn-warning btn-sm" onclick="openEditModal(<?= $canteen['canteen_id'] ?>)">Edit</button>
                    <button class="btn btn-danger btn-sm" onclick="openDeleteModal(<?= $canteen['canteen_id'] ?>)">Delete</button>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>  
    </div>
</div>

<script src="../js/search.js"></script>
