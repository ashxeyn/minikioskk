<?php
require_once '../classes/accountClass.php';

$accountObj = new Account();
$role = isset($_GET['role']) ? $_GET['role'] : '';
?>

<head>
    <link rel="stylesheet" href="../css/table.css">
    <style>
        /* Modal styling */
        .modal-backdrop {
            opacity: 0.5;
        }

        .modal {
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: #fff;
            position: relative;
            z-index: 1050;
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .modal-header {
            border-bottom: 1px solid #dee2e6;
            background-color: #fff;
            border-top-left-radius: 0.5rem;
            border-top-right-radius: 0.5rem;
        }

        .modal-body {
            background-color: #fff;
        }

        .modal-footer {
            border-top: 1px solid #dee2e6;
            background-color: #fff;
            border-bottom-left-radius: 0.5rem;
            border-bottom-right-radius: 0.5rem;
        }
    </style>
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
            <button type="button" class="btn btn-primary" onclick="openAddUserModal()">Add User</button>
        </div>
        
        <!-- Add User Modal -->
        <div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addUserForm">
                            <!-- Basic Fields -->
                            <div class="mb-3">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="given_name" class="form-label">Given Name</label>
                                <input type="text" class="form-control" id="given_name" name="given_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="middle_name" class="form-label">Middle Name</label>
                                <input type="text" class="form-control" id="middle_name" name="middle_name">
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="mb-3">
                                <label for="role" class="form-label">Role</label>
                                <select class="form-select" id="role" name="role" required onchange="toggleAdditionalFields()">
                                    <option value="">Select Role</option>
                                    <option value="student">Student</option>
                                    <option value="employee">Employee</option>
                                    <option value="manager">Manager</option>
                                    <option value="guest">Guest</option>
                                </select>
                            </div>
                            
                            <!-- Role-specific fields -->
                            <div id="programField" style="display: none;">
                                <label for="program_id" class="form-label">Program</label>
                                <select class="form-select" id="program_id" name="program_id">
                                    <option value="">Select Program</option>
                                </select>
                            </div>
                            
                            <div id="departmentField" style="display: none;">
                                <label for="department_id" class="form-label">Department</label>
                                <select class="form-select" id="department_id" name="department_id">
                                    <option value="">Select Department</option>
                                </select>
                            </div>
                            
                            <div id="canteenField" style="display: none;">
                                <label for="canteen_id" class="form-label">Canteen</label>
                                <select class="form-select" id="canteen_id" name="canteen_id">
                                    <option value="">Select Canteen</option>
                                </select>
                            </div>
                            
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Save User</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- User Table -->
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
