function loadProgramTable() {
    $.ajax({
        url: '../programs/view_programs.php',
        method: 'GET',
        success: function(response) {
            $('#programTable').html(response);
            initializeDataTable();
        }
    });
}

function loadDepartments() {
    $.ajax({
        url: '../programs/get_departments.php',
        method: 'GET',
        success: function(response) {
            const departments = JSON.parse(response);
            let options = '<option value="">Select Department</option>';
            departments.forEach(dept => {
                options += `<option value="${dept.department_id}">${dept.department_name} (${dept.college_name})</option>`;
            });
            $('#department_id, #edit_department_id').html(options);
        }
    });
}

function openAddModal() {
    $('#addProgramModal').modal('show');
}

function openEditModal(programId) {
    $.ajax({
        url: '../programs/fetchProgramDetails.php',
        method: 'GET',
        data: { program_id: programId },
        success: function(response) {
            const program = JSON.parse(response);
            $('#edit_program_id').val(program.program_id);
            $('#edit_program_name').val(program.program_name);
            $('#edit_department_id').val(program.department_id);
            $('#edit_description').val(program.description);
            $('#editProgramModal').modal('show');
        }
    });
}

function initializeDataTable() {
    $('#programTable table').DataTable({
        responsive: true,
        columns: [
            { data: 'program_name' },
            { data: 'department_name' },
            { data: 'college_name' },
            { data: 'description' },
            { data: 'actions', orderable: false }
        ]
    });
}

$('#addProgramForm').submit(function(e) {
    e.preventDefault();
    const formData = $(this).serialize();

    $.ajax({
        url: '../programs/addProgram.php',
        method: 'POST',
        data: formData,
        success: function(response) {
            if (response === 'success') {
                $('#addProgramModal').modal('hide');
                loadProgramTable();
            } else {
                alert('Failed to add manager: ' + response);
            }
        },
        error: function() {
            alert('Error occurred while adding the manager.');
        }
    });
});

$('#editProgramForm').submit(function(e) {
    e.preventDefault();

    const formData = $(this).serialize();

    $.ajax({
        url: '../programs/editProgram.php',
        type: 'POST',
        data: formData,
        success: function(response) {
            if (response === 'success') {
                $('#editProgramModal').modal('hide');
                loadProgramTable();
            } else {
                alert('Failed to update program. Please try again.');
            }
        },
        error: function() {
            alert('An error occurred. Please try again.');
        }
    });
});

function openDeleteModal(programId) {
    $('#deleteProgramId').val(programId);
    $('#deleteProgramModal').modal('show');
}

$('#deleteProgramForm').submit(function(e) {
    e.preventDefault();

    const programId = $('#deleteProgramId').val();

    $.ajax({
        url: '../programs/deleteProgram.php',
        type: 'POST',
        data: { program_id: programId },
        success: function(response) {
            if (response === 'success') {
                $('#deleteProgramModal').modal('hide');
                loadProgramTable();
            } else {
                alert('Failed to delete program. Please try again.');
            }
        },
        error: function() {
            alert('An error occurred. Please try again.');
        }
    });
});

$(document).ready(function() {
    loadProgramTable();
    loadDepartments();
});
