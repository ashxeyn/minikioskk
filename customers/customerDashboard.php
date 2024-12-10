<?php
session_start();
require_once '../classes/accountClass.php';

// Initialize account object
$account = new Account();

// Check if session user_id exists; if not, handle as guest
if (isset($_SESSION['user_id'])) {
    $account->user_id = $_SESSION['user_id'];
    $userInfo = $account->UserInfo();
} else {
    $userInfo = null; // Or handle logic for a guest user
}

// Default navigation bar file logic
$topNavFile = isset($_SESSION['user_id']) ? '../includes/_topnav.php' : '../includes/_topnav2.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Amaranth">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/customer-cart.css">
    <link rel="stylesheet" href="../css/customer-order.css">
    <link rel="stylesheet" href="../css/customer.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../customers/dashboard_content.js"></script>
</head>
<body>

<!-- Dynamically include the correct top navigation bar -->
<?php require_once $topNavFile; ?>

<div class="container-fluid">
    <div class="row">
        <?php require_once '../includes/_sidebar3.php'; ?>
        <div class="col py-3">
            <div id="contentArea" class="container mt-4">
                <?php if ($userInfo): ?>
                    <p>Welcome back, <?php echo htmlspecialchars($userInfo['name']); ?>!</p>
                <?php else: ?>
                    <p>Welcome, guest! You can explore our features as a guest user.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>
