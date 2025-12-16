<?php
/**
 * Logout Page
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/paths.php';

logoutUser();
header('Location: ' . baseUrl('index.php'));
exit;

