function loadUserTable() {
    $.ajax({
        url: '../users/view_accounts.php',
        type: 'GET',
        success: function(response) {
            $('#accountTable').html(response);
        }
    });
}

function openAddManagerModal() {
    $('#addManagerModal').modal('show');
}

$('#addManagerForm').submit(function (e) {
    e.preventDefault();
    const formData = $(this).serialize();

    $.ajax({
        url: '../users/addManager.php',
        method: 'POST',
        data: formData,
        success: function (response) {
            if (response === 'success') {
                $('#addManagerModal').modal('hide');
                loadUserTable();
            } else {
                alert('Failed to add manager: ' + response);
            }
        },
        error: function () {
            alert('Error occurred while adding the manager.');
        }
    });
});

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

$('#editForm').submit(function(e) {
    e.preventDefault();
    const formData = $(this).serialize();

    $.ajax({
        url: '../users/editUser.php',
        type: 'POST',
        data: formData,
        success: function() {
            $('#editUserModal').modal('hide');
            loadUserTable();
        }
    });
});

function openDeleteModal(userId) {
    $('#deleteUserId').val(userId);
    $('#deleteUserModal').modal('show');
}

$('#deleteForm').submit(function(e) {
    e.preventDefault();
    const userId = $('#deleteUserId').val();

    $.ajax({
        url: '../users/deleteUser.php',
        type: 'POST',
        data: { user_id: userId },
        success: function() {
            $('#deleteUserModal').modal('hide');
            loadUserTable();
        }
    });
});

$(document).ready(function() {
    loadUserTable();
});
