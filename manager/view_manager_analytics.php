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
$topSellingProductsManager = $manager->getTopSellingProducts();
$monthlySales = $manager->getMonthlySales();  
?>

<html>
<head>
    <title>Manager Analytics</title>
    
</head>
<body>

<div class="container-fluid">
    <div class="row justify-content-center my-4">
        <!-- Customers Section -->
        <div class="col-12 col-sm-6 col-md-6 col-xl-3 pb-4">
            <div class="card widget-flat mb-0">
                <div class="card-body">
                    <div class="float-end me-2">
                        <i class="bi bi-people fs-1 brand-color"></i>
                    </div>
                    <h5 class="text-muted fw-normal mt-0" title="Number of Customers">Customers</h5>
                    <h3 class="my-3"><?php echo $customerCount; ?></h3>
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
                    <h5 class="text-muted fw-normal mt-0" title="Number of Orders">Completed Orders</h5>
                    <h3 class="my-3"><?php echo $completedOrders; ?></h3>
                </div>
            </div>
        </div>

        <!-- Total Sales Section -->
        <div class="col-12 col-sm-6 col-md-6 col-xl-3 pb-4">
            <div class="card widget-flat mb-0">
                <div class="card-body">
                    <div class="float-end me-2">
                        <i class="bi bi-graph-up fs-1 brand-color"></i>
                    </div>
                    <h5 class="text-muted fw-normal mt-0" title="Total Sales" style="color: teal;">Total Sales</h5>
                    <h3 class="my-3"><?php echo clean_input($totalSales, 2); ?></h3>
                </div>
            </div>
        </div>

    <!-- Data Sections -->
    <div class="row">
        <!-- Top Selling Products Section -->
        <div class="col-lg-6 col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 style="color: teal;">Top Selling Products</h4>
                </div>
                <div class="card-body">
                    <table class="table table-centered table-nowrap">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Total Sold</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($topSellingProductsManager) {
                                foreach ($topSellingProductsManager as $product) {
                                    echo "<tr>";
                                    echo "<td>" . clean_input($product['product_name']) . "</td>";
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

        <!-- Monthly Sales Report Section -->
        <div class="col-lg-6 col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 style="color: teal;">Monthly Sales Report</h4>
                </div>
                <div class="card-body">
                    <canvas id="monthlySalesChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const ctx = document.getElementById('monthlySalesChart').getContext('2d');

        const months = <?php echo json_encode(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']); ?>;
        const salesData = <?php echo json_encode($monthlySales); ?>;

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    label: 'Monthly Sales',
                    data: salesData,
                    borderColor: '#FFA500',
                    backgroundColor: 'rgba(255, 165, 0, 0.5)',
                    borderWidth: 2,
                    tension: 0.3 // Smooth curves
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Month'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Sales Amount (PHP)'
                        },
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function (tooltipItem) {
                                return `₱ ${tooltipItem.raw.toLocaleString()}`;
                            }
                        }
                    }
                }
            }
        });
    });
</script>

</body>
</html>
