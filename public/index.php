<?php
/**
 * Home Page - Display Featured Listings
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/paths.php';

$pdo = getDBConnection();

// Get featured approved listings (latest 6)
$stmt = $pdo->prepare("
    SELECT l.*, u.name as owner_name, 
           (SELECT image_path FROM listing_images WHERE listing_id = l.id AND is_primary = 1 LIMIT 1) as primary_image,
           (SELECT image_path FROM listing_images WHERE listing_id = l.id LIMIT 1) as first_image
    FROM listings l
    JOIN users u ON l.user_id = u.id
    WHERE l.status = 'approved'
    ORDER BY l.created_at DESC
    LIMIT 6
");
$stmt->execute();
$listings = $stmt->fetchAll();

$pageTitle = 'Home';
include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <section class="hero">
        <h1>Find Your Perfect Rental</h1>
        <p>Discover amazing properties for rent in your area</p>
        <div class="hero-actions">
            <a href="<?php echo baseUrl('listings.php'); ?>" class="btn btn-primary">Browse Listings</a>
            <?php if (isLoggedIn() && !isAdmin()): ?>
                <a href="<?php echo baseUrl('create-listing.php'); ?>" class="btn btn-secondary">Create Listing</a>
            <?php endif; ?>
        </div>
    </section>
    
    <section class="featured-listings">
        <h2>Featured Listings</h2>
        
        <?php if (empty($listings)): ?>
            <div class="empty-state">
                <p>No listings available at the moment.</p>
                <?php if (isLoggedIn() && !isAdmin()): ?>
                    <a href="<?php echo baseUrl('create-listing.php'); ?>" class="btn btn-primary">Create First Listing</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="listings-grid">
                <?php foreach ($listings as $listing): ?>
                    <div class="listing-card">
                        <a href="<?php echo baseUrl('listing-details.php?id=' . $listing['id']); ?>">
                            <div class="listing-image">
                                <?php 
                                $imagePath = $listing['primary_image'] ?? $listing['first_image'] ?? '/assets/images/placeholder.jpg';
                                if (!file_exists(__DIR__ . '/..' . $imagePath)) {
                                    $imagePath = '/assets/images/placeholder.jpg';
                                }
                                ?>
                                <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="<?php echo htmlspecialchars($listing['title']); ?>">
                            </div>
                            <div class="listing-content">
                                <h3><?php echo htmlspecialchars($listing['title']); ?></h3>
                                <div class="listing-price">$<?php echo number_format($listing['price'], 2); ?>/month</div>
                                <div class="listing-location">📍 <?php echo htmlspecialchars($listing['address']); ?></div>
                                <?php if ($listing['property_type']): ?>
                                    <div class="listing-type"><?php echo htmlspecialchars($listing['property_type']); ?></div>
                                <?php endif; ?>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="view-all">
                <a href="<?php echo baseUrl('listings.php'); ?>" class="btn btn-secondary">View All Listings</a>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

