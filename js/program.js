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
    $('#addProgramForm')[0].reset();
    
    $('#addProgramModal').modal('show');
}

function openEditModal(programId) {
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
    if ($.fn.DataTable.isDataTable('#programsTable')) {
        $('#programsTable').DataTable().destroy();
    }
    
    $('#programsTable').DataTable({
        responsive: true,
        pageLength: 10,
        order: [[1, "asc"]],
        columnDefs: [
            {
                targets: -1,
                orderable: false,
                searchable: false
            }
        ],
        language: {
            search: "Search:",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "Showing 0 to 0 of 0 entries",
            infoFiltered: "(filtered from _MAX_ total entries)"
        }
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

// Move all the functions from view_programs.php to here
function editProgram(programId) {
    $.ajax({
        url: '../programs/fetchProgramDetails.php',
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

// Initialize everything when the document is ready
$(document).ready(function() {
    // Wait for DataTables to load
    if (typeof $.fn.DataTable !== 'undefined') {
        initializeDataTable();
    } else {
        console.error('DataTables not loaded');
    }
    
    // Set up event handlers for college dropdowns
    $('#college_id').change(function() {
        loadDepartments($(this).val(), '#department_id');
    });

    $('#edit_college_id').change(function() {
        loadDepartments($(this).val(), '#edit_department_id');
    });
});
