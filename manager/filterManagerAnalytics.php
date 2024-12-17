<?php
require_once '../classes/managerClass.php';
require_once '../tools/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();

    if (!isset($_SESSION['canteen_id'])) {
        echo json_encode(['error' => 'Unauthorized access.']);
        exit;
    }

    $canteenId = $_SESSION['canteen_id'];
    $manager = new Manager($canteenId);

    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];

    $customerCount = $manager->getCustomerCountByDate($startDate, $endDate);
    $completedOrders = $manager->getCompletedOrdersByDate($startDate, $endDate);
    $totalSales = $manager->getTotalSalesByDate($startDate, $endDate);
    $topSellingProducts = $manager->getTopSellingProductsByDate($startDate, $endDate);
    $monthlySales = $manager->getMonthlySalesByDate($startDate, $endDate);

    echo json_encode([
        'customer_count' => $customerCount,
        'completed_orders' => $completedOrders,
        'total_sales' => $totalSales,
        'top_selling_products' => $topSellingProducts,
        'monthly_sales' => $monthlySales
    ]);
}
?>