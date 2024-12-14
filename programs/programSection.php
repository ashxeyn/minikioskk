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

<!-- Make sure jQuery is loaded before program.js -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="../js/program.js"></script>
