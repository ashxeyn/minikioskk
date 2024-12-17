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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniEats</title>
    
    <!-- CSS -->
    <link href="../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/datatables/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Amaranth">
    
    <style>
    * {
        font-family: 'Amaranth', sans-serif;
    }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div id="programTable">
            <?php include 'view_programs.php'; ?>
        </div>
    </div>

    <?php include 'program_modals.php'; ?>

    <!-- JavaScript -->
    <script src="../assets/jquery/jquery.min.js"></script>
    <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/datatables/js/jquery.dataTables.min.js"></script>
    <script src="../assets/datatables/js/dataTables.bootstrap5.min.js"></script>
    
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
