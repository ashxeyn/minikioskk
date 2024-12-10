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
    <link rel="stylesheet" href="path/to/bootstrap.css">
    <script> "../js/canteen.js" </script>
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

</body>
</html>
