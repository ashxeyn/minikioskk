<?php
session_start();

require_once '../classes/databaseClass.php';
require_once '../tools/functions.php';
require_once '../classes/accountClass.php';

$username = $password = '';
$accountObj = new Account();
$loginErr = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean_input($_POST['username']);
    $password = clean_input($_POST['password']);

    $user = $accountObj->fetch($username);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];

        if ($user['is_admin']) {
            $_SESSION['role'] = 'admin';
            header('Location: ../admin/adminDashboard.php');
        } elseif ($user['is_manager']) {
            $_SESSION['role'] = 'manager';
            $_SESSION['canteen_id'] = $accountObj->getManagerCanteen($username);
            header('Location: ../manager/managerDashboard.php');
        } elseif ($user['is_employee'] || $user['is_student']) {
            $_SESSION['role'] = $user['is_employee'] ? 'employee' : 'student';
            header('Location: ../customers/customerDashboard.php');
        }
        exit();
    } else {
        $loginErr = 'Invalid username or password.';
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

.login-container {
    background: #ffffff;
    padding: 40px;
    border-radius: 10px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
    width: 300px;
    color: white;
    text-align: center;
    
}

.login-container h2 {
    margin-bottom: 20px;
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
    pointer-events: auto;
    transition: 0.2s ease all;
}

.input-group input:focus ~ label,
.input-group input:not(:placeholder-shown) ~ label {
    top: -15px;
    left: 10px;
    font-size: 12px;
    color: #33363F;
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

.actions button:hover {
    background: #1C666F;
}

.links {
    margin-top: 20px;
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
    <title>Login</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Amaranth">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <form action="login.php" method="post">
            <div class="input-group">
                <input type="text" name="username" placeholder="" value="<?= $username ?>" required>
                <label for="username">Username</label>
            </div>
            <div class="input-group">
                <input type="password" name="password" placeholder="" required>
                <label for="password">Password</label>
                <p class="text-danger"><?= $loginErr ?></p>
            </div>
            <div class="actions">
                 <button type="submit">Sign in</button>
            </div>
        </form>
        <form action="guest.php" method="post">
            <button type="submit">Continue as Guest</button>
        </form>
        <div class="links">
            <p>Don't have an account?</p>
            <a href="signup.php">Sign up here.</a>
        </div>
    </div> 
</body>
</html>
