<?php
session_start();
include '../config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

// Handle redirect for image management
if (isset($_GET['room_id']) && isset($_GET['action']) && $_GET['action'] === 'images') {
    $room_id = intval($_GET['room_id']);
    
    // Fetch room name
    $stmt = $pdo->prepare("SELECT name FROM rooms WHERE id = ?");
    $stmt->execute([$room_id]);
    $room = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($room) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                openImagesModal({$room_id}, '" . htmlspecialchars($room['name']) . "');
            });
        </script>";
    }
}

// Handle form submission for adding/updating rooms
$message = '';
$error = '';

// Handle adding a new room
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_room'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $amenities = trim($_POST['amenities']);
    $price = floatval($_POST['price']);
    $capacity = intval($_POST['capacity']);
    
    if (empty($name) || $price <= 0) {
        $error = 'Please provide a valid room name and price.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO rooms (name, description, amenities, price, capacity) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $description, $amenities, $price, $capacity]);
            $message = 'Room added successfully!';
        } catch (PDOException $e) {
            $error = 'Error adding room: ' . $e->getMessage();
        }
    }
}

// Handle updating a room
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_room'])) {
    $id = intval($_POST['room_id']);
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $amenities = trim($_POST['amenities']);
    $price = floatval($_POST['price']);
    $capacity = intval($_POST['capacity']);
    
    if (empty($name) || $price <= 0) {
        $error = 'Please provide a valid room name and price.';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE rooms SET name = ?, description = ?, amenities = ?, price = ?, capacity = ? WHERE id = ?");
            $stmt->execute([$name, $description, $amenities, $price, $capacity, $id]);
            $message = 'Room updated successfully!';
        } catch (PDOException $e) {
            $error = 'Error updating room: ' . $e->getMessage();
        }
    }
}

// Handle deleting a room
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_room'])) {
    $id = intval($_POST['room_id']);
    
    try {
        // First, delete associated room images and their files
        $stmt = $pdo->prepare("SELECT image_path FROM hotel_images WHERE room_id = ?");
        $stmt->execute([$id]);
        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($images as $image) {
            $file_path = '../' . $image['image_path'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        
        // Delete the image records from the database
        $stmt = $pdo->prepare("DELETE FROM hotel_images WHERE room_id = ?");
        $stmt->execute([$id]);
        
        // Then delete the room
        $stmt = $pdo->prepare("DELETE FROM rooms WHERE id = ?");
        $stmt->execute([$id]);
        
        $message = 'Room deleted successfully!';
    } catch (PDOException $e) {
        $error = 'Error deleting room: ' . $e->getMessage();
    }
}

// Handle image upload for rooms
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_images'])) {
    $room_id = intval($_POST['room_id']);
    
    // Get room name for room_type
    $stmt = $pdo->prepare("SELECT name FROM rooms WHERE id = ?");
    $stmt->execute([$room_id]);
    $room = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($room) {
        $room_type = strtolower(str_replace(' ', '_', $room['name']));
        
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
                                // Insert image with room_id to associate with specific room
                                $stmt = $pdo->prepare("INSERT INTO hotel_images (hotel_id, room_id, image_path, caption) VALUES (?, ?, ?, ?)");
                                // Using hotel_id 1 as default, you may want to make this configurable
                                $stmt->execute([1, $room_id, $image_path, ucfirst($room['name']) . ' Room Image']);
                                $uploaded_count++;
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
    } else {
        $error = 'Room not found.';
    }
}

