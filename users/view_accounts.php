<?php
require_once '../classes/accountClass.php';

$accountObj = new Account();
$role = isset($_GET['role']) ? $_GET['role'] : '';
?>

<head>
    <link rel="stylesheet" href="../css/table.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
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
        
        <div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addNewUserForm">
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
                                <select class="form-select" id="role" name="role" required>
                                    <option value="">Select Role</option>
                                    <option value="student">Student</option>
                                    <option value="employee">Employee</option>
                                    <option value="manager">Manager</option>
                                    <option value="guest">Guest</option>
                                </select>
                            </div>
                            <div id="studentFields" style="display: none;">
                                <div class="mb-3">
                                    <label for="program_id" class="form-label">Program</label>
                                    <select class="form-select" id="program_id" name="program_id">
                                    </select>
                                </div>
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
        
        <table id="userTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="userTableBody">
            </tbody>
        </table>
    </div>
</div>

<script>
function showSuccessModal(message) {
    document.getElementById('successMessage').textContent = message;
    const successModal = new bootstrap.Modal(document.getElementById('successModal'));
    successModal.show();
}

function showErrorModal(message) {
    document.getElementById('errorMessage').textContent = message;
    const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
    errorModal.show();
}

function searchUsers(keyword, role) {
    fetch(`../ajax/search_users.php?keyword=${encodeURIComponent(keyword)}&role=${encodeURIComponent(role)}`)
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('userTableBody');
            tbody.innerHTML = '';
            let counter = 1;
            data.forEach(user => {
                const fullName = `${escapeHtml(user.last_name)}, ${escapeHtml(user.given_name)} ${escapeHtml(user.middle_name)}`;
                tbody.innerHTML += `
                    <tr>
                        <td>${counter}</td>
                        <td>${fullName}</td>
                        <td>${escapeHtml(user.username)}</td>
                        <td>${escapeHtml(user.email)}</td>
                        <td>${escapeHtml(user.role)}</td>
                        <td>${escapeHtml(user.created_at)}</td>
                        <td>
                            <button class="btn btn-warning btn-sm" onclick="openEditModal(${user.user_id})">Edit</button>
                            <button class="btn btn-danger btn-sm" onclick="openDeleteModal(${user.user_id})">Delete</button>
                        </td>
                    </tr>
                `;
                counter++; 
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

searchUsers('', '');

function openAddUserModal() {
    $('#addUserModal').modal('show');
}

document.getElementById('addNewUserForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('../users/addUser.php', {
        method: 'POST',
        body: new URLSearchParams(formData)
    })
    .then(response => response.text())
    .then(result => {
        if (result === 'success') {
            const modal = bootstrap.Modal.getInstance(document.getElementById('addUserModal'));
            modal.hide();
            this.reset();
            searchUsers('', '');
            showSuccessModal('User added successfully!');
        } else {
            showErrorModal('Failed to add user: ' + result);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showErrorModal('Failed to add user');
    });
});

function openEditModal(userId) {
    fetch(`../users/fetchUserDetails.php?user_id=${userId}`)
        .then(response => response.json())
        .then(user => {
            document.getElementById('editUserId').value = user.user_id;
            document.getElementById('edit_last_name').value = user.last_name;
            document.getElementById('edit_given_name').value = user.given_name;
            document.getElementById('edit_middle_name').value = user.middle_name;
            document.getElementById('edit_email').value = user.email;
            document.getElementById('edit_username').value = user.username;
            document.getElementById('edit_role').value = user.role;
            const editModal = new bootstrap.Modal(document.getElementById('editUserModal'));
            editModal.show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to fetch user details');
        });
}

function openDeleteModal(userId) {
    document.getElementById('deleteUserId').value = userId;
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteUserModal'));
    deleteModal.show();
}

document.getElementById('editUserForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('../users/editUser.php', {
        method: 'POST',
        body: new URLSearchParams(formData)
    })
    .then(response => response.text())
    .then(result => {
        if (result === 'success') {
            const modal = bootstrap.Modal.getInstance(document.getElementById('editUserModal'));
            modal.hide();
            searchUsers('', '');
            showSuccessModal('User updated successfully!');
        } else {
            showErrorModal('Failed to update user: ' + result);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showErrorModal('Failed to update user');
    });
});

document.getElementById('deleteUserForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const userId = document.getElementById('deleteUserId').value;
    
    fetch('../users/deleteUser.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `user_id=${userId}`
    })
    .then(response => response.text())
    .then(result => {
        if (result === 'success') {
            const modal = bootstrap.Modal.getInstance(document.getElementById('deleteUserModal'));
            modal.hide();
            searchUsers('', '');
            showSuccessModal('User deleted successfully!');
        } else {
            showErrorModal('Failed to delete user: ' + result);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showErrorModal('Failed to delete user');
    });
});
</script>
