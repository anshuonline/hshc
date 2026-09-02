<?php
session_start();
include '../config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

// Handle form submission for adding images
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_image'])) {
    $hotel_id = intval($_POST['hotel_id']);
    $caption = trim($_POST['caption']);
    
    // Handle multiple file uploads
    if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
        $upload_dir = '../uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $uploaded_count = 0;
        $error_count = 0;
        $total_files = 0;
        
        for ($i = 0; $i < count($_FILES['images']['name']); $i++) {
            // Skip if no file was selected for this index
            if (empty($_FILES['images']['name'][$i])) {
                continue;
            }
            
            $total_files++;
            
            if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                $file_name = uniqid() . '_' . basename($_FILES['images']['name'][$i]);
                $target_file = $upload_dir . $file_name;
                
                // Check if image file is actual image
                $check = getimagesize($_FILES['images']['tmp_name'][$i]);
                if ($check !== false) {
                    if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $target_file)) {
                        $image_path = 'uploads/' . $file_name;
                        
                        try {
                            $stmt = $pdo->prepare("INSERT INTO hotel_images (hotel_id, image_path, caption) VALUES (?, ?, ?)");
                            $stmt->execute([$hotel_id, $image_path, $caption]);
                            $uploaded_count++;
                            
                            // Check if we need to limit carousel images
                            $stmt = $pdo->query("SELECT COUNT(*) as count FROM hotel_images WHERE usage_type IN ('carousel', 'both')");
                            $result = $stmt->fetch(PDO::FETCH_ASSOC);
                            
                            if ($result['count'] > 10) {
                                // Remove the oldest carousel images
                                $stmt = $pdo->prepare("DELETE FROM hotel_images WHERE id IN (SELECT id FROM (SELECT id FROM hotel_images WHERE usage_type IN ('carousel', 'both') ORDER BY created_at ASC LIMIT " . ($result['count'] - 10) . ") as t)");
                                $stmt->execute();
                            }
                        } catch (PDOException $e) {
                            $error_count++;
                        }
                    } else {
                        $error_count++;
                    }
                } else {
                    $error_count++;
                }
            } else {
                $error_count++;
            }
        }
        
        if ($uploaded_count > 0) {
            $message = $uploaded_count . ' image(s) uploaded successfully!';
        }
        
        if ($error_count > 0) {
            if ($uploaded_count > 0) {
                $error = 'Partial success: ' . $error_count . ' image(s) failed to upload.';
            } else {
                $error = 'Failed to upload ' . $error_count . ' image(s).';
            }
        } else if ($total_files == 0) {
            $error = 'No images were selected for upload.';
        } else if ($uploaded_count == 0 && $total_files > 0) {
            $error = 'Failed to upload all selected images.';
        }
    } else {
        $error = 'Please select images to upload.';
    }
}

// Handle batch operations
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['batch_action'])) {
    $selected_images = $_POST['selected_images'] ?? [];
    $action = $_POST['action'];
    
    if (empty($selected_images)) {
        $error = 'Please select at least one image.';
    } else {
        try {
            switch ($action) {
                case 'delete':
                    foreach ($selected_images as $image_id) {
                        $image_id = intval($image_id);
                        // First, get the image path to delete the file
                        $stmt = $pdo->prepare("SELECT image_path FROM hotel_images WHERE id = ?");
                        $stmt->execute([$image_id]);
                        $image = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($image) {
                            // Delete the image file from the server
                            $file_path = '../' . $image['image_path'];
                            if (file_exists($file_path)) {
                                unlink($file_path);
                            }
                            
                            // Delete the record from the database
                            $stmt = $pdo->prepare("DELETE FROM hotel_images WHERE id = ?");
                            $stmt->execute([$image_id]);
                        }
                    }
                    $message = count($selected_images) . ' image(s) deleted successfully!';
                    break;
            }
        } catch (PDOException $e) {
            $error = 'Error performing batch operation: ' . $e->getMessage();
        }
    }
}

