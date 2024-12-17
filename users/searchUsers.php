<?php
require_once '../classes/accountClass.php';

header('Content-Type: application/json');

try {
    $search = $_GET['search'] ?? '';
    $role = $_GET['role'] ?? '';

    $account = new Account();
    $results = $account->searchUsers($search, $role);

    // Format the results
    $formattedResults = array_map(function($user) {
        return [
            'user_id' => $user['user_id'],
            'last_name' => $user['last_name'],
            'given_name' => $user['given_name'],
            'middle_name' => $user['middle_name'],
            'email' => $user['email'],
            'username' => $user['username'],
            'role' => $user['role'],
            'created_at' => $user['created_at']
        ];
    }, $results);

    echo json_encode($formattedResults);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?> 