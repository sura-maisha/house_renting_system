<?php
/**
 * Admin - Approve Listings Page
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/middleware.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/paths.php';

requireAdmin();

$pdo = getDBConnection();

$error = '';
$success = '';

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $listingId = intval($_POST['listing_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    
    if ($listingId && in_array($action, ['approve', 'reject'])) {
        try {
            $status = $action === 'approve' ? 'approved' : 'rejected';
            $stmt = $pdo->prepare("UPDATE listings SET status = ? WHERE id = ?");
            $stmt->execute([$status, $listingId]);
            $success = "Listing {$action}d successfully";
        } catch (PDOException $e) {
            $error = 'Failed to update listing: ' . $e->getMessage();
        }
    }
}

// Get pending listings
$stmt = $pdo->prepare("
    SELECT l.*, u.name as owner_name, u.email as owner_email,
           (SELECT image_path FROM listing_images WHERE listing_id = l.id LIMIT 1) as first_image
    FROM listings l
    JOIN users u ON l.user_id = u.id
    WHERE l.status = 'pending'
    ORDER BY l.created_at ASC
");
$stmt->execute();
$pendingListings = $stmt->fetchAll();

$pageTitle = 'Approve Listings';
include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Approve Listings</h1>
        <p>Review and approve or reject pending listings</p>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <?php if (empty($pendingListings)): ?>
        <div class="empty-state">
            <p>No pending listings to review.</p>
            <a href="/admin/dashboard.php" class="btn btn-primary">Back to Dashboard</a>
        </div>
    <?php else: ?>
        <div class="pending-listings">
            <?php foreach ($pendingListings as $listing): ?>
                <div class="pending-listing-card">
                    <div class="pending-listing-content">
                        <div class="pending-listing-image">
                            <?php 
                            $imagePath = $listing['first_image'] ?? '/assets/images/placeholder.jpg';
                            if (!file_exists(__DIR__ . '/..' . $imagePath)) {
                                $imagePath = '/assets/images/placeholder.jpg';
                            }
                            ?>
                            <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="<?php echo htmlspecialchars($listing['title']); ?>">
                        </div>
                        <div class="pending-listing-details">
                            <h3><?php echo htmlspecialchars($listing['title']); ?></h3>
                            <div class="listing-meta-info">
                                <p><strong>Owner:</strong> <?php echo htmlspecialchars($listing['owner_name']); ?> (<?php echo htmlspecialchars($listing['owner_email']); ?>)</p>
                                <p><strong>Price:</strong> $<?php echo number_format($listing['price'], 2); ?>/month</p>
                                <p><strong>Location:</strong> <?php echo htmlspecialchars($listing['address']); ?></p>
                                <?php if ($listing['property_type']): ?>
                                    <p><strong>Type:</strong> <?php echo htmlspecialchars($listing['property_type']); ?></p>
                                <?php endif; ?>
                                <?php if ($listing['bedrooms']): ?>
                                    <p><strong>Bedrooms:</strong> <?php echo $listing['bedrooms']; ?></p>
                                <?php endif; ?>
                                <p><strong>Created:</strong> <?php echo date('M d, Y H:i', strtotime($listing['created_at'])); ?></p>
                            </div>
                            <div class="listing-description-preview">
                                <p><?php echo nl2br(htmlspecialchars(substr($listing['description'], 0, 200))); ?><?php echo strlen($listing['description']) > 200 ? '...' : ''; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="pending-listing-actions">
                        <form method="POST" action="" style="display: inline;">
                            <input type="hidden" name="listing_id" value="<?php echo $listing['id']; ?>">
                            <input type="hidden" name="action" value="approve">
                            <button type="submit" class="btn btn-success">Approve</button>
                        </form>
                        <form method="POST" action="" style="display: inline;">
                            <input type="hidden" name="listing_id" value="<?php echo $listing['id']; ?>">
                            <input type="hidden" name="action" value="reject">
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to reject this listing?');">Reject</button>
                        </form>
                        <a href="/listing-details.php?id=<?php echo $listing['id']; ?>" class="btn btn-secondary" target="_blank">View Full Details</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

