<?php
/**
 * Error Handler Configuration
 * Prevents output before headers to avoid session/header errors
 */

// Set error reporting (hide warnings in production, show in development)
// For production, use: error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
// For development, use: error_reporting(E_ALL);
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

// Don't display errors on screen (log them instead)
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', '/var/log/php_errors.log');

// Custom error handler to prevent output before headers
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    // Only handle warnings and notices that might break headers
    if ($errno === E_WARNING || $errno === E_NOTICE) {
        // Log the error but don't output it
        error_log("PHP $errno: $errstr in $errfile on line $errline");
        return true; // Suppress the error
    }
    return false; // Let PHP handle other errors
}

// Set custom error handler
set_error_handler('customErrorHandler');

