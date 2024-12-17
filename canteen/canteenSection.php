<?php
require_once '../classes/canteenClass.php';
?>

<!-- First add all required CSS -->
<link rel="stylesheet" href="../assets/datatables/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="../assets/datatables/css/responsive.bootstrap5.min.css">

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="text-end mb-3">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCanteenModal">
                    <i class="bi bi-plus-circle"></i> Add Canteen
                </button>
            </div>
            <div class="table-responsive">
                <table id="canteenTable" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Location</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Include modal files -->
<?php 
include 'addCanteenModal.html';
include 'editCanteenModal.html'; 
include 'deleteCanteenModal.html'; 
?>

<!-- Add scripts in correct order -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable directly without checking isDataTable
    let dataTable = $('#canteenTable').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: '../ajax/search_canteens.php',
            type: 'GET',
            dataSrc: function(json) {
                return json.data || [];
            }
        },
        columns: [
            { 
                data: null,
                render: function (data, type, row, meta) {
                    return meta.row + 1;
                }
            },
            { data: 'name' },
            { data: 'campus_location' },
            { 
                data: 'created_at',
                render: function(data) {
                    if (!data) return 'N/A';
                    const date = new Date(data.replace(' ', 'T'));
                    if (isNaN(date.getTime())) return 'Invalid Date';
                    
                    return date.toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                }
            },
            {
                data: 'canteen_id',
                render: function(data) {
                    return `
                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editCanteenModal" onclick="openEditModal(${data})" title="Edit">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteCanteenModal" onclick="openDeleteModal(${data})" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    `;
                }
            }
        ],
        responsive: true,
        order: [[1, 'asc']]
    });

    // Form handlers
    $('#addCanteenForm').on('submit', function(e) {
        e.preventDefault();
        const formData = {
            name: $('#name').val(),
            location: $('#location').val()
        };

        $.post('../canteen/addCanteen.php', formData)
            .done(function(response) {
                if (response === 'success') {
                    $('#addCanteenModal').modal('hide');
                    $('#addCanteenForm')[0].reset();
                    dataTable.ajax.reload();
                    alert('Canteen added successfully!');
                } else {
                    alert('Failed to add canteen');
                }
            })
            .fail(function(xhr, status, error) {
                alert('Error: ' + error);
            });
    });

    $('#editCanteenForm').on('submit', function(e) {
        e.preventDefault();
        const formData = {
            canteen_id: $('#editCanteenId').val(),
            name: $('#edit_name').val(),
            location: $('#edit_location').val()
        };

        $.post('../canteen/editCanteen.php', formData)
            .done(function(response) {
                if (response === 'success') {
                    $('#editCanteenModal').modal('hide');
                    dataTable.ajax.reload();
                    alert('Canteen updated successfully!');
                } else {
                    alert('Failed to update canteen');
                }
            })
            .fail(function(xhr, status, error) {
                console.error('Update error:', error);
                alert('Error updating canteen: ' + error);
            });
    });

    $('#confirmDeleteBtn').on('click', function() {
        const canteenId = $('#deleteCanteenId').val();
        
        $.post('../canteen/deleteCanteen.php', { canteen_id: canteenId })
            .done(function(response) {
                if (response === 'success') {
                    $('#deleteCanteenModal').modal('hide');
                    dataTable.ajax.reload();
                    alert('Canteen deleted successfully!');
                } else {
                    alert('Failed to delete canteen');
                }
            })
            .fail(function(xhr, status, error) {
                alert('Error: ' + error);
            });
    });
});

// Modal functions
function openEditModal(canteenId) {
    $.get('../canteen/fetch_Canteen.php', { canteen_id: canteenId })
        .done(function(response) {
            try {
                const canteen = JSON.parse(response);
                $('#editCanteenId').val(canteen.canteen_id);
                $('#edit_name').val(canteen.name);
                $('#edit_location').val(canteen.campus_location);
            } catch (e) {
                console.error('Error parsing response:', e);
                alert('Error loading canteen data');
            }
        })
        .fail(function(xhr, status, error) {
            alert('Error loading canteen data');
        });
}

function openDeleteModal(canteenId) {
    $('#deleteCanteenId').val(canteenId);
}
</script>