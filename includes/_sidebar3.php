<?php
require_once '../classes/accountClass.php';
?>

    <div class="col-auto col-md-3 col-xl-2 px-sm-2 px-0">
        <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 text-white min-vh-100">
            <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start" id="menu">
                <li class="side-nav-title">Home</li>
                <li class="nav-item">
                    <a href="customerDashboard.php" class="nav-link align-middle px-0">
                        <i class="fs-5 bi-house"></i>
                        <span class="ms-1 d-none d-sm-inline">Home</span>
                    </a>
                </li>
                <li class="side-nav-title">Order</li>
                <li class="nav-item">
                    <a href="#" class="nav-link align-middle px-0" id="cartButton" onclick="loadCartSection()">
                        <i class="fs-5 bi-box-seam"></i>
                        <span class="ms-1 d-none d-sm-inline">My Cart</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link align-middle px-0" id="statusButton" onclick="loadOrderStatusSection()">
                        <i class="fs-5 bi-table"></i>
                        <span class="ms-1 d-none d-sm-inline">Status</span>
                    </a>
                </li>
            </ul>
            <hr>
        </div>
    </div>