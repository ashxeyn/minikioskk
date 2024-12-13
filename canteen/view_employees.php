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
    $canteenId = $_SESSION['canteen_id'] ?? null;
    if (!$canteenId) {
        throw new Exception("Canteen ID not found in session");
    }
    $employees = $employee->fetchCanteenEmployees($canteenId);
} catch (Exception $e) {
    error_log("Error in view_employees: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Co-Employees</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
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
                                <th>Employee ID</th>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($employees)): ?>
                                <?php foreach ($employees as $employee): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($employee['user_id']) ?></td>
                                        <td>
                                            <?= htmlspecialchars($employee['last_name'] . ', ' . 
                                                $employee['given_name'] . ' ' . 
                                                ($employee['middle_name'] ?? '')) ?>
                                        </td>
                                        <td><?= htmlspecialchars($employee['username']) ?></td>
                                        <td><?= htmlspecialchars($employee['email']) ?></td>
                                        <td>
                                            <span class="badge <?= $employee['status'] === 'active' ? 'bg-success' : 'bg-danger' ?>">
                                                <?= ucfirst($employee['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-primary" onclick="editEmployee(<?= $employee['user_id'] ?>)">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" onclick="deleteEmployee(<?= $employee['user_id'] ?>)">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Include Modals -->
    <?php include 'addEmployeeModal.html'; ?>
    <?php include 'editEmployeeModal.html'; ?>

    <style>
    .badge {
        padding: 0.5em 0.8em;
    }
    .btn-group {
        gap: 0.25rem;
    }
    </style>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <!-- Custom JS -->
    <script src="../js/manager.js"></script>
    <script>
        $(document).ready(function() {
            $('#employeesTable').DataTable();
        });
    </script>
</body>
</html>