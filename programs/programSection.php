<div class="container">
    <h3>Program Management</h3>
    <div id="programTable">
        <?php include 'view_programs.php'; ?>
    </div>
</div>

<?php 
include 'addProgramModal.html';
include 'editProgramModal.html';
include 'deleteProgramModal.html';
include '../users/messageModals.html';
?>

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">
<script type="text/javascript" charset="utf-8" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.js"></script>

<script src="../js/program.js"></script>
