<?php
session_start();
include 'config/db.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Fetch approved reviews with user names
$stmt = $pdo->prepare("SELECT r.*, u.name as user_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.status = 'approved' ORDER BY r.created_at DESC");
$stmt->execute();
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate average rating
$averageRating = 0;
$totalReviews = count($reviews);
if ($totalReviews > 0) {
    $ratingSum = 0;
    foreach ($reviews as $review) {
        $ratingSum += $review['rating'];
    }
    $averageRating = round($ratingSum / $totalReviews, 1);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest Reviews - Demo Hotel & Resort</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#059669',
                        secondary: '#047857',
                        accent: '#10b981',
                        light: '#ecfdf5',
                        dark: '#065f46',
                    },
                    fontFamily: {
                        'serif': ['Playfair Display', 'serif'],
                        'sans': ['Poppins', 'sans-serif']
                    },
                    keyframes: {
                        'fade-in': {
                            '0%': { opacity: '0', transform: 'translateY(10px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' }
                        },
                        'fade-in-up': {
                            '0%': { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' }
                        }
                    },
                    animation: {
                        'fade-in': 'fade-in 0.5s ease-out',
                        'fade-in-up': 'fade-in-up 0.5s ease-out'
                    }
                }
            }
        }
    </script>
    <style type="text/tailwindcss">
        @layer utilities {
            .animate-fade-in {
                animation: fade-in 0.5s ease-out;
            }
            .animate-fade-in-up {
                animation: fade-in-up 0.5s ease-out;
            }
        }
    </style>
