<?php
require_once '../classes/managerClass.php';
require_once '../tools/functions.php';

if (!isset($_SESSION['canteen_id'])) {
    die('Unauthorized access.');
}

$canteenId = $_SESSION['canteen_id'];
$manager = new Manager($canteenId);

// Get initial data
$totalSales = $manager->getTotalSales();
$customerCount = $manager->getCustomerCount();
$completedOrders = $manager->getCompletedOrders();
$topSellingProducts = $manager->getTopSellingProducts();
$monthlySales = $manager->getMonthlySales();
?>

<div class="container-fluid">
    <!-- Filter Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">Filter Analytics</h5>
                    <form id="filterForm" class="row g-3">
                        <div class="col-md-5">
                            <input type="date" id="startDate" class="form-control" required>
                        </div>
                        <div class="col-md-5">
                            <input type="date" id="endDate" class="form-control" required>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-orange w-100">Filter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row mb-4">
        <!-- Customers -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted fw-normal mt-0">Customers</h6>
                            <h3 class="my-2" id="customerCount"><?= number_format($customerCount) ?></h3>
                        </div>
                        <div class="icon-box bg-light-primary">
                            <i class="bi bi-people fs-3 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Completed Orders -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted fw-normal mt-0">Completed Orders</h6>
                            <h3 class="my-2" id="completedOrders"><?= number_format($completedOrders) ?></h3>
                        </div>
                        <div class="icon-box bg-light-success">
                            <i class="bi bi-cart-check fs-3 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Sales -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted fw-normal mt-0">Total Sales</h6>
                            <h3 class="my-2" id="totalSales">₱<?= number_format($totalSales, 2) ?></h3>
                        </div>
                        <div class="icon-box bg-light-info">
                            <i class="bi bi-graph-up fs-3 text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row">
        <!-- Top Selling Products -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Top Selling Products</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Product Name</th>
                                    <th class="text-end">Total Sold</th>
                                </tr>
                            </thead>
                            <tbody id="topSellingProducts">
                                <?php foreach ($topSellingProducts as $product): ?>
                                <tr>
                                    <td><?= htmlspecialchars($product['product_name']) ?></td>
                                    <td class="text-end"><?= number_format($product['total_sold']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Sales Chart -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Monthly Sales Report</h5>
                </div>
                <div class="card-body">
                    <canvas id="monthlySalesChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border: none;
    box-shadow: 0 0 10px rgba(0,0,0,.1);
    margin-bottom: 24px;
}

.icon-box {
    padding: 12px;
    border-radius: 8px;
}

.bg-light-primary {
    background-color: rgba(13, 110, 253, 0.1);
}

.bg-light-success {
    background-color: rgba(25, 135, 84, 0.1);
}

.bg-light-info {
    background-color: rgba(13, 202, 240, 0.1);
}

.card-header {
    background-color: transparent;
    border-bottom: 1px solid rgba(0,0,0,.125);
    padding: 1rem;
}

.table th {
    font-weight: 600;
    background-color: #f8f9fa;
}

.table td, .table th {
    padding: 1rem;
    vertical-align: middle;
}

/* Orange button style */
.btn-orange {
    background-color: #FF6B00;
    border-color: #FF6B00;
    color: white;
    opacity: 0.9;
}

.btn-orange:hover {
    background-color: #E65100;
    border-color: #E65100;
    color: white;
}

.btn-orange:active, .btn-orange:focus {
    background-color: #E65100 !important;
    border-color: #E65100 !important;
    color: white !important;
    box-shadow: 0 0 0 0.25rem rgba(255, 107, 0, 0.25) !important;
}

</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize the monthly sales chart
    const monthlySalesData = <?= json_encode($monthlySales) ?>;
    const ctx = document.getElementById('monthlySalesChart').getContext('2d');
    const months = ['January', 'February', 'March', 'April', 'May', 'June', 
                   'July', 'August', 'September', 'October', 'November', 'December'];
    
    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: months,
            datasets: [{
                label: 'Monthly Sales',
                data: monthlySalesData,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₱' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Handle filter form submission
    document.getElementById('filterForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;

        // Fetch filtered data
        fetch('../ajax/filterAnalytics.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                start_date: startDate,
                end_date: endDate,
                canteen_id: <?= $canteenId ?>
            })
        })
        .then(response => response.json())
        .then(data => {
            // Update stats
            document.getElementById('customerCount').textContent = data.customerCount.toLocaleString();
            document.getElementById('completedOrders').textContent = data.completedOrders.toLocaleString();
            document.getElementById('totalSales').textContent = '₱' + data.totalSales.toLocaleString(undefined, {minimumFractionDigits: 2});

            // Update top selling products
            const tbody = document.getElementById('topSellingProducts');
            tbody.innerHTML = data.topSellingProducts.map(product => `
                <tr>
                    <td>${product.product_name}</td>
                    <td class="text-end">${product.total_sold.toLocaleString()}</td>
                </tr>
            `).join('');

            // Update chart
            chart.data.datasets[0].data = data.monthlySales;
            chart.update();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error fetching filtered data');
        });
    });
});
</script>
