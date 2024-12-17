<?php
require_once '../classes/accountClass.php';

$accountObj = new Account();
$role = isset($_GET['role']) ? $_GET['role'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Accounts</title>
    <link rel="stylesheet" href="../assets/datatables/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="../assets/datatables/css/buttons.bootstrap5.min.css">
    <link rel="stylesheet" href="../assets/datatables/css/responsive.bootstrap5.min.css">
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        /* Add this in the head section */
        .alert {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="row mb-4">
            <div class="col-md-6">
                <h2>Manage Accounts</h2>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="text-end mb-3">
                    <button type="button" class="btn btn-primary" onclick="openAddUserModal()">
                        <i class="bi bi-plus-circle"></i> Add User
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="accountsTable" width="100%">
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
                            <!-- Table body will be populated by DataTables -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
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
                                <option value="admin">Admin</option>
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

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editUserForm">
                        <input type="hidden" id="editUserId" name="user_id">
                        <div class="mb-3">
                            <label for="edit_last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="edit_last_name" name="last_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_given_name" class="form-label">Given Name</label>
                            <input type="text" class="form-control" id="edit_given_name" name="given_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_middle_name" class="form-label">Middle Name</label>
                            <input type="text" class="form-control" id="edit_middle_name" name="middle_name">
                        </div>
                        <div class="mb-3">
                            <label for="edit_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="edit_username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_role" class="form-label">Role</label>
                            <select class="form-select" id="edit_role" name="role" required>
                                <option value="admin">Admin</option>
                                <option value="student">Student</option>
                                <option value="employee">Employee</option>
                                <option value="manager">Manager</option>
                                <option value="guest">Guest</option>
                            </select>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete User Modal -->
    <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this user?</p>
                    <input type="hidden" id="deleteUserId">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/jquery/jquery.min.js"></script>
    <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/datatables/js/jquery.dataTables.min.js"></script>
    <script src="../assets/datatables/js/dataTables.bootstrap5.min.js"></script>
    <script src="../assets/datatables/js/dataTables.buttons.min.js"></script>
    <script src="../assets/datatables/js/buttons.bootstrap5.min.js"></script>

    <script>
    $(document).ready(function() {
        // Initialize DataTable
        const table = $('#accountsTable').DataTable({
            "responsive": true,
            "pageLength": 10,
            "order": [[1, "asc"]],
            "ajax": {
                "url": "../users/searchUsers.php",
                "type": "GET",
                "dataSrc": function(json) {
                    return json.map(function(item, index) {
                        return {
                            "DT_RowId": item.user_id,
                            "counter": index + 1,
                            "name": `${item.last_name}, ${item.given_name} ${item.middle_name || ''}`,
                            "username": item.username,
                            "email": item.email,
                            "role": item.role,
                            "created_at": item.created_at,
                            "user_id": item.user_id
                        };
                    });
                }
            },
            "columns": [
                { 
                    "data": "counter",
                    "searchable": false,
                    "orderable": false
                },
                { "data": "name" },
                { "data": "username" },
                { "data": "email" },
                { "data": "role" },
                { 
                    "data": "created_at",
                    "render": function(data) {
                        return data ? new Date(data).toLocaleString() : '';
                    }
                },
                {
                    "data": "user_id",
                    "render": function(data, type, row) {
                        return `
                            <button class="btn btn-warning btn-sm" onclick="openEditModal(${data})">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="openDeleteModal(${data})">
                                <i class="bi bi-trash"></i>
                            </button>
                        `;
                    },
                    "searchable": false,
                    "orderable": false
                }
            ],
            "columnDefs": [
                {
                    "targets": [0, 6],
                    "searchable": false,
                    "orderable": false
                }
            ]
        });
    });

    // Your existing functions (modified to work with DataTables)
    function openAddUserModal() {
        $('#addUserModal').modal('show');
    }

    function openEditModal(userId) {
        fetch(`../users/fetchUserDetails.php?user_id=${userId}`)
            .then(response => response.json())
            .then(user => {
                // Fill the edit modal with user data
                $('#editUserId').val(user.user_id);
                $('#edit_last_name').val(user.last_name);
                $('#edit_given_name').val(user.given_name);
                $('#edit_middle_name').val(user.middle_name);
                $('#edit_email').val(user.email);
                $('#edit_username').val(user.username);
                $('#edit_role').val(user.role);
                $('#editUserModal').modal('show');
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Error loading user details', false);
            });
    }

    function openDeleteModal(userId) {
        $('#deleteUserId').val(userId);
        $('#deleteUserModal').modal('show');
    }

    function showAlert(message, isSuccess) {
        // Remove any existing alerts
        $('.alert').remove();
        
        const alertDiv = $(`
            <div class="alert alert-${isSuccess ? 'success' : 'danger'} alert-dismissible fade show" 
                 role="alert" style="position: fixed; top: 20px; left: 50%; transform: translateX(-50%); z-index: 9999; min-width: 300px; text-align: center;">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `).appendTo('body');
        
        // Auto dismiss after 3 seconds
        setTimeout(() => {
            alertDiv.fadeOut('slow', function() {
                $(this).remove();
            });
        }, 3000);
    }

    // Add form submission handlers
    $('#addNewUserForm').on('submit', function(e) {
        e.preventDefault();
        
        // Basic frontend validation
        const formData = {
            last_name: $('#last_name').val().trim(),
            given_name: $('#given_name').val().trim(),
            middle_name: $('#middle_name').val().trim(),
            email: $('#email').val().trim(),
            username: $('#username').val().trim(),
            password: $('#password').val(),
            role: $('#role').val()
        };

        // Validate required fields
        if (!formData.last_name || !formData.given_name || !formData.email || 
            !formData.username || !formData.password || !formData.role) {
            showAlert('Please fill out all required fields', false);
            return;
        }

        // Email validation
        if (!isValidEmail(formData.email)) {
            showAlert('Please enter a valid email address', false);
            return;
        }

        // Disable the submit button to prevent double submission
        const submitButton = $(this).find('button[type="submit"]');
        submitButton.prop('disabled', true);

        $.ajax({
            url: '../users/addUser.php',
            method: 'POST',
            data: formData,
            dataType: 'json',
            cache: false,
            success: function(response) {
                if (response.success) {
                    $('#addUserModal').modal('hide');
                    $('#addNewUserForm')[0].reset();
                    showAlert(response.message, true);
                    $('#accountsTable').DataTable().ajax.reload(null, false);
                } else {
                    showAlert(response.message || 'Failed to add user', false);
                }
            },
            error: function(xhr) {
                let errorMessage = 'Error adding user';
                try {
                    const response = JSON.parse(xhr.responseText);
                    errorMessage = response.message || errorMessage;
                } catch (e) {
                    console.error('Error parsing error response:', e);
                }
                showAlert(errorMessage, false);
            },
            complete: function() {
                // Re-enable the submit button
                submitButton.prop('disabled', false);
            }
        });

        // Prevent form from submitting normally
        return false;
    });

    // Add this helper function for email validation
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    // Similar modifications for edit and delete handlers
    $('#editUserForm').on('submit', function(e) {
        e.preventDefault();
        const userId = $('#editUserId').val();
        const formData = {
            user_id: userId,
            last_name: $('#edit_last_name').val(),
            given_name: $('#edit_given_name').val(),
            middle_name: $('#edit_middle_name').val(),
            email: $('#edit_email').val(),
            username: $('#edit_username').val(),
            role: $('#edit_role').val()
        };

        $.ajax({
            url: '../users/editUser.php',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#editUserModal').modal('hide');
                    showAlert(response.message, true);
                    // Reload the table after a short delay to ensure the modal is hidden
                    setTimeout(() => {
                        $('#accountsTable').DataTable().ajax.reload(null, false);
                    }, 300);
                } else {
                    showAlert(response.message || 'Failed to update user', false);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                showAlert('Error updating user: ' + error, false);
            }
        });
    });

    // Add this after your edit form handler
    $('#confirmDeleteBtn').on('click', function() {
        const userId = $('#deleteUserId').val();
        
        $.ajax({
            url: '../users/deleteUser.php',
            method: 'POST',
            data: { user_id: userId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#deleteUserModal').modal('hide');
                    showAlert(response.message, true);
                    setTimeout(() => {
                        $('#accountsTable').DataTable().ajax.reload(null, false);
                    }, 300);
                } else {
                    showAlert(response.message || 'Failed to delete user', false);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                showAlert('Error deleting user: ' + error, false);
            }
        });
    });
    </script>
</body>
</html>
