<?php
/**
 * Admin Dashboard
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/middleware.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/paths.php';

requireAdmin();

$pdo = getDBConnection();

// Get statistics
$stats = [
    'total_listings' => $pdo->query("SELECT COUNT(*) FROM listings")->fetchColumn(),
    'pending_listings' => $pdo->query("SELECT COUNT(*) FROM listings WHERE status = 'pending'")->fetchColumn(),
    'approved_listings' => $pdo->query("SELECT COUNT(*) FROM listings WHERE status = 'approved'")->fetchColumn(),
    'total_users' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn(),
];

// Get recent listings
$stmt = $pdo->query("
    SELECT l.*, u.name as owner_name,
           (SELECT image_path FROM listing_images WHERE listing_id = l.id LIMIT 1) as first_image
    FROM listings l
    JOIN users u ON l.user_id = u.id
    ORDER BY l.created_at DESC
    LIMIT 10
");
$recentListings = $stmt->fetchAll();

$pageTitle = 'Admin Dashboard';
include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Admin Dashboard</h1>
    </div>
    
    <div class="admin-stats">
        <div class="stat-card">
            <h3>Total Listings</h3>
            <div class="stat-number"><?php echo $stats['total_listings']; ?></div>
        </div>
        <div class="stat-card stat-warning">
            <h3>Pending Approval</h3>
            <div class="stat-number"><?php echo $stats['pending_listings']; ?></div>
        </div>
        <div class="stat-card stat-success">
            <h3>Approved</h3>
            <div class="stat-number"><?php echo $stats['approved_listings']; ?></div>
        </div>
        <div class="stat-card">
            <h3>Total Users</h3>
            <div class="stat-number"><?php echo $stats['total_users']; ?></div>
        </div>
    </div>
    
    <div class="admin-actions">
        <a href="/admin/approve-listings.php" class="btn btn-primary">Review Pending Listings</a>
        <a href="/admin/users.php" class="btn btn-secondary">Manage Users</a>
    </div>
    
    <div class="admin-section">
        <h2>Recent Listings</h2>
        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Owner</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentListings)): ?>
                        <tr>
                            <td colspan="7">No listings found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentListings as $listing): ?>
                            <tr>
                                <td><?php echo $listing['id']; ?></td>
                                <td><?php echo htmlspecialchars($listing['title']); ?></td>
                                <td><?php echo htmlspecialchars($listing['owner_name']); ?></td>
                                <td>$<?php echo number_format($listing['price'], 2); ?></td>
                                <td><span class="status-badge status-<?php echo $listing['status']; ?>"><?php echo ucfirst($listing['status']); ?></span></td>
                                <td><?php echo date('M d, Y', strtotime($listing['created_at'])); ?></td>
                                <td>
                                    <a href="/listing-details.php?id=<?php echo $listing['id']; ?>" class="btn btn-small">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

