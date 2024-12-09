<?php
require_once '../classes/databaseClass.php';

function createAdminAccount() {
    $db = new Database();
    $connection = $db->connect();

    $admin_email = '';
    $admin_password = 'admin123';
    $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

    $admin_username = '';
    $last_name = '';
    $given_name = '';
    $middle_name = '';

    $sql = "INSERT INTO Users (email, password, username, last_name, given_name, middle_name, is_admin)
            VALUES ('$admin_email', '$hashed_password', '$admin_username', '$last_name', '$given_name', '$middle_name', 1)";

    if ($connection->query($sql) === TRUE) {
        echo "Admin account created successfully.";
    } else {
        echo "Failed to create admin account. Error: " . $connection->error;
    }
}

//createAdminAccount();
?>
