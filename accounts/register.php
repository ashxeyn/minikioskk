<?php
session_start();
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

            if ($isManagerAdded) {
                // Set session variables
                $_SESSION['user_id'] = $accountObj->user_id;
                $_SESSION['username'] = $username;
                $_SESSION['role'] = 'manager';
                $_SESSION['canteen_id'] = $canteenId;
                
                // Redirect to pending page
                header('Location: pending.php');
                exit;
            } else {
                $response = 'failure';
            }
        } else {
            $response = 'failure';
        }
    } else {
        $response = 'failure'; 
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Canteen and Manager</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Amaranth">
</head>
<body>  
    <div class="register-container">
        <h2>Register Your Canteen</h2>
        
        <!-- Progress Bar -->
        <div class="progress-bar">
            <div class="step active" data-step="1">
                <div class="step-number">1</div>
                <div class="step-label">Personal</div>
            </div>
            <div class="step" data-step="2">
                <div class="step-number">2</div>
                <div class="step-label">Account</div>
            </div>
            <div class="step" data-step="3">
                <div class="step-number">3</div>
                <div class="step-label">Canteen</div>
            </div>
        </div>

        <form id="registrationForm" action="register.php" method="POST" autocomplete="off">
            <!-- Step 1: Personal Details -->
            <div class="form-step" id="step1">
                <div class="input-group">
                    <input type="text" id="last_name" name="last_name" placeholder="" value="<?= $last_name ?>" required>
                    <label for="last_name">Last Name</label>
                    <span class="text-danger"><?= $lastNameErr ?></span>
                </div>
                <div class="input-group">
                    <input type="text" id="given_name" name="given_name" placeholder="" value="<?= $given_name ?>" required>
                    <label for="given_name">Given Name</label>
                    <span class="text-danger"><?= $givenNameErr ?></span>
                </div>
                <div class="input-group">
                    <input type="text" id="middle_name" name="middle_name" placeholder="" value="<?= $middle_name ?>" required>
                    <label for="middle_name">Middle Name</label>
                    <span class="text-danger"><?= $middleNameErr ?></span>
                </div>
                <div class="actions">
                    <button type="button" onclick="nextStep(2)">Next</button>
                </div>
            </div>

            <!-- Step 2: Account Details -->
            <div class="form-step" id="step2" style="display: none;">
                <div class="input-group">
                    <input type="email" id="email" name="email" placeholder="" value="<?= $email ?>" required>
                    <label for="email">Email</label>
                    <span class="text-danger"><?= $emailErr ?></span>
                </div>
                <div class="input-group">
                    <input type="text" id="username" name="username" placeholder="" value="<?= $username ?>" required>
                    <label for="username">Username</label>
                    <span class="text-danger"><?= $usernameErr ?></span>
                </div>
                <div class="input-group">
                    <input type="password" id="password" name="password" placeholder="" required>
                    <label for="password">Password</label>
                    <span class="text-danger"><?= $passwordErr ?></span>
                </div>
                <div class="actions">
                    <button type="button" onclick="prevStep(1)">Previous</button>
                    <button type="button" onclick="nextStep(3)">Next</button>
                </div>
            </div>

            <!-- Step 3: Canteen Details -->
            <div class="form-step" id="step3" style="display: none;">
                <div class="input-group">
                    <input type="text" id="canteen_name" name="canteen_name" placeholder="" value="<?= $canteen_name ?>" required>
                    <label for="canteen_name">Canteen Name</label>
                    <span class="text-danger"><?= $canteenNameErr ?></span>
                </div>
                <div class="input-group">
                    <input type="text" id="campus_location" name="campus_location" placeholder="" value="<?= $campus_location ?>" required>
                    <label for="campus_location">Campus Location</label>
                    <span class="text-danger"><?= $campusLocationErr ?></span>
                </div>
                <div class="actions">
                    <button type="button" onclick="prevStep(2)">Previous</button>
                    <button type="submit">Register</button>
                </div>
            </div>
        </form>
        
        <div class="links">
            <p>Already have an account?</p>
            <a href="login.php">Sign in here.</a>
        </div>
    </div>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Amaranth', sans-serif;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(-45deg, #FF1B1C, #FF7F11, #BEB7A4, #087F8C);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
            height: 100vh;
        }

        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .register-container {
            background: #ffffff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
            width: 400px;
            color: #33363F;
            text-align: center;
        }

        .progress-bar {
            display: flex;
            justify-content: space-between;
            margin: 20px 0 40px;
            position: relative;
        }

        .progress-bar::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 2px;
            background: #BEB7A4;
            transform: translateY(-50%);
            z-index: 1;
        }

        .step {
            position: relative;
            z-index: 2;
            background: white;
            width: 60px;
            text-align: center;
        }

        .step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #BEB7A4;
            color: white;
            line-height: 30px;
            margin: 0 auto 5px;
        }

        .step.active .step-number {
            background: #087F8C;
        }

        .step-label {
            font-size: 12px;
            color: #33363F;
        }

        .input-group {
            position: relative;
            margin-bottom: 30px;
        }

        .input-group input {
            width: 100%;
            padding: 10px;
            background: none;
            border: 1px solid #BEB7A4;
            border-radius: 5px;
            color: #33363F;
            outline: none;
        }

        .input-group label {
            position: absolute;
            top: 10px;
            left: 10px;
            color: #33363F;
            pointer-events: none;
            transition: 0.2s ease all;
        }

        .input-group input:focus ~ label,
        .input-group input:not(:placeholder-shown) ~ label {
            top: -15px;
            left: 10px;
            font-size: 12px;
            color: #33363F;
        }

        .actions {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .actions button {
            padding: 10px 20px;
            background: #087F8C;
            border: none;
            border-radius: 5px;
            color: white;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .actions button:hover {
            background: #1C666F;
        }

        .text-danger {
            color: red;
            font-size: 10px;
            margin-top: 5px;
            text-align: left;
        }

        .links {
            margin-top: 20px;
        }

        .links a {
            color: #087F8C;
            text-decoration: none;
        }

        .links a:hover {
            text-decoration: underline;
        }
    </style>

    <script>
        function nextStep(step) {
            // Hide all steps
            document.querySelectorAll('.form-step').forEach(el => el.style.display = 'none');
            // Show target step
            document.getElementById('step' + step).style.display = 'block';
            // Update progress bar
            updateProgress(step);
        }

        function prevStep(step) {
            document.querySelectorAll('.form-step').forEach(el => el.style.display = 'none');
            document.getElementById('step' + step).style.display = 'block';
            updateProgress(step);
        }

        function updateProgress(step) {
            // Reset all steps
            document.querySelectorAll('.step').forEach(el => el.classList.remove('active'));
            // Activate current and previous steps
            for (let i = 1; i <= step; i++) {
                document.querySelector(`.step[data-step="${i}"]`).classList.add('active');
            }
        }
    </script>
</body>
</html>
