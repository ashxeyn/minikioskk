<?php
require_once '../classes/accountClass.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        throw new Exception('Invalid request method');
    }

    $user_id = $_POST['user_id'] ?? '';
    if (empty($user_id)) {
        throw new Exception('User ID is required');
    }

    $accountObj = new Account();
    $result = $accountObj->deleteUser($user_id);

    echo json_encode([
        'success' => $result,
        'message' => $result ? 'User deleted successfully' : 'Failed to delete user'
    ]);

} catch (Exception $e) {
    error_log("Error in deleteUser.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
