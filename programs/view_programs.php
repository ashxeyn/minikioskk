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
                        <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#addCollegeModal">
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
                                                <button class="btn btn-warning btn-sm" data-action="edit-college" data-college-id="<?= $college['college_id'] ?>">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="btn btn-danger btn-sm" data-action="delete-college" data-college-id="<?= $college['college_id'] ?>">
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
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addDepartmentModal">
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
                                                <button class="btn btn-warning btn-sm" data-action="edit-department" data-department-id="<?= $dept['department_id'] ?>">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="btn btn-danger btn-sm" data-action="delete-department" data-department-id="<?= $dept['department_id'] ?>">
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

