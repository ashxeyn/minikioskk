<?php
session_start(); 

require_once '../tools/functions.php';
require_once '../classes/accountClass.php';

$accountObj = new Account();
$signupErr = $usernameErr = $emailErr = '';
$last_name = $given_name = $middle_name = $email = $username = $password = '';
$program_id = $department_id = null;
$role = isset($_POST['role']) ? $_POST['role'] : '';

$programs = $accountObj->fetchPrograms();
$departments = $accountObj->fetchDepartments();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $last_name = clean_input($_POST['last_name']);
    $given_name = clean_input($_POST['given_name']);
    $middle_name = clean_input($_POST['middle_name']);
    $email = clean_input($_POST['email']);
    $username = clean_input($_POST['username']);
    $password = clean_input($_POST['password']);
    $role = clean_input($_POST['role']);

   
    $is_student = ($role === 'student') ? 1 : 0;
    $is_employee = ($role === 'employee') ? 1 : 0;
    $is_guest = ($role === 'guest') ? 1 : 0;
    
    
    if ($role === 'student' && empty($_POST['program_id'])) {
        $signupErr = "Please select a program";
        throw new Exception($signupErr);
    }

 
    if ($role === 'employee' && empty($_POST['department_id'])) {
        $signupErr = "Please select a department";
        throw new Exception($signupErr);
    }

    
    $program_id = null;
    if ($role === 'student' && !empty($_POST['program_id'])) {
        $program_id = clean_input($_POST['program_id']);
    }

    $department_id = null;
    if ($role === 'employee' && !empty($_POST['department_id'])) {
        $department_id = clean_input($_POST['department_id']);
    }

    try {
        // Email validation for wmsu.edu.ph domain (except for guests)
        if ($role !== 'guest') {
            if (!preg_match('/@wmsu\.edu\.ph$/', $email)) {
                $emailErr = "Please use your WMSU email (@wmsu.edu.ph)";
                throw new Exception($emailErr);
            }
        }

        if ($accountObj->signup(
            $last_name,
            $given_name,
            $middle_name,
            $email,
            $username,
            $password,
            $is_student,
            $is_employee,
            $is_guest,
            $program_id,
            $department_id
        )) {
            $_SESSION['user_id'] = $accountObj->user_id;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $is_student ? 'student' : ($is_employee ? 'employee' : 'guest');
            header('Location: ../customers/customerDashboard.php');
            exit;
        } else {
            $signupErr = "Error creating account. Please try again.";
        }
    } catch (Exception $e) {
        $signupErr = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Sign Up</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Amaranth">
</head>
<body>
    <div class="signup-container">
        <h2>Sign Up</h2>
        <form action="" method="POST" autocomplete="off">
            <div class="role-group">
                <label>Select Role:</label>
                <div class="radio-group">
                    <div class="radio-item">
                        <input type="radio" id="student" name="role" value="student" 
                            <?= ($role === 'student') ? 'checked' : '' ?> 
                            onchange="toggleProgramSelect(this.value)" required>
                        <label for="student">Student</label>
                    </div>
                    <div class="radio-item">
                        <input type="radio" id="employee" name="role" value="employee" 
                            <?= ($role === 'employee') ? 'checked' : '' ?> 
                            onchange="toggleProgramSelect(this.value)">
                        <label for="employee">Employee</label>
                    </div>
                    <div class="radio-item">
                        <input type="radio" id="guest" name="role" value="guest" 
                            <?= ($role === 'guest') ? 'checked' : '' ?> 
                            onchange="toggleProgramSelect(this.value)">
                        <label for="guest">Guest</label>
                    </div>
                </div>
            </div>

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
                <input type="email" id="email" name="email" value="<?= $email ?>" required>
                <label for="email">Email</label>
                <span class="text-danger"><?= $emailErr ?></span>
                <small class="form-text text-muted" id="emailHelp">
                    <?php if ($role !== 'guest'): ?>
                        Must be a valid @wmsu.edu.ph email address
                    <?php endif; ?>
                </small>
            </div>
            
             <div id="programSelect" class="input-group" style="display: <?= ($role === 'student') ? 'block' : 'none' ?>;">
                <select name="program_id" <?= ($role === 'student') ? 'required' : '' ?>>
                    <option value="" disabled selected>Select Program</option>
                    <?php foreach ($programs as $program): ?>
                        <option value="<?= $program['program_id'] ?>" 
                            <?= ($program_id == $program['program_id']) ? 'selected' : '' ?>>
                            <?= clean_input($program['program_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
            </div>

            <div id="departmentSelect" class="input-group" style="display: <?= ($role === 'employee') ? 'block' : 'none' ?>;">
                <select name="department_id" <?= ($role === 'employee') ? 'required' : '' ?>>
                    <option value="" disabled selected>Select Department</option>
                    <?php foreach ($departments as $department): ?>
                        <option value="<?= $department['department_id'] ?>" 
                            <?= ($department_id == $department['department_id']) ? 'selected' : '' ?>>
                            <?= clean_input($department['department_name']) ?> 
                            (<?= clean_input($department['college_abbreviation']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
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

           

            <span class="text-danger"><?= $signupErr ?></span>
            <div class="actions">
                <button type="submit">Sign Up</button>
            </div>
        </form>

        <div class="links">
            <p>Already have an account?</p>
            <a href="login.php">Login here.</a>
        </div>
    </div>

    <script>
        function toggleProgramSelect(role) {
            const programSelect = document.getElementById('programSelect');
            const departmentSelect = document.getElementById('departmentSelect');
            const emailHelp = document.getElementById('emailHelp');
            
            if (role === 'student') {
                programSelect.style.display = 'block';
                programSelect.querySelector('select').required = true;
                departmentSelect.style.display = 'none';
                departmentSelect.querySelector('select').required = false;
            } else if (role === 'employee') {
                programSelect.style.display = 'none';
                programSelect.querySelector('select').required = false;
                departmentSelect.style.display = 'block';
                departmentSelect.querySelector('select').required = true;
            } else {
                programSelect.style.display = 'none';
                programSelect.querySelector('select').required = false;
                departmentSelect.style.display = 'none';
                departmentSelect.querySelector('select').required = false;
            }
            
            if (role === 'guest') {
                emailHelp.style.display = 'none';
            } else {
                emailHelp.style.display = 'block';
                emailHelp.textContent = 'Must be a valid @wmsu.edu.ph email address';
            }
        }
    </script>

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
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            width: 400px;
            color: white;
        }

        .signup-container h2 {
            margin-bottom: 25px;
            color: #212529;
            font-size: 24px;
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

        #programSelect, #departmentSelect {
            margin-top: 20px;
            transition: all 0.3s ease;
        }

        #programSelect select, #departmentSelect select {
            width: 100%;
            padding: 10px;
            border: 1px solid #BEB7A4;
            border-radius: 5px;
            color: #33363F;
            background-color: white;
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

        .role-group {
            margin-bottom: 30px;
            padding: 15px;
            background: #F8F9FA;
            border-radius: 8px;
            border: 1px solid #E9ECEF;
        }

        .role-group label {
            color: #212529;
            font-size: 14px;
            display: block;
            margin-bottom: 12px;
        }

        .radio-group {
            display: flex;
            gap: 25px;
            margin: 10px 0;
        }

        .radio-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .radio-item input[type="radio"] {
            appearance: none;
            -webkit-appearance: none;
            width: 16px;
            height: 16px;
            border: 2px solid #087F8C;
            border-radius: 50%;
            outline: none;
            cursor: pointer;
        }

        .radio-item input[type="radio"]:checked {
            background-color: #087F8C;
            border: 2px solid #087F8C;
            box-shadow: inset 0 0 0 3px #fff;
        }

        .radio-item label {
            color: #495057;
            font-size: 14px;
            margin: 0;
            cursor: pointer;
        }

        #programSelect {
            margin-top: 20px;
            transition: all 0.3s ease;
        }

        #programSelect select {
            width: 100%;
            padding: 10px;
            border: 1px solid #BEB7A4;
            border-radius: 5px;
            color: #33363F;
            background-color: white;
        }


        #departmentSelect {
            margin-top: 20px;
            transition: all 0.3s ease;
        }

        #departmentSelect select {
            width: 100%;
            padding: 10px;
            border: 1px solid #BEB7A4;
            border-radius: 5px;
            color: #33363F;
            background-color: white;
        }


    </style>
</body>
</html>
