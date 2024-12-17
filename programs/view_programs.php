<?php

require_once '../classes/programClass.php';
require_once '../tools/functions.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

try {
    $program = new Program();
    $programs = $program->fetchPrograms();
    $colleges = $program->fetchColleges();
    $departments = $program->fetchAllDepartments();
} catch (Exception $e) {
    error_log("Error in view_programs: " . $e->getMessage());
    $programs = [];
    $colleges = [];
    $departments = [];
}
?>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2>Manage Programs</h2>
        </div>
       
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="programs-tab" data-bs-toggle="tab" data-bs-target="#programs" type="button" role="tab">
                Programs
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="colleges-tab" data-bs-toggle="tab" data-bs-target="#colleges" type="button" role="tab">
                Colleges
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="departments-tab" data-bs-toggle="tab" data-bs-target="#departments" type="button" role="tab">
                Departments
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="myTabContent">
        <!-- Programs Tab -->
        <div class="tab-pane fade show active" id="programs" role="tabpanel">
            <div class="card">
                <div class="card-body">
                    <div class="text-end mb-3">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProgramModal">
                            <i class="bi bi-plus-circle"></i> Add Program
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="programsTable" width="100%">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Program Name</th>
                                    <th>Department</th>
                                    <th>College</th>
                                    <th>Description</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($programs)): 
                                    $counter = 1; ?>
                                    <?php foreach ($programs as $prog): ?>
                                        <tr>
                                            <td><?= $counter++ ?></td>
                                            <td><?= htmlspecialchars($prog['program_name']) ?></td>
                                            <td><?= htmlspecialchars($prog['department_name']) ?></td>
                                            <td><?= htmlspecialchars($prog['college_name']) ?></td>
                                            <td><?= htmlspecialchars($prog['description']) ?></td>
                                            <td>
                                                <button class="btn btn-warning btn-sm" data-action="edit" data-program-id="<?= $prog['program_id'] ?>">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="btn btn-danger btn-sm" data-action="delete" data-program-id="<?= $prog['program_id'] ?>">
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

        <!-- Colleges Tab -->
        <div class="tab-pane fade" id="colleges" role="tabpanel">
            <div class="card">
                <div class="card-body">
                    <div class="text-end mb-3">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCollegeModal">
                            <i class="bi bi-plus-circle"></i> Add College
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="collegesTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>College Name</th>
                                    <th>Abbreviation</th>
                                    <th>Description</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($colleges)): ?>
                                    <?php foreach ($colleges as $college): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($college['college_id']) ?></td>
                                            <td><?= htmlspecialchars($college['college_name']) ?></td>
                                            <td><?= htmlspecialchars($college['abbreviation']) ?></td>
                                            <td><?= htmlspecialchars($college['description']) ?></td>
                                            <td>
                                                <button class="btn btn-warning btn-sm edit-college" 
                                                    data-id="<?= $college['college_id'] ?>"
                                                    data-name="<?= htmlspecialchars($college['college_name']) ?>"
                                                    data-abbr="<?= htmlspecialchars($college['abbreviation']) ?>"
                                                    data-desc="<?= htmlspecialchars($college['description']) ?>">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="btn btn-danger btn-sm delete-college" data-id="<?= $college['college_id'] ?>">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No colleges found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Departments Tab -->
        <div class="tab-pane fade" id="departments" role="tabpanel">
            <div class="card">
                <div class="card-body">
                    <div class="text-end mb-3">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDepartmentModal">
                            <i class="bi bi-plus-circle"></i> Add Department
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="departmentsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Department Name</th>
                                    <th>College</th>
                                    <th>Description</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($departments)): ?>
                                    <?php foreach ($departments as $dept): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($dept['department_id']) ?></td>
                                            <td><?= htmlspecialchars($dept['department_name']) ?></td>
                                            <td><?= htmlspecialchars($dept['college_name']) ?></td>
                                            <td><?= htmlspecialchars($dept['description']) ?></td>
                                            <td>
                                                <button class="btn btn-warning btn-sm edit-department"
                                                    data-id="<?= $dept['department_id'] ?>"
                                                    data-name="<?= htmlspecialchars($dept['department_name']) ?>"
                                                    data-college="<?= $dept['college_id'] ?>"
                                                    data-desc="<?= htmlspecialchars($dept['description']) ?>">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="btn btn-danger btn-sm delete-department" data-id="<?= $dept['department_id'] ?>">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No departments found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit College Modal -->
