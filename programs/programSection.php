<div class="container">
    <h3>Program Management</h3>
    <div id="programTable">
        <?php include 'view_programs.php'; ?>
    </div>
</div>

<!-- Include Modals -->
<?php 
include 'addProgramModal.html';
include 'editProgramModal.html';
include 'deleteProgramModal.html';
include '../users/messageModals.html'; // For success/error messages
?>

<!-- Include Required Libraries -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.js"></script>

<!-- Include JavaScript -->
<script src="../js/program.js"></script>
