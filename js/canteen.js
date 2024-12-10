

function loadCanteenTable() {
    $.ajax({
        url: '../canteen/view_canteen.php',
        method: 'GET',
        success: function (response) {
            $('#canteenTable').html(response);
            initializeCanteenButtons(); // Initialize buttons after loading the table
        },
        error: function () {
            $('#canteenTable').html('<p class="text-danger">Failed to load canteen table.</p>');
        }
    });
}

function openAddCanteenModal() {
    $('#addCanteenModal').modal('show');
}

$('#addCanteenForm').submit(function (e) {
    e.preventDefault();
    const formData = $(this).serialize();

    $.ajax({
        url: '../canteen/addCanteen.php',
        method: 'POST',
        data: formData,
        success: function (response) {
            if (response === 'success') {
                $('#addCanteenModal').modal('hide');
                loadCanteenTable();
            } else {
                alert('Failed to add canteen: ' + response);
            }
        },
        error: function () {
            alert('Error occurred while adding the canteen.');
        }
    });
});

function openEditModal(canteen_id) {
    $.ajax({
        url: '../canteen/fetch_Canteen.php',
        method: 'GET',
        data: { canteen_id: canteen_id },
        success: function (response) {
            const data = JSON.parse(response);
            $('#editCanteenId').val(data.canteen_id);
            $('#editCanteenName').val(data.name);
            $('#editCampusLocation').val(data.campus_location);
            $('#editCanteenModal').modal('show');
        },
        error: function () {
            alert('Failed to fetch canteen details.');
        }
    });
}

$('#editCanteenForm').submit(function (e) {
    e.preventDefault();
    const formData = $(this).serialize();
    $.ajax({
        url: '../canteen/editCanteen.php',
        method: 'POST',
        data: formData,
        success: function () {
            $('#editCanteenModal').modal('hide');
            loadCanteenTable();
        },
        error: function () {
            alert('Failed to edit canteen.');
        }
    });
});

function openDeleteModal(canteen_id) {
    $('#deleteCanteenId').val(canteen_id);
    $('#deleteCanteenModal').modal('show');
}

$('#confirmDeleteCanteen').click(function () {
    const canteen_id = $('#deleteCanteenId').val();
    $.ajax({
        url: '../canteen/deleteCanteen.php',
        method: 'POST',
        data: { canteen_id: canteen_id },
        success: function () {
            $('#deleteCanteenModal').modal('hide');
            loadCanteenTable();
        },
        error: function () {
            alert('Failed to delete canteen.');
        }
    });
});

function approveManager(userId) {
    $.post('../canteen/approveRegistration.php', { user_id: userId }, function(response) {
        if (response === 'success') {
            location.reload();
        } else {
            alert('Failed to approve manager. Please try again.');
        }
    });
}

function rejectManager(userId) {
    if (confirm('Are you sure you want to reject this registration?')) {
        $.post('../canteen/rejectRegistration.php', { user_id: userId }, function(response) {
            if (response === 'success') {
                location.reload();
            } else {
                alert('Failed to reject manager. Please try again.');
            }
        });
    }
}

function initializeCanteenButtons() {
    // Initialize edit buttons
    let editButtons = document.querySelectorAll('.editBtn');
    editButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            let canteenId = this.dataset.id;
            openEditModal(canteenId);
        });
    });

    // Initialize delete buttons
    let deleteButtons = document.querySelectorAll('.deleteBtn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            let canteenId = this.dataset.id;

            if (confirm("Do you want to delete this canteen?")) {
                $.ajax({
                    url: '../canteen/deleteCanteen.php',
                    method: 'POST',
                    data: { canteen_id: canteenId },
                    success: function (response) {
                        if (response === 'success') {
                            loadCanteenTable();
                        } else {
                            alert('Failed to delete canteen: ' + response);
                        }
                    },
                    error: function () {
                        alert('Error occurred while deleting the canteen.');
                    }
                });
            }
        });
    });
}

$(document).ready(function () {
    loadCanteenTable();
});
