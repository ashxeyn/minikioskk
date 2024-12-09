<?php
require_once '../classes/programClass.php';

$programObj = new Program();
$keyword = isset($_GET['search']) ? $_GET['search'] : '';  

$programs = $programObj->searchPrograms($keyword); 
?>

<head>
    <link rel="stylesheet" href="../css/table.css">
</head>

<div class='center'>
    <div class='table'>
        <form autocomplete='off'>
            <input type="search" name="search" id="search" placeholder="Search...">
        </form>
        <div class="mb-3">
            <button type="button" class="btn btn-primary" onclick="openAddProgramModal()">Add Program</button>
        </div>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Program ID</th>
                    <th>Program Name</th>
                    <th>Department</th>
                    <th>College</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($programs)): ?>
                    <?php foreach ($programs as $program): ?>
                        <tr id="program-<?= $program['program_id'] ?>">
                            <td><?= ($program['program_id']) ?></td>
                            <td><?= ($program['program_name']) ?></td>
                            <td><?= ($program['department']) ?></td>
                            <td><?= ($program['college']) ?></td>
                            <td>
                                <button class="btn btn-warning btn-sm" onclick="openEditModal(<?= $program['program_id'] ?>)">Edit</button>
                                <button class="btn btn-danger btn-sm" onclick="openDeleteModal(<?= $program['program_id'] ?>)">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">No programs found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="../js/search.js"></script>

