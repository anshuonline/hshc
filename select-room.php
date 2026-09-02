<?php
session_start();
include 'config/db.php';

// Fetch all rooms
$stmt = $pdo->query("SELECT * FROM rooms ORDER BY name");
$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

// If no rooms exist, redirect to book now page
if (empty($rooms)) {
    header('Location: book-now.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Room - Demo Hotel & Resort</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#059669', // green-600
                        secondary: '#047857', // green-700
                        accent: '#10b981', // green-500
                        light: '#ecfdf5', // green-50
                        dark: '#065f46', // green-800
                    },
                    fontFamily: {
                        'serif': ['Playfair Display', 'serif'],
                        'sans': ['Poppins', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        .heading {
            font-family: 'Playfair Display', serif;
        }
        .room-card {
            transition: all 0.3s ease;
            border-radius: 1rem;
        }
        .room-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 50px -12px rgba(5, 150, 105, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            box-shadow: 0 4px 6px rgba(5, 150, 105, 0.2);
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(5, 150, 105, 0.3);
        }
        .btn-secondary {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            box-shadow: 0 4px 6px rgba(16, 185, 129, 0.2);
            transition: all 0.3s ease;
        }
        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(16, 185, 129, 0.3);
        }
        .price-tag {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
        }
        .feature-icon {
            background-color: #ecfdf5;
            color: #059669;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <?php include 'includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="relative bg-gradient-to-r from-primary to-secondary text-white py-24">
        <div class="absolute inset-0 bg-black opacity-40"></div>
        <div class="container mx-auto px-4 relative z-10 text-center">
            <h1 class="heading text-4xl md:text-6xl font-bold mb-6">Select Your Perfect Room</h1>
            <p class="text-xl max-w-3xl mx-auto mb-8">Experience luxury and comfort in our thoughtfully designed accommodations</p>
            <div class="flex justify-center space-x-4">
                <span class="inline-flex items-center px-4 py-2 bg-white bg-opacity-20 rounded-full text-sm">
                    <i class="fas fa-mountain mr-2"></i>Mountain View
                </span>
                <span class="inline-flex items-center px-4 py-2 bg-white bg-opacity-20 rounded-full text-sm">
                    <i class="fas fa-wifi mr-2"></i>Free WiFi
                </span>
                <span class="inline-flex items-center px-4 py-2 bg-white bg-opacity-20 rounded-full text-sm">
                    <i class="fas fa-concierge-bell mr-2"></i>24/7 Service
                </span>
            </div>
        </div>
    </section>

    <!-- Room Selection -->
    <section class="py-20">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="heading text-4xl font-bold text-gray-800 mb-4">
                    <i class="fas fa-bed text-primary mr-2"></i>Our Luxury Accommodations
                </h2>
                <p class="text-gray-600 max-w-2xl mx-auto text-lg">Each room is designed to provide the ultimate comfort and relaxation during your stay in Demo City</p>
                <div class="w-24 h-1 bg-primary mx-auto mt-6 rounded-full"></div>
            </div>

            <?php if (empty($rooms)): ?>
                <div class="bg-white rounded-2xl shadow-xl p-12 text-center max-w-2xl mx-auto">
                    <i class="fas fa-bed text-5xl text-gray-300 mb-6"></i>
                    <h3 class="text-2xl font-semibold text-gray-800 mb-4">No Rooms Available</h3>
                    <p class="text-gray-600 mb-8">We're currently updating our room listings. Please check back later.</p>
                    <a href="index.php" class="inline-block btn-primary text-white px-8 py-4 rounded-lg hover:shadow-lg transition-all duration-300 font-medium text-lg">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Home
                    </a>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
                    <?php foreach ($rooms as $room): ?>
                        <div class="room-card bg-white rounded-2xl shadow-lg overflow-hidden">
                            <div class="p-8">
                                <div class="flex justify-between items-start mb-6">
                                    <h3 class="heading text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($room['name']); ?></h3>
                                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-bold text-white price-tag">
                                        ₹<?php echo number_format($room['price']); ?><span class="text-xs font-normal opacity-90">/night</span>
                                    </span>
                                </div>
                                
                                <?php if (isset($room['booking_pause']) && $room['booking_pause'] == 1): ?>
                                    <div class="mb-4">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-pause-circle mr-1"></i> Booking Paused
                                        </span>
                                    </div>
                                <?php endif; ?>
                                
                                <p class="text-gray-600 mb-8"><?php echo htmlspecialchars($room['description']); ?></p>
                                
                                <div class="flex items-center text-gray-500 mb-8 pb-6 border-b border-gray-100">
                                    <div class="feature-icon w-12 h-12 rounded-full flex items-center justify-center mr-3">
                                        <i class="fas fa-user-friends text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-800">Guest Capacity</div>
                                        <div><?php echo $room['capacity']; ?> Guests</div>
                                    </div>
                                </div>
                                
                                <div class="flex space-x-4">
                                    <a href="room-details.php?room_id=<?php echo $room['id']; ?>" 
                                       class="flex-1 btn-primary text-white text-center py-4 rounded-lg hover:shadow-lg transition-all duration-300 font-medium">
                                        View Details
                                    </a>
                                    <?php if (isset($room['booking_pause']) && $room['booking_pause'] == 1): ?>
                                        <button class="flex-1 btn-secondary text-white text-center py-4 rounded-lg font-medium cursor-not-allowed opacity-50" disabled>
                                            Book Now (Paused)
                                        </button>
                                    <?php else: ?>
                                        <a href="book-now.php?room_id=<?php echo $room['id']; ?>" 
                                           class="flex-1 btn-secondary text-white text-center py-4 rounded-lg hover:shadow-lg transition-all duration-300 font-medium">
                                            Book Now
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>
</body>
</html>