// Handle image deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_image'])) {
    $image_id = intval($_POST['image_id']);
    
    try {
        // First, get the image path to delete the file
        $stmt = $pdo->prepare("SELECT image_path FROM hotel_images WHERE id = ?");
        $stmt->execute([$image_id]);
        $image = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($image) {
            // Delete the image file from the server
            $file_path = '../' . $image['image_path'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            
            // Delete the record from the database
            $stmt = $pdo->prepare("DELETE FROM hotel_images WHERE id = ?");
            $stmt->execute([$image_id]);
            
            $message = 'Image deleted successfully!';
        } else {
            $error = 'Image not found.';
        }
    } catch (PDOException $e) {
        $error = 'Error deleting image: ' . $e->getMessage();
    }
}

// Handle image usage update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_usage'])) {
    $image_id = intval($_POST['image_id']);
    $usage_type = $_POST['usage_type'];
    
    // Validate carousel position
    $carousel_position = null;
    if (!empty($_POST['carousel_position']) && is_numeric($_POST['carousel_position'])) {
        $pos = intval($_POST['carousel_position']);
        if ($pos >= 1 && $pos <= 10) {
            $carousel_position = $pos;
        }
    }
    
    try {
        // If setting as cover, remove cover status from all other images
        if ($usage_type === 'cover' || $usage_type === 'both') {
            $stmt = $pdo->prepare("UPDATE hotel_images SET usage_type = CASE WHEN usage_type = 'cover' THEN 'none' WHEN usage_type = 'both' THEN 'carousel' ELSE usage_type END WHERE id != ? AND usage_type IN ('cover', 'both')");
            $stmt->execute([$image_id]);
        }
        
        $stmt = $pdo->prepare("UPDATE hotel_images SET usage_type = ?, carousel_position = ? WHERE id = ?");
        $stmt->execute([$usage_type, $carousel_position, $image_id]);
        
        // If this is a carousel image, limit to 10 images and remove older ones
        if ($usage_type === 'carousel' || $usage_type === 'both') {
            // Get count of carousel images
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM hotel_images WHERE usage_type IN ('carousel', 'both')");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['count'] > 10) {
                // Remove the oldest carousel images
                $limit = $result['count'] - 10;
                $stmt = $pdo->prepare("DELETE FROM hotel_images WHERE id IN (SELECT id FROM (SELECT id FROM hotel_images WHERE usage_type IN ('carousel', 'both') ORDER BY created_at ASC LIMIT " . $limit . ") as t)");
                $stmt->execute();
            }
        }
        
        $message = 'Image usage updated successfully!';
    } catch (PDOException $e) {
        $error = 'Error updating image usage: ' . $e->getMessage();
    }
}

// Pagination setup
$images_per_page = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$page = max(1, $page);

// Fetch total count of images
$stmt = $pdo->query("SELECT COUNT(*) as total FROM hotel_images");
$total_images = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_images / $images_per_page);
$offset = ($page - 1) * $images_per_page;

