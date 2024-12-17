<?php
session_start();
require_once '../tools/functions.php';
require_once '../classes/canteenClass.php';
require_once '../classes/registerClass.php';

$registerObj = new RegisterAccount();
$canteenObj = new Canteen();

$last_name = $given_name = $middle_name = $email = $password = $username = '';
$canteen_name = $campus_location = '';
$response = '';

$lastNameErr = $givenNameErr = $middleNameErr = $passwordErr = $emailErr = $usernameErr = $canteenNameErr = $campusLocationErr = '';

$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
unset($_SESSION['error']); // Clear the error after displaying

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
    } elseif (!preg_match("/^[a-zA-Z ]*$/", $last_name)) {
        $lastNameErr = "Only letters and spaces allowed.";
    }

    if (empty($given_name)) {
        $givenNameErr = "Given Name is required.";
    } elseif (!preg_match("/^[a-zA-Z ]*$/", $given_name)) {
        $givenNameErr = "Only letters and spaces allowed.";
    }

    if (!empty($middle_name) && !preg_match("/^[a-zA-Z ]*$/", $middle_name)) {
        $middleNameErr = "Only letters and spaces allowed.";
    }

    if (empty($email)) {
        $emailErr = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $emailErr = "Invalid email format.";
    } elseif (!preg_match("/@wmsu\.edu\.ph$/", $email)) {
        $emailErr = "Must use a WMSU email address (@wmsu.edu.ph).";
    } elseif ($registerObj->emailExist($email)) {
        $emailErr = "Email already exists.";
    }

    if (empty($username)) {
        $usernameErr = "Username is required.";
    } elseif (!preg_match("/^[a-zA-Z0-9_]{3,20}$/", $username)) {
        $usernameErr = "Username must be 3-20 characters and can only contain letters, numbers, and underscores.";
    } elseif ($registerObj->usernameExist($username)) {
        $usernameErr = "Username already exists.";
    }

    if (empty($password)) {
        $passwordErr = "Password is required.";
    } else {
        if (strlen($password) < 8) {
            $passwordErr = "Password must be at least 8 characters long.";
        } elseif (!preg_match("/[A-Z]/", $password)) {
            $passwordErr = "Password must contain at least one uppercase letter.";
        } elseif (!preg_match("/[a-z]/", $password)) {
            $passwordErr = "Password must contain at least one lowercase letter.";
        } elseif (!preg_match("/[0-9]/", $password)) {
            $passwordErr = "Password must contain at least one number.";
        } elseif (!preg_match("/[!@#$%^&*()\-_=+{};:,<.>]/", $password)) {
            $passwordErr = "Password must contain at least one special character.";
        }
    }

    if (empty($canteen_name)) {
        $canteenNameErr = "Canteen Name is required.";
    }

    $valid_locations = ['Main Campus', 'College of Law', 'College of Medicine', 'College of Nursing', 'College of Engineering'];

    if (empty($campus_location)) {
        $campusLocationErr = "Campus Location is required.";
    } elseif (!in_array($campus_location, $valid_locations)) {
        $campusLocationErr = "Invalid campus location selected.";
    }

    if (
        empty($lastNameErr) && empty($givenNameErr) && empty($emailErr) && empty($usernameErr) &&
        empty($canteenNameErr) && empty($campusLocationErr)
    ) {
        try {
            // First add the user
            $registerObj->last_name = $last_name;
            $registerObj->given_name = $given_name;
            $registerObj->middle_name = $middle_name;
            $registerObj->email = $email;
            $registerObj->username = $username;
            $registerObj->password = $password;

            $userId = $registerObj->addUser();

            if (!$userId) {
                throw new Exception(isset($_SESSION['error']) ? $_SESSION['error'] : 'Failed to create user account');
            }

            // Then add the canteen
            $canteenObj->name = $canteen_name;
            $canteenObj->campus_location = $campus_location;

            $canteenId = $canteenObj->registerCanteen();

            if (!$canteenId) {
                throw new Exception('Failed to register canteen');
            }

            // Finally add the manager relationship
            $isManagerAdded = $registerObj->addPendingManager($canteenId);
            
            if (!$isManagerAdded) {
                throw new Exception('Failed to add manager role');
            }

            // If everything succeeded, set session and redirect
            $_SESSION['user_id'] = $userId;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = 'manager';
            $_SESSION['canteen_id'] = $canteenId;
            
            header('Location: pending.php');
            exit;

        } catch (Exception $e) {
            $response = $e->getMessage();
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
    <link href="../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</head>
<body>  
    <div class="register-container">
        <h2>Register Your Canteen</h2>
        
        <!-- Progress Bar -->
        <div class="registration-progress">
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
                    <input type="text" id="middle_name" name="middle_name" placeholder="" value="<?= $middle_name ?>">
                    <label for="middle_name">Middle Name (Optional)</label>
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
                    <select id="campus_location" name="campus_location" required>
                        <option value="" disabled selected>Select Campus Location</option>
                        <option value="Main Campus" <?= $campus_location === 'Main Campus' ? 'selected' : '' ?>>Main Campus</option>
                        <option value="College of Law" <?= $campus_location === 'College of Law' ? 'selected' : '' ?>>College of Law</option>
                        <option value="College of Medicine" <?= $campus_location === 'College of Medicine' ? 'selected' : '' ?>>College of Medicine</option>
                        <option value="College of Nursing" <?= $campus_location === 'College of Nursing' ? 'selected' : '' ?>>College of Nursing</option>
                        <option value="College of Engineering" <?= $campus_location === 'College of Engineering' ? 'selected' : '' ?>>College of Engineering</option>
                    </select>
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

        .registration-progress {
            display: flex;
            justify-content: space-between;
            margin: 20px 0 40px;
            position: relative;
            background: none;
            height: auto;
            padding: 0;
            box-shadow: none;
        }

        .registration-progress::before {
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
            padding: 0;
            margin: 0;
            border: none;
        }

        .step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #BEB7A4;
            color: white;
            line-height: 30px;
            margin: 0 auto 5px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .step.active .step-number {
            background: #087F8C;
        }

        .step-label {
            font-size: 12px;
            color: #33363F;
            margin: 0;
            padding: 0;
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

        .input-group select {
            width: 100%;
            padding: 10px;
            background: none;
            border: 1px solid #BEB7A4;
            border-radius: 5px;
            color: #33363F;
            outline: none;
            cursor: pointer;
        }

        .input-group select:focus {
            border-color: #087F8C;
        }

        .input-group select option {
            background: white;
            color: #33363F;
        }
    </style>

    <script>
        // Initialize modal
        let responseModal;
        document.addEventListener('DOMContentLoaded', function() {
            responseModal = new bootstrap.Modal(document.getElementById('responseModal'));
        });

        // Function to show response messages
        function showResponse(message, success = true) {
            const responseMessage = document.getElementById('responseMessage');
            if (responseMessage) {
                responseMessage.textContent = message;
                responseMessage.className = success ? 'text-success' : 'text-danger';
                responseModal.show();
            }
        }

        function validateStep1() {
            const lastName = document.getElementById('last_name').value;
            const givenName = document.getElementById('given_name').value;
            const middleName = document.getElementById('middle_name').value;
            let isValid = true;
            let errorMessage = '';

            // Validate Last Name
            if (!lastName) {
                errorMessage += 'Last Name is required.\n';
                isValid = false;
            } else if (!/^[a-zA-Z ]*$/.test(lastName)) {
                errorMessage += 'Last Name can only contain letters and spaces.\n';
                isValid = false;
            }

            // Validate Given Name
            if (!givenName) {
                errorMessage += 'Given Name is required.\n';
                isValid = false;
            } else if (!/^[a-zA-Z ]*$/.test(givenName)) {
                errorMessage += 'Given Name can only contain letters and spaces.\n';
                isValid = false;
            }

            // Validate Middle Name (if provided)
            if (middleName && !/^[a-zA-Z ]*$/.test(middleName)) {
                errorMessage += 'Middle Name can only contain letters and spaces.\n';
                isValid = false;
            }

            if (!isValid) {
                showResponse(errorMessage, false);
            }
            return isValid;
        }

        function validateStep2() {
            const email = document.getElementById('email').value;
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            let isValid = true;
            let errorMessage = '';

            // Validate Email
            if (!email) {
                errorMessage += 'Email is required.\n';
                isValid = false;
            } else if (!/^[^\s@]+@wmsu\.edu\.ph$/.test(email)) {
                errorMessage += 'Must use a valid WMSU email address (@wmsu.edu.ph).\n';
                isValid = false;
            }

            // Validate Username
            if (!username) {
                errorMessage += 'Username is required.\n';
                isValid = false;
            } else if (!/^[a-zA-Z0-9_]{3,20}$/.test(username)) {
                errorMessage += 'Username must be 3-20 characters and can only contain letters, numbers, and underscores.\n';
                isValid = false;
            }

            // Validate Password
            if (!password) {
                errorMessage += 'Password is required.\n';
                isValid = false;
            } else {
                if (password.length < 8) {
                    errorMessage += 'Password must be at least 8 characters long.\n';
                    isValid = false;
                }
                if (!/[A-Z]/.test(password)) {
                    errorMessage += 'Password must contain at least one uppercase letter.\n';
                    isValid = false;
                }
                if (!/[a-z]/.test(password)) {
                    errorMessage += 'Password must contain at least one lowercase letter.\n';
                    isValid = false;
                }
                if (!/[0-9]/.test(password)) {
                    errorMessage += 'Password must contain at least one number.\n';
                    isValid = false;
                }
                if (!/[!@#$%^&*()\-_=+{};:,<.>]/.test(password)) {
                    errorMessage += 'Password must contain at least one special character.\n';
                    isValid = false;
                }
            }

            if (!isValid) {
                showResponse(errorMessage, false);
            }
            return isValid;
        }

        function validateStep3() {
            const canteenName = document.getElementById('canteen_name').value;
            const campusLocation = document.getElementById('campus_location').value;
            let isValid = true;
            let errorMessage = '';

            if (!canteenName) {
                errorMessage += 'Canteen Name is required.\n';
                isValid = false;
            }

            if (!campusLocation) {
                errorMessage += 'Please select a Campus Location.\n';
                isValid = false;
            }

            const validLocations = [
                'Main Campus', 
                'College of Law', 
                'College of Medicine', 
                'College of Nursing', 
                'College of Engineering'
            ];
            
            if (!validLocations.includes(campusLocation)) {
                errorMessage += 'Invalid campus location selected.\n';
                isValid = false;
            }

            if (!isValid) {
                showResponse(errorMessage, false);
            }
            return isValid;
        }

        function nextStep(step) {
            // Validate current step before proceeding
            let canProceed = false;
            
            if (step === 2) {
                canProceed = validateStep1();
            } else if (step === 3) {
                canProceed = validateStep2();
            }

            if (canProceed) {
                // Hide all steps
                document.querySelectorAll('.form-step').forEach(el => el.style.display = 'none');
                // Show target step
                document.getElementById('step' + step).style.display = 'block';
                // Update progress bar
                updateProgress(step);
            }
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

        // Add form submit validation
        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            if (!validateStep3()) {
                e.preventDefault();
                return;
            }
        });

        // Real-time password validation
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const errorSpan = this.parentElement.querySelector('.text-danger');
            
            // Reset error message
            errorSpan.textContent = '';
            
            // Check password strength
            if (password.length < 8) {
                errorSpan.textContent = 'Password must be at least 8 characters long.';
            } else if (!/[A-Z]/.test(password)) {
                errorSpan.textContent = 'Password must contain at least one uppercase letter.';
            } else if (!/[a-z]/.test(password)) {
                errorSpan.textContent = 'Password must contain at least one lowercase letter.';
            } else if (!/[0-9]/.test(password)) {
                errorSpan.textContent = 'Password must contain at least one number.';
            } else if (!/[!@#$%^&*()\-_=+{};:,<.>]/.test(password)) {
                errorSpan.textContent = 'Password must contain at least one special character.';
            }
        });
    </script>

    <!-- Response Modal -->
    <div class="modal fade" id="responseModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Validation Message</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="responseMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
