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
    // Clear form fields
    $('#addProgramForm')[0].reset();
    
    // Show modal
    $('#addProgramModal').modal('show');
}

function openEditModal(programId) {
    // First load departments
    $.ajax({
        url: '../programs/get_departments.php',
        method: 'GET',
        success: function(response) {
            const departments = JSON.parse(response);
            let options = '<option value="">Select Department</option>';
            departments.forEach(dept => {
                options += `<option value="${dept.department_id}">${dept.department_name} (${dept.college_name})</option>`;
            });
            $('#edit_department_id').html(options);
            
            // Then fetch program details
            $.ajax({
                url: '../programs/fetchProgramDetails.php',
                method: 'GET',
                data: { program_id: programId },
                success: function(response) {
                    const program = JSON.parse(response);
                    $('#editProgramId').val(program.program_id);
                    $('#edit_program_name').val(program.program_name);
                    $('#edit_department_id').val(program.department_id);
                    $('#edit_description').val(program.description);
                    $('#editProgramModal').modal('show');
                }
            });
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

$('#addProgramForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = {
        program_name: $('#program_name').val(),
        department: $('#department').val(),
        college: $('#college').val(),
        description: $('#description').val()
    };

    $.ajax({
        url: '../programs/addProgram.php',
        method: 'POST',
        data: formData,
        success: function(response) {
            if (response === 'success') {
                $('#addProgramModal').modal('hide');
                loadProgramTable();
                showAlert('Program added successfully!', 'success');
                // Clear form
                $('#addProgramForm')[0].reset();
            } else {
                showAlert('Error adding program', 'danger');
            }
        },
        error: function() {
            showAlert('Error adding program', 'danger');
        }
    });
});

$('#editProgramForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = {
        program_id: $('#editProgramId').val(),
        program_name: $('#edit_program_name').val(),
        department_id: $('#edit_department_id').val(),
        description: $('#edit_description').val()
    };

    $.ajax({
        url: '../programs/editProgram.php',
        method: 'POST',
        data: formData,
        success: function(response) {
            if (response === 'success') {
                $('#editProgramModal').modal('hide');
                loadProgramTable();
                showAlert('Program updated successfully!', 'success');
            } else {
                showAlert('Error updating program', 'danger');
            }
        },
        error: function() {
            showAlert('Error updating program', 'danger');
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

function saveProgram() {
    const formData = {
        program_name: $('#program_name').val(),
        department_id: $('#department').val(),
        college_id: $('#college').val(),
        description: $('#description').val()
    };

    $.ajax({
        url: '../programs/addProgram.php',
        method: 'POST',
        data: formData,
        success: function(response) {
            if (response === 'success') {
                $('#addProgramModal').modal('hide');
                loadProgramTable();
                showAlert('Program added successfully!', 'success');
                // Clear form
                $('#addProgramForm')[0].reset();
            } else {
                showAlert('Error adding program', 'danger');
            }
        },
        error: function() {
            showAlert('Error adding program', 'danger');
        }
    });
}

function showAlert(message, type) {
    const alertDiv = $(`<div class="alert alert-${type} alert-dismissible fade show" role="alert">
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>`);
    
    $('.container').prepend(alertDiv);
    setTimeout(() => alertDiv.alert('close'), 3000);
}

// Add this function to load colleges for the dropdown
function loadColleges() {
    $.ajax({
        url: '../programs/get_colleges.php',
        method: 'GET',
        success: function(response) {
            const colleges = JSON.parse(response);
            let options = '<option value="">Select College</option>';
            colleges.forEach(college => {
                options += `<option value="${college.college_id}">${college.college_name}</option>`;
            });
            $('#college').html(options);
        }
    });
}

$(document).ready(function() {
    loadProgramTable();
    loadDepartments();
});
