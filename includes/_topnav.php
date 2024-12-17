<?php
require_once '../classes/accountClass.php';


$userInfo = [];
if (isset($_SESSION['user_id'])) {
    $accountObj = new Account();
    $userInfo = $accountObj->getUserById($_SESSION['user_id']);
}
?>

<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/style.css">
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</head>

<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">UniEats</a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav ms-auto">
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> Account
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="../accounts/login.php">
                                <i class="bi bi-box-arrow-in-right"></i> Login
                            </a></li>
                            <li><a class="dropdown-item" href="../accounts/signup.php">
                                <i class="bi bi-person-plus"></i> Sign Up
                            </a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i>
                            <?php echo htmlspecialchars($userInfo['username'] ?? 'User'); ?>
                            <?php if (isset($userInfo['role'])): ?>
                                <small>(<?php echo htmlspecialchars(ucfirst($userInfo['role'])); ?>)</small>
                            <?php endif; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'user'): ?>
                                <li><a class="dropdown-item" href="../accounts/register.php">
                                    <i class="bi bi-shop"></i> Register your Canteen
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="../accounts/logout.php">
                                <i class="bi bi-box-arrow-right"></i> Sign out
                            </a></li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var dropdowns = document.querySelectorAll('.dropdown-toggle');
    dropdowns.forEach(function(dropdown) {
        dropdown.addEventListener('click', function(event) {
            event.preventDefault();
            var dropdownMenu = this.nextElementSibling;
            dropdownMenu.classList.toggle('show');
        });
    });
});
</script>

<style>
.dropdown-menu {
    margin-top: 0;
}

.navbar-nav .dropdown-menu {
    position: absolute;
}

@media (max-width: 991.98px) {
    .navbar-nav .dropdown-menu {
        position: static;
    }
}

.navbar {
    padding: 0.5rem 1rem;
}

.nav-link {
    padding: 0.5rem 1rem;
}

.dropdown-item {
    padding: 0.5rem 1rem;
}

.dropdown-item i {
    margin-right: 0.5rem;
}
</style>
