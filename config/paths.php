<?php
/**
 * Path Configuration
 * Handles base URL for both Docker and XAMPP environments
 */

// Check if we're in Docker (document root is /var/www/html/public)
$isDocker = (isset($_SERVER['DOCUMENT_ROOT']) && strpos($_SERVER['DOCUMENT_ROOT'], '/var/www/html/public') !== false);

// Get the script directory relative to document root
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$scriptDir = dirname($scriptName);

if ($isDocker) {
    // In Docker, document root is /var/www/html/public
    // All PHP files are directly accessible (index.php, login.php, etc.)
    // Assets are accessible via /assets/ (configured in Apache)
    $basePath = '';
} else {
    // XAMPP or other setup - calculate based on script location
    if (strpos($scriptDir, '/admin') !== false || strpos($scriptDir, '\\admin') !== false) {
        // We're in admin directory, go up 2 levels to project root
        $basePath = dirname(dirname($scriptDir));
    } elseif (strpos($scriptDir, '/public') !== false || strpos($scriptDir, '\\public') !== false) {
        // We're in public directory, go up 1 level to project root
        $basePath = dirname($scriptDir);
    } elseif ($scriptDir === '/' || $scriptDir === '\\' || $scriptDir === '.') {
        // We're at document root
        $basePath = '';
    } else {
        // Fallback
        $basePath = $scriptDir;
    }
}

// Normalize base path
if ($basePath === '/' || $basePath === '\\' || $basePath === '.') {
    $basePath = '';
} else {
    $basePath = rtrim(str_replace('\\', '/', $basePath), '/');
}

// Define base URL constant
define('BASE_PATH', $basePath);

/**
 * Get full URL path
 * 
 * @param string $path Relative path from project root
 * @return string Full path
 */
function baseUrl($path = '') {
    $path = ltrim($path, '/');
    
    // In Docker, document root is /var/www/html/public
    // - Assets: /assets/css/main.css (via Apache alias)
    // - PHP files: /index.php, /login.php, etc. (directly in public)
    // - Admin: /admin/dashboard.php (but admin is outside public, so we need ../admin)
    
    if (BASE_PATH === '') {
        // Docker setup
        if (strpos($path, 'assets/') === 0) {
            // Assets are aliased to /assets/
            return '/' . $path;
        } elseif (strpos($path, 'admin/') === 0) {
            // Admin files need special handling - they're outside public directory
            // For now, we'll need to access them differently or move them
            // Actually, in Docker with public as docroot, admin files won't be accessible
            // We need to either:
            // 1. Move admin to public/admin
            // 2. Create symlinks
            // 3. Configure Apache to allow access
            // For now, return as-is and we'll handle it
            return '/' . $path;
        } elseif (strpos($path, 'public/') === 0) {
            // Remove 'public/' prefix in Docker since we're already in public
            return '/' . substr($path, 7);
        }
        return '/' . $path;
    }
    return BASE_PATH . '/' . $path;
}
