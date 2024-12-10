<?php
require_once '../tools/functions.php';
require_once '../classes/canteenClass.php';
require_once '../classes/accountClass.php';

$accountObj = new Account();
$canteenObj = new Canteen();

$last_name = $given_name = $middle_name = $email = $password = $username = '';
$canteen_name = $campus_location = '';
$response = '';

$lastNameErr = $givenNameErr = $middleNameErr = $passwordErr = $emailErr = $usernameErr = $canteenNameErr = $campusLocationErr = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $last_name = clean_input($_POST['last_name']);
    $given_name = clean_input($_POST['given_name']);
    $middle_name = clean_input($_POST['middle_name']);
    $email = clean_input($_POST['email']);
    $username = clean_input($_POST['username']);
    $password = clean_input($_POST['password']);
    $canteen_name = clean_input($_POST['canteen_name']);
    $campus_location = clean_input($_POST['campus_location']);

    if (empty($last_name)) {
        $lastNameErr = "Last Name is required.";
    }
    if (empty($given_name)) {
        $givenNameErr = "Given Name is required.";
    }
    if (empty($middle_name)) {
        $middleNameErr = "Middle Name is required.";
    }
    if (empty($email)) {
        $emailErr = "Email is required.";
    }
    if (empty($username)) {
        $usernameErr = "Username is required.";
    }
    if (empty($password)) {
        $passwordErr = "Please enter your password.";
    }
    if (empty($canteen_name)) {
        $canteenNameErr = "Canteen Name is required.";
    }
    if (empty($campus_location)) {
        $campusLocationErr = "Campus Location is required.";
    }

    if (
        empty($lastNameErr) && empty($givenNameErr) && empty($emailErr) && empty($usernameErr) &&
        empty($canteenNameErr) && empty($campusLocationErr)
    ) {
        $canteenObj->name = $canteen_name;
        $canteenObj->campus_location = $campus_location;

        $canteenId = $canteenObj->registerCanteen();

        if ($canteenId) {
            $accountObj->last_name = $last_name;
            $accountObj->given_name = $given_name;
            $accountObj->middle_name = $middle_name;
            $accountObj->email = $email;
            $accountObj->username = $username;
            $accountObj->password = $password;

            $isManagerAdded = $accountObj->addPendingManager($canteenId);

            $response = $isManagerAdded ? 'success' : 'failure';
            // if ($response === 'success') {
            //     header('Location: ../customers/customerDashboard.php');
            //     exit;
            // }
        } else {
            $response = 'failure';
        }
    } else {
        $response = 'failure'; 
    }

    //echo $response;
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Canteen and Manager</title>
    <link rel="stylesheet" href="path/to/bootstrap.css"> <!-- Replace with actual Bootstrap path -->
</head>
<body>
    <div class="container mt-5">
        <h2>Register Your Canteen</h2>
        <form action="register.php" method="POST" autocomplete="off">
            <!-- Manager Details -->
            <h4>Manager Details</h4>
            <div class="form-group">
                <label for="last_name">Last Name:</label>
                <input type="text" id="last_name" name="last_name" class="form-control" value="<?= $last_name ?>" required>
                <span class="text-danger"><?= $lastNameErr ?></span>
            </div>
            <div class="form-group">
                <label for="given_name">Given Name:</label>
                <input type="text" id="given_name" name="given_name" class="form-control" value="<?= $given_name ?>" required>
                <span class="text-danger"><?= $givenNameErr ?></span>
            </div>
            <div class="form-group">
                <label for="middle_name">Middle Name:</label>
                <input type="text" id="middle_name" name="middle_name" value="<?= $middle_name ?>" class="form-control">
                <span class="text-danger"><?= $middleNameErr ?></span>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" class="form-control" value="<?= $email ?>" required>
                <span class="text-danger"><?= $emailErr ?></span>
            </div>
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" class="form-control" value="<?= $username ?>" required>
                <span class="text-danger"><?= $usernameErr ?></span>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" class="form-control" value="<?= $password ?>" required>
                <span class="text-danger"><?= $passwordErr ?></span>
            </div>

            <!-- Canteen Details -->
            <h4 class="mt-4">Canteen Details</h4>
            <div class="form-group">
                <label for="canteen_name">Canteen Name:</label>
                <input type="text" id="canteen_name" name="canteen_name" class="form-control" value="<?= $canteen_name ?>"required>
                <span class="text-danger"><?= $canteenNameErr ?></span>
            </div>
            <div class="form-group">
                <label for="campus_location">Campus Location:</label>
                <input type="text" id="campus_location" name="campus_location" class="form-control" value="<?= $campus_location ?>" required>
                <span class="text-danger"><?= $campusLocationErr ?></span>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary mt-3">Register</button>
        </form>
    </div>
</body>
</html>
