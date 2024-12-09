<?php
require_once '../classes/accountClass.php';

$accountObj = new Account();

$role = isset($_GET['role']) ? $_GET['role'] : '';
$keyword = isset($_GET['search']) ? $_GET['search'] : '';

$users = $accountObj->searchUsers($keyword, $role);
?>

<head>
    <link rel="stylesheet" href="../css/table.css">
</head>

<div class="container">
    <div id="userTable"></div>
</div>

<div class='center'>
    <div class='table'>
        <form autocomplete='off'>
            <input type="search" name="search" id="search" placeholder="Search users...">
        </form>
        <select id="filter" class="form-select w-auto" onchange="filter()">
            <option value="">All Roles</option>
            <option value="student" <?= $role === 'student' ? 'selected' : '' ?>>Student</option>
            <option value="employee" <?= $role === 'employee' ? 'selected' : '' ?>>Employee</option>
            <option value="manager" <?= $role === 'manager' ? 'selected' : '' ?>>Manager</option>
            <option value="guest" <?= $role === 'guest' ? 'selected' : '' ?>>Guest</option>
        </select>
        <div class="mb-3">
            <button type="button" class="btn btn-primary" onclick="openAddManagerModal()">Add Manager</button>
        </div>
        <table class="table table-bordered" id="table"> 
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Last Name</th>
                    <th>Given Name</th>
                    <th>Middle Name</th>
                    <th>Email</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $user): ?>
                        <tr id="user-<?= $user['user_id'] ?>" class="dataRow">
                            <td><?= $user['user_id'] ?></td>
                            <td><?= $user['last_name'] ?></td>
                            <td><?= $user['given_name'] ?></td>
                            <td><?= $user['middle_name'] ?></td>
                            <td><?= $user['email'] ?></td>
                            <td><?= $user['username'] ?></td>
                            <td><?= ($user['role']) ?></td> 
                            <td>
                                <button class="btn btn-warning btn-sm" onclick="openEditModal(<?= $user['user_id'] ?>)">Edit</button>
                                <button class="btn btn-danger btn-sm" onclick="openDeleteModal(<?= $user['user_id'] ?>)">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">No users found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="../js/search.js"></script>