// Fetch all hotels for the dropdown
$stmt = $pdo->query("SELECT id, name FROM hotels ORDER BY name");
$hotels = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch images with hotel names (paginated)
$stmt = $pdo->prepare("SELECT hi.*, h.name as hotel_name FROM hotel_images hi JOIN hotels h ON hi.hotel_id = h.id ORDER BY hi.created_at DESC LIMIT ? OFFSET ?");
$stmt->bindValue(1, $images_per_page, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Images - Demo Hotel & Resort</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <?php include 'includes/navbar.php'; ?>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-900">Manage Hotel Images</h1>
            <button onclick="openAddModal()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md">
                <i class="fas fa-plus mr-1"></i> Upload Images
            </button>
        </div>

        <?php if ($message): ?>
            <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($message); ?></span>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <!-- Batch Operations -->
        <div class="mb-6 bg-white rounded-lg shadow p-4">
            <form method="POST" id="batchForm">
                <input type="hidden" name="batch_action" value="1">
                <div class="flex flex-wrap items-center gap-4">
                    <div class="flex items-center">
                        <input type="checkbox" id="select-all" class="h-4 w-4 text-blue-600 rounded">
                        <label for="select-all" class="ml-2 text-sm text-gray-700">Select All</label>
                    </div>
                    <select name="action" class="border rounded px-3 py-2 text-sm">
                        <option value="">Choose action...</option>
                        <option value="delete">Delete Selected</option>
                    </select>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm">
                        Apply
                    </button>
                </div>
            </form>
        </div>

        <!-- Images Grid -->
        <?php if (empty($images)): ?>
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Hotel Images</h2>
                </div>
                <div class="p-6 text-center">
                    <p class="text-gray-500">No images found.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <?php foreach ($images as $image): ?>
                    <div class="bg-white rounded-lg shadow overflow-hidden relative group">
                        <div class="relative">
                            <input type="checkbox" name="selected_images[]" value="<?php echo $image['id']; ?>" class="absolute top-2 left-2 h-5 w-5 text-blue-600 rounded opacity-0 group-hover:opacity-100 checked:opacity-100" onchange="updateSelectAll()">
                            <img src="../<?php echo htmlspecialchars($image['image_path']); ?>" alt="<?php echo htmlspecialchars($image['caption']); ?>" class="w-full h-48 object-cover">
                        </div>
                        <div class="p-4">
                            <h3 class="font-medium text-gray-900 truncate"><?php echo htmlspecialchars($image['hotel_name']); ?></h3>
                            <p class="text-sm text-gray-500 mt-1 truncate"><?php echo htmlspecialchars($image['caption']); ?></p>
                            <p class="text-xs text-gray-400 mt-2"><?php echo date('M j, Y', strtotime($image['created_at'])); ?></p>
                            
                            <!-- Usage Info -->
                            <div class="mt-2">
                                <?php if ($image['usage_type'] === 'cover'): ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        Cover Image
                                    </span>
                                <?php elseif ($image['usage_type'] === 'carousel'): ?>
                                    <?php if (!is_null($image['carousel_position']) && $image['carousel_position'] > 0): ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Carousel #<?php echo $image['carousel_position']; ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            Carousel (No Position)
                                        </span>
                                    <?php endif; ?>
                                <?php elseif ($image['usage_type'] === 'both'): ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        Cover & Carousel
                                    </span>
                                    <?php if (!is_null($image['carousel_position']) && $image['carousel_position'] > 0): ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 mt-1">
                                            Position #<?php echo $image['carousel_position']; ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 mt-1">
                                            No Carousel Position
                                        </span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity flex space-x-1">
                            <button onclick="openUsageModal(<?php echo $image['id']; ?>, '<?php echo $image['usage_type']; ?>', <?php echo !is_null($image['carousel_position']) ? $image['carousel_position'] : 'null'; ?>)" class="bg-blue-500 hover:bg-blue-600 text-white p-2 rounded-full shadow-md" title="Set Usage">
                                <i class="fas fa-cog"></i>
                            </button>
                            <form method="POST" class="inline">
                                <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                                <input type="hidden" name="delete_image" value="1">
                                <button type="button" onclick="confirmDelete(this.form)" class="bg-red-500 hover:bg-red-600 text-white p-2 rounded-full shadow-md" title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="mt-6 flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Showing <?php echo min($images_per_page, $total_images - ($page - 1) * $images_per_page); ?> of <?php echo $total_images; ?> images
                    </div>
                    <div class="flex space-x-2">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>" class="px-3 py-1 rounded-md bg-gray-200 hover:bg-gray-300">&laquo; Previous</a>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <?php if ($i == $page): ?>
                                <span class="px-3 py-1 rounded-md bg-blue-500 text-white"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?page=<?php echo $i; ?>" class="px-3 py-1 rounded-md bg-gray-200 hover:bg-gray-300"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?>" class="px-3 py-1 rounded-md bg-gray-200 hover:bg-gray-300">Next &raquo;</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Add Image Modal -->
    <div id="addModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Upload Images</h3>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="add_image" value="1">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="hotel_id">Hotel *</label>
                        <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="hotel_id" name="hotel_id" required>
                            <option value="">Select a hotel</option>
                            <?php foreach ($hotels as $hotel): ?>
                                <option value="<?php echo $hotel['id']; ?>"><?php echo htmlspecialchars($hotel['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="image">Images *</label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="image" name="images[]" type="file" accept="image/*" required multiple>
                        <p class="text-gray-500 text-xs mt-1">You can select multiple images to upload</p>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="caption">Caption</label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="caption" name="caption" type="text">
                    </div>
                    <div class="flex items-center justify-between">
                        <button type="button" onclick="closeAddModal()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Cancel
                        </button>
                        <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Upload Images
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Usage Modal -->
    <div id="usageModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Set Image Usage</h3>
                <form method="POST" id="usageForm">
                    <input type="hidden" name="update_usage" value="1">
                    <input type="hidden" name="image_id" id="usageImageId">
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Usage Type</label>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="radio" name="usage_type" value="carousel" class="usage-type-radio" required>
                                <span class="ml-2">Carousel Only</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="usage_type" value="cover" class="usage-type-radio">
                                <span class="ml-2">Cover Image Only</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="usage_type" value="both" class="usage-type-radio">
                                <span class="ml-2">Both Cover and Carousel</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-4" id="carouselPositionField" style="display: none;">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="carousel_position">Carousel Position (1-10)</label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="carousel_position" name="carousel_position" type="number" min="1" max="10" placeholder="1-10">
                        <p class="text-gray-500 text-xs mt-1">Set position for carousel display (1 = first, 2 = second, etc.)</p>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <button type="button" onclick="closeUsageModal()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Cancel
                        </button>
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Save Usage
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('addModal').classList.remove('hidden');
        }

        function closeAddModal() {
            document.getElementById('addModal').classList.add('hidden');
        }
        
        // Usage modal functions
        function openUsageModal(imageId, usageType, carouselPosition) {
            document.getElementById('usageImageId').value = imageId;
            document.getElementById('usageModal').classList.remove('hidden');
            
            // Set the selected usage type
            const usageRadios = document.querySelectorAll('.usage-type-radio');
            usageRadios.forEach(radio => {
                radio.checked = (radio.value === usageType);
            });
            
            // Set carousel position if available
            if (carouselPosition !== null) {
                document.getElementById('carousel_position').value = carouselPosition;
            } else {
                document.getElementById('carousel_position').value = '';
            }
            
            // Show/hide carousel position field based on usage type
            toggleCarouselPosition();
        }
        
        function closeUsageModal() {
            document.getElementById('usageModal').classList.add('hidden');
        }
        
        function toggleCarouselPosition() {
            const carouselRadio = document.querySelector('input[name="usage_type"]:checked');
            const positionField = document.getElementById('carouselPositionField');
            
            if (carouselRadio && (carouselRadio.value === 'carousel' || carouselRadio.value === 'both')) {
                positionField.style.display = 'block';
            } else {
                positionField.style.display = 'none';
            }
        }
        
        // Add event listeners to usage type radios
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('usage-type-radio')) {
                toggleCarouselPosition();
            }
        });
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const addModal = document.getElementById('addModal');
            const usageModal = document.getElementById('usageModal');
            if (event.target == addModal) {
                closeAddModal();
            }
            if (event.target == usageModal) {
                closeUsageModal();
            }
        }
        
        // Confirm deletion
        function confirmDelete(form) {
            if (confirm('Are you sure you want to delete this image? This action cannot be undone.')) {
                form.submit();
            }
        }
        
        // Select all functionality
        document.getElementById('select-all').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('input[name="selected_images[]"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
        
        // Update select all checkbox state
        function updateSelectAll() {
            const checkboxes = document.querySelectorAll('input[name="selected_images[]"]');
            const selectAll = document.getElementById('select-all');
            const allChecked = Array.from(checkboxes).every(checkbox => checkbox.checked);
            selectAll.checked = allChecked;
        }
        
        // Batch form submission
        document.getElementById('batchForm').addEventListener('submit', function(e) {
            const action = this.action.value;
            const selectedImages = document.querySelectorAll('input[name="selected_images[]"]:checked');
            
            if (action === '') {
                e.preventDefault();
                alert('Please select an action.');
                return;
            }
            
            if (selectedImages.length === 0) {
                e.preventDefault();
                alert('Please select at least one image.');
                return;
            }
            
            if (action === 'delete') {
                if (!confirm('Are you sure you want to delete ' + selectedImages.length + ' image(s)? This action cannot be undone.')) {
                    e.preventDefault();
                }
            }
        });
    </script>
</body>
</html>