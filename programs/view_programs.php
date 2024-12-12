<?php
require_once '../classes/programClass.php';
require_once '../tools/functions.php';

$program = new Program();
$programs = $program->fetchPrograms();
?>

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>Program Name</th>
                <th>Department</th>
                <th>College</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($programs as $program): ?>
            <tr>
                <td><?= htmlspecialchars($program['program_name']) ?></td>
                <td><?= htmlspecialchars($program['department_name']) ?></td>
                <td><?= htmlspecialchars($program['college_name']) ?></td>
                <td><?= htmlspecialchars($program['description']) ?></td>
                <td>
                    <button class="btn btn-warning btn-sm" onclick="openEditModal(<?= $program['program_id'] ?>)">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-danger btn-sm" onclick="deleteProgram(<?= $program['program_id'] ?>)">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

