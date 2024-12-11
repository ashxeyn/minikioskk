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
        <!-- Welcome text -->
        <a class="navbar-brand" href="customerDashboard.php">Minikiosk</a>

        <!-- Right-aligned navigation options -->
        <div class="ms-auto d-flex align-items-center">
            <?php if (!isset($_SESSION['user_id'])): ?>
                <!-- Show these buttons only for guests -->
                <ul class="navbar-nav ms-3 d-flex flex-row">
                    <li class="nav-item me-3">
                        <a class="nav-link text-white" href="../accounts/login.php">Login</a>
                    </li>
                    <li class="nav-item me-3">
                        <a class="nav-link text-white" href="../accounts/signup.php">Sign Up</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-white" href="../accounts/register.php">Register your Canteen</a>
                    </li>
                </ul>
            <?php else: ?>
                <!-- Show these options for logged-in users -->
                <ul class="navbar-nav ms-3 d-flex flex-row align-items-center">
                    <li class="nav-item me-3">
                        <a class="nav-link text-white" href="myCart.php">
                            <i class="bi bi-cart"></i>
                            <span id="cartCount" class="badge bg-danger"></span>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <button class="btn btn-link nav-link dropdown-toggle text-white" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i>
                            <?php echo htmlspecialchars($userInfo['name'] ?? 'User'); ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person"></i> Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../accounts/logout.php"><i class="bi bi-box-arrow-right"></i> Sign out</a></li>
                        </ul>
                    </li>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</nav>
