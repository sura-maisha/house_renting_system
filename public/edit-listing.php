<?php
/**
 * Edit Listing Page
 */

// Set error reporting - suppress display but log errors
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Start output buffering to prevent any accidental output before headers
ob_start();

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/middleware.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/paths.php';

requireLogin();
require_once __DIR__ . '/../config/paths.php';
if (isAdmin()) {
    header('Location: ' . baseUrl('admin/dashboard.php'));
    exit;
}

$listingId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$pdo = getDBConnection();

// Get listing
$stmt = $pdo->prepare("SELECT * FROM listings WHERE id = ? AND user_id = ?");
$stmt->execute([$listingId, getCurrentUserId()]);
$listing = $stmt->fetch();

if (!$listing) {
    header('Location: ' . baseUrl('listings.php'));
    exit;
}

// Get existing images
$stmt = $pdo->prepare("SELECT * FROM listing_images WHERE listing_id = ? ORDER BY is_primary DESC, id ASC");
$stmt->execute([$listingId]);
$existingImages = $stmt->fetchAll();

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check for upload size errors BEFORE processing
    $maxPostSize = ini_get('post_max_size');
    $maxPostSizeBytes = return_bytes($maxPostSize);
    
    if (isset($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'] > $maxPostSizeBytes) {
        $error = 'File upload size exceeds the limit. Maximum allowed size is ' . $maxPostSize . '. Please reduce the file sizes and try again.';
    } elseif (empty($_POST) && isset($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'] > 0) {
        // POST data exceeded post_max_size
        $error = 'File upload size exceeds the limit. Maximum allowed size is ' . $maxPostSize . '. Please reduce the file sizes and try again.';
    } else {
        $title = sanitizeInput($_POST['title'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
    $address = sanitizeInput($_POST['address'] ?? '');
    $propertyType = sanitizeInput($_POST['property_type'] ?? '');
    $bedrooms = isset($_POST['bedrooms']) ? intval($_POST['bedrooms']) : null;
    $bathrooms = isset($_POST['bathrooms']) ? intval($_POST['bathrooms']) : null;
    $areaSqft = isset($_POST['area_sqft']) ? intval($_POST['area_sqft']) : null;
    
    // Validation
    if (empty($title) || empty($description) || empty($address) || $price <= 0) {
        $error = 'Please fill in all required fields';
    } else {
        try {
            $pdo->beginTransaction();
            
            // Update listing
            $stmt = $pdo->prepare("
                UPDATE listings 
                SET title = ?, description = ?, price = ?, address = ?, property_type = ?, 
                    bedrooms = ?, bathrooms = ?, area_sqft = ?, status = 'pending'
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([
                $title,
                $description,
                $price,
                $address,
                $propertyType ?: null,
                $bedrooms ?: null,
                $bathrooms ?: null,
                $areaSqft ?: null,
                $listingId,
                getCurrentUserId()
            ]);
            
            // Handle new image uploads
            $uploadDir = __DIR__ . '/../assets/uploads/listings/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
                foreach ($_FILES['images']['name'] as $key => $name) {
                    if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                        $file = [
                            'name' => $_FILES['images']['name'][$key],
                            'type' => $_FILES['images']['type'][$key],
                            'tmp_name' => $_FILES['images']['tmp_name'][$key],
                            'error' => $_FILES['images']['error'][$key],
                            'size' => $_FILES['images']['size'][$key]
                        ];
                        
                        $validation = validateImageUpload($file);
                        if ($validation['success']) {
                            $extension = pathinfo($name, PATHINFO_EXTENSION);
                            $filename = uniqid('listing_') . '_' . time() . '.' . $extension;
                            $filepath = $uploadDir . $filename;
                            
                            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                                $relativePath = '/assets/uploads/listings/' . $filename;
                                $isPrimary = (empty($existingImages) && $key === 0) ? 1 : 0;
                                $stmt = $pdo->prepare("INSERT INTO listing_images (listing_id, image_path, is_primary) VALUES (?, ?, ?)");
                                $stmt->execute([$listingId, $relativePath, $isPrimary]);
                            }
                        }
                    }
                }
            }
            
            // Handle image deletion
            if (isset($_POST['delete_images']) && is_array($_POST['delete_images'])) {
                foreach ($_POST['delete_images'] as $imageId) {
                    $stmt = $pdo->prepare("SELECT image_path FROM listing_images WHERE id = ? AND listing_id = ?");
                    $stmt->execute([$imageId, $listingId]);
                    $image = $stmt->fetch();
                    
                    if ($image) {
                        $filepath = __DIR__ . '/..' . $image['image_path'];
                        if (file_exists($filepath)) {
                            unlink($filepath);
                        }
                        $stmt = $pdo->prepare("DELETE FROM listing_images WHERE id = ?");
                        $stmt->execute([$imageId]);
                    }
                }
            }
            
            $pdo->commit();
            $success = 'Listing updated successfully! It will be reviewed again by admin.';
            
            // Refresh listing data
            $stmt = $pdo->prepare("SELECT * FROM listings WHERE id = ?");
            $stmt->execute([$listingId]);
            $listing = $stmt->fetch();
            
            $stmt = $pdo->prepare("SELECT * FROM listing_images WHERE listing_id = ? ORDER BY is_primary DESC, id ASC");
            $stmt->execute([$listingId]);
            $existingImages = $stmt->fetchAll();
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Failed to update listing: ' . $e->getMessage();
        }
    }
    }
}

// End output buffering before including header
ob_end_flush();

$pageTitle = 'Edit Listing';
include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Edit Listing</h1>
    </div>
    
    <div class="form-container">
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="" enctype="multipart/form-data" class="listing-form">
            <div class="form-group">
                <label for="title">Title *</label>
                <input type="text" id="title" name="title" required value="<?php echo htmlspecialchars($listing['title']); ?>">
            </div>
            
            <div class="form-group">
                <label for="description">Description *</label>
                <textarea id="description" name="description" rows="6" required><?php echo htmlspecialchars($listing['description']); ?></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="price">Price per Month ($) *</label>
                    <input type="number" id="price" name="price" required min="0" step="0.01" value="<?php echo $listing['price']; ?>">
                </div>
                
                <div class="form-group">
                    <label for="property_type">Property Type</label>
                    <select id="property_type" name="property_type">
                        <option value="">Select Type</option>
                        <option value="Apartment" <?php echo ($listing['property_type'] === 'Apartment') ? 'selected' : ''; ?>>Apartment</option>
                        <option value="House" <?php echo ($listing['property_type'] === 'House') ? 'selected' : ''; ?>>House</option>
                        <option value="Condo" <?php echo ($listing['property_type'] === 'Condo') ? 'selected' : ''; ?>>Condo</option>
                        <option value="Townhouse" <?php echo ($listing['property_type'] === 'Townhouse') ? 'selected' : ''; ?>>Townhouse</option>
                        <option value="Studio" <?php echo ($listing['property_type'] === 'Studio') ? 'selected' : ''; ?>>Studio</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="address">Address *</label>
                <textarea id="address" name="address" rows="2" required><?php echo htmlspecialchars($listing['address']); ?></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="bedrooms">Bedrooms</label>
                    <input type="number" id="bedrooms" name="bedrooms" min="0" value="<?php echo $listing['bedrooms'] ?? ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="bathrooms">Bathrooms</label>
                    <input type="number" id="bathrooms" name="bathrooms" min="0" step="0.5" value="<?php echo $listing['bathrooms'] ?? ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="area_sqft">Area (sq ft)</label>
                    <input type="number" id="area_sqft" name="area_sqft" min="0" value="<?php echo $listing['area_sqft'] ?? ''; ?>">
                </div>
            </div>
            
            <?php if (!empty($existingImages)): ?>
                <div class="form-group">
                    <label>Existing Images</label>
                    <div class="existing-images">
                        <?php foreach ($existingImages as $image): ?>
                            <div class="existing-image-item">
                                <img src="<?php echo htmlspecialchars($image['image_path']); ?>" alt="Listing image">
                                <label class="delete-checkbox">
                                    <input type="checkbox" name="delete_images[]" value="<?php echo $image['id']; ?>">
                                    Delete
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="images">Add More Images</label>
                <input type="file" id="images" name="images[]" accept="image/*" multiple>
                <small>You can select multiple images.</small>
                <div id="imagePreview" class="image-preview"></div>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Update Listing</button>
                <a href="/profile.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

