<head>
    <link rel="stylesheet" href="../css/style.css?v=1.0">
  
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
   
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
       <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>

<nav class="navbar navbar-dark">
    <div class="container-fluid d-flex justify-content-between align-items-center" style="padding-left: 25px;">
       
        <a class="navbar-brand" href="customerDashboard.php">UniEats</a>

       
        <div class="ms-auto d-flex align-items-center">
            <?php if (!isset($_SESSION['user_id'])): ?>
                <ul class="navbar-nav ms-3 d-flex flex-row">
                    <li class="nav-item dropdown">
                        <button class="btn btn-link nav-link dropdown-toggle text-white" type="button" id="authDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i>
                            Account
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="authDropdown">
                            <li><a class="dropdown-item" href="../accounts/login.php"><i class="bi bi-box-arrow-in-right"></i> Login</a></li>
                            <li><a class="dropdown-item" href="../accounts/signup.php"><i class="bi bi-person-plus"></i> Sign Up</a></li>
                            <li><hr class="dropdown-divider"></li>
                        </ul>
                    </li>
                </ul>
            <?php else: ?>
                <ul class="navbar-nav ms-3 d-flex flex-row align-items-center">
                    <li class="nav-item dropdown">
                        <button class="btn btn-link nav-link dropdown-toggle text-white" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i>
                            <?php echo htmlspecialchars($userInfo['username'] ?? 'User'); ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../accounts/logout.php"><i class="bi bi-box-arrow-right"></i> Sign out</a></li>
                            <li><a class="dropdown-item" href="../accounts/register.php"><i class="bi bi-shop"></i> Register your Canteen</a></li>
                        </ul>
                    </li>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</nav>
