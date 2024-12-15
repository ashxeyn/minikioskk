function loadProgramTable() {
    $.ajax({
        url: '../programs/view_programs.php',
        method: 'GET',
        success: function(response) {
            $('#programTable').html(response);
            setTimeout(initializeDataTable, 100);
            initializeEventHandlers();
        },
        error: function(xhr, status, error) {
            console.error('Error loading program table:', error);
        }
    });
}

function loadDepartments(college_id = null, target_id = '#department_id') {
    $.ajax({
        url: '../programs/get_departments.php',
        method: 'GET',
        data: college_id ? { college_id: college_id } : {},
        success: function(response) {
            try {
                const departments = JSON.parse(response);
                let options = '<option value="">Select Department</option>';
                departments.forEach(dept => {
                    options += `<option value="${dept.department_id}">${dept.department_name} (${dept.college_name})</option>`;
                });
                $(target_id).html(options);
            } catch (e) {
                console.error('Error parsing departments:', e);
                showAlert('Error loading departments', 'danger');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading departments:', error);
            showAlert('Error loading departments', 'danger');
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
    if (typeof $.fn.DataTable === 'undefined') {
        console.error('DataTables not loaded');
        return;
    }

    const table = $('#programsTable');
    if (!table.length) {
        console.error('Table not found');
        return;
    }

    try {
        if ($.fn.DataTable.isDataTable(table)) {
            table.DataTable().destroy();
        }

        table.DataTable({
            responsive: true,
            pageLength: 10,
            order: [[1, "asc"]],
            columnDefs: [
                {
                    targets: -1,
                    orderable: false,
                    searchable: false
                }
            ]
        });
    } catch (error) {
        console.error('Error initializing DataTable:', error);
    }
}

function initializeEventHandlers() {
    // Handle add program form submission
    $('#addProgramForm').off('submit').on('submit', function(e) {
        e.preventDefault();
        handleAddProgram($(this));
    });

    // Handle edit program form submission
    $('#editProgramForm').off('submit').on('submit', function(e) {
        e.preventDefault();
        handleEditProgram($(this));
    });

    // Handle delete program confirmation
    $(document).off('click', '.delete-program').on('click', '.delete-program', function() {
        const programId = $(this).data('id');
        if (confirm('Are you sure you want to delete this program?')) {
            deleteProgram(programId);
        }
    });

    // Handle edit button clicks
    $(document).on('click', '[data-action="edit"]', function() {
        const programId = $(this).data('program-id');
        editProgram(programId);
    });

    // Handle delete button clicks
    $(document).on('click', '[data-action="delete"]', function() {
        const programId = $(this).data('program-id');
        deleteProgram(programId);
    });

    // Handle college selection change
    $('#college_id').on('change', function() {
        const collegeId = $(this).val();
        if (collegeId) {
            loadDepartments(collegeId, '#department_id');
        } else {
            $('#department_id').html('<option value="">Select College First</option>');
        }
    });

    $('#edit_college_id').on('change', function() {
        const collegeId = $(this).val();
        if (collegeId) {
            loadDepartments(collegeId, '#edit_department_id');
        } else {
            $('#edit_department_id').html('<option value="">Select College First</option>');
        }
    });
}

function handleAddProgram(form) {
    const formData = {
        program_name: form.find('#program_name').val(),
        department: form.find('#department').val(),
        college: form.find('#college').val(),
        description: form.find('#description').val()
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
                form[0].reset();
            } else {
                showAlert('Error adding program', 'danger');
            }
        },
        error: function() {
            showAlert('Error adding program', 'danger');
        }
    });
}

function handleEditProgram(form) {
    const formData = {
        program_id: form.find('#editProgramId').val(),
        program_name: form.find('#edit_program_name').val(),
        department_id: form.find('#edit_department_id').val(),
        description: form.find('#edit_description').val()
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
}

function openDeleteModal(programId) {
    $('#deleteProgramId').val(programId);
    $('#deleteProgramModal').modal('show');
}

function saveProgram() {
    // Get the form values
    const formData = {
        program_name: $('#program_name').val(),
        department_id: $('#department_id').val(),
        description: $('#description').val()
    };

    // Validate required fields
    if (!formData.program_name || !formData.department_id) {
        showAlert('Please fill in all required fields', 'danger');
        return;
    }

    // Send the AJAX request
    $.ajax({
        url: '../programs/addProgram.php',
        method: 'POST',
        data: formData,
        success: function(response) {
            try {
                const result = JSON.parse(response);
                if (result.success) {
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('addProgramModal'));
                    modal.hide();
                    
                    // Remove modal backdrop and cleanup
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');
                    
                    // Reload data and show success message
                    loadProgramSection();
                    showAlert('Program added successfully!', 'success');
                    
                    // Reset form
                    $('#addProgramForm')[0].reset();
                } else {
                    showAlert(result.message || 'Error adding program', 'danger');
                }
            } catch (e) {
                console.error('Error parsing response:', e);
                showAlert('Error adding program', 'danger');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
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
function loadColleges(target_id = '#college_id') {
    $.ajax({
        url: '../programs/get_colleges.php',
        method: 'GET',
        success: function(response) {
            try {
                const colleges = JSON.parse(response);
                let options = '<option value="">Select College</option>';
                colleges.forEach(college => {
                    options += `<option value="${college.college_id}">${college.college_name} (${college.abbreviation})</option>`;
                });
                $(target_id).html(options);
            } catch (e) {
                console.error('Error parsing colleges:', e);
                showAlert('Error loading colleges', 'danger');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading colleges:', error);
            showAlert('Error loading colleges', 'danger');
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
                
                // Set form values
                $('#edit_program_id').val(program.program_id);
                $('#edit_program_name').val(program.program_name);
                $('#edit_description').val(program.description);
                
                // Load colleges first
                loadColleges('#edit_college_id');
                
                // Set college and load departments after a small delay
                setTimeout(() => {
                    $('#edit_college_id').val(program.college_id);
                    loadDepartments(program.college_id, '#edit_department_id');
                    
                    // Set department after another small delay
                    setTimeout(() => {
                        $('#edit_department_id').val(program.department_id);
                        
                    }, 500);
                }, 500);
                
                
                // Show modal
                const editModal = new bootstrap.Modal(document.getElementById('editProgramModal'));
                editModal.show();
            } catch (e) {
                console.error('Error parsing program details:', e);
                showAlert('Error loading program details', 'danger');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
            showAlert('Error loading program details', 'danger');
        }
    });
}

function deleteProgram(programId) {
    if (confirm('Are you sure you want to delete this program?')) {
        $.ajax({
            url: '../programs/deleteProgram.php',
            type: 'POST',
            data: { program_id: programId },
            success: function(response) {
                try {
                    const result = JSON.parse(response);
                    if (result.success) {
                        // Load entire program section instead of just the table
                        loadProgramSection();
                        showAlert('Program deleted successfully!', 'success');
                    } else {
                        showAlert(result.message || 'Error deleting program', 'danger');
                    }
                } catch (e) {
                    console.error('Error parsing response:', e);
                    showAlert('Error deleting program', 'danger');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                showAlert('Error deleting program', 'danger');
            }
        });
    }
}

// Update loadProgramSection function
function loadProgramSection() {
    $.ajax({
        url: "../programs/programSection.php",
        method: 'GET',
        success: function(response) {
            $('#contentArea').html(response);
            // Initialize DataTable and event handlers after content is loaded
            setTimeout(() => {
                initializeDataTable();
                initializeEventHandlers();
                // Initialize tooltips if you're using them
                $('[data-bs-toggle="tooltip"]').tooltip();
            }, 100);
        },
        error: function(xhr, status, error) {
            console.error('Error loading program section:', error);
            $('#contentArea').html('<p class="text-danger">Failed to load Program section. Please try again.</p>');
        }
    });
}

// Document ready handler
$(document).ready(function() {
    // Initialize event handlers
    initializeEventHandlers();
    
    // Initialize DataTable if table exists
    if ($('#programsTable').length) {
        setTimeout(initializeDataTable, 100);
    }

    // Load colleges when add modal is shown
    $('#addProgramModal').on('show.bs.modal', function() {
        loadColleges('#college_id');
        $('#department_id').html('<option value="">Select College First</option>');
    });

    // Load colleges when department modal is shown
    $('#addDepartmentModal').on('show.bs.modal', function() {
        loadColleges('#dept_college_id');
    });

    initializeDataTables();
    initializeEventHandlers();
    
    // Handle tab changes
    $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
    });
});

function initializeModals() {
    // Ensure Bootstrap is loaded
    if (typeof bootstrap === 'undefined') {
        console.error('Bootstrap not loaded');
        return;
    }

    // Initialize all modals on the page
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modalEl => {
        new bootstrap.Modal(modalEl);
    });
}

// Fixing the delete program form submission
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

// Add this function to handle program updates
function updateProgram() {
    const formData = {
        program_id: $('#edit_program_id').val(),
        program_name: $('#edit_program_name').val(),
        department_id: $('#edit_department_id').val(),
        description: $('#edit_description').val()
    };

    // Validate required fields
    if (!formData.program_name || !formData.department_id) {
        showAlert('Please fill in all required fields', 'danger');
        return;
    }

    $.ajax({
        url: '../programs/editProgram.php',
        method: 'POST',
        data: formData,
        success: function(response) {
            try {
                const result = JSON.parse(response);
                if (result.success) {
                    cleanupModal('editProgramModal');
                    loadProgramSection();
                    showSuccessModal('Program updated successfully!');
                } else {
                    showAlert(result.message || 'Error updating program', 'danger');
                }
            } catch (e) {
                console.error('Error parsing response:', e);
                showAlert('Error updating program', 'danger');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
            showAlert('Error updating program', 'danger');
        }
    });
}

// Make sure the function is available globally
window.updateProgram = updateProgram;
window.editProgram = editProgram;

// Add these functions to handle college and department operations

function saveCollege() {
    const formData = {
        college_name: $('#college_name').val(),
        abbreviation: $('#abbreviation').val(),
        description: $('#college_description').val()
    };

    $.ajax({
        url: '../programs/addCollege.php',
        method: 'POST',
        data: formData,
        success: function(response) {
            try {
                const result = JSON.parse(response);
                if (result.success) {
                    cleanupModal('addCollegeModal');
                    loadColleges();
                    showAlert('College added successfully!', 'success');
                    $('#addCollegeForm')[0].reset();
                } else {
                    showAlert(result.message || 'Error adding college', 'danger');
                }
            } catch (e) {
                console.error('Error parsing response:', e);
                showAlert('Error adding college', 'danger');
            }
        },
        error: function() {
            showAlert('Error adding college', 'danger');
        }
    });
}

function saveDepartment() {
    const formData = {
        college_id: $('#dept_college_id').val(),
        department_name: $('#department_name').val(),
        description: $('#department_description').val()
    };

    $.ajax({
        url: '../programs/addDepartment.php',
        method: 'POST',
        data: formData,
        success: function(response) {
            try {
                const result = JSON.parse(response);
                if (result.success) {
                    cleanupModal('addDepartmentModal');
                    loadDepartments();
                    showAlert('Department added successfully!', 'success');
                    $('#addDepartmentForm')[0].reset();
                } else {
                    showAlert(result.message || 'Error adding department', 'danger');
                }
            } catch (e) {
                console.error('Error parsing response:', e);
                showAlert('Error adding department', 'danger');
            }
        },
        error: function() {
            showAlert('Error adding department', 'danger');
        }
    });
}

// Add this helper function
function cleanupModal(modalId) {
    const modal = bootstrap.Modal.getInstance(document.getElementById(modalId));
    if (modal) {
        modal.hide();
    }
    $('.modal-backdrop').remove();
    $('body').removeClass('modal-open');
    $('body').css('padding-right', '');
}

// Add these functions to handle the modals
function showSuccessModal(message) {
    $('#successMessage').text(message);
    const successModal = new bootstrap.Modal(document.getElementById('successModal'));
    successModal.show();
}

function showDeleteConfirmModal(programId) {
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    
    // Set up the confirm button handler
    $('#confirmDelete').off('click').on('click', function() {
        deleteModal.hide();
        performDelete(programId);
    });
    
    deleteModal.show();
}

// Update the delete function to use confirmation modal
function deleteProgram(programId) {
    showDeleteConfirmModal(programId);
}

// Actual delete operation
function performDelete(programId) {
    $.ajax({
        url: '../programs/deleteProgram.php',
        type: 'POST',
        data: { program_id: programId },
        success: function(response) {
            try {
                const result = JSON.parse(response);
                if (result.success) {
                    loadProgramSection();
                    showSuccessModal('Program deleted successfully!');
                } else {
                    showAlert(result.message || 'Error deleting program', 'danger');
                }
            } catch (e) {
                console.error('Error parsing response:', e);
                showAlert('Error deleting program', 'danger');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
            showAlert('Error deleting program', 'danger');
        }
    });
}

// Make sure the function is available globally
window.saveProgram = saveProgram;

function initializeDataTables() {
    const tables = ['#programsTable', '#collegesTable', '#departmentsTable'];
    
    tables.forEach(tableId => {
        if ($(tableId).length) {
            if ($.fn.DataTable.isDataTable(tableId)) {
                $(tableId).DataTable().destroy();
            }
            
            $(tableId).DataTable({
                responsive: true,
                pageLength: 10,
                order: [[1, "asc"]],
                columnDefs: [
                    {
                        targets: -1,
                        orderable: false,
                        searchable: false
                    }
                ]
            });
        }
    });
}
