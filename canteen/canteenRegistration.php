<?php
require_once '../classes/accountClass.php';
require_once '../tools/functions.php';

$accountObj = new Account();
$pendingManagers = $accountObj->getPendingManagers();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Approve Registrations</title>
    \
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h2>Pending Manager Registrations</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Last Name</th>
                    <th>Given Name</th>
                    <th>Middle Name</th>
                    <th>Email</th>
                    <th>Canteen</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($pendingManagers): ?>
                    <?php foreach ($pendingManagers as $manager): ?>
                        <tr>
                            <td><?= clean_input($manager['user_id']) ?></td>
                            <td><?= clean_input($manager['last_name']) ?></td>
                            <td><?= clean_input($manager['given_name']) ?></td>
                            <td><?= clean_input($manager['middle_name']) ?></td>
                            <td><?= clean_input($manager['email']) ?></td>
                            <td><?= clean_input($manager['canteen_name']) ?></td>
                            <td>
                                <button class="btn btn-success" onclick="approveManager(<?= $manager['user_id'] ?>)">Approve</button>
                                <button class="btn btn-danger" onclick="rejectManager(<?= $manager['user_id'] ?>)">Reject</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No pending registrations</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Response Modal -->
    <div class="modal fade" id="responseModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Status Update</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="responseMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Action</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="confirmMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmAction">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    function showResponseModal(message, success) {
        const responseMessage = $('#responseMessage');
        responseMessage.text(message);
        
        if (success) {
            responseMessage.removeClass('text-danger').addClass('text-success');
        } else {
            responseMessage.removeClass('text-success').addClass('text-danger');
        }
        
        const modal = new bootstrap.Modal(document.getElementById('responseModal'));
        modal.show();
        
        // Auto reload after successful action
        if (success) {
            setTimeout(() => {
                location.reload();
            }, 1500);
        }
    }

    function approveManager(userId) {
        $.post('../canteen/approveRegistration.php', { user_id: userId }, function(response) {
            if (response === 'success') {
                showResponseModal('Manager registration approved successfully!', true);
            } else {
                showResponseModal('Failed to approve manager registration.', false);
            }
        });
    }

    function rejectManager(userId) {
        // Show confirmation modal
        const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
        $('#confirmMessage').text('Are you sure you want to reject this registration?');
        $('#confirmAction').off('click').on('click', function() {
            confirmModal.hide();
            
            $.post('../canteen/rejectRegistration.php', { user_id: userId }, function(response) {
                if (response === 'success') {
                    showResponseModal('Manager registration rejected successfully!', true);
                } else {
                    showResponseModal('Failed to reject manager registration.', false);
                }
            });
        });
        confirmModal.show();
    }
    </script>
</body>
</html>
