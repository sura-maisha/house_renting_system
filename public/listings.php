<?php
/**
 * Listings Page - Browse All Listings with Search and Filter
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/middleware.php';
require_once __DIR__ . '/../config/database.php';

$pdo = getDBConnection();

// Get filter parameters
$keyword = sanitizeInput($_GET['keyword'] ?? '');
$minPrice = isset($_GET['min_price']) ? floatval($_GET['min_price']) : null;
$maxPrice = isset($_GET['max_price']) ? floatval($_GET['max_price']) : null;
$location = sanitizeInput($_GET['location'] ?? '');
$propertyType = sanitizeInput($_GET['property_type'] ?? '');

// Build query
$where = ["l.status = 'approved'"];
$params = [];

if (!empty($keyword)) {
    $where[] = "(l.title LIKE ? OR l.description LIKE ? OR l.address LIKE ?)";
    $searchTerm = "%{$keyword}%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if ($minPrice !== null && $minPrice > 0) {
    $where[] = "l.price >= ?";
    $params[] = $minPrice;
}

if ($maxPrice !== null && $maxPrice > 0) {
    $where[] = "l.price <= ?";
    $params[] = $maxPrice;
}

if (!empty($location)) {
    $where[] = "l.address LIKE ?";
    $params[] = "%{$location}%";
}

if (!empty($propertyType)) {
    $where[] = "l.property_type = ?";
    $params[] = $propertyType;
}

$whereClause = implode(' AND ', $where);

// Get listings
$sql = "
    SELECT l.*, u.name as owner_name,
           (SELECT image_path FROM listing_images WHERE listing_id = l.id AND is_primary = 1 LIMIT 1) as primary_image,
           (SELECT image_path FROM listing_images WHERE listing_id = l.id LIMIT 1) as first_image
    FROM listings l
    JOIN users u ON l.user_id = u.id
    WHERE {$whereClause}
    ORDER BY l.created_at DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$listings = $stmt->fetchAll();

// Get unique property types for filter
$stmt = $pdo->query("SELECT DISTINCT property_type FROM listings WHERE property_type IS NOT NULL AND status = 'approved'");
$propertyTypes = $stmt->fetchAll(PDO::FETCH_COLUMN);

$pageTitle = 'Browse Listings';
include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Browse Listings</h1>
    </div>
    
    <div class="listings-layout">
        <aside class="filters-sidebar">
            <h3>Filters</h3>
            <form method="GET" action="" class="filters-form">
                <div class="form-group">
                    <label for="keyword">Keyword</label>
                    <input type="text" id="keyword" name="keyword" value="<?php echo htmlspecialchars($keyword); ?>" placeholder="Search...">
                </div>
                
                <div class="form-group">
                    <label for="min_price">Min Price</label>
                    <input type="number" id="min_price" name="min_price" value="<?php echo $minPrice ? htmlspecialchars($minPrice) : ''; ?>" min="0" step="0.01">
                </div>
                
                <div class="form-group">
                    <label for="max_price">Max Price</label>
                    <input type="number" id="max_price" name="max_price" value="<?php echo $maxPrice ? htmlspecialchars($maxPrice) : ''; ?>" min="0" step="0.01">
                </div>
                
                <div class="form-group">
                    <label for="location">Location</label>
                    <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($location); ?>" placeholder="City, State">
                </div>
                
                <div class="form-group">
                    <label for="property_type">Property Type</label>
                    <select id="property_type" name="property_type">
                        <option value="">All Types</option>
                        <?php foreach ($propertyTypes as $type): ?>
                            <option value="<?php echo htmlspecialchars($type); ?>" <?php echo $propertyType === $type ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($type); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="/listings.php" class="btn btn-secondary">Clear</a>
                </div>
            </form>
        </aside>
        
        <div class="listings-content">
            <div class="listings-header">
                <p class="results-count"><?php echo count($listings); ?> listing(s) found</p>
            </div>
            
            <?php if (empty($listings)): ?>
                <div class="empty-state">
                    <p>No listings match your criteria.</p>
                    <a href="/listings.php" class="btn btn-primary">Clear Filters</a>
                </div>
            <?php else: ?>
                <div class="listings-grid">
                    <?php foreach ($listings as $listing): ?>
                        <div class="listing-card">
                            <a href="/listing-details.php?id=<?php echo $listing['id']; ?>">
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
                                    <?php if ($listing['bedrooms']): ?>
                                        <div class="listing-details">🛏️ <?php echo $listing['bedrooms']; ?> bed</div>
                                    <?php endif; ?>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

