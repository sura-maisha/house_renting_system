<?php
/**
 * Listing Details Page
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/paths.php';

$listingId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$listingId) {
    header('Location: ' . baseUrl('listings.php'));
    exit;
}

$pdo = getDBConnection();

// Get listing details
$stmt = $pdo->prepare("
    SELECT l.*, u.name as owner_name, u.email as owner_email, u.phone as owner_phone
    FROM listings l
    JOIN users u ON l.user_id = u.id
    WHERE l.id = ? AND l.status = 'approved'
");
$stmt->execute([$listingId]);
$listing = $stmt->fetch();

if (!$listing) {
    header('Location: ' . baseUrl('listings.php'));
    exit;
}

// Get listing images
$stmt = $pdo->prepare("SELECT * FROM listing_images WHERE listing_id = ? ORDER BY is_primary DESC, id ASC");
$stmt->execute([$listingId]);
$images = $stmt->fetchAll();

$pageTitle = $listing['title'];
include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="listing-details">
        <div class="listing-gallery">
            <?php if (!empty($images)): ?>
                <div class="main-image">
                    <img src="<?php echo htmlspecialchars($images[0]['image_path']); ?>" alt="<?php echo htmlspecialchars($listing['title']); ?>" id="mainImage">
                </div>
                <?php if (count($images) > 1): ?>
                    <div class="thumbnail-images">
                        <?php foreach ($images as $image): ?>
                            <img src="<?php echo htmlspecialchars($image['image_path']); ?>" 
                                 alt="Thumbnail" 
                                 class="thumbnail <?php echo $image['is_primary'] ? 'active' : ''; ?>"
                                 onclick="changeMainImage('<?php echo htmlspecialchars($image['image_path']); ?>')">
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="main-image">
                    <img src="/assets/images/placeholder.jpg" alt="<?php echo htmlspecialchars($listing['title']); ?>">
                </div>
            <?php endif; ?>
        </div>
        
        <div class="listing-info">
            <h1><?php echo htmlspecialchars($listing['title']); ?></h1>
            <div class="listing-price-large">$<?php echo number_format($listing['price'], 2); ?>/month</div>
            
            <div class="listing-meta">
                <div class="meta-item">
                    <strong>📍 Location:</strong>
                    <span><?php echo htmlspecialchars($listing['address']); ?></span>
                </div>
                <?php if ($listing['property_type']): ?>
                    <div class="meta-item">
                        <strong>Property Type:</strong>
                        <span><?php echo htmlspecialchars($listing['property_type']); ?></span>
                    </div>
                <?php endif; ?>
                <?php if ($listing['bedrooms']): ?>
                    <div class="meta-item">
                        <strong>Bedrooms:</strong>
                        <span><?php echo $listing['bedrooms']; ?></span>
                    </div>
                <?php endif; ?>
                <?php if ($listing['bathrooms']): ?>
                    <div class="meta-item">
                        <strong>Bathrooms:</strong>
                        <span><?php echo $listing['bathrooms']; ?></span>
                    </div>
                <?php endif; ?>
                <?php if ($listing['area_sqft']): ?>
                    <div class="meta-item">
                        <strong>Area:</strong>
                        <span><?php echo number_format($listing['area_sqft']); ?> sq ft</span>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="listing-description">
                <h2>Description</h2>
                <p><?php echo nl2br(htmlspecialchars($listing['description'])); ?></p>
            </div>
            
            <div class="owner-info">
                <h2>Contact Owner</h2>
                <div class="owner-details">
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($listing['owner_name']); ?></p>
                    <?php if ($listing['owner_email']): ?>
                        <p><strong>Email:</strong> <a href="mailto:<?php echo htmlspecialchars($listing['owner_email']); ?>"><?php echo htmlspecialchars($listing['owner_email']); ?></a></p>
                    <?php endif; ?>
                    <?php if ($listing['owner_phone']): ?>
                        <p><strong>Phone:</strong> <a href="tel:<?php echo htmlspecialchars($listing['owner_phone']); ?>"><?php echo htmlspecialchars($listing['owner_phone']); ?></a></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function changeMainImage(imagePath) {
    document.getElementById('mainImage').src = imagePath;
    document.querySelectorAll('.thumbnail').forEach(thumb => {
        thumb.classList.remove('active');
        if (thumb.src.includes(imagePath)) {
            thumb.classList.add('active');
        }
    });
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>

