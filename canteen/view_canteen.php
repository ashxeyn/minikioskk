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
            <input type="search" name="search" id="searchCanteen" placeholder="Search canteens..." 
                   onkeyup="searchCanteens(this.value)">
        </form>
        <!-- <div class="mb-3">
            <button type="button" class="btn btn-primary" onclick="openAddCanteenModal()">Add Canteen</button>
        </div> -->
        <table id="canteenTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Location</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="canteenTableBody">
                <?php 
                $counter = 1;
                foreach ($canteens as $canteen): 
                ?>
                    <tr>
                        <td><?= $counter++ ?></td>
                        <td><?= htmlspecialchars($canteen['name']) ?></td>
                        <td><?= htmlspecialchars($canteen['campus_location']) ?></td>
                        <td>
                            <button class="btn btn-warning btn-sm" onclick="openEditModal(<?= $canteen['canteen_id'] ?>)" title="Edit">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="openDeleteModal(<?= $canteen['canteen_id'] ?>)" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>  
    </div>
</div>

<script>
function searchCanteens(keyword) {
    fetch(`../ajax/search_canteens.php?keyword=${encodeURIComponent(keyword)}`)
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('canteenTableBody');
            tbody.innerHTML = '';
            
            data.forEach((canteen, index) => {
                tbody.innerHTML += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${escapeHtml(canteen.name)}</td>
                        <td>${escapeHtml(canteen.campus_location)}</td>
                        <td>
                            <button class="btn btn-warning btn-sm" onclick="openEditModal(${canteen.canteen_id})" title="Edit">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="openDeleteModal(${canteen.canteen_id})" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
        })
        .catch(error => console.error('Error:', error));
}

function escapeHtml(unsafe) {
    return unsafe
        .toString()
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}
</script>