<div class="modal fade" id="editCollegeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit College</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editCollegeForm">
                    <input type="hidden" id="edit_college_id" name="college_id">
                    <div class="mb-3">
                        <label for="edit_college_name" class="form-label">College Name</label>
                        <input type="text" class="form-control" id="edit_college_name" name="college_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_abbreviation" class="form-label">Abbreviation</label>
                        <input type="text" class="form-control" id="edit_abbreviation" name="abbreviation" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_college_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_college_description" name="description" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="updateCollege()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Department Modal -->
<div class="modal fade" id="editDepartmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Department</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editDepartmentForm">
                    <input type="hidden" id="edit_department_id" name="department_id">
                    <div class="mb-3">
                        <label for="edit_department_name" class="form-label">Department Name</label>
                        <input type="text" class="form-control" id="edit_department_name" name="department_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_department_college_id" class="form-label">College</label>
                        <select class="form-select" id="edit_department_college_id" name="college_id" required>
                            <!-- Options will be loaded dynamically -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_department_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_department_description" name="description" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="updateDepartment()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Alert Modal -->
<div class="modal fade" id="alertModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="alertMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<script>
// Define the functions in the same file to ensure they're available
function editCollege(id, name, abbreviation, description) {
    $('#edit_college_id').val(id);
    $('#edit_college_name').val(name);
    $('#edit_abbreviation').val(abbreviation);
    $('#edit_college_description').val(description);
    $('#editCollegeModal').modal('show');
}

function editDepartment(id, name, collegeId, description) {
    $('#edit_department_id').val(id);
    $('#edit_department_name').val(name);
    $('#edit_department_description').val(description);
    
    // Load colleges first
    loadColleges('#edit_department_college_id');
    
    // Set selected college after a short delay
    setTimeout(() => {
        $('#edit_department_college_id').val(collegeId);
    }, 500);
    
    $('#editDepartmentModal').modal('show');
}

function updateCollege() {
    const formData = {
        college_id: $('#edit_college_id').val(),
        college_name: $('#edit_college_name').val(),
        abbreviation: $('#edit_abbreviation').val(),
        description: $('#edit_college_description').val()
    };

    $.ajax({
        url: '../programs/editCollege.php',
        type: 'POST',
        data: formData,
        success: function(response) {
            try {
                const result = JSON.parse(response);
                if (result.success) {
                    $('#editCollegeModal').modal('hide');
                    showAlert('College updated successfully', 'success');
                    location.reload();
                } else {
                    showAlert(result.message || 'Error updating college', 'danger');
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('Error processing response', 'danger');
            }
        },
        error: function() {
            showAlert('Error updating college', 'danger');
        }
    });
}

function updateDepartment() {
    const formData = {
        department_id: $('#edit_department_id').val(),
        department_name: $('#edit_department_name').val(),
        college_id: $('#edit_department_college_id').val(),
        description: $('#edit_department_description').val()
    };

    $.ajax({
        url: '../programs/editDepartment.php',
        type: 'POST',
        data: formData,
        success: function(response) {
            try {
                const result = JSON.parse(response);
                if (result.success) {
                    $('#editDepartmentModal').modal('hide');
                    showAlert('Department updated successfully', 'success');
                    location.reload();
                } else {
                    showAlert(result.message || 'Error updating department', 'danger');
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('Error processing response', 'danger');
            }
        },
        error: function() {
            showAlert('Error updating department', 'danger');
        }
    });
}

function showAlert(message, type) {
    const alertDiv = $(`<div class="alert alert-${type} alert-dismissible fade show" role="alert">
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>`);
    
    $('.container-fluid').prepend(alertDiv);
    setTimeout(() => alertDiv.alert('close'), 3000);
}

// Initialize event handlers when document is ready
$(document).ready(function() {
    // College handlers
    $(document).on('click', '.edit-college', function() {
        const btn = $(this);
        editCollege(
            btn.data('id'),
            btn.data('name'),
            btn.data('abbr'),
            btn.data('desc')
        );
    });

    $(document).on('click', '.delete-college', function() {
        deleteCollege($(this).data('id'));
    });

    // Department handlers
    $(document).on('click', '.edit-department', function() {
        const btn = $(this);
        editDepartment(
            btn.data('id'),
            btn.data('name'),
            btn.data('college'),
            btn.data('desc')
        );
    });

    $(document).on('click', '.delete-department', function() {
        deleteDepartment($(this).data('id'));
    });
});
</script>

