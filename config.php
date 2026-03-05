<?php
session_start();

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'rsoa_rsoa278_1');
define('DB_PASS', '123456');
define('DB_NAME', 'rsoa_rsoa278_1');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8");

// Timezone
date_default_timezone_set('America/New_York');

// Categories for CNN-style content
$categories = [
    'politics' => 'Politics',
    'business' => 'Business',
    'technology' => 'Technology',
    'health' => 'Health',
    'entertainment' => 'Entertainment',
    'sports' => 'Sports',
    'travel' => 'Travel',
    'world' => 'World News'
];

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);
$current_user = $is_logged_in ? $_SESSION : null;
?>
