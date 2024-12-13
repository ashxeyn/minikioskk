<?php
require_once '../classes/accountClass.php';
?>

    <div class="col-auto col-md-3 col-xl-2 px-sm-2 px-0">
        <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 text-white min-vh-100">
            <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start" id="menu">
                <li class="side-nav-title">Home</li>
                <li class="nav-item">
                    <a href="adminDashboard.php" class="nav-link align-middle px-0">
                        <i class="fs-5 bi-house"></i>
                        <span class="ms-1 d-none d-sm-inline">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="adminDashboard.php?page=orders" class="nav-link align-middle px-0">
                        <i class="fs-5 bi-table"></i>
                        <span class="ms-1 d-none d-sm-inline">Orders</span>
                    </a>
                </li>
                <li class="side-nav-title">Settings</li>
                <li class="nav-item">
                    <a href="#" class="nav-link align-middle px-0" id="canteenButton" onclick="loadCanteenSection()">
                        <i class="fs-5 bi-shop"></i>
                        <span class="ms-1 d-none d-sm-inline">Canteen</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link align-middle px-0" id="accountsButton" onclick="loadAccountsSection()">
                        <i class="fs-5 bi-person"></i>
                        <span class="ms-1 d-none d-sm-inline">Accounts</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link align-middle px-0" id="productButton" onclick="loadProductsSection()">
                        <i class="fs-5 bi-box-seam"></i>
                        <span class="ms-1 d-none d-sm-inline">Products</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link align-middle px-0" id="programButton" onclick="loadProgramSection()">
                        <i class="fs-5 bi-collection"></i>
                        <span class="ms-1 d-none d-sm-inline">Programs</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link align-middle px-0" id="registrationButton" onclick="loadRegistrationSection()">
                        <i class="fs-5 bi-collection"></i>
                        <span class="ms-1 d-none d-sm-inline">Registrations</span>
                    </a>
                </li>
            </ul>
            <hr>
        </div>
    </div>