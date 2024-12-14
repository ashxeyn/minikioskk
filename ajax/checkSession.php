<?php
session_start();
require_once '../classes/databaseClass.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'manager') {
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

try {
    if (!isset($_SESSION['canteen_id'])) {
        $db = new Database();
        $conn = $db->connect();
        
        $sql = "SELECT canteen_id FROM managers 
                WHERE user_id = :user_id 
                AND status = 'accepted'";
                
        $stmt = $conn->prepare($sql);
        $stmt->execute(['user_id' => $_SESSION['user_id']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result && isset($result['canteen_id'])) {
            $_SESSION['canteen_id'] = $result['canteen_id'];
            echo json_encode(['success' => true, 'canteen_id' => $result['canteen_id']]);
        } else {
            echo json_encode(['error' => 'No active canteen assignment found']);
        }
    } else {
        echo json_encode(['success' => true, 'canteen_id' => $_SESSION['canteen_id']]);
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} 