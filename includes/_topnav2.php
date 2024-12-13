<head>
    <link rel="stylesheet" href="../css/style.css?v=1.0">
    <!-- Add Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Add Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Add Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>

<nav class="navbar navbar-dark">
    <div class="container-fluid d-flex justify-content-between align-items-center" style="padding-left: 25px;">
        <a class="navbar-brand" href="customerDashboard.php">UniEats</a>

        <div class="ms-auto d-flex align-items-center">
            <?php if (!isset($_SESSION['user_id'])): ?>
                <ul class="navbar-nav ms-3 d-flex flex-row">
                    <li class="nav-item me-3">
                        <a class="nav-link text-white" href="../accounts/login.php">Login</a>
                    </li>
                    <li class="nav-item me-3">
                        <a class="nav-link text-white" href="../accounts/signup.php">Sign Up</a>
                    </li>
                </ul>
            <?php else: ?>
                <ul class="navbar-nav ms-3 d-flex flex-row align-items-center">
                    <li class="nav-item">
                        <a class="nav-link text-white" href="../accounts/register.php">Register Canteen</a>
                    </li>
                    <li class="nav-item dropdown">
                    <div class="dropdown">
                        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="https://ashallendesign.co.uk/blog/13-placeholder-avatar-and-image-websites" alt="sheesh" width="30" height="30" class="rounded-circle">
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
                            <li><a class="dropdown-item" href="../accounts/logout.php">Sign out</a></li>
                        </ul>
                    </div>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</nav>
