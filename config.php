<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); 
define('DB_PASS', ''); // 
define('DB_NAME', 'social_lite');

// Create connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Start session
session_start();
?>