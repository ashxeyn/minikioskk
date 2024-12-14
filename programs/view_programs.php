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
        </tbody>
    </table>
</div>

<script>
$(document).ready(function() {
    // Initialize DataTable with proper destroy and configuration
    if ($.fn.DataTable.isDataTable('#programsTable')) {
        $('#programsTable').DataTable().destroy();
    }

    const table = $('#programsTable').DataTable({
        processing: true,
        serverSide: false,
        data: <?php echo json_encode($programs); ?>,
        columns: [
            { data: 'program_name', render: function(data) { return escapeHtml(data); } },
            { data: 'department_name', render: function(data) { return escapeHtml(data); } },
            { data: 'college_name', render: function(data) { return escapeHtml(data); } },
            { data: 'description', render: function(data) { return escapeHtml(data); } },
            { 
                data: 'program_id',
                render: function(data) {
                    return `
                        <button class="btn btn-warning btn-sm" onclick="openEditModal(${data})">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="deleteProgram(${data})">
                            <i class="bi bi-trash"></i>
                        </button>
                    `;
                }
            }
        ],
        dom: 'lrtip', // Removes default search box
        pageLength: 10,
        language: {
            emptyTable: "No programs found"
        }
    });

    // Helper function to escape HTML
    function escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Custom search functionality
    $('#searchProgram').on('keyup', function() {
        table.search(this.value).draw();
    });

    // College filter
    $('#collegeFilter').on('change', function() {
        const collegeId = $(this).val();
        if (collegeId) {
            table.column(2)
                .search($(this).find('option:selected').text())
                .draw();
        } else {
            table.column(2).search('').draw();
        }
    });
});
</script>

