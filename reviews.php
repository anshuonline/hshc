<?php
session_start();
include 'config/db.php';
include 'includes/header.php'; // Include standard luxury header

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

<link rel="stylesheet" href="css/reviews.css">

<!-- Hero Section -->
<section class="relative h-[50vh] flex items-center justify-center overflow-hidden bg-[#030712] pt-24 border-b border-white/5">
    <div class="absolute inset-0 z-0">
        <div class="absolute inset-0 bg-black/70 z-10 backdrop-blur-[2px]"></div>
        <img src="images/hero_reviews.jpg" class="w-full h-full object-cover" alt="Luxury Hotel">
    </div>
    
    <div class="relative z-20 text-center px-4" data-aos="fade-down" data-aos-duration="1500">
        <p class="text-accent uppercase tracking-[0.4em] text-sm font-medium mb-4 font-sans">Guest Experiences</p>
        <h1 class="text-5xl md:text-6xl font-serif text-white mb-6 tracking-wide drop-shadow-lg">
            Our <span class="font-light italic text-accent">Reviews</span>
        </h1>
        <div class="w-16 h-[1px] bg-accent mx-auto"></div>
    </div>
</section>

<!-- Content Section -->
<section class="py-24 bg-[#030712] relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        
        <!-- Rating Summary -->
        <?php if ($totalReviews > 0): ?>
            <div class="glass-effect-dark border border-white/5 shadow-2xl mb-16 relative overflow-hidden p-10" data-aos="fade-up">
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-accent to-transparent opacity-50"></div>
                
                <div class="flex flex-col lg:flex-row items-center justify-between gap-12">
                    <div class="text-center lg:text-left">
                        <h2 class="text-2xl font-serif text-white mb-6">Overall Impression</h2>
                        <div class="flex flex-col sm:flex-row items-center mt-4">
                            <!-- Rating Badge -->
                            <div class="relative">
                                <div class="absolute -inset-1 bg-accent/20 rounded-full blur opacity-75 animate-pulse"></div>
                                <div class="relative w-28 h-28 rounded-full border border-accent flex flex-col items-center justify-center shadow-[0_0_30px_rgba(212,175,55,0.2)] bg-black/50">
                                    <span class="text-4xl font-serif text-accent mb-1"><?php echo $averageRating; ?></span>
                                    <span class="text-xs text-gray-400 font-sans tracking-widest uppercase">out of 5</span>
                                </div>
                            </div>
                            <div class="ml-0 sm:ml-8 mt-6 sm:mt-0">
                                <div class="flex justify-center sm:justify-start space-x-1">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?php if ($i <= floor($averageRating)): ?>
                                            <i class="fas fa-star text-accent text-lg"></i>
                                        <?php elseif ($i - 0.5 <= $averageRating): ?>
                                            <i class="fas fa-star-half-alt text-accent text-lg"></i>
                                        <?php else: ?>
                                            <i class="far fa-star text-accent/30 text-lg"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                                <p class="text-gray-300 font-sans mt-3 text-center sm:text-left tracking-wide"><?php echo $totalReviews; ?> reviews</p>
                                <p class="text-gray-500 font-sans mt-1 text-sm text-center sm:text-left tracking-widest uppercase font-light">Based on verified stays</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="w-full lg:w-96 border-t lg:border-t-0 lg:border-l border-white/10 pt-8 lg:pt-0 lg:pl-12">
                        <h3 class="text-sm font-sans tracking-[0.2em] uppercase text-gray-400 mb-6">Distribution</h3>
                        <?php
                        $ratingDistribution = array_fill(1, 5, 0);
                        foreach ($reviews as $review) {
                            $ratingDistribution[$review['rating']]++;
                        }
                        ?>
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <div class="flex items-center mb-4">
                                <span class="w-12 text-gray-400 text-xs font-sans tracking-widest uppercase"><?php echo $i; ?> star</span>
                                <div class="flex-1 ml-4">
                                    <div class="w-full bg-white/5 h-1">
                                        <div class="bg-accent h-1 transition-all duration-1000 ease-out" style="width: <?php echo $totalReviews > 0 ? ($ratingDistribution[$i] / $totalReviews * 100) : 0; ?>%"></div>
                                    </div>
                                </div>
                                <span class="w-8 text-right text-accent text-sm ml-4 font-serif"><?php echo $ratingDistribution[$i]; ?></span>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Reviews List -->
        <div class="mb-16">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-12 border-b border-white/10 pb-6">
                <h2 class="text-2xl font-serif text-white">Guest Testimonials</h2>
                <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']): ?>
                    <a href="write-review.php" class="mt-6 md:mt-0 inline-flex items-center text-sm font-sans tracking-[0.2em] uppercase px-6 py-3 border border-accent text-accent hover:bg-accent hover:text-[#030712] transition-colors duration-300">
                        <i class="fas fa-pen mr-3"></i> Share Experience
                    </a>
                <?php endif; ?>
            </div>

            <?php if (empty($reviews)): ?>
                <div class="text-center py-24 glass-effect-dark border border-white/5">
                    <i class="fas fa-quote-right text-accent/30 text-5xl mb-6"></i>
                    <h3 class="text-xl font-serif text-white mb-2">Be the First</h3>
                    <p class="text-gray-400 font-sans font-light mb-8 max-w-md mx-auto">We invite you to share your Grand Luxe experience and help inspire future guests.</p>
                    <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']): ?>
                        <a href="write-review.php" class="inline-flex items-center text-sm font-sans tracking-[0.2em] uppercase px-8 py-3 bg-accent text-[#030712] hover:bg-accent-light transition-colors duration-300">
                            Write a Review
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="inline-flex items-center text-sm font-sans tracking-[0.2em] uppercase px-8 py-3 bg-accent text-[#030712] hover:bg-accent-light transition-colors duration-300">
                            Sign in to Review
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <?php foreach ($reviews as $review): ?>
                        <div class="glass-effect-dark border border-white/5 p-8 relative group hover:border-accent/30 transition-colors duration-500" data-aos="fade-up">
                            <i class="fas fa-quote-left text-4xl text-white/5 absolute top-6 right-6"></i>
                            
                            <div class="flex items-center mb-6">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 rounded-full border border-accent/50 flex items-center justify-center bg-black/30">
                                        <span class="text-accent font-serif text-lg"><?php echo strtoupper(substr($review['user_name'], 0, 1)); ?></span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-serif text-white tracking-wide"><?php echo htmlspecialchars($review['user_name']); ?></h3>
                                    <div class="flex items-center mt-1 space-x-1">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <?php if ($i <= $review['rating']): ?>
                                                <i class="fas fa-star text-accent text-xs"></i>
                                            <?php else: ?>
                                                <i class="far fa-star text-accent/30 text-xs"></i>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if (!empty($review['comment'])): ?>
                                <div class="text-gray-400 font-sans font-light leading-relaxed mb-6 italic">
                                    "<?php echo nl2br(htmlspecialchars($review['comment'])); ?>"
                                </div>
                            <?php endif; ?>
                            
                            <div class="border-t border-white/10 pt-4 text-xs font-sans tracking-widest uppercase text-gray-500">
                                <?php echo date('F j, Y', strtotime($review['created_at'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>