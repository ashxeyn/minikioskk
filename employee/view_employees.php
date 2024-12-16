<?php
require_once '../includes/session.php';
require_once '../classes/employeeClass.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'manager') {
    header('Location: ../login.php');
    exit;
}

$employee = new Employee();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Employees</title>
    <!-- Include your CSS and JS files -->
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/datatables.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col">
                <h2>Manage Employees</h2>
            </div>
            <div class="col text-end">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
                    Add New Employee
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table id="employeesTable" class="table table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <!-- Add Employee Modal -->
    <div class="modal fade" id="addEmployeeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Employee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addEmployeeForm">
                        <div class="mb-3">
                            <label for="firstName" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="firstName" name="given_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="lastName" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="lastName" name="last_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="department" class="form-label">Department</label>
                            <select class="form-control" id="department" name="department_id" required>
                                <!-- Will be populated via AJAX -->
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveEmployee">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Employee Modal -->
    <div class="modal fade" id="editEmployeeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Employee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editEmployeeForm">
                        <input type="hidden" id="editUserId" name="user_id">
                        <div class="mb-3">
                            <label for="editFirstName" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="editFirstName" name="given_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="editLastName" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="editLastName" name="last_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="editUsername" class="form-label">Username</label>
                            <input type="text" class="form-control" id="editUsername" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="editEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="editEmail" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="editDepartment" class="form-label">Department</label>
                            <select class="form-control" id="editDepartment" name="department_id" required>
                                <!-- Will be populated via AJAX -->
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="updateEmployee">Update</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Include your JS files -->
    <script src="../assets/js/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/datatables.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            const table = $('#employeesTable').DataTable({
                ajax: {
                    url: '../ajax/getEmployees.php',
                    dataSrc: ''
                },
                columns: [
                    { data: 'name' },
                    { data: 'username' },
                    { data: 'email' },
                    {
                        data: null,
                        render: function(data, type, row) {
                            return `
                                <button class="btn btn-sm btn-primary edit-btn" data-id="${row.user_id}">Edit</button>
                                <button class="btn btn-sm btn-danger delete-btn" data-id="${row.user_id}">Delete</button>
                            `;
                        }
                    }
                ]
            });

            // Load departments
            function loadDepartments() {
                $.get('../ajax/getDepartments.php', function(departments) {
                    const options = departments.map(dept => 
                        `<option value="${dept.department_id}">${dept.name}</option>`
                    ).join('');
                    $('#department, #editDepartment').html(options);
                });
            }

            loadDepartments();

            // Add Employee
            $('#saveEmployee').click(function() {
                const formData = new FormData($('#addEmployeeForm')[0]);
                $.ajax({
                    url: '../ajax/addEmployee.php',
                    method: 'POST',
                    data: Object.fromEntries(formData),
                    success: function(response) {
                        $('#addEmployeeModal').modal('hide');
                        table.ajax.reload();
                        alert('Employee added successfully');
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON?.error || 'Error adding employee');
                    }
                });
            });

            // Edit Employee
            $('#employeesTable').on('click', '.edit-btn', function() {
                const userId = $(this).data('id');
                $.get('../ajax/getEmployee.php', { user_id: userId }, function(employee) {
                    $('#editUserId').val(employee.user_id);
                    $('#editFirstName').val(employee.given_name);
                    $('#editLastName').val(employee.last_name);
                    $('#editUsername').val(employee.username);
                    $('#editEmail').val(employee.email);
                    $('#editDepartment').val(employee.department_id);
                    $('#editEmployeeModal').modal('show');
                });
            });

            // Update Employee
            $('#updateEmployee').click(function() {
                const formData = new FormData($('#editEmployeeForm')[0]);
                $.ajax({
                    url: '../ajax/updateEmployee.php',
                    method: 'POST',
                    data: Object.fromEntries(formData),
                    success: function(response) {
                        $('#editEmployeeModal').modal('hide');
                        table.ajax.reload();
                        alert('Employee updated successfully');
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON?.error || 'Error updating employee');
                    }
                });
            });

            // Delete Employee
            $('#employeesTable').on('click', '.delete-btn', function() {
                if (confirm('Are you sure you want to delete this employee?')) {
                    const userId = $(this).data('id');
                    $.ajax({
                        url: '../ajax/deleteEmployee.php',
                        method: 'POST',
                        data: { user_id: userId },
                        success: function(response) {
                            table.ajax.reload();
                            alert('Employee deleted successfully');
                        },
                        error: function(xhr) {
                            alert(xhr.responseJSON?.error || 'Error deleting employee');
                        }
                    });
                }
            });
        });
    </script>
</body>
</html> 