// Fetch all rooms
$stmt = $pdo->query("SELECT * FROM rooms ORDER BY name");
$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Rooms - Demo Hotel & Resort</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#3b82f6',
                        secondary: '#10b981',
                        accent: '#8b5cf6',
                        dark: '#1e293b',
                        light: '#f8fafc'
                    }
                }
            }
        }
    </script>
    <style>
        :root {
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --secondary: #10b981;
            --secondary-dark: #059669;
            --danger: #ef4444;
            --danger-dark: #dc2626;
            --warning: #f59e0b;
            --warning-dark: #d97706;
            --light: #f8fafc;
            --dark: #1e293b;
            --gray: #94a3b8;
            --border: #e2e8f0;
        }
        
        body {
            background-color: #f1f5f9;
            font-family: 'Inter', sans-serif;
        }
        
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
        }
        
        .card:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-weight: 500;
            transition: background-color 0.2s;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
        }
        
        .btn-secondary {
            background-color: var(--secondary);
            color: white;
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-weight: 500;
            transition: background-color 0.2s;
        }
        
        .btn-secondary:hover {
            background-color: var(--secondary-dark);
        }
        
        .btn-danger {
            background-color: var(--danger);
            color: white;
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-weight: 500;
            transition: background-color 0.2s;
        }
        
        .btn-danger:hover {
            background-color: var(--danger-dark);
        }
        
        .btn-warning {
            background-color: var(--warning);
            color: white;
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-weight: 500;
            transition: background-color 0.2s;
        }
        
        .btn-warning:hover {
            background-color: var(--warning-dark);
        }
        
        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--border);
            color: var(--dark);
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .btn-outline:hover {
            background-color: var(--light);
        }
        
        .form-input {
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 0.75rem;
            transition: border-color 0.2s;
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .nav-link {
            position: relative;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            transition: all 0.2s;
        }
        
        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .nav-link.active {
            background-color: rgba(255, 255, 255, 0.2);
        }
        
        .modal {
            transition: opacity 0.3s ease;
        }
        
        .modal-content {
            transition: transform 0.3s ease;
        }
        
        .room-image {
            transition: all 0.3s ease;
        }
        
        .room-image:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-blue-600 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <span class="text-xl font-bold">Demo Hotel & Resort Admin</span>
                    </div>
                    <div class="hidden md:block">
                        <div class="ml-10 flex items-baseline space-x-2">
                            <a href="dashboard.php" class="nav-link">Dashboard</a>
                            <a href="hotels.php" class="nav-link">Hotels</a>
                            <a href="images.php" class="nav-link">Images</a>
                            <a href="rooms.php" class="nav-link active">Rooms</a>
                            <a href="admins.php" class="nav-link">Admins</a>
                        </div>
                    </div>
                </div>
                <div class="hidden md:block">
                    <div class="ml-4 flex items-center md:ml-6">
                        <span class="mr-4 text-sm">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!</span>
                        <a href="logout.php" class="btn-outline text-white border-white hover:bg-white hover:text-blue-600">
                            <i class="fas fa-sign-out-alt mr-2"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Room Management</h1>
                <p class="text-gray-600">Manage your hotel room inventory</p>
            </div>
            <button onclick="openAddModal()" class="btn-secondary flex items-center">
                <i class="fas fa-plus mr-2"></i> Add New Room
            </button>
        </div>

        <?php if ($message): ?>
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg" role="alert">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <span><?php echo htmlspecialchars($message); ?></span>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg" role="alert">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            </div>
        <?php endif; ?>

        <!-- Rooms Grid -->
        <?php if (empty($rooms)): ?>
            <div class="card">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Rooms</h2>
                </div>
                <div class="p-12 text-center">
                    <i class="fas fa-bed text-5xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-medium text-gray-900 mb-2">No Rooms Found</h3>
                    <p class="text-gray-600 mb-6">Get started by adding your first room.</p>
                    <button onclick="openAddModal()" class="btn-secondary">
                        <i class="fas fa-plus mr-2"></i> Add Your First Room
                    </button>
                </div>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($rooms as $room): ?>
                    <div class="card">
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900"><?php echo htmlspecialchars($room['name']); ?></h3>
                                    <p class="text-2xl font-bold text-blue-600 mt-2">₹<?php echo number_format($room['price']); ?><span class="text-base font-normal text-gray-500">/night</span></p>
                                </div>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                    <i class="fas fa-user-friends mr-1"></i>
                                    <?php echo $room['capacity']; ?> Guests
                                </span>
                            </div>
                            
                            <p class="text-gray-600 mb-6"><?php echo htmlspecialchars($room['description']); ?></p>
                            
                            <div class="flex flex-wrap gap-2">
                                <a href="edit_room.php?id=<?php echo $room['id']; ?>" class="btn-outline flex items-center">
                                    <i class="fas fa-edit mr-2"></i> Edit
                                </a>
                                <button onclick="openImagesModal(<?php echo $room['id']; ?>, '<?php echo htmlspecialchars($room['name']); ?>')" class="btn-outline flex items-center">
                                    <i class="fas fa-images mr-2"></i> Images
                                </button>
                                <button onclick="confirmDelete(<?php echo $room['id']; ?>)" class="btn-danger flex items-center">
                                    <i class="fas fa-trash-alt mr-2"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Add Room Modal -->
    <div id="addModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border-0 w-11/12 md:w-1/2 shadow-xl rounded-xl bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-900">Add New Room</h3>
                    <button onclick="closeAddModal()" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <form method="POST">
                    <input type="hidden" name="add_room" value="1">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2" for="name">Room Name *</label>
                        <input class="form-input w-full" id="name" name="name" type="text" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2" for="description">Description</label>
                        <textarea class="form-input w-full" id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2" for="amenities">Amenities (JSON format)</label>
                        <textarea class="form-input w-full" id="amenities" name="amenities" rows="4" placeholder='{"wifi": true, "ac": true, "breakfast": false}'></textarea>
                        <p class="text-gray-500 text-xs mt-1">Enter amenities in JSON format. Example: {"wifi": true, "ac": true, "breakfast": false}</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2" for="price">Price per Night (₹) *</label>
                            <input class="form-input w-full" id="price" name="price" type="number" min="1" step="0.01" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2" for="capacity">Capacity (Guests) *</label>
                            <input class="form-input w-full" id="capacity" name="capacity" type="number" min="1" required>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeAddModal()" class="btn-outline">
                            Cancel
                        </button>
                        <button type="submit" class="btn-secondary">
                            Add Room
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Images Management Modal -->
    <div id="imagesModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border-0 w-11/12 md:w-1/2 shadow-xl rounded-xl bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-900">Manage <span id="roomNameHeader"></span> Images</h3>
                    <button onclick="closeImagesModal()" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="upload_images" value="1">
                    <input type="hidden" name="room_id" id="images_room_id">
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2" for="room_images">Upload Images</label>
                        <input class="form-input w-full" id="room_images" name="images[]" type="file" accept="image/*" multiple>
                        <p class="text-gray-500 text-xs mt-1">You can select multiple images to upload</p>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeImagesModal()" class="btn-outline">
                            Cancel
                        </button>
                        <button type="submit" class="btn-secondary">
                            Upload Images
                        </button>
                    </div>
                </form>
                
                <!-- Preview existing images -->
                <div class="mt-8">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="text-lg font-medium text-gray-900">Current Images</h4>
                    </div>
                    <div id="currentImagesContainer" class="grid grid-cols-2 sm:grid-cols-3 gap-4 max-h-96 overflow-y-auto">
                        <!-- Images will be loaded here dynamically -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-1/3 mx-auto p-5 border-0 w-11/12 md:w-1/3 shadow-xl rounded-xl bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mt-4">Confirm Deletion</h3>
                <div class="mt-4 px-7 py-3">
                    <p class="text-gray-600">Are you sure you want to delete this room? This action cannot be undone and all associated images will be removed.</p>
                </div>
                <form method="POST" id="deleteForm">
                    <input type="hidden" name="delete_room" value="1">
                    <input type="hidden" name="room_id" id="delete_room_id">
                    <div class="items-center px-4 py-3 flex justify-center space-x-3">
                        <button type="button" onclick="closeDeleteModal()" class="btn-outline">
                            Cancel
                        </button>
                        <button type="submit" class="btn-danger">
                            Delete Room
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Add Modal Functions
        function openAddModal() {
            document.getElementById('addModal').classList.remove('hidden');
        }

        function closeAddModal() {
            document.getElementById('addModal').classList.add('hidden');
        }

        // Images Modal Functions
        function openImagesModal(roomId, roomName) {
            document.getElementById('images_room_id').value = roomId;
            document.getElementById('roomNameHeader').textContent = roomName;
            document.getElementById('imagesModal').classList.remove('hidden');
            loadRoomImages(roomId);
        }

        function closeImagesModal() {
            document.getElementById('imagesModal').classList.add('hidden');
        }

        // Delete Modal Functions
        function confirmDelete(roomId) {
            document.getElementById('delete_room_id').value = roomId;
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }

        // Load room images
        function loadRoomImages(roomId) {
            const container = document.getElementById('currentImagesContainer');
            container.innerHTML = '<p class="text-gray-500 text-sm text-center col-span-3">Loading images...</p>';
            
            // Make AJAX call to fetch room-specific images
            fetch('get_room_images.php?room_id=' + roomId)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.images.length > 0) {
                        let html = '';
                        data.images.forEach(image => {
                            html += `
                                <div class="relative group">
                                    <img src="../${image.image_path}" alt="${image.caption || 'Room image'}" class="w-full h-32 object-cover rounded-lg room-image">
                                    <button onclick="deleteRoomImage(${image.id})" class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-8 h-8 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity shadow-md">
                                        <i class="fas fa-times text-sm"></i>
                                    </button>
                                </div>
                            `;
                        });
                        container.innerHTML = html;
                    } else {
                        container.innerHTML = '<p class="text-gray-500 text-sm text-center col-span-3">No images uploaded for this room yet. Upload images using the form above.</p>';
                    }
                })
                .catch(error => {
                    container.innerHTML = '<p class="text-red-500 text-sm text-center col-span-3">Error loading images</p>';
                });
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            const addModal = document.getElementById('addModal');
            const imagesModal = document.getElementById('imagesModal');
            const deleteModal = document.getElementById('deleteModal');
            
            if (event.target == addModal) {
                closeAddModal();
            }
            if (event.target == imagesModal) {
                closeImagesModal();
            }
            if (event.target == deleteModal) {
                closeDeleteModal();
            }
        }
        
        // Delete room image
        function deleteRoomImage(imageId) {
            if (confirm('Are you sure you want to delete this image?')) {
                // Make AJAX call to delete the image
                fetch('delete_room_image.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({image_id: imageId})
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Reload the images
                        const roomId = document.getElementById('images_room_id').value;
                        loadRoomImages(roomId);
                    } else {
                        alert('Error deleting image: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Error deleting image: ' + error.message);
                });
            }
        }
    </script>
</body>
</html>