<?php
/**
 * Database Configuration
 * 
 * Automatically switches between Docker and XAMPP based on USE_DOCKER constant
 */

require_once __DIR__ . '/env.php';

// Database configuration based on environment
if (USE_DOCKER) {
    // Docker configuration
    define('DB_HOST', 'mysql');
    define('DB_NAME', 'rental_db');
    define('DB_USER', 'rental_user');
    define('DB_PASS', 'rental_password');
    define('DB_PORT', '3306');
} else {
    // XAMPP configuration
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'rental_db');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_PORT', '3306');
}

/**
 * Get PDO Database Connection
 * 
 * @return PDO Database connection instance
 */
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}

