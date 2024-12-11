<?php
if (!empty($_SERVER['HTTPS']) && ('on' == $_SERVER['HTTPS'])) {
    $uri = 'https://';
} else {
    $uri = 'http://';
}

$uri .= $_SERVER['HTTP_HOST'];

$uri .= $_SERVER["REQUEST_URI"];

header('Location: ' . $url . "customers/customerDashboard.php");

exit;
