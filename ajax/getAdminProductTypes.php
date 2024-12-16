<?php
require_once '../classes/adminProductTypeClass.php';

header('Content-Type: application/json');

try {
    $adminProductType = new AdminProductType();
    $types = $adminProductType->getAllProductTypesAdmin();
    
    if (empty($types)) {
        throw new Exception("No product types found");
    }
    
    echo json_encode($types);
} catch (Exception $e) {
    error_log("Error in getAdminProductTypes.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to load product types',
        'details' => $e->getMessage()
    ]);
}
?> 