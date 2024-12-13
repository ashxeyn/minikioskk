<?php
require_once '../classes/accountClass.php';

$search = $_GET['search'] ?? '';
$role = $_GET['role'] ?? '';

$account = new Account();
$results = $account->searchUsers($search, $role);

header('Content-Type: application/json');
echo json_encode($results);
?> 