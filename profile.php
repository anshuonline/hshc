<?php
session_start();
include 'config/db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
    header('Location: login.php');
    exit;
}

// Fetch user details
$stmt = $pdo->prepare("SELECT name, email, phone, country, created_at FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    // If user not found, logout
    session_destroy();
    header('Location: login.php');
    exit;
}
?>

<?php include 'includes/header.php'; ?>

<!-- Profile Section -->
<section class="min-h-screen pt-40 pb-24 bg-[#030712] relative overflow-hidden">
    <div class="absolute top-0 right-0 w-96 h-96 bg-accent/5 rounded-full blur-[120px] pointer-events-none"></div>
    
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10" data-aos="fade-up" data-aos-duration="1000">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-serif text-white tracking-wide">Your <span class="italic font-light text-accent">Profile</span></h1>
            <div class="w-16 h-[1px] bg-accent mx-auto mt-4 mb-4"></div>
            <p class="text-gray-400 font-sans text-sm font-light uppercase tracking-widest">Manage your Grand Luxe account</p>
        </div>
        
        <div class="glass-effect-dark p-8 md:p-12 border border-white/10 shadow-[0_0_50px_rgba(0,0,0,0.5)]">
            <div class="flex items-center mb-10 pb-8 border-b border-white/10">
                <div class="w-20 h-20 rounded-full border border-accent flex items-center justify-center font-serif text-3xl bg-black/50 text-accent shadow-[0_0_15px_rgba(212,175,55,0.2)]">
                    <?php echo strtoupper($user['name'][0]); ?>
                </div>
                <div class="ml-6">
                    <h2 class="text-2xl font-serif text-white"><?php echo htmlspecialchars($user['name']); ?></h2>
                    <p class="text-gray-400 text-sm font-sans font-light mt-1 tracking-wider uppercase">Member since <?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                <div>
                    <h3 class="text-lg font-serif text-white mb-6 border-b border-white/10 pb-2 inline-block">Personal Information</h3>
                    <div class="space-y-6">
                        <div>
                            <label class="block text-xs uppercase tracking-widest text-gray-500 font-sans mb-1">Full Name</label>
                            <p class="text-gray-200 font-sans"><?php echo htmlspecialchars($user['name']); ?></p>
                        </div>
                        <div>
                            <label class="block text-xs uppercase tracking-widest text-gray-500 font-sans mb-1">Email Address</label>
                            <p class="text-gray-200 font-sans"><?php echo htmlspecialchars($user['email']); ?></p>
                        </div>
                        <div>
                            <label class="block text-xs uppercase tracking-widest text-gray-500 font-sans mb-1">Phone Number</label>
                            <p class="text-gray-200 font-sans"><?php echo htmlspecialchars($user['phone']); ?></p>
                        </div>
                        <div>
                            <label class="block text-xs uppercase tracking-widest text-gray-500 font-sans mb-1">Country</label>
                            <p class="text-gray-200 font-sans"><?php echo htmlspecialchars($user['country'] ?? 'Not specified'); ?></p>
                        </div>
                    </div>
                </div>
                
                <div>
                    <h3 class="text-lg font-serif text-white mb-6 border-b border-white/10 pb-2 inline-block">Quick Actions</h3>
                    <div class="space-y-4">
                        <a href="my-bookings.php" class="block w-full text-left bg-black/30 hover:bg-white/5 border border-white/10 p-4 transition-colors group">
                            <div class="flex items-center justify-between text-gray-300 group-hover:text-white">
                                <span class="font-sans text-sm tracking-widest uppercase">View My Bookings</span>
                                <i class="fas fa-chevron-right text-accent text-xs"></i>
                            </div>
                        </a>
                        <a href="book-now.php" class="block w-full text-left bg-black/30 hover:bg-white/5 border border-white/10 p-4 transition-colors group">
                            <div class="flex items-center justify-between text-gray-300 group-hover:text-white">
                                <span class="font-sans text-sm tracking-widest uppercase">Make a New Reservation</span>
                                <i class="fas fa-chevron-right text-accent text-xs"></i>
                            </div>
                        </a>
                        <a href="logout.php" class="block w-full text-left bg-black/30 hover:bg-red-900/20 border border-white/10 p-4 transition-colors group mt-8">
                            <div class="flex items-center justify-between text-gray-400 group-hover:text-red-400">
                                <span class="font-sans text-sm tracking-widest uppercase">Sign Out</span>
                                <i class="fas fa-sign-out-alt text-xs"></i>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>