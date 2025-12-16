<?php
/**
 * Header Template
 */
require_once __DIR__ . '/../config/paths.php';
startSession();
$currentPage = basename($_SERVER['PHP_SELF']);
$isLoggedIn = isLoggedIn();
$isAdmin = isAdmin();
$userName = $_SESSION['user_name'] ?? 'Guest';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>Rental Platform</title>
    <link rel="stylesheet" href="<?php echo baseUrl('assets/css/main.css'); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="<?php echo baseUrl('index.php'); ?>">🏠 Rental Platform</a>
                </div>
                <nav class="nav">
                    <a href="<?php echo baseUrl('index.php'); ?>" class="<?php echo $currentPage === 'index.php' ? 'active' : ''; ?>">Home</a>
                    <a href="<?php echo baseUrl('listings.php'); ?>" class="<?php echo $currentPage === 'listings.php' ? 'active' : ''; ?>">Listings</a>
                    <?php if ($isLoggedIn): ?>
                        <?php if (!$isAdmin): ?>
                            <a href="<?php echo baseUrl('create-listing.php'); ?>" class="<?php echo $currentPage === 'create-listing.php' ? 'active' : ''; ?>">Create Listing</a>
                        <?php endif; ?>
                        <a href="<?php echo baseUrl('profile.php'); ?>" class="<?php echo $currentPage === 'profile.php' ? 'active' : ''; ?>">Profile</a>
                        <?php if ($isAdmin): ?>
                            <a href="<?php echo baseUrl('admin/dashboard.php'); ?>" class="<?php echo strpos($currentPage, 'admin') !== false ? 'active' : ''; ?>">Admin</a>
                        <?php endif; ?>
                        <div class="user-menu">
                            <span class="user-name"><?php echo htmlspecialchars($userName); ?></span>
                            <a href="<?php echo baseUrl('logout.php'); ?>" class="btn-logout">Logout</a>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo baseUrl('login.php'); ?>" class="btn-login">Login</a>
                        <a href="<?php echo baseUrl('register.php'); ?>" class="btn-register">Register</a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>
    <main class="main-content">

