<?php
session_start();
require_once '../classes/employeeClass.php';
require_once '../tools/functions.php';

// Check if user is logged in as manager
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'manager') {
    header('Location: ../login.php');
    exit;
}

try {
    $employee = new Employee();
    
    // Check if canteen_id is not set in session
    if (!isset($_SESSION['canteen_id'])) {
        // Try to fetch canteen_id from managers table
        $db = new Database();
        $conn = $db->connect();
        $sql = "SELECT canteen_id FROM managers WHERE user_id = :user_id AND status = 'accepted'";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['user_id' => $_SESSION['user_id']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result && isset($result['canteen_id'])) {
            $_SESSION['canteen_id'] = $result['canteen_id'];
        } else {
            throw new Exception("No active canteen assignment found for this manager");
        }
    }

    $employees = $employee->fetchCanteenEmployees($_SESSION['canteen_id']);
} catch (Exception $e) {
    error_log("Error in view_employees: " . $e->getMessage());
    echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Co-Employees</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
    .badge {
        padding: 0.5em 0.8em;
    }
    .btn-group {
        gap: 0.25rem;
    }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="row mb-4">
            <div class="col-md-6">
                <h2>Manage Co-Employees</h2>
            </div>
            <div class="col-md-6 text-end">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
                    <i class="bi bi-plus-circle"></i> Add Co-Employee
                </button>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="employeesTable" width="100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Include Modals -->
    <?php include 'addEmployeeModal.html'; ?>
    <?php include 'editEmployeeModal.html'; ?>

    <!-- Response Modal -->
    <div class="modal fade" id="responseModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Action Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="responseMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Load scripts in correct order -->
    <!-- jQuery first -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <!-- Custom JS -->
    <script src="../js/manager.js"></script>

    <script>
        // Initialize modals
        let responseModal;
        
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize the response modal
            responseModal = new bootstrap.Modal(document.getElementById('responseModal'));
        });

        function showConfirmation(message, callback) {
            const responseMessage = document.getElementById('responseMessage');
            responseMessage.textContent = message;
            responseMessage.className = 'text-primary';
            
            const modalElement = document.getElementById('responseModal');
            const modalFooter = modalElement.querySelector('.modal-footer');
            modalFooter.innerHTML = `
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmAction">Confirm</button>
            `;
            
            document.getElementById('confirmAction').onclick = callback;
            
            if (responseModal) {
                responseModal.show();
            } else {
                responseModal = new bootstrap.Modal(modalElement);
                responseModal.show();
            }
        }

        function showResponse(message, success = true) {
            const responseMessage = document.getElementById('responseMessage');
            responseMessage.textContent = message;
            responseMessage.className = success ? 'text-success' : 'text-danger';
            
            const modalElement = document.getElementById('responseModal');
            const modalFooter = modalElement.querySelector('.modal-footer');
            modalFooter.innerHTML = `
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            `;
            
            if (responseModal) {
                responseModal.show();
            } else {
                responseModal = new bootstrap.Modal(modalElement);
                responseModal.show();
            }
            
            if (success) {
                modalElement.addEventListener('hidden.bs.modal', function() {
                    $('#employeesTable').DataTable().ajax.reload(null, false);
                }, { once: true });
            }
        }

        function approveEmployee(userId) {
            showConfirmation('Are you sure you want to approve this employee?', function() {
                const responseModal = bootstrap.Modal.getInstance(document.getElementById('responseModal'));
                responseModal.hide();
                
                const formData = new FormData();
                formData.append('user_id', userId);
                
                fetch('../ajax/approveEmployee.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    showResponse(
                        data.success ? 'Employee approved successfully' : (data.message || 'Error approving employee'),
                        data.success
                    );
                    
                    if (data.success) {
                        $('#employeesTable').DataTable().ajax.reload(null, false);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showResponse('Error approving employee', false);
                });
            });
        }

        document.getElementById('addEmployeeForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = 'Adding...';
            submitBtn.disabled = true;
            
            fetch('../ajax/addEmployee.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Reset button state
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                
                // Hide the add modal
                const addModal = bootstrap.Modal.getInstance(document.getElementById('addEmployeeModal'));
                addModal.hide();
                
                showResponse(
                    data.success ? 'Employee added successfully' : (data.message || 'Error adding employee'),
                    data.success
                );
                
                if (data.success) {
                    this.reset();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Reset button state
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                showResponse('Error adding employee', false);
            });
        });

        $(document).ready(function() {
            // Check session before initializing DataTable
            $.ajax({
                url: '../ajax/checkSession.php',
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        initializeDataTable();
                    } else {
                        console.error('Session error:', response.error);
                        showResponse('Error: ' + response.error, false);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Ajax error:', error);
                    showResponse('Error checking session', false);
                }
            });
        });

        function initializeDataTable() {
            try {
                $('#employeesTable').DataTable({
                    "processing": true,
                    "serverSide": true,
                    "ajax": {
                        "url": "../ajax/getEmployees.php",
                        "type": "POST",
                        "error": function(xhr, error, thrown) {
                            console.error('DataTables error:', error, thrown);
                            if (error === "Canteen ID not found in session") {
                                location.reload(); // Reload to re-check session
                            }
                        }
                    },
                    "columns": [
                        { 
                            "data": null,
                            "render": function (data, type, row, meta) {
                                return meta.row + meta.settings._iDisplayStart + 1;
                            }
                        },
                        { "data": "name" },
                        { "data": "username" },
                        { "data": "email" },
                        { "data": "manager_status" },
                        { "data": "actions" }
                    ],
                    "columnDefs": [
                        { "orderable": false, "targets": [5] }, // Actions column not sortable
                        { "orderable": false, "searchable": false, "targets": [0] } // Counter column not sortable or searchable
                    ],
                    "pageLength": 10,
                    "responsive": true,
                    "order": [[1, "asc"]], // Order by name column
                    "language": {
                        "processing": "Loading...",
                        "search": "Search:",
                        "lengthMenu": "Show _MENU_ entries",
                        "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                        "infoEmpty": "Showing 0 to 0 of 0 entries",
                        "infoFiltered": "(filtered from _MAX_ total entries)",
                        "emptyTable": "No employees found",
                        "zeroRecords": "No matching records found"
                    }
                });
            } catch (error) {
                console.error('DataTables initialization error:', error);
                showResponse('Error initializing table', false);
            }
        }
    </script>
</body>
</html>