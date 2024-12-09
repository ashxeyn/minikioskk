function loadProgramTable() {
    $.ajax({
        url: '../programs/view_programs.php',
        type: 'GET',
        success: function(response) {
            $('#programTable').html(response);
        }
    });
}

function openAddProgramModal() {
    $('#addProgramModal').modal('show');
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

function openEditModal(programId) {
    $.ajax({
        url: '../programs/fetchProgramDetails.php',
        type: 'GET',
        data: { program_id: programId },
        success: function(response) {
            const program = JSON.parse(response);
            $('#editProgramId').val(program.program_id);
            $('#edit_program_name').val(program.program_name);
            $('#edit_department').val(program.department);
            $('#editProgramModal').modal('show');
        }
    });
}

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
});
