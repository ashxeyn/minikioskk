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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
                <?php if ($pendingManagers): 
                    $counter = 1 ?>
                    <?php foreach ($pendingManagers as $manager): ?>
                        <tr>
                            <td><?= $counter++?></td>
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

    <!-- Rejection Reason Modal -->
    <div class="modal fade" id="rejectionReasonModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Rejection Reason</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="rejectionForm">
                        <input type="hidden" id="reject_user_id">
                        <div class="mb-3">
                            <label for="rejection_reason" class="form-label">Please provide a reason for rejection:</label>
                            <textarea class="form-control" id="rejection_reason" rows="3" required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="submitRejection()">Submit Rejection</button>
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
        $('#reject_user_id').val(userId);
        $('#rejectionReasonModal').modal('show');
    }

    function submitRejection() {
        const userId = $('#reject_user_id').val();
        const reason = $('#rejection_reason').val();
        
        if (!reason.trim()) {
            alert('Please provide a rejection reason');
            return;
        }

        $.post('../canteen/rejectRegistration.php', { 
            user_id: userId,
            reason: reason 
        })
        .done(function(response) {
            if (response === 'success') {
                $('#rejectionReasonModal').modal('hide');
                showResponseModal('Manager registration rejected successfully!', true);
            } else {
                showResponseModal('Failed to reject manager registration.', false);
            }
        })
        .fail(function(xhr, status, error) {
            showResponseModal('Error: ' + error, false);
        });
    }
    </script>
</body>
</html>
