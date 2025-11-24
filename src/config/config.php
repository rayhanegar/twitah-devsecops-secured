<?php
/**
 * Database Configuration
 * Loads environment variables from Docker environment (passed from .env file)
 * 
 * Single Source of Truth: /home/dso505/sns-devsecops/.env
 */

// Load environment variables with secure defaults
$host = getenv('DB_HOST') ?: 'sns-dso-db';
$user = getenv('DB_USER') ?: 'sns_user';
$pass = getenv('DB_PASSWORD');
$dbname = getenv('DB_NAME') ?: 'twita_db';

// Validate required environment variables
if (empty($pass)) {
    error_log('[CONFIG ERROR] DB_PASSWORD environment variable is not set');
    die('Database configuration error. Please check environment variables.');
}

// Create database connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    error_log('[DB CONNECTION ERROR] ' . $conn->connect_error);
    die('Connection failed: Unable to connect to database');
}

// Set charset to UTF-8 for proper character handling
$conn->set_charset('utf8mb4');
?>
