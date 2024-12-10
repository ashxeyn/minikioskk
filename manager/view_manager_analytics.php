<?php
require_once '../classes/managerClass.php';
require_once '../tools/functions.php';

if (!isset($_SESSION['canteen_id'])) {
    die('Unauthorized access.');
}

$canteenId = $_SESSION['canteen_id'];
$manager = new Manager($canteenId);

$totalSales = $manager->getTotalSales();
$customerCount = $manager->getCustomerCount();
$completedOrders = $manager->getCompletedOrders();
$topSellingProducts = $manager->getTopSellingProducts();
$monthlySales = $manager->getMonthlySales();
?>

<html>
<head>
    <title>Manager Analytics</title>
</head>
<body>
<div id="monthlySalesData" style="display:none;"><?php echo json_encode($monthlySales); ?></div>
<div class="container-fluid">
    <div class="row justify-content-center my-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted">Filter Analytics</h5>
                    <form id="filterForm">
                        <div class="row">
                            <div class="col-md-5">
                                <input type="date" id="startDate" name="start_date" class="form-control" required>
                            </div>
                            <div class="col-md-5">
                                <input type="date" id="endDate" name="end_date" class="form-control" required>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Filter</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center my-4">
        <div class="col-12 col-sm-6 col-md-6 col-xl-3 pb-4">
            <div class="card widget-flat mb-0">
                <div class="card-body">
                    <div class="float-end me-2">
                        <i class="bi bi-people fs-1 brand-color"></i>
                    </div>
                    <h5 class="text-muted fw-normal mt-0">Customers</h5>
                    <h3 id="customerCount"><?php echo $customerCount; ?></h3>
                </div>
            </div>
        </div>
        <!-- Completed Orders Section -->
        <div class="col-12 col-sm-6 col-md-6 col-xl-3 pb-4">
            <div class="card widget-flat mb-0">
                <div class="card-body">
                    <div class="float-end me-2">
                        <i class="bi bi-cart3 fs-1 brand-color"></i>
                    </div>
                    <h5 class="text-muted fw-normal mt-0">Completed Orders</h5>
                    <h3 id="completedOrders"><?php echo $completedOrders; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-6 col-xl-3 pb-4">
            <div class="card widget-flat mb-0">
                <div class="card-body">
                    <div class="float-end me-2">
                        <i class="bi bi-graph-up fs-1 brand-color"></i>
                    </div>
                    <h5 class="text-muted fw-normal mt-0">Total Sales</h5>
                    <h3 id="totalSales">₱<?php echo number_format($totalSales, 2); ?></h3>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-6 col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Top Selling Products</h4>
                </div>
                <div class="card-body">
                    <table class="table table-centered table-nowrap">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Total Sold</th>
                            </tr>
                        </thead>
                        <tbody id="topSellingProducts">
                            <?php foreach ($topSellingProducts as $product): ?>
                                <tr>
                                    <td><?php echo clean_input($product['product_name']); ?></td>
                                    <td><?php echo clean_input($product['total_sold']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Monthly Sales Report</h4>
                </div>
                <div class="card-body">
                    <canvas id="monthlySalesChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
