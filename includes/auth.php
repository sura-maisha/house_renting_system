<?php

/**
 * Authentication Helper Functions
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Start session if not already started
 */
function startSession()
{
    if (session_status() === PHP_SESSION_NONE) {
        // Check if headers have been sent
        if (headers_sent($file, $line)) {
            // Headers already sent, can't start session
            // This usually means there was output before session_start()
            error_log("Warning: Cannot start session - headers already sent in $file on line $line");
            return false;
        }
        @session_start();
    }
    return true;
}

/**
 * Check if user is logged in
 * 
 * @return bool True if user is logged in
 */
function isLoggedIn()
{
    startSession();
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is admin
 * 
 * @return bool True if user is admin
 */
function isAdmin()
{
    startSession();
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Get current user ID
 * 
 * @return int|null User ID or null if not logged in
 */
function getCurrentUserId()
{
    startSession();
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user role
 * 
 * @return string|null User role or null if not logged in
 */
function getCurrentUserRole()
{
    startSession();
    return $_SESSION['role'] ?? null;
}

/**
 * Login user
 * 
 * @param string $email User email
 * @param string $password User password
 * @return array Result array with success status and message
 */
function loginUser($email, $password)
{
    startSession();

    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            return [
                'success' => true,
                'message' => 'Login successful',
                'role' => $user['role']
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Invalid email or password'
            ];
        }
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ];
    }
}

/**
 * Register new user
 * 
 * @param array $data User data (name, email, password, phone, address)
 * @return array Result array with success status and message
 */
function registerUser($data)
{
    try {
        $pdo = getDBConnection();

        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$data['email']]);
        if ($stmt->fetch()) {
            return [
                'success' => false,
                'message' => 'Email already registered'
            ];
        }

        // Hash password
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        // Insert user
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, phone, address) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['name'],
            $data['email'],
            $hashedPassword,
            $data['phone'] ?? null,
            $data['address'] ?? null
        ]);

        return [
            'success' => true,
            'message' => 'Registration successful. Please login.'
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Registration failed: ' . $e->getMessage()
        ];
    }
}

/**
 * Logout user
 */
function logoutUser()
{
    startSession();
    $_SESSION = array();
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    session_destroy();
}

/**
 * Get user by ID
 * 
 * @param int $userId User ID
 * @return array|null User data or null if not found
 */
function getUserById($userId)
{
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT id, name, email, role, phone, address, created_at FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return null;
    }
}
