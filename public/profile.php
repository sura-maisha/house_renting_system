<?php

/**
 * User Profile Page
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/middleware.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();
require_once __DIR__ . '/../config/paths.php';
if (isAdmin()) {
    header('Location: ' . baseUrl('admin/dashboard.php'));
    exit;
}

$pdo = getDBConnection();
$userId = getCurrentUserId();

// Get user data
$user = getUserById($userId);

// Get user's listings
$stmt = $pdo->prepare("
    SELECT l.*, 
           (SELECT image_path FROM listing_images WHERE listing_id = l.id LIMIT 1) as first_image
    FROM listings l
    WHERE l.user_id = ?
    ORDER BY l.created_at DESC
");
$stmt->execute([$userId]);
$listings = $stmt->fetchAll();

$error = '';
$success = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = sanitizeInput($_POST['name'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $address = sanitizeInput($_POST['address'] ?? '');

    if (empty($name)) {
        $error = 'Name is required';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, address = ? WHERE id = ?");
            $stmt->execute([$name, $phone, $address, $userId]);
            $success = 'Profile updated successfully';
            $user = getUserById($userId);
            $_SESSION['user_name'] = $user['name'];
        } catch (PDOException $e) {
            $error = 'Failed to update profile: ' . $e->getMessage();
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $error = 'Please fill in all password fields';
    } elseif (strlen($newPassword) < 6) {
        $error = 'New password must be at least 6 characters';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'New passwords do not match';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $userData = $stmt->fetch();

            if ($userData && password_verify($currentPassword, $userData['password'])) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashedPassword, $userId]);
                $success = 'Password changed successfully';
            } else {
                $error = 'Current password is incorrect';
            }
        } catch (PDOException $e) {
            $error = 'Failed to change password: ' . $e->getMessage();
        }
    }
}

// Handle listing deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_listing'])) {
    $listingId = intval($_POST['listing_id'] ?? 0);

    try {
        $pdo->beginTransaction();

        // Get images to delete
        $stmt = $pdo->prepare("SELECT image_path FROM listing_images WHERE listing_id = ?");
        $stmt->execute([$listingId]);
        $images = $stmt->fetchAll();

        // Delete image files
        foreach ($images as $image) {
            $filepath = __DIR__ . '/..' . $image['image_path'];
            if (file_exists($filepath)) {
                unlink($filepath);
            }
        }

        // Delete listing (cascade will delete images from DB)
        $stmt = $pdo->prepare("DELETE FROM listings WHERE id = ? AND user_id = ?");
        $stmt->execute([$listingId, $userId]);

        $pdo->commit();
        $success = 'Listing deleted successfully';

        // Refresh listings
        $stmt = $pdo->prepare("
            SELECT l.*, 
                   (SELECT image_path FROM listing_images WHERE listing_id = l.id LIMIT 1) as first_image
            FROM listings l
            WHERE l.user_id = ?
            ORDER BY l.created_at DESC
        ");
        $stmt->execute([$userId]);
        $listings = $stmt->fetchAll();
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = 'Failed to delete listing: ' . $e->getMessage();
    }
}

$pageTitle = 'My Profile';
include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>My Profile</h1>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <div class="profile-layout">
        <div class="profile-section">
            <h2>Profile Information</h2>
            <form method="POST" action="" class="profile-form">
                <input type="hidden" name="update_profile" value="1">
                <div class="form-group">
                    <label for="name">Name *</label>
                    <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($user['name']); ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                    <small>Email cannot be changed</small>
                </div>

                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </div>
            </form>
        </div>

        <div class="profile-section">
            <h2>Change Password</h2>
            <form method="POST" action="" class="profile-form">
                <input type="hidden" name="change_password" value="1">
                <div class="form-group">
                    <label for="current_password">Current Password *</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>

                <div class="form-group">
                    <label for="new_password">New Password *</label>
                    <input type="password" id="new_password" name="new_password" required minlength="6">
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm New Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Change Password</button>
                </div>
            </form>
        </div>
    </div>

    <div class="profile-section">
        <div class="" style="display: flex; align-items:center;justify-content: space-between; width:100%; margin-bottom:24px;">
            <h2 style="margin-bottom: 0;">My Listings</h2>
            <a href="/create-listing.php" class="btn btn-primary">Create New Listing</a>
        </div>

        <?php if (empty($listings)): ?>
            <div class="empty-state">
                <p>You haven't created any listings yet.</p>
                <a href="/create-listing.php" class="btn btn-primary">Create Your First Listing</a>
            </div>
        <?php else: ?>
            <div class="listings-grid">
                <?php foreach ($listings as $listing): ?>
                    <div class="listing-card">
                        <a href="/listing-details.php?id=<?php echo $listing['id']; ?>">
                            <div class="listing-image">
                                <?php
                                $imagePath = $listing['first_image'] ?? '/assets/images/placeholder.jpg';
                                if (!file_exists(__DIR__ . '/..' . $imagePath)) {
                                    $imagePath = '/assets/images/placeholder.jpg';
                                }
                                ?>
                                <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="<?php echo htmlspecialchars($listing['title']); ?>">
                                <span class="listing-status status-<?php echo $listing['status']; ?>"><?php echo ucfirst($listing['status']); ?></span>
                            </div>
                            <div class="listing-content">
                                <h3><?php echo htmlspecialchars($listing['title']); ?></h3>
                                <div class="listing-price">$<?php echo number_format($listing['price'], 2); ?>/month</div>
                                <div class="listing-location">📍 <?php echo htmlspecialchars($listing['address']); ?></div>
                            </div>
                        </a>
                        <div class="listing-actions">
                            <a href="/edit-listing.php?id=<?php echo $listing['id']; ?>" class="btn btn-small">Edit</a>
                            <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this listing?');">
                                <input type="hidden" name="delete_listing" value="1">
                                <input type="hidden" name="listing_id" value="<?php echo $listing['id']; ?>">
                                <button type="submit" class="btn btn-small btn-danger">Delete</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>