</head>
<body class="bg-gray-50 font-sans">
    <?php include 'includes/header_simple.php'; ?>

    <!-- Hero Section with Background Image -->
    <div class="relative bg-gradient-to-r from-primary to-secondary py-20">
        <div class="absolute inset-0 z-0">
            <img src="https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1920&q=80" alt="Hotel Background" class="w-full h-full object-cover opacity-20">
            <div class="absolute inset-0 bg-gradient-to-r from-primary to-secondary opacity-80"></div>
        </div>
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl md:text-5xl font-serif font-bold text-white">Guest Reviews</h1>
            <p class="mt-4 text-xl text-green-100 max-w-3xl mx-auto">Discover why our guests love staying at Demo Hotel & Resort</p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Rating Summary -->
        <?php if ($totalReviews > 0): ?>
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-12 animate-fade-in">
                <div class="p-6 md:p-8">
                    <div class="flex flex-col lg:flex-row items-center justify-between">
                        <div class="text-center lg:text-left mb-8 lg:mb-0">
                            <h2 class="text-2xl font-serif font-bold text-gray-900">Overall Rating</h2>
                            <div class="flex flex-col sm:flex-row items-center mt-4">
                                <!-- Rating Badge -->
                                <div class="relative">
                                    <div class="absolute -inset-1 bg-gradient-to-r from-yellow-400 to-yellow-500 rounded-full blur opacity-75 animate-pulse"></div>
                                    <div class="relative bg-white rounded-full p-1">
                                        <div class="w-24 h-24 rounded-full bg-gradient-to-br from-yellow-300 to-yellow-500 flex flex-col items-center justify-center shadow-lg transform transition-transform hover:scale-105">
                                            <span class="text-3xl font-bold text-gray-900"><?php echo $averageRating; ?></span>
                                            <span class="text-sm text-gray-700">out of 5</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="ml-0 sm:ml-6 mt-4 sm:mt-0">
                                    <div class="flex justify-center sm:justify-start">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <?php if ($i <= floor($averageRating)): ?>
                                                <i class="fas fa-star text-yellow-400 text-xl"></i>
                                            <?php elseif ($i - 0.5 <= $averageRating): ?>
                                                <i class="fas fa-star-half-alt text-yellow-400 text-xl"></i>
                                            <?php else: ?>
                                                <i class="far fa-star text-yellow-400 text-xl"></i>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    </div>
                                    <p class="text-gray-600 mt-2 text-center sm:text-left"><?php echo $totalReviews; ?> reviews</p>
                                    <p class="text-gray-500 mt-1 text-center sm:text-left">Based on guest experiences</p>
                                </div>
                            </div>
                        </div>
                        <!-- Decorative Image for Overall Rating -->
                        <div class="hidden xl:block">
                            <div class="relative">
                                <div class="absolute -inset-2 bg-gradient-to-r from-accent to-secondary rounded-xl blur opacity-75"></div>
                                <div class="relative bg-white rounded-xl p-2 transform transition-transform hover:scale-105">
                                    <img src="https://images.unsplash.com/photo-1611892440504-42a792e24d32?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=200&q=80" alt="Hotel Excellence" class="rounded-lg w-32 h-32 object-cover shadow-lg">
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-6 mt-6 lg:mt-0 lg:w-80">
                            <h3 class="text-lg font-serif font-bold text-gray-900 mb-4">Rating Distribution</h3>
                            <?php
                            // Calculate rating distribution
                            $ratingDistribution = array_fill(1, 5, 0);
                            foreach ($reviews as $review) {
                                $ratingDistribution[$review['rating']]++;
                            }
                            ?>
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <div class="flex items-center mb-2">
                                    <span class="w-8 text-gray-600"><?php echo $i; ?> star</span>
                                    <div class="flex-1 ml-2">
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="bg-yellow-400 h-2 rounded-full transition-all duration-1000 ease-out" style="width: <?php echo $totalReviews > 0 ? ($ratingDistribution[$i] / $totalReviews * 100) : 0; ?>%"></div>
                                        </div>
                                    </div>
                                    <span class="w-10 text-right text-gray-600 ml-2"><?php echo $ratingDistribution[$i]; ?></span>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Reviews List -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="p-6 md:p-8">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
                    <h2 class="text-2xl font-serif font-bold text-gray-900">Guest Reviews</h2>
                    <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']): ?>
                        <a href="write-review.php" class="mt-4 md:mt-0 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-accent hover:bg-secondary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent transform transition-all hover:scale-105">
                            <i class="fas fa-plus mr-2"></i>
                            Write a Review
                        </a>
                    <?php endif; ?>
                </div>

                <?php if (empty($reviews)): ?>
                    <div class="text-center py-12">
                        <i class="fas fa-comments text-gray-300 text-5xl mb-4"></i>
                        <h3 class="text-xl font-medium text-gray-900 mb-2">No reviews yet</h3>
                        <p class="text-gray-500 mb-6">Be the first to share your experience with us.</p>
                        <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']): ?>
                            <a href="write-review.php" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-accent hover:bg-secondary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent transform transition-all hover:scale-105">
                                Write a Review
                            </a>
                        <?php else: ?>
                            <a href="login.php" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-accent hover:bg-secondary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent transform transition-all hover:scale-105">
                                Login to Write a Review
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="space-y-8">
                        <?php foreach ($reviews as $review): ?>
                            <div class="border-b border-gray-200 pb-8 last:border-0 last:pb-0 animate-fade-in-up">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <div class="bg-accent rounded-full w-12 h-12 flex items-center justify-center transform transition-transform hover:scale-110">
                                            <span class="text-white font-bold"><?php echo strtoupper(substr($review['user_name'], 0, 1)); ?></span>
                                        </div>
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                                            <div>
                                                <h3 class="text-lg font-medium text-gray-900"><?php echo htmlspecialchars($review['user_name']); ?></h3>
                                                <div class="flex items-center mt-1">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <?php if ($i <= $review['rating']): ?>
                                                            <i class="fas fa-star text-yellow-400"></i>
                                                        <?php else: ?>
                                                            <i class="far fa-star text-yellow-400"></i>
                                                        <?php endif; ?>
                                                    <?php endfor; ?>
                                                    <span class="ml-2 text-gray-500 text-sm"><?php echo date('M j, Y', strtotime($review['created_at'])); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        <?php if (!empty($review['comment'])): ?>
                                            <div class="mt-4 text-gray-700">
                                                <p><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>