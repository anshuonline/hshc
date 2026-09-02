<?php
session_start();
include 'config/db.php';

// Get room ID from URL parameter
$room_id = isset($_GET['room_id']) ? intval($_GET['room_id']) : 0;

// Fetch room details based on room ID
$stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
$stmt->execute([$room_id]);
$room = $stmt->fetch(PDO::FETCH_ASSOC);

// Parse amenities if they exist
$amenities = [];
if (!empty($room['amenities'])) {
    $amenities = json_decode($room['amenities'], true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $amenities = [];
    }
}

// If room not found, redirect to room selection
if (!$room) {
    header('Location: select-room.php');
    exit;
}

// Fetch room-specific images
$stmt = $pdo->prepare("SELECT * FROM hotel_images WHERE room_id = ? ORDER BY created_at DESC");
$stmt->execute([$room['id']]);
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($room['name']); ?> - Demo Hotel & Resort</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        .heading {
            font-family: 'Playfair Display', serif;
        }
        .room-image {
            transition: transform 0.3s ease;
        }
        .room-image:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <?php include 'includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="relative bg-gradient-to-r from-blue-900 to-blue-700 text-white py-20">
        <div class="absolute inset-0 bg-black opacity-50"></div>
        <div class="container mx-auto px-4 relative z-10">
            <h1 class="heading text-4xl md:text-5xl font-bold mb-4"><?php echo htmlspecialchars($room['name']); ?></h1>
            <p class="text-xl max-w-2xl">Experience luxury and comfort in our <?php echo strtolower($room['name']); ?></p>
            <?php if (isset($room['booking_pause']) && $room['booking_pause'] == 1): ?>
                <div class="mt-4">
                    <span class="inline-flex items-center px-4 py-2 rounded-full text-lg font-bold bg-red-600 text-white">
                        <i class="fas fa-pause-circle mr-2"></i> Booking Currently Paused
                    </span>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Room Details -->
    <section class="py-16">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                <!-- Room Information -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
                        <h2 class="heading text-3xl font-bold text-gray-800 mb-6">
                            <i class="fas fa-info-circle text-primary mr-2"></i>Room Overview
                        </h2>
                        <p class="text-gray-600 mb-6"><?php echo htmlspecialchars($room['description']); ?></p>
                        
                        <?php
                        // Parse custom room overview options if they exist
                        $room_overview_options = [];
                        if (!empty($room['room_overview_options'])) {
                            $room_overview_options = json_decode($room['room_overview_options'], true);
                            if (json_last_error() !== JSON_ERROR_NONE) {
                                $room_overview_options = [];
                            }
                        }
                        
                        // Display custom room overview options if they exist
                        if (!empty($room_overview_options)):
                        ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                            <?php foreach ($room_overview_options as $option): ?>
                            <div class="flex items-center">
                                <i class="fas <?php echo htmlspecialchars($option['icon']); ?> text-primary text-xl mr-4"></i>
                                <div>
                                    <h3 class="font-semibold text-gray-800"><?php echo htmlspecialchars($option['title']); ?></h3>
                                    <p class="text-gray-600"><?php echo htmlspecialchars($option['description']); ?></p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <!-- Default predefined options if no custom options are set -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                            <div class="flex items-center">
                                <i class="fas fa-user-friends text-primary text-xl mr-4"></i>
                                <div>
                                    <h3 class="font-semibold text-gray-800">Capacity</h3>
                                    <p class="text-gray-600"><?php echo $room['capacity']; ?> Guests</p>
                                    <?php if ($room['extra_adult_charge'] > 0): ?>
                                        <p class="text-gray-500 text-sm mt-1">Extra adult: ₹<?php echo number_format($room['extra_adult_charge'], 2); ?></p>
                                    <?php endif; ?>
                                    <?php if ($room['extra_child_charge'] > 0): ?>
                                        <p class="text-gray-500 text-sm">Extra child: ₹<?php echo number_format($room['extra_child_charge'], 2); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-ruler-combined text-primary text-xl mr-4"></i>
                                <div>
                                    <h3 class="font-semibold text-gray-800">Size</h3>
                                    <p class="text-gray-600">35 m²</p>
                                </div>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-bed text-primary text-xl mr-4"></i>
                                <div>
                                    <h3 class="font-semibold text-gray-800">Bed Type</h3>
                                    <p class="text-gray-600">King Size Bed</p>
                                </div>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-mountain text-primary text-xl mr-4"></i>
                                <div>
                                    <h3 class="font-semibold text-gray-800">View</h3>
                                    <p class="text-gray-600">Mountain View</p>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="border-t border-gray-200 pt-6">
                            <h3 class="heading text-xl font-bold text-gray-800 mb-4">Amenities</h3>
                            <?php if (!empty($amenities)): ?>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <?php foreach ($amenities as $amenity => $available): ?>
                                        <div class="flex items-center">
                                            <?php if ($available): ?>
                                                <i class="fas fa-check-circle text-green-500 mr-3"></i>
                                                <span class="text-gray-800 font-medium capitalize"><?php echo str_replace('_', ' ', $amenity); ?></span>
                                            <?php else: ?>
                                                <i class="fas fa-times-circle text-red-500 mr-3"></i>
                                                <span class="text-gray-500 capitalize"><?php echo str_replace('_', ' ', $amenity); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-gray-600">No amenities information available for this room.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Room Images -->
                    <?php if (!empty($images)): ?>
                        <div class="bg-white rounded-xl shadow-lg p-8">
                            <h2 class="heading text-3xl font-bold text-gray-800 mb-6">Room Gallery</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <?php foreach (array_slice($images, 0, 4) as $image): ?>
                                    <div class="room-image overflow-hidden rounded-lg shadow-md">
                                        <img src="<?php echo htmlspecialchars($image['image_path']); ?>" 
                                             alt="<?php echo htmlspecialchars($image['caption'] ?: $room['name']); ?>" 
                                             class="w-full h-64 object-cover">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Booking Panel -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-xl shadow-lg p-8 sticky top-8">
                        <h2 class="heading text-2xl font-bold text-gray-800 mb-6">Book This Room</h2>
                        
                        <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-gray-600">Price per night</span>
                                <span class="text-2xl font-bold text-blue-600">₹<?php echo number_format($room['price']); ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">For <?php echo $room['capacity']; ?> guests</span>
                                <span class="text-sm text-gray-500">+ taxes & fees</span>
                            </div>
                        </div>
                        
                        <a href="book-now.php?room_id=<?php echo $room['id']; ?>" 
                           class="block w-full bg-green-600 text-white text-center py-4 rounded-lg hover:bg-green-700 transition duration-300 font-semibold text-lg mb-4 <?php echo (isset($room['booking_pause']) && $room['booking_pause'] == 1) ? 'opacity-50 cursor-not-allowed' : ''; ?>"
                           <?php echo (isset($room['booking_pause']) && $room['booking_pause'] == 1) ? 'disabled' : ''; ?>>
                            <i class="fas fa-calendar-check mr-2"></i>
                            <?php echo (isset($room['booking_pause']) && $room['booking_pause'] == 1) ? 'Booking Paused' : 'Book Now'; ?>
                        </a>
                        
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>
</body>
</html>