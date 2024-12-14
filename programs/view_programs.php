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

