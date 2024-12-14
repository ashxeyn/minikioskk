document.addEventListener('DOMContentLoaded', function() {
    loadUserTable();

    setupFormHandlers();
});

function loadUserTable() {
    $.ajax({
        url: '../users/view_accounts.php',
        type: 'GET',
        success: function(response) {
            $('#accountTable').html(response);
            searchUsers('', '');
        },
        error: function(xhr, status, error) {
            console.error('Error details:', {
                status: status,
                error: error,
                response: xhr.responseText
            });
            alert('Failed to load Accounts section. Please try again.');
        }
    });
}

function setupFormHandlers() {
    $(document).on('submit', '#addNewUserForm', function(e) {
        e.preventDefault();
        saveUser();
    });

    $(document).on('submit', '#editForm', function(e) {
        e.preventDefault();
        const formData = $(this).serialize();

        $.ajax({
            url: '../users/editUser.php',
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response === 'success') {
                    $('#editUserModal').modal('hide');
                    loadUserTable();
                } else {
                    alert('Failed to update user: ' + response);
                }
            }
        });
    });
    $(document).on('submit', '#deleteForm', function(e) {
        e.preventDefault();
        const userId = $('#deleteUserId').val();

        $.ajax({
            url: '../users/deleteUser.php',
            type: 'POST',
            data: { user_id: userId },
            success: function(response) {
                if (response === 'success') {
                    $('#deleteUserModal').modal('hide');
                    loadUserTable();
                } else {
                    alert('Failed to delete user: ' + response);
                }
            }
        });
    });
}

