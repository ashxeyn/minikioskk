<?php
session_start();
require_once '../classes/employeeClass.php';

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'manager') {
    echo json_encode([
        'draw' => $_POST['draw'] ?? 1,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'error' => 'Unauthorized access'
    ]);
    exit;
}

try {
    $employee = new Employee();
    $canteenId = $_SESSION['canteen_id'] ?? null;
    
    if (!$canteenId) {
        throw new Exception("Canteen ID not found in session");
    }

    // Get total records count
    $totalRecords = $employee->getTotalEmployeesCount($canteenId);
    
    // Get filtered records count and data
    $search = $_POST['search']['value'] ?? '';
    $start = $_POST['start'] ?? 0;
    $length = $_POST['length'] ?? 10;
    $orderColumn = $_POST['order'][0]['column'] ?? 0;
    $orderDir = $_POST['order'][0]['dir'] ?? 'asc';
    
    $columns = ['user_id', 'name', 'username', 'email', 'status'];
    $orderBy = $columns[$orderColumn] ?? 'user_id';
    
    $result = $employee->getEmployeesForDataTable($canteenId, $search, $start, $length, $orderBy, $orderDir);
    
    // Format data for DataTables
    $data = [];
    foreach ($result['data'] as $employee) {
        $actions = '';
        if ($employee['manager_status'] === 'pending') {
            $actions = sprintf(
                '<div class="btn-group">
                    <button class="btn btn-sm btn-success" onclick="approveEmployee(%d)">
                        <i class="bi bi-check-circle"></i> Approve
                    </button>
                </div>',
                $employee['user_id']
            );
        }

        $data[] = [
            "DT_RowId" => "employee_" . $employee['user_id'],
            "user_id" => $employee['user_id'], // Keep for actions
            "name" => htmlspecialchars($employee['last_name'] . ', ' . 
                     $employee['given_name'] . ' ' . 
                     ($employee['middle_name'] ?? '')),
            "username" => htmlspecialchars($employee['username']),
            "email" => htmlspecialchars($employee['email']),
            "manager_status" => sprintf(
                '<span class="badge %s">%s</span>',
                $employee['manager_status'] === 'accepted' ? 'bg-success' : 
                ($employee['manager_status'] === 'pending' ? 'bg-warning' : 'bg-danger'),
                ucfirst($employee['manager_status'])
            ),
            "actions" => $actions
        ];
    }

    // Return the response in DataTables format
    echo json_encode([
        "draw" => isset($_POST['draw']) ? intval($_POST['draw']) : 0,
        "recordsTotal" => $result['total_count'],
        "recordsFiltered" => $result['filtered_count'],
        "data" => $data
    ]);

} catch (Exception $e) {
    echo json_encode([
        'draw' => $_POST['draw'] ?? 1,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'error' => $e->getMessage()
    ]);
}
?> 