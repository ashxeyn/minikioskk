<?php
// includes/header.php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Mini Kiosk'; ?></title>
    
    <!-- jQuery -->
    <script src="../assets/jquery/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap CSS -->
    <link href="../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="../assets/datatables/css/jquery.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="../assets/datatables/css/responsive.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="../assets/datatables/css/buttons.dataTables.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/customer-order.css">
    
    <!-- DataTables JS -->
    <script type="text/javascript" src="../assets/datatables/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="../assets/datatables/js/dataTables.responsive.min.js"></script>
    <script type="text/javascript" src="../assets/datatables/js/dataTables.buttons.min.js"></script>
</head>
<body> 