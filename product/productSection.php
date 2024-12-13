<div class="container">
    <!-- Alert container -->
    
    
    <!-- Load the product table content here -->
    <div id="productTableContent">
        <?php include 'view_products.php'; ?>
    </div>
</div>

<!-- Include Modals -->
<?php 
include 'addProductModal.html';
include 'editProductModal.html';
include 'deleteProductModal.html';
include 'stockModal.html';
?>

<!-- Include JavaScript -->
<script src="../js/admin.js"></script>
<script src="../js/product.js"></script>
<script src="../js/productOperations.js"></script>
