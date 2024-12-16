<?php
session_start();
require_once '../classes/employeeClass.php';
require_once '../tools/functions.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'manager') {
    header('Location: ../login.php');
    exit;
}

try {
    $employee = new Employee();

    if (!isset($_SESSION['canteen_id'])) {
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
    
    <!-- DataTables CSS -->
    <link href="../assets/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="../assets/datatables/css/responsive.dataTables.min.css" rel="stylesheet">
    <link href="../assets/datatables/css/buttons.dataTables.min.css" rel="stylesheet">
    
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
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php include 'addEmployeeModal.html'; ?>

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
    <script src="../assets/jquery/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="../assets/datatables/js/jquery.dataTables.min.js"></script>
    <script src="../assets/datatables/js/dataTables.responsive.min.js"></script>
    <script src="../assets/datatables/js/dataTables.buttons.min.js"></script>

    <script>
        $(document).ready(function() {
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
                                location.reload();
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
                        { "data": "manager_status" }
                    ],
                    "columnDefs": [
                        { "orderable": false, "searchable": false, "targets": [0] }
                    ],
                    "pageLength": 10,
                    "responsive": true,
                    "order": [[1, "asc"]],
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

        function showResponse(message, success = true) {
            const responseMessage = document.getElementById('responseMessage');
            responseMessage.textContent = message;
            responseMessage.className = success ? 'text-success' : 'text-danger';
            modalFooter.innerHTML = `

            const modalElement = document.getElementById('responseModal');
            const modalFooter = modalElement.querySelector('.modal-footer');
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            `;

            const responseModal = bootstrap.Modal.getOrCreateInstance(modalElement);
            responseModal.show();
        }
    </script>
</body>
</html>
