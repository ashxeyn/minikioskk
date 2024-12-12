document.addEventListener('DOMContentLoaded', function() {
    // Load initial user table
    loadUserTable();

    // Setup form submission handlers
    setupFormHandlers();
});

function loadUserTable() {
    $.ajax({
        url: '../users/view_accounts.php',
        type: 'GET',
        success: function(response) {
            $('#accountTable').html(response);
            // Initialize search after loading table
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
    // Add User form submission
    $(document).on('submit', '#addUserForm', function(e) {
        e.preventDefault();
        saveUser();
    });

    // Edit User form submission
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

    // Delete User form submission
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
    // Reset form
    document.getElementById('addUserForm').reset();
    
    // Show loading state
    const programSelect = document.getElementById('program_id');
    const canteenSelect = document.getElementById('canteen_id');
    programSelect.innerHTML = '<option value="">Loading programs...</option>';
    canteenSelect.innerHTML = '<option value="">Loading canteens...</option>';
    
    // Populate programs dropdown
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

    // Populate canteens dropdown
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
    const role = document.getElementById('role').value;
    const programField = document.getElementById('programField');
    const canteenField = document.getElementById('canteenField');
    
    programField.style.display = (role === 'student' || role === 'employee') ? 'block' : 'none';
    canteenField.style.display = (role === 'manager') ? 'block' : 'none';
    
    if (role === 'student' || role === 'employee') {
        document.getElementById('program_id').required = true;
    } else {
        document.getElementById('program_id').required = false;
    }
    
    if (role === 'manager') {
        document.getElementById('canteen_id').required = true;
    } else {
        document.getElementById('canteen_id').required = false;
    }
}

function saveUser() {
    const form = document.getElementById('addUserForm');
    
    // Use jQuery serialize to properly get all form data
    const formData = new FormData(form);
    
    $.ajax({
        url: '../users/addUser.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            console.log('Server response:', response); // Debug log
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
            alert('Error occurred while adding the user.');
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