<?php
require_once '../classes/accountClass.php';
?>

<head>
<link rel="stylesheet" href="../css/style.css">
</head>


    <div class="col-auto col-md-3 col-xl-2 px-sm-2 px-0">
        <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 text-white min-vh-100">
            <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start" id="menu">
                <li class="side-nav-title">Home</li>
                <li class="nav-item">
                    <a href="managerDashboard.php" class="nav-link align-middle px-0">
                        <i class="fs-5 bi-house"></i>
                        <span class="ms-1 d-none d-sm-inline">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link align-middle px-0" id="orderButton" onclick="loadOrdersSection()">
                        <i class="fs-5 bi-table"></i>
                        <span class="ms-1 d-none d-sm-inline">Orders</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link align-middle px-0" id="productButton" onclick="loadProductsSection()">
                        <i class="fs-5 bi-box-seam"></i>
                        <span class="ms-1 d-none d-sm-inline">Products</span>
                    </a>
                </li>
                <li class="side-nav-title">Management</li>    
                <li class="nav-item">
                    <a href="#" class="nav-link align-middle px-0" id="employeeButton" onclick="loadEmployeesSection()">
                        <i class="fs-5 bi-people"></i>
                        <span class="ms-1 d-none d-sm-inline">Co-Employees</span>
                    </a>
                </li>
            </ul>
            <hr>
        </div>
    </div>