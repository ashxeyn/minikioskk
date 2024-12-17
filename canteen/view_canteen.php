<?php
require_once '../classes/canteenClass.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Canteens</title>
    <!-- CSS files -->
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/datatables/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="../assets/datatables/css/responsive.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Add jQuery first -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Use CDN Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Then other JavaScript files -->
    <script src="../assets/datatables/js/jquery.dataTables.min.js"></script>
    <script src="../assets/datatables/js/dataTables.bootstrap5.min.js"></script>
    <script src="../assets/datatables/js/dataTables.responsive.min.js"></script>
    <script src="../assets/datatables/js/responsive.bootstrap5.min.js"></script>
</head>
<body>
    <h3>Manage Canteens</h3>
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <div class="text-end mb-3">
                    <button type="button" class="btn btn-primary" onclick="openAddCanteenModal()">
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

    <!-- Include all modal files -->
    <?php 
    include 'addCanteenModal.html';
    include 'editCanteenModal.html'; 
    include 'deleteCanteenModal.html'; 
    ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var addModal = new bootstrap.Modal(document.getElementById('addCanteenModal'));
        var editModal = new bootstrap.Modal(document.getElementById('editCanteenModal'));
        var deleteModal = new bootstrap.Modal(document.getElementById('deleteCanteenModal'));

        let dataTable;

        function initializeDataTable() {
            if ($.fn.DataTable.isDataTable('#canteenTable')) {
                $('#canteenTable').DataTable().destroy();
            }

            dataTable = $('#canteenTable').DataTable({
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
                            return new Date(data).toLocaleString();
                        }
                    },
                    {
                        data: 'canteen_id',
                        render: function(data) {
                            return `
                                <button class="btn btn-warning btn-sm" onclick="openEditModal(${data})" title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button class="btn btn-danger btn-sm" onclick="openDeleteModal(${data})" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            `;
                        }
                    }
                ],
                order: [[1, 'asc']],
                pageLength: 10,
                responsive: true,
                language: {
                    emptyTable: 'No canteens found',
                    zeroRecords: 'No matching canteens found'
                },
                columnDefs: [
                    {
                        targets: [0, 4],
                        searchable: false,
                        orderable: false
                    }
                ]
            });
        }

        // Add Canteen form submission
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
                        
                    } else {
                        alert('Failed to add canteen');
                    }
                })
                .fail(function(xhr, status, error) {
                    alert('Error: ' + error);
                });
        });

        // Edit Canteen form submission
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
                     
                    } else {
                        alert('Failed to update canteen');
                    }
                })
                .fail(function(xhr, status, error) {
                    alert('Error: ' + error);
                });
        });

        // Delete Canteen handler
        $('#confirmDeleteBtn').on('click', function() {
            const canteenId = $('#deleteCanteenId').val();
            
            $.post('../canteen/deleteCanteen.php', { canteen_id: canteenId })
                .done(function(response) {
                    if (response === 'success') {
                        $('#deleteCanteenModal').modal('hide');
                        dataTable.ajax.reload();
                     
                    } else {
                        alert('Failed to delete canteen');
                    }
                })
                .fail(function(xhr, status, error) {
                    alert('Error: ' + error);
                });
        });

        // Modal opening functions
        window.openAddCanteenModal = function() {
            const addModal = document.getElementById('addCanteenModal');
            const bsModal = new bootstrap.Modal(addModal);
            bsModal.show();
        };

        window.openEditModal = function(canteenId) {
            $.get('../canteen/fetch_Canteen.php', { canteen_id: canteenId })
                .done(function(response) {
                    try {
                        const canteen = JSON.parse(response);
                        $('#editCanteenId').val(canteen.canteen_id);
                        $('#edit_name').val(canteen.name);
                        $('#edit_location').val(canteen.campus_location);
                        
                        const editModal = document.getElementById('editCanteenModal');
                        const bsModal = new bootstrap.Modal(editModal);
                        bsModal.show();
                    } catch (e) {
                        console.error('Error parsing response:', e);
                        alert('Error loading canteen data');
                    }
                })
                .fail(function(xhr, status, error) {
                    console.error('Error fetching canteen:', error);
                    alert('Error loading canteen data');
                });
        };

        window.openDeleteModal = function(canteenId) {
            $('#deleteCanteenId').val(canteenId);
            const deleteModal = document.getElementById('deleteCanteenModal');
            const bsModal = new bootstrap.Modal(deleteModal);
            bsModal.show();
        };

        // Initialize table
        initializeDataTable();
    });
    </script>
</body>
</html>

