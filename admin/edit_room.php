<?php
session_start();
include '../config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

// Get room ID from URL parameter
$room_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Handle form submission for updating room
$message = '';
$error = '';

// Fetch room details
$room = null;
if ($room_id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
        $stmt->execute([$room_id]);
        $room = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$room) {
            $error = 'Room not found.';
        }
    } catch (PDOException $e) {
        $error = 'Error fetching room: ' . $e->getMessage();
    }
} else {
    $error = 'Invalid room ID.';
}

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_image'])) {
    if (isset($_FILES['room_images']) && is_array($_FILES['room_images']['name'])) {
        $upload_dir = '../uploads/rooms/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $uploaded_count = 0;
        $error_count = 0;
        
        // Process each uploaded file
        for ($i = 0; $i < count($_FILES['room_images']['name']); $i++) {
            if ($_FILES['room_images']['error'][$i] === UPLOAD_ERR_OK) {
                $file_name = time() . '_' . $i . '_' . basename($_FILES['room_images']['name'][$i]);
                $target_file = $upload_dir . $file_name;
                
                $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
                $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
                
                if (in_array($imageFileType, $allowed_types)) {
                    if (move_uploaded_file($_FILES['room_images']['tmp_name'][$i], $target_file)) {
                        try {
                            $stmt = $pdo->prepare("INSERT INTO hotel_images (room_id, image_path, caption) VALUES (?, ?, ?)");
                            $stmt->execute([$room_id, 'uploads/rooms/' . $file_name, $room['name']]);
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
            // Refresh images
            try {
                $stmt = $pdo->prepare("SELECT * FROM hotel_images WHERE room_id = ? ORDER BY created_at DESC");
                $stmt->execute([$room_id]);
                $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $error = 'Error fetching room images: ' . $e->getMessage();
            }
        }
        
        if ($error_count > 0) {
            $error = 'Failed to upload ' . $error_count . ' image(s). Please check file formats and try again.';
        }
    } else {
        $error = 'Please select at least one image to upload.';
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_room'])) {
    $name = trim($_POST['name']);
    $room_overview = trim($_POST['room_overview']); // Room overview field
    $price = floatval($_POST['price']);
    $capacity = intval($_POST['capacity']);
    $max_adults = intval($_POST['max_adults']);
    $max_children = intval($_POST['max_children']);
    $extra_adult_charge = floatval($_POST['extra_adult_charge']);
    $extra_child_charge = floatval($_POST['extra_child_charge']);
    $booking_pause = isset($_POST['booking_pause']) ? 1 : 0; // New booking pause field
    
    // Process custom room overview options
    $room_overview_options = [];
    if (isset($_POST['option_icon']) && is_array($_POST['option_icon'])) {
        for ($i = 0; $i < count($_POST['option_icon']); $i++) {
            $icon = trim($_POST['option_icon'][$i]);
            $title = trim($_POST['option_title'][$i]);
            $description = trim($_POST['option_description'][$i]);
            
            if (!empty($icon) || !empty($title) || !empty($description)) {
                $room_overview_options[] = [
                    'icon' => $icon,
                    'title' => $title,
                    'description' => $description
                ];
            }
        }
    }
    
    $room_overview_options_json = !empty($room_overview_options) ? json_encode($room_overview_options) : null;
    
    // Use room_overview as the description
    $final_description = $room_overview;
    
    // Process additional charges
    $additional_charges = [];
    if (isset($_POST['additional_charge_name']) && is_array($_POST['additional_charge_name'])) {
        for ($i = 0; $i < count($_POST['additional_charge_name']); $i++) {
            $charge_name = trim($_POST['additional_charge_name'][$i]);
            $charge_type = $_POST['additional_charge_type'][$i];
            $charge_amount = floatval($_POST['additional_charge_amount'][$i]);
            
            if (!empty($charge_name) && $charge_amount >= 0) {
                if ($charge_type === 'percentage') {
                    $additional_charges[$charge_name] = [
                        'amount' => $charge_amount . '%',
                        'type' => 'percentage'
                    ];
                } else {
                    $additional_charges[$charge_name] = [
                        'amount' => $charge_amount,
                        'type' => 'fixed'
                    ];
                }
            }
        }
    }
    
    $additional_charges_json = !empty($additional_charges) ? json_encode($additional_charges) : null;
    
    // Process amenities from checkboxes
    $amenities = [];
    if (isset($_POST['amenity_name']) && is_array($_POST['amenity_name'])) {
        for ($i = 0; $i < count($_POST['amenity_name']); $i++) {
            $amenity_name = trim($_POST['amenity_name'][$i]);
            // Use indexed checkbox: amenity_available_0, amenity_available_1, etc.
            $is_available = isset($_POST['amenity_available_' . $i]) ? true : false;
            
            if (!empty($amenity_name)) {
                $amenities[$amenity_name] = $is_available;
            }
        }
    }
    
    $amenities_json = !empty($amenities) ? json_encode($amenities) : null;
    
    if (empty($name) || $price <= 0) {
        $error = 'Please provide a valid room name and price.';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE rooms SET name = ?, description = ?, amenities = ?, price = ?, capacity = ?, max_adults = ?, max_children = ?, extra_adult_charge = ?, extra_child_charge = ?, additional_charges = ?, booking_pause = ?, room_overview_options = ? WHERE id = ?");
            $stmt->execute([$name, $final_description, $amenities_json, $price, $capacity, $max_adults, $max_children, $extra_adult_charge, $extra_child_charge, $additional_charges_json, $booking_pause, $room_overview_options_json, $room_id]);
            $message = 'Room updated successfully!';
            
            // Refresh room data
            $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
            $stmt->execute([$room_id]);
            $room = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $error = 'Error updating room: ' . $e->getMessage();
        }
    }
}

// Parse amenities for display
$room_amenities = [];
if ($room && !empty($room['amenities'])) {
    $room_amenities = json_decode($room['amenities'], true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $room_amenities = [];
    }
}

// Get all unique amenities from all rooms for autocomplete suggestions
$all_amenities = [];
try {
    $stmt = $pdo->query("SELECT DISTINCT amenities FROM rooms WHERE amenities IS NOT NULL AND amenities != ''");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $room_amenities_data = json_decode($row['amenities'], true);
        if (is_array($room_amenities_data)) {
            foreach ($room_amenities_data as $amenity => $available) {
                if (!in_array($amenity, $all_amenities)) {
                    $all_amenities[] = $amenity;
                }
            }
        }
    }
} catch (PDOException $e) {
    // Continue with empty array if there's an error
}

// Fetch room images
$images = [];
if ($room_id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM hotel_images WHERE room_id = ? ORDER BY created_at DESC");
        $stmt->execute([$room_id]);
        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = 'Error fetching room images: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Room - Demo Hotel & Resort</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --secondary: #10b981;
            --secondary-dark: #059669;
            --danger: #ef4444;
            --danger-dark: #dc2626;
            --light: #f8fafc;
            --dark: #0f172a;
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
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
        
        .amenity-checkbox:checked + .amenity-label {
            background-color: var(--secondary);
            color: white;
        }
        
        .image-checkbox:checked + img {
            border: 3px solid var(--primary);
            opacity: 0.8;
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
        
        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--border);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <?php include 'includes/navbar.php'; ?>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edit Room</h1>
                <p class="text-gray-600"><?php echo $room ? htmlspecialchars($room['name']) : 'Room'; ?></p>
            </div>
            <a href="rooms.php" class="btn-outline flex items-center">
                <i class="fas fa-arrow-left mr-2"></i> Back to Rooms
            </a>
        </div>

        <?php if ($error): ?>
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg" role="alert">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($message): ?>
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg" role="alert">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <span><?php echo htmlspecialchars($message); ?></span>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($room): ?>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Room Details Form -->
                <div class="lg:col-span-2">
                    <div class="card">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900">Room Information</h2>
                        </div>
                        <form method="POST" class="p-6">
                            <input type="hidden" name="update_room" value="1">
                            <div class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2" for="name">Room Name *</label>
                                        <input class="form-input w-full" id="name" name="name" type="text" value="<?php echo htmlspecialchars($room['name']); ?>" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2" for="price">Price per Night (₹) *</label>
                                        <input class="form-input w-full" id="price" name="price" type="number" min="1" step="0.01" value="<?php echo $room['price']; ?>" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2" for="capacity">Base Guest Capacity *</label>
                                        <input class="form-input w-full" id="capacity" name="capacity" type="number" min="1" value="<?php echo $room['capacity']; ?>" required>
                                        <p class="text-gray-500 text-xs mt-1">Minimum number of guests included in base price</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2" for="max_adults">Maximum Adults</label>
                                        <input class="form-input w-full" id="max_adults" name="max_adults" type="number" min="1" value="<?php echo isset($room['max_adults']) ? $room['max_adults'] : '2'; ?>">
                                        <p class="text-gray-500 text-xs mt-1">Maximum number of adults allowed in this room</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2" for="max_children">Maximum Children</label>
                                        <input class="form-input w-full" id="max_children" name="max_children" type="number" min="0" value="<?php echo isset($room['max_children']) ? $room['max_children'] : '2'; ?>">
                                        <p class="text-gray-500 text-xs mt-1">Maximum number of children allowed in this room</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2" for="extra_adult_charge">Extra Adult Charge (₹)</label>
                                        <input class="form-input w-full" id="extra_adult_charge" name="extra_adult_charge" type="number" min="0" step="0.01" value="<?php echo isset($room['extra_adult_charge']) ? $room['extra_adult_charge'] : '0.00'; ?>">
                                        <p class="text-gray-500 text-xs mt-1">Charge for each additional adult beyond the base capacity</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2" for="extra_child_charge">Extra Child Charge (₹)</label>
                                        <input class="form-input w-full" id="extra_child_charge" name="extra_child_charge" type="number" min="0" step="0.01" value="<?php echo isset($room['extra_child_charge']) ? $room['extra_child_charge'] : '0.00'; ?>">
                                        <p class="text-gray-500 text-xs mt-1">Charge for each child beyond the base capacity</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2" for="booking_pause">Booking Pause</label>
                                        <div class="flex items-center">
                                            <input type="checkbox" id="booking_pause" name="booking_pause" value="1" class="h-5 w-5 text-indigo-600 rounded" <?php echo (isset($room['booking_pause']) && $room['booking_pause'] == 1) ? 'checked' : ''; ?>>
                                            <label for="booking_pause" class="ml-2 text-gray-700">Pause bookings for this room</label>
                                        </div>
                                        <p class="text-gray-500 text-xs mt-1">When enabled, users won't be able to book this room</p>
                                    </div>
                                </div>
                                
                                <!-- Additional Charges Section -->
                                <div class="mt-8">
                                    <h3 class="section-title">Additional Charges</h3>
                                    <p class="text-gray-600 text-sm mb-4">Add custom charges like GST, service fees, etc. (Use % for percentage or fixed amount)</p>
                                    
                                    <div id="additional-charges-container" class="space-y-3 mb-4">
                                        <?php 
                                        $additional_charges = [];
                                        if (!empty($room['additional_charges'])) {
                                            $additional_charges = json_decode($room['additional_charges'], true);
                                            if (json_last_error() !== JSON_ERROR_NONE) {
                                                $additional_charges = [];
                                            }
                                        }
                                        
                                        $charge_count = 0;
                                        if (!empty($additional_charges)) {
                                            foreach ($additional_charges as $charge_name => $charge_data) {
                                                $charge_amount = is_array($charge_data) ? $charge_data['amount'] : $charge_data;
                                                $charge_type = is_array($charge_data) ? $charge_data['type'] : (strpos($charge_amount, '%') !== false ? 'percentage' : 'fixed');
                                                if ($charge_type === 'percentage') {
                                                    $charge_amount = str_replace('%', '', $charge_amount);
                                                }
                                                
                                                echo '<div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">';
                                                echo '<input type="text" name="additional_charge_name[]" value="' . htmlspecialchars($charge_name) . '" class="form-input flex-1" placeholder="Charge name (e.g., GST, Service Fee)">';
                                                echo '<select name="additional_charge_type[]" class="form-input w-24">';
                                                echo '<option value="fixed" ' . ($charge_type === 'fixed' ? 'selected' : '') . '>Fixed</option>';
                                                echo '<option value="percentage" ' . ($charge_type === 'percentage' ? 'selected' : '') . '>%</option>';
                                                echo '</select>';
                                                echo '<input type="number" name="additional_charge_amount[]" value="' . htmlspecialchars($charge_amount) . '" class="form-input w-24" min="0" step="0.01" placeholder="Amount">';
                                                echo '<button type="button" class="bg-red-100 hover:bg-red-200 text-red-600 rounded-full w-8 h-8 flex items-center justify-center remove-additional-charge">';
                                                echo '<i class="fas fa-times text-sm"></i>';
                                                echo '</button>';
                                                echo '</div>';
                                                $charge_count++;
                                            }
                                        }
                                        
                                        // Add at least one empty field for new charges
                                        if ($charge_count == 0) {
                                            echo '<div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">';
                                            echo '<input type="text" name="additional_charge_name[]" class="form-input flex-1" placeholder="Charge name (e.g., GST, Service Fee)">';
                                            echo '<select name="additional_charge_type[]" class="form-input w-24">';
                                            echo '<option value="fixed">Fixed</option>';
                                            echo '<option value="percentage">%</option>';
                                            echo '</select>';
                                            echo '<input type="number" name="additional_charge_amount[]" class="form-input w-24" min="0" step="0.01" placeholder="Amount">';
                                            echo '<button type="button" class="bg-red-100 hover:bg-red-200 text-red-600 rounded-full w-8 h-8 flex items-center justify-center remove-additional-charge">';
                                            echo '<i class="fas fa-times text-sm"></i>';
                                            echo '</button>';
                                            echo '</div>';
                                        }
                                        ?>
                                    </div>
                                    
                                    <button type="button" id="add-additional-charge" class="btn-outline flex items-center">
                                        <i class="fas fa-plus mr-2"></i> Add Charge
                                    </button>
                                    <p class="text-gray-500 text-xs mt-2">Note: Percentage charges are calculated on the total base price (room price × nights × rooms)</p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2" for="room_overview">
                                        <i class="fas fa-info-circle text-blue-600 mr-2"></i>Room Overview
                                    </label>
                                    <textarea class="form-input w-full" id="room_overview" name="room_overview" rows="6" placeholder="Enter room overview details..."><?php echo htmlspecialchars($room['description']); ?></textarea>
                                    <p class="text-gray-500 text-xs mt-1">This will be displayed as the room overview on the room details page</p>
                                </div>
                                
                                <!-- Custom Room Overview Options -->
                                <div class="mt-8">
                                    <h3 class="section-title">
                                        <i class="fas fa-list text-blue-600 mr-2"></i>Custom Room Overview Options
                                    </h3>
                                    <p class="text-gray-600 text-sm mb-4">Add custom options for room overview (e.g., features, amenities, etc.)</p>
                                    
                                    <div id="room-overview-options-container" class="space-y-3 mb-4">
                                        <?php 
                                        $room_overview_options = [];
                                        if (!empty($room['room_overview_options'])) {
                                            $room_overview_options = json_decode($room['room_overview_options'], true);
                                            if (json_last_error() !== JSON_ERROR_NONE) {
                                                $room_overview_options = [];
                                            }
                                        }
                                        
                                        $option_count = 0;
                                        if (!empty($room_overview_options)) {
                                            foreach ($room_overview_options as $option) {
                                                $icon = isset($option['icon']) ? $option['icon'] : 'fa-check';
                                                $title = isset($option['title']) ? $option['title'] : '';
                                                $description = isset($option['description']) ? $option['description'] : '';
                                                
                                                echo '<div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg room-overview-option">';
                                                echo '<div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-3">';
                                                echo '<input type="text" name="option_icon[]" value="' . htmlspecialchars($icon) . '" class="form-input" placeholder="Icon class (e.g., fa-wifi)">';
                                                echo '<input type="text" name="option_title[]" value="' . htmlspecialchars($title) . '" class="form-input" placeholder="Title (e.g., Wi-Fi)">';
                                                echo '<input type="text" name="option_description[]" value="' . htmlspecialchars($description) . '" class="form-input" placeholder="Description (e.g., Free Wi-Fi)">';
                                                echo '</div>';
                                                echo '<button type="button" class="bg-red-100 hover:bg-red-200 text-red-600 rounded-full w-8 h-8 flex items-center justify-center remove-room-overview-option">';
                                                echo '<i class="fas fa-times text-sm"></i>';
                                                echo '</button>';
                                                echo '</div>';
                                                $option_count++;
                                            }
                                        }
                                        
                                        // Add at least one empty field for new options
                                        if ($option_count == 0) {
                                            echo '<div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg room-overview-option">';
                                            echo '<div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-3">';
                                            echo '<input type="text" name="option_icon[]" class="form-input" placeholder="Icon class (e.g., fa-wifi)">';
                                            echo '<input type="text" name="option_title[]" class="form-input" placeholder="Title (e.g., Wi-Fi)">';
                                            echo '<input type="text" name="option_description[]" class="form-input" placeholder="Description (e.g., Free Wi-Fi)">';
                                            echo '</div>';
                                            echo '<button type="button" class="bg-red-100 hover:bg-red-200 text-red-600 rounded-full w-8 h-8 flex items-center justify-center remove-room-overview-option">';
                                            echo '<i class="fas fa-times text-sm"></i>';
                                            echo '</button>';
                                            echo '</div>';
                                        }
                                        ?>
                                    </div>
                                    
                                    <button type="button" id="add-room-overview-option" class="btn-outline flex items-center">
                                        <i class="fas fa-plus mr-2"></i> Add Option
                                    </button>
                                    <p class="text-gray-500 text-xs mt-2">Note: Use Font Awesome icon classes (e.g., fa-wifi, fa-tv, fa-mountain)</p>
                                </div>
                                
                                <div>
                                    <h3 class="section-title">Amenities</h3>
                                    <p class="text-gray-600 text-sm mb-4">Manage amenities for this room (check for available, uncheck for unavailable):</p>
                                    
                                    <div id="amenities-container" class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
                                        <?php 
                                        $amenity_count = 0;
                                        if (!empty($room_amenities)) {
                                            foreach ($room_amenities as $amenity => $available) {
                                                echo '<div class="flex items-center p-3 bg-gray-50 rounded-lg">';
                                                echo '<input type="checkbox" id="amenity_' . $amenity_count . '" name="amenity_available_' . $amenity_count . '" value="1" class="h-5 w-5 text-green-600 rounded" ' . ($available ? 'checked' : '') . '>';
                                                echo '<input type="text" name="amenity_name[]" value="' . htmlspecialchars($amenity) . '" class="ml-3 form-input flex-1" placeholder="Enter amenity">';
                                                echo '<button type="button" class="ml-2 bg-red-100 hover:bg-red-200 text-red-600 rounded-full w-8 h-8 flex items-center justify-center remove-amenity">';
                                                echo '<i class="fas fa-times text-sm"></i>';
                                                echo '</button>';
                                                echo '</div>';
                                                $amenity_count++;
                                            }
                                        }
                                        
                                        // Add at least one empty field for new amenities
                                        if ($amenity_count == 0) {
                                            echo '<div class="flex items-center p-3 bg-gray-50 rounded-lg">';
                                            echo '<input type="checkbox" id="amenity_0" name="amenity_available_0" value="1" class="h-5 w-5 text-green-600 rounded" checked>';
                                            echo '<input type="text" name="amenity_name[]" class="ml-3 form-input flex-1" placeholder="Enter amenity">';
                                            echo '<button type="button" class="ml-2 bg-red-100 hover:bg-red-200 text-red-600 rounded-full w-8 h-8 flex items-center justify-center remove-amenity">';
                                            echo '<i class="fas fa-times text-sm"></i>';
                                            echo '</button>';
                                            echo '</div>';
                                        }
                                        ?>
                                    </div>
                                    
                                    <button type="button" id="add-amenity" class="btn-outline flex items-center">
                                        <i class="fas fa-plus mr-2"></i> Add Amenity
                                    </button>
                                    
                                    <?php if (!empty($all_amenities)): ?>
                                    <div class="mt-4">
                                        <p class="text-gray-600 text-sm mb-2">Suggested amenities:</p>
                                        <div class="flex flex-wrap gap-2">
                                            <?php foreach ($all_amenities as $amenity): ?>
                                            <span class="suggestion-tag bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm px-3 py-1 rounded-full cursor-pointer" data-amenity="<?php echo htmlspecialchars($amenity); ?>">
                                                <?php echo htmlspecialchars($amenity); ?>
                                            </span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="flex justify-end">
                                    <button type="submit" class="btn-secondary flex items-center">
                                        <i class="fas fa-save mr-2"></i> Save Changes
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Room Images Section -->
                <div>
                    <div class="card sticky top-8">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900">Room Images</h2>
                        </div>
                        <div class="p-6">
                            <!-- Image Upload Form -->
                            <div class="mb-6">
                                <h3 class="font-medium text-gray-900 mb-3">Upload New Images</h3>
                                <form method="POST" enctype="multipart/form-data" class="space-y-4">
                                    <input type="hidden" name="upload_image" value="1">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2" for="room_images">Select Images</label>
                                        <input type="file" id="room_images" name="room_images[]" accept="image/*" class="form-input w-full" multiple required>
                                        <p class="text-gray-500 text-xs mt-1">You can select multiple images at once</p>
                                    </div>
                                    <button type="submit" class="btn-secondary w-full flex items-center justify-center">
                                        <i class="fas fa-upload mr-2"></i> Upload Images
                                    </button>
                                </form>
                            </div>
                            
                            <!-- Preview existing images -->
                            <div>
                                <div class="flex justify-between items-center mb-3">
                                    <h3 class="font-medium text-gray-900">Current Images</h3>
                                    <div class="flex gap-2">
                                        <button type="button" id="select-all-images" class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-800 py-1 px-2 rounded">
                                            Select All
                                        </button>
                                        <button type="button" id="delete-selected-images" class="text-xs bg-red-100 hover:bg-red-200 text-red-600 py-1 px-2 rounded">
                                            <i class="fas fa-trash mr-1"></i> Delete
                                        </button>
                                    </div>
                                </div>
                                <?php if (!empty($images)): ?>
                                    <div id="currentImagesContainer" class="grid grid-cols-2 gap-3">
                                        <?php foreach ($images as $image): ?>
                                            <div class="relative group">
                                                <div class="image-checkbox-container">
                                                    <input type="checkbox" class="image-checkbox absolute top-2 left-2 z-10 w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" data-image-id="<?php echo $image['id']; ?>">
                                                </div>
                                                <img src="../<?php echo htmlspecialchars($image['image_path']); ?>" 
                                                     alt="<?php echo htmlspecialchars($image['caption'] ?: $room['name']); ?>" 
                                                     class="w-full h-24 object-cover rounded-lg cursor-pointer border-2 border-transparent">
                                                <button onclick="deleteRoomImage(<?php echo $image['id']; ?>)" 
                                                        class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity shadow-sm">
                                                    <i class="fas fa-times text-xs"></i>
                                                </button>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-8">
                                        <i class="fas fa-image text-gray-300 text-3xl mb-2"></i>
                                        <p class="text-gray-500 text-sm">No images uploaded yet</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Handle select all images
        document.getElementById('select-all-images').addEventListener('click', function() {
            const checkboxes = document.querySelectorAll('.image-checkbox');
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            
            checkboxes.forEach(cb => {
                cb.checked = !allChecked;
            });
        });
        
        // Handle delete selected images
        document.getElementById('delete-selected-images').addEventListener('click', function() {
            const selectedCheckboxes = document.querySelectorAll('.image-checkbox:checked');
            
            if (selectedCheckboxes.length === 0) {
                alert('Please select at least one image to delete.');
                return;
            }
            
            if (confirm(`Are you sure you want to delete ${selectedCheckboxes.length} image(s)?`)) {
                const imageIds = Array.from(selectedCheckboxes).map(cb => parseInt(cb.getAttribute('data-image-id')));
                
                // Make AJAX calls to delete each image
                let deletePromises = imageIds.map(imageId => {
                    return fetch('delete_room_image.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({image_id: imageId})
                    })
                    .then(response => response.json());
                });
                
                // Wait for all deletions to complete
                Promise.all(deletePromises)
                .then(results => {
                    // Check if all deletions were successful
                    const allSuccessful = results.every(result => result.success);
                    
                    if (allSuccessful) {
                        // Remove deleted images from DOM
                        selectedCheckboxes.forEach(cb => {
                            cb.closest('.group').remove();
                        });
                        alert(`${imageIds.length} image(s) deleted successfully!`);
                    } else {
                        // Some deletions failed
                        const failedCount = results.filter(result => !result.success).length;
                        alert(`Failed to delete ${failedCount} image(s). Please try again.`);
                    }
                })
                .catch(error => {
                    alert('Error deleting images: ' + error.message);
                });
            }
        });
        
        // Delete single room image (existing function)
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
                        // Instead of reloading the page, remove the image element from DOM
                        const imageElement = document.querySelector(`button[onclick="deleteRoomImage(${imageId})"]`).closest('.group');
                        if (imageElement) {
                            imageElement.remove();
                        }
                        // Show success message
                        alert('Image deleted successfully!');
                    } else {
                        alert('Error deleting image: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Error deleting image: ' + error.message);
                });
            }
        }
        
        // Add new amenity input field
        document.getElementById('add-amenity').addEventListener('click', function() {
            const container = document.getElementById('amenities-container');
            const itemCount = container.children.length;
            const newItem = document.createElement('div');
            newItem.className = 'flex items-center p-3 bg-gray-50 rounded-lg';
            newItem.innerHTML = `
                <input type="checkbox" id="amenity_${itemCount}" name="amenity_available_${itemCount}" value="1" class="h-5 w-5 text-green-600 rounded" checked>
                <input type="text" name="amenity_name[]" class="ml-3 form-input flex-1" placeholder="Enter amenity">
                <button type="button" class="ml-2 bg-red-100 hover:bg-red-200 text-red-600 rounded-full w-8 h-8 flex items-center justify-center remove-amenity">
                    <i class="fas fa-times text-sm"></i>
                </button>
            `;
            container.appendChild(newItem);
        });
        
        // Remove amenity input field
        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-amenity')) {
                const item = e.target.closest('.flex.items-center.p-3.bg-gray-50.rounded-lg');
                const container = document.getElementById('amenities-container');
                if (container.children.length > 1) {
                    item.remove();
                } else {
                    // Clear the input but don't remove the last item
                    item.querySelector('input[type="text"]').value = '';
                    item.querySelector('input[type="checkbox"]').checked = true;
                }
            }
        });
        
        // Add suggestion to amenities
        document.addEventListener('click', function(e) {
            if (e.target.closest('.suggestion-tag')) {
                const amenity = e.target.closest('.suggestion-tag').getAttribute('data-amenity');
                const container = document.getElementById('amenities-container');
                
                // Check if amenity already exists
                let exists = false;
                container.querySelectorAll('input[name="amenity_name[]"]').forEach(input => {
                    if (input.value === amenity) {
                        exists = true;
                    }
                });
                
                if (!exists) {
                    const itemCount = container.children.length;
                    const newItem = document.createElement('div');
                    newItem.className = 'flex items-center p-3 bg-gray-50 rounded-lg';
                    newItem.innerHTML = `
                        <input type="checkbox" id="amenity_${itemCount}" name="amenity_available_${itemCount}" value="1" class="h-5 w-5 text-green-600 rounded" checked>
                        <input type="text" name="amenity_name[]" value="${amenity}" class="ml-3 form-input flex-1" placeholder="Enter amenity">
                        <button type="button" class="ml-2 bg-red-100 hover:bg-red-200 text-red-600 rounded-full w-8 h-8 flex items-center justify-center remove-amenity">
                            <i class="fas fa-times text-sm"></i>
                        </button>
                    `;
                    container.appendChild(newItem);
                }
            }
        });
        
        // Add new additional charge input field
        document.getElementById('add-additional-charge').addEventListener('click', function() {
            const container = document.getElementById('additional-charges-container');
            const newItem = document.createElement('div');
            newItem.className = 'flex items-center gap-3 p-3 bg-gray-50 rounded-lg';
            newItem.innerHTML = `
                <input type="text" name="additional_charge_name[]" class="form-input flex-1" placeholder="Charge name (e.g., GST, Service Fee)">
                <select name="additional_charge_type[]" class="form-input w-24">
                    <option value="fixed">Fixed</option>
                    <option value="percentage">%</option>
                </select>
                <input type="number" name="additional_charge_amount[]" class="form-input w-24" min="0" step="0.01" placeholder="Amount">
                <button type="button" class="bg-red-100 hover:bg-red-200 text-red-600 rounded-full w-8 h-8 flex items-center justify-center remove-additional-charge">
                    <i class="fas fa-times text-sm"></i>
                </button>
            `;
            container.appendChild(newItem);
        });
        
        // Remove additional charge input field
        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-additional-charge')) {
                const item = e.target.closest('.flex.items-center.gap-3.p-3.bg-gray-50.rounded-lg');
                const container = document.getElementById('additional-charges-container');
                if (container.children.length > 1) {
                    item.remove();
                } else {
                    // Clear the inputs but don't remove the last item
                    item.querySelector('input[name="additional_charge_name[]"]').value = '';
                    item.querySelector('select[name="additional_charge_type[]"]').value = 'fixed';
                    item.querySelector('input[name="additional_charge_amount[]"]').value = '';
                }
            }
        });
        
        // Add new room overview option input field
        document.getElementById('add-room-overview-option').addEventListener('click', function() {
            const container = document.getElementById('room-overview-options-container');
            const newItem = document.createElement('div');
            newItem.className = 'flex items-center gap-3 p-3 bg-gray-50 rounded-lg room-overview-option';
            newItem.innerHTML = `
                <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-3">
                    <input type="text" name="option_icon[]" class="form-input" placeholder="Icon class (e.g., fa-wifi)">
                    <input type="text" name="option_title[]" class="form-input" placeholder="Title (e.g., Wi-Fi)">
                    <input type="text" name="option_description[]" class="form-input" placeholder="Description (e.g., Free Wi-Fi)">
                </div>
                <button type="button" class="bg-red-100 hover:bg-red-200 text-red-600 rounded-full w-8 h-8 flex items-center justify-center remove-room-overview-option">
                    <i class="fas fa-times text-sm"></i>
                </button>
            `;
            container.appendChild(newItem);
        });
        
        // Remove room overview option input field
        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-room-overview-option')) {
                const item = e.target.closest('.flex.items-center.gap-3.p-3.bg-gray-50.rounded-lg.room-overview-option');
                const container = document.getElementById('room-overview-options-container');
                if (container.children.length > 1) {
                    item.remove();
                } else {
                    // Clear the inputs but don't remove the last item
                    item.querySelector('input[name="option_icon[]"]').value = '';
                    item.querySelector('input[name="option_title[]"]').value = '';
                    item.querySelector('input[name="option_description[]"]').value = '';
                }
            }
        });

        // Open images modal (redirect to rooms.php with room ID)
        function openImagesModal(roomId, roomName) {
            // Redirect to rooms.php with room ID and action
            window.location.href = 'rooms.php?room_id=' + roomId + '&action=images';
        }
        // Re-index amenity checkboxes before form submission
        // This ensures checkbox indices match amenity_name[] indices even after deletions
        document.querySelector('form').addEventListener('submit', function(e) {
            const container = document.getElementById('amenities-container');
            const items = container.querySelectorAll('.flex.items-center.p-3.bg-gray-50.rounded-lg');
            items.forEach(function(item, index) {
                const checkbox = item.querySelector('input[type="checkbox"]');
                if (checkbox) {
                    checkbox.id = 'amenity_' + index;
                    checkbox.name = 'amenity_available_' + index;
                }
            });
        });
    </script>
</body>
</html>