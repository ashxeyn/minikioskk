<?php
session_start();
require_once '../classes/managerClass.php';

header('Content-Type: application/json');

try {
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($_SESSION['canteen_id'])) {
        throw new Exception('Unauthorized access');
    }

    $manager = new Manager($_SESSION['canteen_id']);
    
    $startDate = $data['start_date'];
    $endDate = $data['end_date'];

    $response = [
        'customerCount' => $manager->getCustomerCountByDate($startDate, $endDate),
        'completedOrders' => $manager->getCompletedOrdersByDate($startDate, $endDate),
        'totalSales' => $manager->getTotalSalesByDate($startDate, $endDate),
        'topSellingProducts' => $manager->getTopSellingProductsByDate($startDate, $endDate),
        'monthlySales' => $manager->getMonthlySalesByDate($startDate, $endDate)
    ];

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} 