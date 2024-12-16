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
    
    <!-- jQuery -->
    <script src="../assets/jquery/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap -->
    <link href="../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="../assets/datatables/js/jquery.dataTables.min.js"></script>
    <script src="../assets/datatables/js/dataTables.responsive.min.js"></script>
    <script src="../assets/datatables/js/dataTables.buttons.min.js"></script>

    <!-- DataTables CSS -->
    <link href="../assets/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="../assets/datatables/css/responsive.dataTables.min.css" rel="stylesheet">
    <link href="../assets/datatables/css/buttons.dataTables.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="../assets/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
    .btn-group {
        gap: 0.25rem;
    }
    .table {
        background-color: transparent !important;
    }
    .table thead th {
        background-color: transparent !important;
    }
    .table-striped tbody tr:nth-of-type(odd) {
        background-color: transparent !important;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.02) !important;
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
                <button type="button" class="btn btn-primary" onclick="openAddEmployeeModal()">
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
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($employees as $emp): ?>
                            <tr>
                                <td></td>
                                <td><?= htmlspecialchars($emp['name']) ?></td>
                                <td><?= htmlspecialchars($emp['username']) ?></td>
                                <td><?= htmlspecialchars($emp['email']) ?></td>
                                <td>
                                    <div class="btn-group">
                                      
                                        <button class="btn btn-sm btn-danger" onclick="deleteEmployee(<?= $emp['user_id'] ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php include 'addEmployeeModal.html'; ?>

    <!-- Response Modal -->
    <div class="modal fade" id="responseModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Message</h5>
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

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this employee? This action cannot be undone.</p>
                    <p class="text-danger"><strong>Warning:</strong> This will permanently remove the employee's access and all associated data.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        // Initialize DataTable
        const table = $('#employeesTable').DataTable({
            "processing": true,
            "serverSide": false, // Using local data
            "ajax": {
                "url": "../ajax/getEmployees.php",
                "type": "POST",
                "data": function(d) {
                    d.canteen_id = <?php echo $_SESSION['canteen_id']; ?>;
                }
            },
            "pageLength": 10,
            "responsive": true,
            "order": [[1, "asc"]], // Order by name column
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
                { 
                    "data": null,
                    "render": function(data, type, row) {
                        return `
                            <div class="btn-group">
                                <button class="btn btn-sm btn-danger" onclick="deleteEmployee(${row.user_id})">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        `;
                    }
                }
            ],
            "columnDefs": [
                { "orderable": false, "targets": [0, 4] },
                { "searchable": false, "targets": [0, 4] }
            ]
        });

        // Add Employee Form Submission
        $('#addEmployeeForm').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('canteen_id', '<?php echo $_SESSION['canteen_id']; ?>');
            
            $.ajax({
                url: '../ajax/addEmployee.php',
                type: 'POST',
                data: Object.fromEntries(formData),
                success: function(response) {
                    const result = typeof response === 'string' ? JSON.parse(response) : response;
                    if (result.success) {
                        $('#addEmployeeModal').modal('hide');
                        showResponse('Employee added successfully', true);
                        $('#addEmployeeForm')[0].reset();
                        table.ajax.reload();
                    } else {
                        showResponse(result.message || 'Error adding employee', false);
                    }
                },
                error: function(xhr, status, error) {
                    showResponse('Error submitting form: ' + error, false);
                }
            });
        });

        // Store the user ID to be deleted
        let userIdToDelete = null;

        // Update delete employee function
        window.deleteEmployee = function(userId) {
            userIdToDelete = userId;
            $('#deleteConfirmModal').modal('show');
        };

        // Handle delete confirmation
        $('#confirmDelete').click(function() {
            if (userIdToDelete) {
                // Show loading state
                $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Deleting...');
                
                $.ajax({
                    url: '../ajax/deleteEmployee.php',
                    type: 'POST',
                    data: { 
                        user_id: userIdToDelete,
                        canteen_id: <?php echo $_SESSION['canteen_id']; ?>
                    },
                    success: function(response) {
                        $('#deleteConfirmModal').modal('hide');
                        const result = typeof response === 'string' ? JSON.parse(response) : response;
                        if (result.success) {
                            showResponse('Employee deleted successfully', true);
                            // Reload only the table data
                            table.ajax.reload(null, false);
                        } else {
                            showResponse(result.error || 'Error deleting employee', false);
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#deleteConfirmModal').modal('hide');
                        showResponse('Error deleting employee: ' + error, false);
                    },
                    complete: function() {
                        // Reset button state
                        $('#confirmDelete').prop('disabled', false).text('Delete');
                    }
                });
            }
        });

        // Function to show response messages
        function showResponse(message, success = true) {
            const responseMessage = document.getElementById('responseMessage');
            if (responseMessage) {
                responseMessage.textContent = message;
                responseMessage.className = success ? 'text-success' : 'text-danger';
                
                const responseModal = new bootstrap.Modal(document.getElementById('responseModal'));
                responseModal.show();
            }
        }
    });

    // Function to open Add Employee Modal
    function openAddEmployeeModal() {
        $('#addEmployeeModal').modal('show');
    }
    </script>
</body>
</html>
