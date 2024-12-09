<?php
session_start(); 

require_once '../tools/functions.php';
require_once '../classes/accountClass.php';

$accountObj = new Account();
$signupErr = $usernameErr = $emailErr = '';
$last_name = $given_name = $middle_name = $email = $username = $password = '';
$program_id = null;
$is_student = 0;
$is_employee = 0;
$is_guest = 0;

$programs = $accountObj->fetchPrograms();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $last_name = clean_input($_POST['last_name']);
    $given_name = clean_input($_POST['given_name']);
    $middle_name = clean_input($_POST['middle_name']);
    $email = clean_input($_POST['email']);
    $username = clean_input($_POST['username']);
    $password = clean_input($_POST['password']);

    if ($accountObj->usernameExist($username)) {
        $usernameErr = "Username already exists!";
    }
    elseif (empty($_POST['email'])) {
        $emailErr = "Email is required!.";
    }
    elseif ($accountObj->emailExist($email)) {
        $emailErr = "Email is already registered!";
    }
    elseif (!$accountObj->validateEmail($email)) {
        $signupErr = 'Please use a WMSU email.';
    } else {
        if (isset($_POST['role'])) {
            if ($_POST['role'] === 'student') {
                $is_student = 1;
                $program_id = clean_input($_POST['program_id']);
            } elseif ($_POST['role'] === 'employee') {
                $is_employee = 1;
            }

            if ($accountObj->signup($last_name, $given_name, $middle_name, $email, $username, $password, $is_student, $is_employee, $is_guest, $program_id)) {
                $_SESSION['username'] = $username;
                header('location: ../customers/customerDashboard.php');
                exit;
            } else {
                $signupErr = 'Error creating account. Please try again.';
            }
        }
    }
}

?>

<style>

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: 'Amaranth', sans-serif;
}

p {
    color:#33363F
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
	0% {
		background-position: 0% 50%;
	}
	50% {
		background-position: 100% 50%;
	}
	100% {
		background-position: 0% 50%;
	}
}

.signup-container {
    background: #ffffff;
    padding: 40px;
    border-radius: 10px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
    width: 350px;
    color: white;
}

.signup-container h2 {
    margin-bottom: 20px;
    color: #33363F;
}

.input-group {
    position: relative;
    margin-bottom: 30px;
}

.input-group input, .select-group select {
    width: 100%;
    padding: 10px;
    background: none;
    border: 1px solid #BEB7A4;
    border-radius: 5px;
    color: #33363F;
    outline: none;
}

.input-group label, .select-group label {
    position: absolute;
    top: 50%;
    left: 10px;
    color: #33363F;
    pointer-events: none;
    transition: 0.2s ease all;
    transform: translateY(-50%); /* Center the label vertically */
}

.input-group input:focus ~ label,
.input-group input:not(:placeholder-shown) ~ label,
.select-group select:focus ~ label {
    top: -15px;  /* Move the label up */
    left: 10px;
    font-size: 12px;
    color: #33363F;
    transform: translateY(0); /* Fixes misalignment */
}

.select-group {
    margin-bottom: 30px;
    text-align: left;
}

.select-group select:focus {
    border-color: #087F8C;
    box-shadow: 0 0 5px rgba(8, 127, 140, 0.5);
}

#programSelect {
    margin-top: 10px;
}

.actions button {
    width: 100%;
    padding: 10px;
    background: #087F8C;
    border: none;
    border-radius: 5px;
    color: white;
    cursor: pointer;
    transition: background 0.3s ease;
}

.links {
    margin-top: 20px;
    text-align: center;
}

.links a {
    color: #00aaff;
    text-decoration: none;
    font-size: 14px;
    display: block;
    margin: 5px 0;
}

.links a:hover {
    text-decoration: underline;
}

.text-danger {
    color: red;
    font-size: 10px;
    margin-top: 10px;
    margin-left: 10px;
    text-align: left;
}

</style>


<!DOCTYPE html>
<html lang="en">
<head>
    <title>Sign Up</title>
    <script>
        function toggleProgramSelection(roleSelect) {
            const programSelect = document.getElementById('programSelect');
            if (roleSelect.value === 'student') {
                programSelect.style.display = 'block';
            } else {
                programSelect.style.display = 'none';
            }
        }
    </script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Amaranth">
</head>
<body>
    <div class="signup-container">
        <h2>Signup</h2>
        <form action="signup.php" method="post">
            <div class="input-group">
                <input type="text" name="last_name" placeholder="" value="<?= $last_name ?>" required>
                <label for="last_name">Last Name</label>
            </div>
            <div class="input-group">
                <input type="text" name="given_name" placeholder="" value="<?= $given_name ?>" required>
                <label for="given_name">Given Name</label>
            </div>
            <div class="input-group">
                <input type="text" name="middle_name" placeholder="" value="<?= $middle_name ?>">
                <label for="middle_name">Middle Name</label>
            </div>
            <div class="input-group">
                <input type="email" name="email" placeholder="" value="<?= $email ?>" required>
                <label for="email">Email</label>
                <span class="text-danger"><?= $emailErr ?></span>
            </div>
            <div class="input-group">
                <input type="text" name="username" placeholder="" value="<?= $username ?>" required>
                <label for="username">Username</label>
                <span class="text-danger"><?= $usernameErr ?></span>
            </div>
            <div class="input-group">
                <input type="password" name="password" placeholder="" required>
                <label for="password">Password</label>
            </div>
            <div class="select-group">
                <select name="role" required onchange="toggleProgramSelection(this);">
                    <option value="" disabled selected>Select Role</option>
                    <option value="student" <?= $is_student ? 'selected' : '' ?>>Student</option>
                    <option value="employee" <?= $is_employee ? 'selected' : '' ?>>Employee</option>
                </select>
            </div>
            <div class="select-group">
                <div id="programSelect" style="display: <?= $is_student ? 'block' : 'none'; ?>">
                    <select name="program_id">
                        <option value="" disabled selected>Select Program</option>
                        <?php foreach ($programs as $program): ?>
                            <option value="<?= $program['program_id'] ?>" <?= ($program_id == $program['program_id']) ? 'selected' : '' ?>><?= clean_input($program['program_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <span class="text-danger"><?= $signupErr ?></span>
            <div class="actions">
                <button type="submit">Sign Up</button>
            </div>
        </form>
        <form action="guest.php" method="post">
            <button type="submit">Continue as Guest</button>
        </form>
        <div class="links">
            <p>Already have an account?</p>
            <a href="login.php">Login here.</a>
        </div>
    </div>
</body>
</html>
