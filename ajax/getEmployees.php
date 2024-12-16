<?php
session_start();
require_once '../classes/employeeClass.php';

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'manager') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

try {
    $employee = new Employee();
    
    // Get the canteen ID from session
    if (!isset($_SESSION['canteen_id'])) {
        throw new Exception("Canteen ID not found in session");
    }

    // Get search parameters from DataTables
    $draw = $_POST['draw'] ?? 1;
    $start = $_POST['start'] ?? 0;
    $length = $_POST['length'] ?? 10;
    $search = $_POST['search']['value'] ?? '';
    $orderColumn = $_POST['order'][0]['column'] ?? 1; // Default to name column
    $orderDir = $_POST['order'][0]['dir'] ?? 'asc';

    // Get total and filtered records
    $totalRecords = $employee->getTotalEmployeesCount($_SESSION['canteen_id']);
    
    // Get the filtered data
    $employees = $employee->fetchCanteenEmployees(
        $_SESSION['canteen_id'],
        $search,
        $start,
        $length,
        $orderColumn,
        $orderDir
    );

    // Format response for DataTables
    echo json_encode([
        'draw' => intval($draw),
        'recordsTotal' => $totalRecords,
        'recordsFiltered' => count($employees),
        'data' => $employees
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Server error: ' . $e->getMessage(),
        'draw' => $_POST['draw'] ?? 1,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => []
    ]);
}
?> 