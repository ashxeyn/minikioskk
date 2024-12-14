<?php
session_start();
require_once '../classes/productClass.php';

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'manager') {
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

try {
    $productObj = new Product();
    $canteenId = $_SESSION['canteen_id'] ?? null;
    
    if (!$canteenId) {
        throw new Exception("Canteen ID not found in session");
    }
    
    $products = $productObj->fetchProducts($canteenId);
    
    // Handle DataTables search
    if (!empty($_POST['search']['value'])) {
        $searchTerm = strtolower($_POST['search']['value']);
        $products = array_filter($products, function($product) use ($searchTerm) {
            return stripos(strtolower($product['product_id']), $searchTerm) !== false ||
                   stripos(strtolower($product['name']), $searchTerm) !== false ||
                   stripos(strtolower($product['description']), $searchTerm) !== false ||
                   stripos(strtolower($product['type']), $searchTerm) !== false ||
                   stripos(strtolower($product['type_category']), $searchTerm) !== false ||
                   stripos(strtolower($product['status']), $searchTerm) !== false ||
                   stripos(strtolower($product['stock_quantity']), $searchTerm) !== false;
        });
    }
    
    // Get total records before pagination
    $totalRecords = count($products);
    $filteredRecords = count($products);
    
    // Handle DataTables pagination
    if (isset($_POST['start']) && isset($_POST['length'])) {
        $start = intval($_POST['start']);
        $length = intval($_POST['length']);
        $products = array_slice($products, $start, $length);
    }
    
    // Format data for DataTables
    $data = [];
    foreach ($products as $product) {
        $actions = sprintf(
            '<div class="btn-group">
                <button class="btn btn-sm btn-primary" onclick="editProduct(%d)">
                    <i class="bi bi-pencil"></i>
                </button>
                <button class="btn btn-sm btn-danger" onclick="deleteProduct(%d)">
                    <i class="bi bi-trash"></i>
                </button>
            </div>',
            $product['product_id'],
            $product['product_id']
        );

        $data[] = [
            "DT_RowId" => "product_" . $product['product_id'],
            "product_id" => $product['product_id'], // Keep for actions
            "name" => htmlspecialchars($product['name']),
            "type" => htmlspecialchars($product['type']) . ' (' . htmlspecialchars($product['type_category']) . ')',
            "description" => htmlspecialchars($product['description']),
            "price" => '₱' . number_format($product['price'], 2),
            "stock" => htmlspecialchars($product['stock_quantity']),
            "status" => sprintf(
                '<span class="badge %s">%s</span>',
                $product['status'] === 'available' ? 'bg-success' : 'bg-danger',
                ucfirst($product['status'])
            ),
            "actions" => $actions
        ];
    }
    
    // Return the response in DataTables format
    echo json_encode([
        "draw" => isset($_POST['draw']) ? intval($_POST['draw']) : 0,
        "recordsTotal" => count($products),
        "recordsFiltered" => count($data),
        "data" => $data
    ]);
    
} catch (Exception $e) {
    error_log("Error fetching products: " . $e->getMessage());
    echo json_encode(['error' => 'Error fetching products']);
} 