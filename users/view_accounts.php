<?php
require_once '../classes/accountClass.php';

$accountObj = new Account();
$role = isset($_GET['role']) ? $_GET['role'] : '';
?>

<head>
    <link rel="stylesheet" href="../css/table.css">
</head>

<div class='center'>
    <div class='table'>
        <form autocomplete='off'>
            <input type="search" name="search" id="searchUser" placeholder="Search users..." 
                   onkeyup="searchUsers(this.value, document.getElementById('filter').value)">
        </form>
        <select id="filter" class="form-select w-auto" onchange="searchUsers(document.getElementById('searchUser').value, this.value)">
            <option value="">All Roles</option>
            <option value="student">Student</option>
            <option value="employee">Employee</option>
            <option value="manager">Manager</option>
            <option value="guest">Guest</option>
        </select>
        <div class="mb-3">
            <button type="button" class="btn btn-primary" onclick="openAddManagerModal()">Add Manager</button>
        </div>
        <table id="userTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="userTableBody">
            </tbody>
        </table>
    </div>
</div>

<script>
function searchUsers(keyword, role) {
    fetch(`../ajax/search_users.php?keyword=${encodeURIComponent(keyword)}&role=${encodeURIComponent(role)}`)
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('userTableBody');
            tbody.innerHTML = '';
            
            data.forEach(user => {
                const fullName = `${escapeHtml(user.last_name)}, ${escapeHtml(user.given_name)} ${escapeHtml(user.middle_name)}`;
                tbody.innerHTML += `
                    <tr>
                        <td>${escapeHtml(user.user_id)}</td>
                        <td>${fullName}</td>
                        <td>${escapeHtml(user.username)}</td>
                        <td>${escapeHtml(user.email)}</td>
                        <td>${escapeHtml(user.role)}</td>
                        <td>${escapeHtml(user.status)}</td>
                        <td>
                            <button class="btn btn-warning btn-sm" onclick="openEditModal(${user.user_id})">Edit</button>
                            <button class="btn btn-danger btn-sm" onclick="openDeleteModal(${user.user_id})">Delete</button>
                        </td>
                    </tr>
                `;
            });
        })
        .catch(error => console.error('Error:', error));
}

function escapeHtml(unsafe) {
    return unsafe
        ? unsafe.toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;")
        : '';
}

// Initial load
searchUsers('', '');
</script>
