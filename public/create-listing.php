<?php

/**
 * Create Listing Page
 */

// Start output buffering FIRST to catch any warnings/errors
if (!ob_get_level()) {
    ob_start();
}

// Set error reporting - suppress display but log errors
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Suppress warnings that might break headers
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    // Log the error but don't output it
    if ($errno === E_WARNING || $errno === E_NOTICE) {
        error_log("PHP $errno: $errstr in $errfile on line $errline");
        return true; // Suppress the error
    }
    return false; // Let PHP handle other errors
}, E_WARNING | E_NOTICE);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/middleware.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/paths.php';

requireLogin();
if (isAdmin()) {
    ob_end_clean();
    header('Location: ' . baseUrl('admin/dashboard.php'));
    exit;
}

$error = '';
$success = '';

// Helper function to convert ini size to bytes
function return_bytes($val)
{
    $val = trim($val);
    $last = strtolower($val[strlen($val) - 1]);
    $val = (int)$val;
    switch ($last) {
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }
    return $val;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Clear any buffered output from warnings
    if (ob_get_level()) {
        ob_clean();
    }

    // Check for upload size errors BEFORE processing
    $maxPostSize = ini_get('post_max_size');
    $maxPostSizeBytes = return_bytes($maxPostSize);
    $contentLength = isset($_SERVER['CONTENT_LENGTH']) ? (int)$_SERVER['CONTENT_LENGTH'] : 0;

    if ($contentLength > $maxPostSizeBytes) {
        $error = 'File upload size exceeds the limit. Maximum allowed size is ' . $maxPostSize . ' total. Please reduce the file sizes and try again.';
    } elseif (empty($_POST) && empty($_FILES) && $contentLength > 0) {
        // POST data exceeded post_max_size - PHP cleared $_POST and $_FILES
        $error = 'File upload size exceeds the limit. Maximum allowed size is ' . $maxPostSize . ' total. Please reduce the file sizes and try again.';
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
                $pdo = getDBConnection();
                $pdo->beginTransaction();

                // Insert listing
                $stmt = $pdo->prepare("
                INSERT INTO listings (user_id, title, description, price, address, property_type, bedrooms, bathrooms, area_sqft, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
            ");
                $stmt->execute([
                    getCurrentUserId(),
                    $title,
                    $description,
                    $price,
                    $address,
                    $propertyType ?: null,
                    $bedrooms ?: null,
                    $bathrooms ?: null,
                    $areaSqft ?: null
                ]);

                $listingId = $pdo->lastInsertId();

                // Handle image uploads
                $uploadDir = __DIR__ . '/../assets/uploads/listings/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $uploadedImages = [];
                if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
                    foreach ($_FILES['images']['name'] as $key => $name) {
                        // Check for upload errors
                        if ($_FILES['images']['error'][$key] === UPLOAD_ERR_INI_SIZE || $_FILES['images']['error'][$key] === UPLOAD_ERR_FORM_SIZE) {
                            $error = 'One or more files exceed the maximum upload size of 5MB.';
                            break;
                        } elseif ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
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
                                    $uploadedImages[] = $relativePath;
                                }
                            }
                        }
                    }
                }

                // Insert images
                if (!empty($uploadedImages)) {
                    $stmt = $pdo->prepare("INSERT INTO listing_images (listing_id, image_path, is_primary) VALUES (?, ?, ?)");
                    foreach ($uploadedImages as $index => $imagePath) {
                        $stmt->execute([$listingId, $imagePath, $index === 0 ? 1 : 0]);
                    }
                }

                $pdo->commit();
                $success = 'Listing created successfully! It will be visible after admin approval.';
                $_POST = []; // Clear form
            } catch (PDOException $e) {
                if (isset($pdo) && $pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $error = 'Failed to create listing: ' . $e->getMessage();
            }
        }
    }
}

// Clean output buffer before including header (in case of any warnings)
if (ob_get_level()) {
    ob_end_clean();
}

$pageTitle = 'Create Listing';
include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Create New Listing</h1>
    </div>

    <div class="form-container">
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST" action="" enctype="multipart/form-data" class="listing-form" id="createListingForm">
            <div class="form-group">
                <label for="title">Title *</label>
                <input type="text" id="title" name="title" required value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="description">Description *</label>
                <textarea id="description" name="description" rows="6" required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="price">Price per Month ($) *</label>
                    <input type="number" id="price" name="price" required min="0" step="0.01" value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="property_type">Property Type</label>
                    <select id="property_type" name="property_type">
                        <option value="">Select Type</option>
                        <option value="Apartment" <?php echo (($_POST['property_type'] ?? '') === 'Apartment') ? 'selected' : ''; ?>>Apartment</option>
                        <option value="House" <?php echo (($_POST['property_type'] ?? '') === 'House') ? 'selected' : ''; ?>>House</option>
                        <option value="Condo" <?php echo (($_POST['property_type'] ?? '') === 'Condo') ? 'selected' : ''; ?>>Condo</option>
                        <option value="Townhouse" <?php echo (($_POST['property_type'] ?? '') === 'Townhouse') ? 'selected' : ''; ?>>Townhouse</option>
                        <option value="Studio" <?php echo (($_POST['property_type'] ?? '') === 'Studio') ? 'selected' : ''; ?>>Studio</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="address">Address *</label>
                <textarea id="address" name="address" rows="2" required><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="bedrooms">Bedrooms</label>
                    <input type="number" id="bedrooms" name="bedrooms" min="0" value="<?php echo htmlspecialchars($_POST['bedrooms'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="bathrooms">Bathrooms</label>
                    <input type="number" id="bathrooms" name="bathrooms" min="0" step="0.5" value="<?php echo htmlspecialchars($_POST['bathrooms'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="area_sqft">Area (sq ft)</label>
                    <input type="number" id="area_sqft" name="area_sqft" min="0" value="<?php echo htmlspecialchars($_POST['area_sqft'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="images">Images</label>
                <input type="file" id="images" name="images[]" accept="image/*" multiple>
                <small>You can select multiple images. First image will be primary. Maximum file size: 20MB per file. Total upload limit: 30MB.</small>
                <div id="imagePreview" class="image-preview"></div>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">Create Listing</button>
                <a href="<?php echo baseUrl('listings.php'); ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>