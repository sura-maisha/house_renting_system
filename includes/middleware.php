<?php
/**
 * Middleware Functions for Access Control
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/paths.php';

/**
 * Require user to be logged in
 * Redirects to login page if not authenticated
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . baseUrl('login.php'));
        exit;
    }
}

/**
 * Require user to be admin
 * Redirects to home page if not admin
 */
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: ' . baseUrl('index.php'));
        exit;
    }
}

/**
 * Redirect to home if already logged in
 * Used on login/register pages
 */
function redirectIfLoggedIn() {
    if (isLoggedIn()) {
        if (isAdmin()) {
            header('Location: ' . baseUrl('admin/dashboard.php'));
        } else {
            header('Location: ' . baseUrl('index.php'));
        }
        exit;
    }
}

/**
 * Sanitize input data
 * 
 * @param string $data Input data
 * @return string Sanitized data
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Validate email format
 * 
 * @param string $email Email address
 * @return bool True if valid
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate file upload (image)
 * 
 * @param array $file $_FILES array element
 * @return array Result with success status and message
 */
function validateImageUpload($file) {
    // Check for upload errors first
    if (!isset($file['error'])) {
        return ['success' => false, 'message' => 'File upload error: No error code provided'];
    }
    
    // Handle different upload error codes
    switch ($file['error']) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return ['success' => false, 'message' => 'File size exceeds the maximum allowed size of 5MB.'];
        case UPLOAD_ERR_PARTIAL:
            return ['success' => false, 'message' => 'File was only partially uploaded.'];
        case UPLOAD_ERR_NO_FILE:
            return ['success' => false, 'message' => 'No file was uploaded.'];
        case UPLOAD_ERR_NO_TMP_DIR:
            return ['success' => false, 'message' => 'Missing temporary folder.'];
        case UPLOAD_ERR_CANT_WRITE:
            return ['success' => false, 'message' => 'Failed to write file to disk.'];
        case UPLOAD_ERR_EXTENSION:
            return ['success' => false, 'message' => 'File upload stopped by extension.'];
    }
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'File upload error (code: ' . $file['error'] . ')'];
    }
    
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return ['success' => false, 'message' => 'Invalid file upload.'];
    }
    
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type. Only images (JPEG, PNG, GIF, WebP) are allowed.'];
    }
    
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'File size exceeds 5MB limit.'];
    }
    
    return ['success' => true];
}

