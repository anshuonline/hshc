<?php
session_start();
include '../config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

// Check if 2FA is set up and verified
$stmt = $pdo->prepare("SELECT authy_setup_complete FROM admins WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

// 2FA disabled temporarily
// if ($admin && $admin['authy_setup_complete'] == 1) {
//     if (!isset($_SESSION['authy_verified']) || $_SESSION['authy_verified'] !== true) {
//         header('Location: verify_2fa.php');
//         exit;
//     }
// } else {
//     header('Location: setup_2fa.php');
//     exit;
// }

// Fetch statistics
$stats = [
    'hotels' => 0,
    'images' => 0,
    'admins' => 0,
    'bookings' => 0,
    'subscribers' => 0,
    'reviews' => 0
];

try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM hotels");
    $stats['hotels'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM hotel_images");
    $stats['images'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM admins");
    $stats['admins'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM bookings");
    $stats['bookings'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM newsletter_subscribers");
    $stats['subscribers'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM reviews");
    $stats['reviews'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
} catch (PDOException $e) {
    $error = 'Failed to fetch statistics: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Demo Hotel & Resort</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#0ea5e9',
                        secondary: '#0284c7',
                        accent: '#06b6d4',
                        dark: '#0c4a6e',
                        light: '#f0f9ff'
                    },
                    fontFamily: {
                        'sans': ['Inter', 'sans-serif']
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 min-h-screen font-sans">
    <!-- Modern Responsive Navigation -->
    <nav class="bg-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <span class="text-primary font-bold text-xl">Demo Hotel & Resort</span>
                        <span class="ml-2 text-gray-500 text-sm">Admin</span>
                    </div>
                </div>
                
                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-1">
                    <a href="dashboard.php" class="bg-primary text-white px-4 py-2 rounded-lg text-sm font-medium">Dashboard</a>
                    <div class="relative" id="manage-dropdown">
                        <button class="text-gray-700 hover:text-primary px-4 py-2 rounded-lg text-sm font-medium flex items-center" id="manage-button">
                            Manage <i class="fas fa-chevron-down ml-1 text-xs"></i>
                        </button>
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 hidden z-50" id="manage-menu">
                            <a href="hotels.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Retreat</a>
                            <a href="images.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Images</a>
                            <a href="rooms.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Rooms</a>
                            <a href="bookings.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Bookings</a>
                            <a href="subscribers.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Subscribers</a>
                            <a href="reviews.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Reviews</a>
                            <a href="admins.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Admins</a>
                        </div>
                    </div>
                    <span class="text-gray-700 px-4 py-2 rounded-lg text-sm font-medium flex items-center">
                        <i class="fas fa-user-circle mr-1"></i><?php echo htmlspecialchars($_SESSION['admin_username']); ?>
                    </span>
                    <a href="logout.php" class="text-gray-700 hover:text-primary px-4 py-2 rounded-lg text-sm font-medium flex items-center">
                        <i class="fas fa-sign-out-alt mr-1"></i>Logout
                    </a>
                </div>
                
                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center">
                    <button id="mobile-menu-button" class="text-gray-700 hover:text-primary">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div id="mobile-menu" class="md:hidden hidden bg-white border-t">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                <a href="dashboard.php" class="bg-primary text-white block px-3 py-2 rounded-md text-base font-medium">Dashboard</a>
                <a href="hotels.php" class="text-gray-700 hover:bg-gray-100 block px-3 py-2 rounded-md text-base font-medium">Hotels</a>
                <a href="images.php" class="text-gray-700 hover:bg-gray-100 block px-3 py-2 rounded-md text-base font-medium">Images</a>
                <a href="rooms.php" class="text-gray-700 hover:bg-gray-100 block px-3 py-2 rounded-md text-base font-medium">Rooms</a>
                <a href="bookings.php" class="text-gray-700 hover:bg-gray-100 block px-3 py-2 rounded-md text-base font-medium">Bookings</a>
                <a href="subscribers.php" class="text-gray-700 hover:bg-gray-100 block px-3 py-2 rounded-md text-base font-medium">Subscribers</a>
                <a href="reviews.php" class="text-gray-700 hover:bg-gray-100 block px-3 py-2 rounded-md text-base font-medium">Reviews</a>
                <a href="admins.php" class="text-gray-700 hover:bg-gray-100 block px-3 py-2 rounded-md text-base font-medium">Admins</a>
                <div class="px-3 py-2 text-gray-700 text-base font-medium border-t border-gray-200 mt-2 pt-2">
                    Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!
                </div>
                <a href="logout.php" class="text-gray-700 hover:bg-gray-100 block px-3 py-2 rounded-md text-base font-medium">Logout</a>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
            <p class="mt-2 text-gray-600">Welcome to your admin panel</p>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 mb-8">
            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow duration-300">
                <div class="flex items-center">
                    <div class="p-3 rounded-lg bg-green-100 text-green-600">
                        <i class="fas fa-hotel text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Hotels</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $stats['hotels']; ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow duration-300">
                <div class="flex items-center">
                    <div class="p-3 rounded-lg bg-blue-100 text-blue-600">
                        <i class="fas fa-images text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Images</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $stats['images']; ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow duration-300">
                <div class="flex items-center">
                    <div class="p-3 rounded-lg bg-amber-100 text-amber-600">
                        <i class="fas fa-user-shield text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Admins</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $stats['admins']; ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow duration-300">
                <div class="flex items-center">
                    <div class="p-3 rounded-lg bg-purple-100 text-purple-600">
                        <i class="fas fa-calendar-check text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Bookings</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $stats['bookings']; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow duration-300">
                <div class="flex items-center">
                    <div class="p-3 rounded-lg bg-indigo-100 text-indigo-600">
                        <i class="fas fa-users text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Subscribers</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $stats['subscribers']; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow duration-300">
                <div class="flex items-center">
                    <div class="p-3 rounded-lg bg-pink-100 text-pink-600">
                        <i class="fas fa-comments text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Reviews</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $stats['reviews']; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800">Quick Actions</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <a href="hotels.php" class="relative rounded-lg border border-gray-300 bg-white px-6 py-5 shadow-sm flex items-center space-x-3 hover:border-gray-400 focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary transition-all duration-200">
                        <div class="flex-shrink-0">
                            <i class="fas fa-hotel text-gray-400 text-xl"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <span class="absolute inset-0" aria-hidden="true"></span>
                            <p class="text-sm font-medium text-gray-900">Manage Hotels</p>
                            <p class="text-sm text-gray-500 truncate">Edit hotel information</p>
                        </div>
                    </a>

                    <a href="images.php" class="relative rounded-lg border border-gray-300 bg-white px-6 py-5 shadow-sm flex items-center space-x-3 hover:border-gray-400 focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary transition-all duration-200">
                        <div class="flex-shrink-0">
                            <i class="fas fa-images text-gray-400 text-xl"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <span class="absolute inset-0" aria-hidden="true"></span>
                            <p class="text-sm font-medium text-gray-900">Manage Images</p>
                            <p class="text-sm text-gray-500 truncate">Upload/delete photos</p>
                        </div>
                    </a>

                    <a href="rooms.php" class="relative rounded-lg border border-gray-300 bg-white px-6 py-5 shadow-sm flex items-center space-x-3 hover:border-gray-400 focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary transition-all duration-200">
                        <div class="flex-shrink-0">
                            <i class="fas fa-bed text-gray-400 text-xl"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <span class="absolute inset-0" aria-hidden="true"></span>
                            <p class="text-sm font-medium text-gray-900">Manage Rooms</p>
                            <p class="text-sm text-gray-500 truncate">Manage room types and images</p>
                        </div>
                    </a>

                    <a href="bookings.php" class="relative rounded-lg border border-gray-300 bg-white px-6 py-5 shadow-sm flex items-center space-x-3 hover:border-gray-400 focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary transition-all duration-200">
                        <div class="flex-shrink-0">
                            <i class="fas fa-calendar-check text-gray-400 text-xl"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <span class="absolute inset-0" aria-hidden="true"></span>
                            <p class="text-sm font-medium text-gray-900">Manage Bookings</p>
                            <p class="text-sm text-gray-500 truncate">View and update bookings</p>
                        </div>
                    </a>

                    <a href="admins.php" class="relative rounded-lg border border-gray-300 bg-white px-6 py-5 shadow-sm flex items-center space-x-3 hover:border-gray-400 focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary transition-all duration-200">
                        <div class="flex-shrink-0">
                            <i class="fas fa-user-shield text-gray-400 text-xl"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <span class="absolute inset-0" aria-hidden="true"></span>
                            <p class="text-sm font-medium text-gray-900">Manage Admins</p>
                            <p class="text-sm text-gray-500 truncate">Add/edit administrators</p>
                        </div>
                    </a>

                    <a href="subscribers.php" class="relative rounded-lg border border-gray-300 bg-white px-6 py-5 shadow-sm flex items-center space-x-3 hover:border-gray-400 focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary transition-all duration-200">
                        <div class="flex-shrink-0">
                            <i class="fas fa-users text-gray-400 text-xl"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <span class="absolute inset-0" aria-hidden="true"></span>
                            <p class="text-sm font-medium text-gray-900">Newsletter Subscribers</p>
                            <p class="text-sm text-gray-500 truncate">View and export subscribers</p>
                        </div>
                    </a>
                    
                    <a href="reviews.php" class="relative rounded-lg border border-gray-300 bg-white px-6 py-5 shadow-sm flex items-center space-x-3 hover:border-gray-400 focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary transition-all duration-200">
                        <div class="flex-shrink-0">
                            <i class="fas fa-comments text-gray-400 text-xl"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <span class="absolute inset-0" aria-hidden="true"></span>
                            <p class="text-sm font-medium text-gray-900">Manage Reviews</p>
                            <p class="text-sm text-gray-500 truncate">Approve/reject guest reviews</p>
                        </div>
                    </a>
                </div>
                
                <div class="mt-8 bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-shield-alt text-blue-500 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                <strong>Security Notice:</strong> Your account is protected with two-factor authentication.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            const mobileMenu = document.getElementById('mobile-menu');
            mobileMenu.classList.toggle('hidden');
        });
        
        // Manage dropdown toggle
        const manageButton = document.getElementById('manage-button');
        const manageMenu = document.getElementById('manage-menu');
        
        manageButton.addEventListener('click', function(e) {
            e.stopPropagation();
            manageMenu.classList.toggle('hidden');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!manageButton.contains(e.target) && !manageMenu.contains(e.target)) {
                manageMenu.classList.add('hidden');
            }
        });
    </script>
</body>
</html>