function openAddUserModal() {

    document.getElementById('addUserForm').reset();
    
  
    const programSelect = document.getElementById('program_id');
    const departmentSelect = document.getElementById('department_id');
    const canteenSelect = document.getElementById('canteen_id');
    
    programSelect.innerHTML = '<option value="">Loading programs...</option>';
    departmentSelect.innerHTML = '<option value="">Loading departments...</option>';
    canteenSelect.innerHTML = '<option value="">Loading canteens...</option>';
    

    $.ajax({
        url: '../programs/get_programs.php',
        type: 'GET',
        dataType: 'json',
        success: function(programs) {
            programSelect.innerHTML = '<option value="">Select Program</option>';
            if (Array.isArray(programs)) {
                programs.forEach(program => {
                    programSelect.innerHTML += `<option value="${escapeHtml(program.program_id)}">${escapeHtml(program.program_name)}</option>`;
                });
            } else {
                console.error('Programs data is not an array:', programs);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error fetching programs:', error);
            programSelect.innerHTML = '<option value="">Error loading programs</option>';
        }
    });


    $.ajax({
        url: '../departments/get_departments.php',
        type: 'GET',
        dataType: 'json',
        success: function(departments) {
            departmentSelect.innerHTML = '<option value="">Select Department</option>';
            if (Array.isArray(departments)) {
                departments.forEach(dept => {
                    departmentSelect.innerHTML += `<option value="${escapeHtml(dept.department_id)}">${escapeHtml(dept.department_name)} (${escapeHtml(dept.college_name)})</option>`;
                });
            } else {
                console.error('Departments data is not an array:', departments);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error fetching departments:', error);
            departmentSelect.innerHTML = '<option value="">Error loading departments</option>';
        }
    });

    $.ajax({
        url: '../canteens/get_canteens.php',
        type: 'GET',
        dataType: 'json',
        success: function(canteens) {
            canteenSelect.innerHTML = '<option value="">Select Canteen</option>';
            if (Array.isArray(canteens)) {
                canteens.forEach(canteen => {
                    canteenSelect.innerHTML += `<option value="${escapeHtml(canteen.canteen_id)}">${escapeHtml(canteen.name)}</option>`;
                });
            } else {
                console.error('Canteens data is not an array:', canteens);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error fetching canteens:', error);
            canteenSelect.innerHTML = '<option value="">Error loading canteens</option>';
        }
    });

    $('#addUserModal').modal('show');
}

function toggleAdditionalFields() {
    const role = document.getElementById('add_role').value;
    const programField = document.getElementById('add_programField');
    const departmentField = document.getElementById('add_departmentField');
    const canteenField = document.getElementById('add_canteenField');
    
    programField.style.display = 'none';
    departmentField.style.display = 'none';
    canteenField.style.display = 'none';
    
    document.getElementById('add_program_id').removeAttribute('required');
    document.getElementById('add_department_id').removeAttribute('required');
    document.getElementById('add_canteen_name').removeAttribute('required');
    document.getElementById('add_campus_location').removeAttribute('required');
    
    switch(role) {
        case 'student':
            programField.style.display = 'block';
            document.getElementById('add_program_id').setAttribute('required', '');
            break;
        case 'employee':
            departmentField.style.display = 'block';
            document.getElementById('add_department_id').setAttribute('required', '');
            break;
        case 'manager':
            canteenField.style.display = 'block';
            document.getElementById('add_canteen_name').setAttribute('required', '');
            document.getElementById('add_campus_location').setAttribute('required', '');
            break;
    }
}

function saveUser() {
    const form = document.getElementById('addNewUserForm');
    const formData = new FormData(form);
    
    const params = new URLSearchParams();
    for (const [key, value] of formData.entries()) {
        params.append(key, value);
    }

    $.ajax({
        url: '../users/addUser.php',
        type: 'POST',
        data: params.toString(),
        contentType: 'application/x-www-form-urlencoded',
        success: function(response) {
            if (response === 'success') {
                $('#addUserModal').modal('hide');
                form.reset();
                loadUserTable();
            } else {
                alert('Failed to add user: ' + response);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error details:', {
                status: status,
                error: error,
                response: xhr.responseText
            });
            alert('Failed to add user. Please try again.');
        }
    });
}

function openEditModal(userId) {
    $.ajax({
        url: '../users/fetchUserDetails.php',
        type: 'GET',
        data: { user_id: userId },
        success: function(response) {
            const user = JSON.parse(response);
            $('#editUserId').val(user.user_id);
            $('#edit_last_name').val(user.last_name);
            $('#edit_given_name').val(user.given_name);
            $('#edit_middle_name').val(user.middle_name);
            $('#edit_email').val(user.email);
            $('#edit_username').val(user.username);
            $('#edit_role').val(user.role);
            $('#editUserModal').modal('show');
        }
    });
}

function openDeleteModal(userId) {
    $('#deleteUserId').val(userId);
    $('#deleteUserModal').modal('show');
}

function searchUsers(search, role) {
    $.ajax({
        url: '../users/searchUsers.php',
        type: 'GET',
        data: { 
            search: search,
            role: role 
        },
        success: function(response) {
            try {
                const users = JSON.parse(response);
                let tableBody = '';
                users.forEach(user => {
                    const fullName = `${user.last_name}, ${user.given_name} ${user.middle_name || ''}`.trim();
                    tableBody += `
                        <tr>
                            <td>${escapeHtml(user.user_id)}</td>
                            <td>${escapeHtml(fullName)}</td>
                            <td>${escapeHtml(user.username)}</td>
                            <td>${escapeHtml(user.email)}</td>
                            <td>${escapeHtml(user.role)}</td>
                            <td>${escapeHtml(user.status)}</td>
                            <td>
                                <button class="btn btn-warning btn-sm" onclick="openEditModal(${user.user_id})">Edit</button>
                                <button class="btn btn-danger btn-sm" onclick="openDeleteModal(${user.user_id})">Delete</button>
                            </td>
                        </tr>
                    `;
                });
                $('#userTable tbody').html(tableBody);
            } catch (e) {
                console.error('Error parsing JSON:', e);
            }
        },
        error: function(xhr, status, error) {
            console.error('Search error:', error);
        }
    });
}

function escapeHtml(unsafe) {
    return unsafe
        ? unsafe.toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;")
        : '';
} 