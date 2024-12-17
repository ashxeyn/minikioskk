<?php
session_start();
require_once '../classes/managerClass.php';

if (!isset($_SESSION['canteen_id']) || !isset($_POST['start_date']) || !isset($_POST['end_date'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameters']);
    exit;
}

try {
    $canteenId = $_SESSION['canteen_id'];
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];
    
    $manager = new Manager($canteenId);
    
    $response = [
        'customer_count' => $manager->getCustomerCountByDate($startDate, $endDate),
        'completed_orders' => $manager->getCompletedOrdersByDate($startDate, $endDate),
        'total_sales' => $manager->getTotalSalesByDate($startDate, $endDate),
        'top_selling_products' => $manager->getTopSellingProductsByDate($startDate, $endDate),
        'monthly_sales' => $manager->getMonthlySalesByDate($startDate, $endDate)
    ];
    
    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error: ' . $e->getMessage()]);
}
?>