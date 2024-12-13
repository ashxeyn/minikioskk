<?php
require_once '../classes/programClass.php';
require_once '../tools/functions.php';

$program = new Program();
$programs = $program->fetchPrograms();
?>

<div class="mb-4">
    <div class="row align-items-center">
        <div class="col-md-4">
            <button type="button" class="btn btn-primary" onclick="openAddModal()">
                <i class="bi bi-plus-circle"></i> Add Program
            </button>
        </div>
        <div class="col-md-4">
            <input type="text" id="searchProgram" class="form-control" placeholder="Search programs...">
        </div>
        <div class="col-md-4">
            <select id="collegeFilter" class="form-select">
                <option value="">All Colleges</option>
                <?php
                $colleges = $program->fetchColleges();
                foreach ($colleges as $college) {
                    echo "<option value='" . htmlspecialchars($college['college_id']) . "'>" . 
                         htmlspecialchars($college['college_name']) . "</option>";
                }
                ?>
            </select>
        </div>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover" id="programsTable">
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

<script>
$(document).ready(function() {
    const table = $('#programsTable').DataTable({
        dom: 'lrtip', // Removes default search box
        pageLength: 10
    });

    $('#searchProgram').on('keyup', function() {
        table.search(this.value).draw();
    });

    $('#collegeFilter').on('change', function() {
        const collegeId = $(this).val();
        if (collegeId) {
            table.column(2) // College column
                .search($(this).find('option:selected').text())
                .draw();
        } else {
            table.column(2).search('').draw();
        }
    });
});
</script>

