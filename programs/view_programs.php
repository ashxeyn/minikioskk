<?php
session_start();
require_once '../classes/programClass.php';
require_once '../tools/functions.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

try {
    $program = new Program();
    $programs = $program->fetchPrograms();
} catch (Exception $e) {
    error_log("Error in view_programs: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Programs</title>
    
    <!-- Local vendor files -->
    <link href="../vendor/bootstrap-5.3.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="../vendor/bootstrap-icons-1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../vendor/datatables/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        .dataTables_wrapper .dataTables_length, 
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 15px;
        }
        .table th, .table td {
            vertical-align: middle;
        }
    </style>
</head>
<body>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2>Manage Programs</h2>
        </div>
        <div class="col-md-6 text-end">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProgramModal">
                <i class="bi bi-plus-circle"></i> Add Program
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="programsTable" width="100%">
                    <thead>
                        <tr>
                            <th>Program ID</th>
                            <th>Program Name</th>
                            <th>Department</th>
                            <th>College</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($programs)): ?>
                            <?php foreach ($programs as $prog): ?>
                                <tr>
                                    <td><?= htmlspecialchars($prog['program_id']) ?></td>
                                    <td><?= htmlspecialchars($prog['program_name']) ?></td>
                                    <td><?= htmlspecialchars($prog['department_name']) ?></td>
                                    <td><?= htmlspecialchars($prog['college_name']) ?></td>
                                    <td><?= htmlspecialchars($prog['description']) ?></td>
                                    <td>
                                        <button class="btn btn-warning btn-sm" onclick="editProgram(<?= $prog['program_id'] ?>)">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm" onclick="deleteProgram(<?= $prog['program_id'] ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
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

<!-- Add Program Modal -->
<div class="modal fade" id="addProgramModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Program</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addProgramForm">
                    <div class="mb-3">
                        <label for="college_id" class="form-label">College</label>
                        <select class="form-select" id="college_id" name="college_id" required>
                            <option value="">Select College</option>
                            <?php
                            $colleges = $program->fetchColleges();
                            foreach ($colleges as $college) {
                                echo "<option value='" . $college['college_id'] . "'>" . 
                                     htmlspecialchars($college['college_name']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="department_id" class="form-label">Department</label>
                        <select class="form-select" id="department_id" name="department_id" required>
                            <option value="">Select Department</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="program_name" class="form-label">Program Name</label>
                        <input type="text" class="form-control" id="program_name" name="program_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="saveProgram()">Save Program</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Program Modal -->
<div class="modal fade" id="editProgramModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Program</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editProgramForm">
                    <input type="hidden" id="edit_program_id" name="program_id">
                    <div class="mb-3">
                        <label for="edit_college_id" class="form-label">College</label>
                        <select class="form-select" id="edit_college_id" name="college_id" required>
                            <option value="">Select College</option>
                            <?php foreach ($colleges as $college): ?>
                                <option value="<?= $college['college_id'] ?>">
                                    <?= htmlspecialchars($college['college_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_department_id" class="form-label">Department</label>
                        <select class="form-select" id="edit_department_id" name="department_id" required>
                            <option value="">Select Department</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_program_name" class="form-label">Program Name</label>
                        <input type="text" class="form-control" id="edit_program_name" name="program_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="updateProgram()">Update Program</button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="../vendor/jquery/jquery-3.6.0.min.js"></script>
<script src="../vendor/bootstrap-5.3.3/js/bootstrap.bundle.min.js"></script>
<script src="../vendor/datatables/jquery.dataTables.min.js"></script>
<script src="../vendor/datatables/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable with specific configuration
    $('#programsTable').DataTable({
        "responsive": true,
        "pageLength": 10,
        "order": [[0, "asc"]],
        "columnDefs": [
            {
                "targets": -1,
                "orderable": false,
                "searchable": false
            }
        ],
        "language": {
            "search": "Search:",
            "lengthMenu": "Show _MENU_ entries",
            "info": "Showing _START_ to _END_ of _TOTAL_ entries",
            "infoEmpty": "Showing 0 to 0 of 0 entries",
            "infoFiltered": "(filtered from _MAX_ total entries)"
        }
    });

    // Load departments when college is selected
    $('#college_id').change(function() {
        loadDepartments($(this).val(), '#department_id');
    });

    $('#edit_college_id').change(function() {
        loadDepartments($(this).val(), '#edit_department_id');
    });
});

function loadDepartments(collegeId, targetSelect) {
    if (!collegeId) {
        $(targetSelect).html('<option value="">Select Department</option>');
        return;
    }

    $.ajax({
        url: '../ajax/getDepartments.php',
        type: 'POST',
        data: { college_id: collegeId },
        success: function(response) {
            try {
                const departments = JSON.parse(response);
                let options = '<option value="">Select Department</option>';
                departments.forEach(dept => {
                    options += `<option value="${dept.department_id}">${dept.department_name}</option>`;
                });
                $(targetSelect).html(options);
            } catch (e) {
                console.error('Error parsing departments:', e);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading departments:', error);
        }
    });
}

function saveProgram() {
    const formData = new FormData(document.getElementById('addProgramForm'));

    $.ajax({
        url: '../ajax/addProgram.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            try {
                const result = JSON.parse(response);
                if (result.success) {
                    $('#addProgramModal').modal('hide');
                    location.reload();
                } else {
                    alert(result.message || 'Error adding program');
                }
            } catch (e) {
                console.error('Error parsing response:', e);
                alert('Error adding program');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
            alert('Error adding program');
        }
    });
}

function editProgram(programId) {
    $.ajax({
        url: '../ajax/getProgramDetails.php',
        type: 'POST',
        data: { program_id: programId },
        success: function(response) {
            try {
                const program = JSON.parse(response);
                $('#edit_program_id').val(program.program_id);
                $('#edit_college_id').val(program.college_id);
                loadDepartments(program.college_id, '#edit_department_id');
                setTimeout(() => {
                    $('#edit_department_id').val(program.department_id);
                }, 500);
                $('#edit_program_name').val(program.program_name);
                $('#edit_description').val(program.description);
                $('#editProgramModal').modal('show');
            } catch (e) {
                console.error('Error parsing program details:', e);
                alert('Error loading program details');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
            alert('Error loading program details');
        }
    });
}

function updateProgram() {
    const formData = new FormData(document.getElementById('editProgramForm'));

    $.ajax({
        url: '../ajax/updateProgram.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            try {
                const result = JSON.parse(response);
                if (result.success) {
                    $('#editProgramModal').modal('hide');
                    location.reload();
                } else {
                    alert(result.message || 'Error updating program');
                }
            } catch (e) {
                console.error('Error parsing response:', e);
                alert('Error updating program');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
            alert('Error updating program');
        }
    });
}

function deleteProgram(programId) {
    if (confirm('Are you sure you want to delete this program?')) {
        $.ajax({
            url: '../ajax/deleteProgram.php',
            type: 'POST',
            data: { program_id: programId },
            success: function(response) {
                try {
                    const result = JSON.parse(response);
                    if (result.success) {
                        location.reload();
                    } else {
                        alert(result.message || 'Error deleting program');
                    }
                } catch (e) {
                    console.error('Error parsing response:', e);
                    alert('Error deleting program');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                alert('Error deleting program');
            }
        });
    }
}
</script>

</body>
</html>

