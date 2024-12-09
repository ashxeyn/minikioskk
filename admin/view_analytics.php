<?php
require_once '../classes/databaseClass.php';
require_once '../classes/adminClass.php'; 
require_once '../tools/functions.php';

$admin = new Admin();
$reportData = $admin->reports();
$topSellingProducts = $admin->getTopSellingProducts();
$totalOrdersByCollege = $admin->getTotalOrdersByCollege();

$colleges = [];
$orderCounts = [];

foreach ($totalOrdersByCollege as $data) {
    $colleges[] = $data['college'];
    $orderCounts[] = (int)$data['total_orders'];
}
?>

<html>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
        </div>
    </div>
    <div class="row">
        <div class="col-12 col-md-12 col-lg-12 col-xl-12 d-flex flex-column">
            <div class="analytic-cards row flex-grow-1 ">
                <div class="col-12 col-sm-6 col-md-6 col-xl-3 pb-4">
                    <div class="card widget-flat mb-0">
                        <div class="card-body">
                            <div class="float-end me-2">
                                <i class="bi bi-people fs-1  brand-color"></i>
                            </div>
                            <h5 class="card-label fw-normal mt-0" title="Number of Customers">Customers</h5>
                            <h3 class="my-3"><?php echo ($reportData['user_count']); ?></h3> 
                            <p class="mb-0 text-muted">
                                <span class="text-<?php echo $reportData['user_percentage_change'] >= 0 ? 'success' : 'danger'; ?> me-2">
                                    <i class="bi bi-arrow-<?php echo $reportData['user_percentage_change'] >= 0 ? 'up' : 'down'; ?>"></i> 
                                    <?php echo abs($reportData['user_percentage_change']), 2; ?>%
                                </span>
                            </p>
                            <p class="mb-0 text-muted pt-2">
                                <span class="text-nowrap">Since last month</span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-md-6 col-xl-3 pb-4">
                    <div class="card widget-flat mb-0">
                        <div class="card-body">
                            <div class="float-end me-2">
                                <i class="bi bi-cart3 fs-1  brand-color"></i>
                            </div>
                            <h5 class="card-label fw-normal mt-0" title="Number of Orders">Completed orders</h5>
                            <h3 class="my-3"><?php echo ($reportData['order_count']); ?></h3>
                            <p class="mb-0 text-muted">
                                <span class="text-<?php echo $reportData['order_percentage_change'] >= 0 ? 'success' : 'danger'; ?> me-2">
                                    <i class="bi bi-arrow-<?php echo $reportData['order_percentage_change'] >= 0 ? 'up' : 'down'; ?>"></i> 
                                    <?php echo abs($reportData['order_percentage_change']), 2; ?>%
                                </span>
                            </p>
                            <p class="mb-0 text-muted pt-2">
                                <span class="text-nowrap">Since last month</span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-md-6 col-xl-3 pb-4">
                    <div class="card widget-flat mb-0">
                        <div class="card-body">
                            <div class="float-end me-2">
                                <i class="bi bi-graph-up fs-1  brand-color"></i>
                            </div>
                            <h5 class="card-label fw-normal mt-0" title="Number of Canteens">Canteens</h5>
                            <h3 class="my-3"><?php echo ($reportData['canteen_count']); ?></h3> 
                            <p class="mb-0 text-muted">
                                <span class="text-<?php echo $reportData['canteen_percentage_change'] >= 0 ? 'success' : 'danger'; ?> me-2">
                                    <i class="bi bi-arrow-<?php echo $reportData['canteen_percentage_change'] >= 0 ? 'up' : 'down'; ?>"></i> 
                                    <?php echo abs($reportData['canteen_percentage_change']), 2; ?>%
                                </span>
                            </p>
                            <p class="mb-0 text-muted pt-2">
                                <span class="text-nowrap">Since last month</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="tables">
                <div class="table_1 card p-4">
                    <div class="d-flex card-header justify-content-between align-items-center w-100 px-2">
                         <h3 class="header-title mb-0">Top Selling Products</h3>
                    </div>
                <div class="card-body p-1 pt-2">
                    <div class="table-responsive">
                        <table class="table table-centered table-nowrap table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Product Name</th>
                                    <th>Canteen Name</th>
                                    <th>Total Sold</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            if ($topSellingProducts) {
                                foreach ($topSellingProducts as $product) {
                                    echo "<tr>";
                                    echo "<td>" . clean_input($product['product_name']) . "</td>";
                                    echo "<td>" . clean_input($product['canteen_name']) . "</td>";
                                    echo "<td>" . clean_input($product['total_sold']) . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='3' class='text-center'>No data available.</td></tr>";
                            }
                            ?>
                            </tbody>
                        </table>
                    </div> 
                </div>
        </div>
                <div class="table_2 card card-h-100 p-3">
                    <div class="d-flex card-header justify-content-between align-items-center w-100">
                        <h3 class="header-title mb-0">College Monthly Orders</h3>
                    </div>
                <!-- Responsive canvas container -->
                    <div class="chart-container" style="position: relative; width: 100%; height: 100%; margin: 50px auto;">
                        <canvas id="ordersByCollegeChart"></canvas>
                    </div>
                </div>
            </div>
<script id="collegesData" type="application/json"><?php echo json_encode($colleges); ?></script>
<script id="orderCountsData" type="application/json"><?php echo json_encode($orderCounts); ?></script>
</html>

<style>
.analytic-cards {
    display: flex;
    justify-content: center;
    gap: 3rem;
    align-content: center;
    align-items: center;
}

.card-label {
    color: #FF7F11;
}

.my-3 {
    color: #087F8C;
}

.bi {
    color: #FF7F11;
}

.pb-4 {
    margin: 10px;
    padding: 0px !important;
}

.tables {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
}

.table_1 {
    height: 30rem;
    width: 30rem;
}

.table_2 {
    height: 30rem;
    width: 50rem;
}

.card-body.p-1.pt-2{
    overflow-y: scroll;
}

.header-title {
    color: #087F8C;
}

</style>