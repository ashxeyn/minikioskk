<?php
require_once '../classes/productTypeClass.php';

try {
    $productType = new ProductType();
    $types = $productType->getAllProductTypes();
    

    error_log("Fetched product types: " . print_r($types, true));
    

    header('Content-Type: application/json');
    $json = json_encode($types);
    

    if ($json === false) {
        error_log("JSON encode error: " . json_last_error_msg());
        throw new Exception("Failed to encode product types");
    }
    
    echo $json;
} catch (Exception $e) {
    error_log("Error in getProductTypes.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to load product types: ' . $e->getMessage()]);
} 