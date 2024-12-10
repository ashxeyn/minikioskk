<head>
    <link rel="stylesheet" href="../css/style.css?v=1.0">
</head>

<nav class="navbar navbar-dark">
    <div class="container-fluid d-flex justify-content-between align-items-center" style="padding-left: 25px;">
        <!-- Welcome text -->
        <a class="navbar-brand" href="#">Minikiosk</a>

        <!-- Right-aligned navigation options -->
        <div class="ms-auto d-flex align-items-center">
            
            <!-- Register Button -->
            <ul class="navbar-nav ms-3">
                <li class="nav-item">
                    <a class="nav-link text-white" href="../accounts/register.php">Register</a>
                </li>
            </ul>
            <!-- Dropdown Menu -->
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="https://github.com/mdo.png" alt="user" width="30" height="30" class="rounded-circle">
                </a>
                <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
                    <li><a class="dropdown-item" href="../accounts/logout.php">Sign out</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>
