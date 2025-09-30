<?php
// Database configuration for XAMPP (MySQL)
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'crop_site';

try {
    // Connect to MySQL server first
    $mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS);
    if ($mysqli->connect_errno) {
        throw new Exception('DB connection failed: ' . $mysqli->connect_error);
    }

    // Create database if it doesn't exist
    $mysqli->query("CREATE DATABASE IF NOT EXISTS `$DB_NAME` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $mysqli->select_db($DB_NAME);

    // Create users table if not exists
    $createUsers = "
    CREATE TABLE IF NOT EXISTS users (
      id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      username VARCHAR(50) NOT NULL UNIQUE,
      email VARCHAR(120) NOT NULL UNIQUE,
      password_hash VARCHAR(255) NOT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";

    if (!$mysqli->query($createUsers)) {
        throw new Exception('Failed creating users table: ' . $mysqli->error);
    }

} catch (Exception $e) {
    error_log('Database error: ' . $e->getMessage());
    http_response_code(500);
    die('Database connection error. Please check if XAMPP MySQL is running.');
}

// Helper to sanitize input (basic)
function input($key) {
    return isset($_POST[$key]) ? trim($_POST[$key]) : '';
}
