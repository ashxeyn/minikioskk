<?php
session_start();

session_destroy();

header('Location: ../customers/customerDashboard.php');
exit; 
