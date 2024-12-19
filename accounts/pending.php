<?php
session_start();
require_once '../classes/accountClass.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$account = new Account();
$account->user_id = $_SESSION['user_id'];
$userInfo = $account->UserInfo();

if (!$userInfo) {
    $statusMessage = 'Error retrieving account information. Please try again later.';
    $statusClass = 'error';
    $userInfo = [
        'status' => 'error',
        'role' => '',
        'manager_status' => 'error',
        'rejection_reason' => null
    ];
} else {
    if ($userInfo['role'] === 'manager') {
        if ($userInfo['status'] === 'approved' && $userInfo['manager_status'] === 'accepted') {
            $_SESSION['canteen_id'] = $userInfo['canteen_id'];
            header('Location: ../manager/managerDashboard.php');
            exit;
        }
        
        if ($userInfo['status'] === 'rejected' || $userInfo['manager_status'] === 'rejected') {
            $statusMessage = 'Your application has been rejected.';
            $statusClass = 'rejected';
        } else {
            $statusMessage = 'Your account is pending approval from the administrator.';
            $statusClass = 'pending';
        }
    } else {
        // Handle non-manager roles
        if ($userInfo['status'] === 'rejected') {
            $statusMessage = 'Your account has been rejected.';
            $statusClass = 'rejected';
        } else if ($userInfo['status'] === 'pending') {
            $statusMessage = 'Your account is pending approval.';
            $statusClass = 'pending';
        } else {
            $statusMessage = 'Your account status is: ' . ucfirst($userInfo['status']);
            $statusClass = $userInfo['status'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Status</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Amaranth">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Amaranth', sans-serif;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(-45deg, #FF1B1C, #FF7F11, #BEB7A4, #087F8C);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
        }

        .pending-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 400px;
            width: 90%;
        }

        .status-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }

        .pending .status-icon {
            color: #FF7F11;
        }

        .rejected .status-icon {
            color: #FF1B1C;
        }

        .error .status-icon {
            color: #dc3545;
        }

        h1 {
            color: #33363F;
            margin-bottom: 20px;
        }

        .message {
            color: #666;
            margin-bottom: 30px;
            line-height: 1.5;
        }

        .logout-btn {
            background: #087F8C;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background 0.3s ease;
        }

        .logout-btn:hover {
            background: #065f6a;
        }

        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .rejection-details {
            text-align: center;
            margin-bottom: 20px;
        }

        .rejection-reason {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            color: #721c24;
        }

        .rejection-reason h4 {
            margin-bottom: 10px;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="pending-container">
        <div class="<?= htmlspecialchars($statusClass) ?>">
            <div class="status-icon">
                <?php if ($userInfo['status'] === 'rejected' || $userInfo['manager_status'] === 'rejected'): ?>
                    ❌
                <?php elseif ($userInfo['status'] === 'error'): ?>
                    ⚠️
                <?php else: ?>
                    ⏳
                <?php endif; ?>
            </div>
            <?php if ($userInfo['status'] === 'rejected' || $userInfo['manager_status'] === 'rejected'): ?>
                <div class="rejection-details">
                    <h1>Application Rejected</h1>
                    <p class="message"><?= htmlspecialchars($statusMessage) ?></p>
                    <?php if (!empty($userInfo['rejection_reason'])): ?>
                        <div class="rejection-reason">
                            <h4>Reason for Rejection:</h4>
                            <p><?= htmlspecialchars($userInfo['rejection_reason']) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <h1><?= htmlspecialchars(ucfirst($userInfo['status'])) ?></h1>
                <p class="message"><?= htmlspecialchars($statusMessage) ?></p>
            <?php endif; ?>
            <a href="logout.php" class="logout-btn">Omkie po</a>
        </div>
    </div>
</body>
</html> 