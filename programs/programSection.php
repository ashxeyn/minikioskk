<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}
?>

<!DOCTYPE html> 
<html lang="en">
<head>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap5.min.css">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div id="programTable">
            <?php include 'view_programs.php'; ?>
        </div>
    </div>

    <?php include 'program_modals.php'; ?>

    <!-- Load scripts in correct order -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/vendor/datatables-1.11.5/js/jquery.dataTables.min.js"></script>
<script src="/vendor/datatables-1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="/vendor/datatables-1.11.5/js/dataTables.responsive.min.js"></script>
<script src="/vendor/datatables-1.11.5/js/responsive.bootstrap5.min.js"></script>
    <!-- Custom JS - Must be loaded after jQuery and Bootstrap -->
    <script src="../js/program.js"></script>
    
    <!-- Initialize after all scripts are loaded -->
    <script>
        $(document).ready(function() {
            if (typeof initializeDataTable === 'function') {
                setTimeout(initializeDataTable, 100);
            }
            if (typeof initializeModals === 'function') {
                initializeModals();
            }
        });
    </script>
</body>
</html>
