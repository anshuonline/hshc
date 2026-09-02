<?php
session_start();
include 'config/db.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
    header('Location: login.php');
    exit;
}

// Fetch user's bookings
$stmt = $pdo->prepare("SELECT b.*, r.name as room_name FROM bookings b LEFT JOIN rooms r ON b.room_id = r.id WHERE b.user_id = ? ORDER BY b.created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<?php include 'includes/header.php'; ?>

<!-- My Bookings Section -->
<section class="min-h-screen pt-40 pb-24 bg-[#030712] relative overflow-hidden">
    <div class="absolute top-0 right-0 w-96 h-96 bg-accent/5 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-96 h-96 bg-secondary/10 rounded-full blur-[120px] pointer-events-none"></div>
    
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10" data-aos="fade-up" data-aos-duration="1000">
        <div class="text-center mb-16">
            <p class="text-accent uppercase tracking-[0.4em] text-sm font-medium mb-4 font-sans">Your Itinerary</p>
            <h1 class="text-4xl md:text-5xl font-serif text-white tracking-wide">My <span class="italic font-light text-accent">Bookings</span></h1>
            <div class="w-16 h-[1px] bg-accent mx-auto mt-6"></div>
        </div>
        
        <?php if (empty($bookings)): ?>
            <div class="glass-effect-dark p-12 text-center border border-white/10 shadow-[0_0_50px_rgba(0,0,0,0.5)]">
                <i class="fas fa-calendar-times text-5xl text-gray-600 mb-6"></i>
                <h3 class="text-2xl font-serif text-white mb-4">No Reservations Found</h3>
                <p class="text-gray-400 font-sans font-light mb-8 max-w-md mx-auto">You haven't made any reservations yet. Experience the epitome of luxury by booking your first stay with us.</p>
                <a href="book-now.php" class="inline-block bg-accent hover:bg-accent-light text-[#030712] px-10 py-4 font-bold tracking-[0.2em] uppercase transition-all duration-300 shadow-[0_0_15px_rgba(212,175,55,0.2)] font-sans text-sm">
                    Reserve a Suite
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 gap-8">
                <?php foreach ($bookings as $booking): ?>
                    <div class="glass-effect-dark border border-white/5 shadow-[0_15px_50px_rgba(0,0,0,0.6)] flex flex-col md:flex-row overflow-hidden group hover:border-accent/40 transition-all duration-500 hover:shadow-[0_20px_60px_rgba(212,175,55,0.15)] relative">
                        <div class="absolute inset-0 bg-gradient-to-r from-accent/0 via-accent/5 to-accent/0 opacity-0 group-hover:opacity-100 transition-opacity duration-700 pointer-events-none"></div>
                        
                        <!-- Booking Status/Date Block -->
                        <div class="bg-black/60 p-8 md:w-1/3 flex flex-col justify-center items-center text-center border-b md:border-b-0 md:border-r border-white/10 relative overflow-hidden backdrop-blur-md">
                            <div class="absolute inset-0 bg-gradient-to-br from-accent/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                            
                            <p class="text-accent uppercase tracking-widest text-xs font-sans mb-2">Booking Ref</p>
                            <p class="text-2xl font-serif text-white mb-6 tracking-widest"><?php echo htmlspecialchars($booking['booking_number']); ?></p>
                            
                            <?php 
                                $status_color = 'text-gray-400';
                                if ($booking['status'] == 'confirmed') $status_color = 'text-green-400';
                                if ($booking['status'] == 'cancelled') $status_color = 'text-red-400';
                            ?>
                            <div class="inline-flex items-center px-4 py-2 border border-white/10 bg-white/5 backdrop-blur-sm rounded-full">
                                <span class="w-2 h-2 rounded-full <?php echo str_replace('text-', 'bg-', $status_color); ?> mr-2"></span>
                                <span class="text-xs uppercase tracking-widest font-sans <?php echo $status_color; ?>"><?php echo htmlspecialchars(ucfirst($booking['status'])); ?></span>
                            </div>
                        </div>
                        
                        <!-- Booking Details Block -->
                        <div class="p-8 md:w-2/3">
                            <div class="flex justify-between items-start mb-6 border-b border-white/10 pb-6">
                                <div>
                                    <h3 class="text-2xl font-serif text-white mb-2"><?php echo htmlspecialchars($booking['room_name'] ?? 'Luxury Suite'); ?></h3>
                                    <p class="text-gray-400 font-sans text-sm font-light uppercase tracking-wider">
                                        <?php echo date('M d, Y', strtotime($booking['check_in'])); ?> &mdash; <?php echo date('M d, Y', strtotime($booking['check_out'])); ?>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-gray-500 font-sans text-xs uppercase tracking-widest mb-1">Total</p>
                                    <p class="text-2xl font-serif text-accent">&#8377;<?php echo number_format($booking['total_price'], 2); ?></p>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-6 mb-8">
                                <div>
                                    <p class="text-gray-500 font-sans text-xs uppercase tracking-widest mb-1">Adults</p>
                                    <p class="text-gray-200 font-sans text-lg"><?php echo htmlspecialchars($booking['adults']); ?></p>
                                </div>
                                <div>
                                    <p class="text-gray-500 font-sans text-xs uppercase tracking-widest mb-1">Children</p>
                                    <p class="text-gray-200 font-sans text-lg"><?php echo htmlspecialchars($booking['children']); ?></p>
                                </div>
                                <div>
                                    <p class="text-gray-500 font-sans text-xs uppercase tracking-widest mb-1">Rooms</p>
                                    <p class="text-gray-200 font-sans text-lg"><?php echo htmlspecialchars($booking['number_of_rooms'] ?? 1); ?></p>
                                </div>
                                <div>
                                    <p class="text-gray-500 font-sans text-xs uppercase tracking-widest mb-1">Nights</p>
                                    <?php 
                                        $check_in = new DateTime($booking['check_in']);
                                        $check_out = new DateTime($booking['check_out']);
                                        $nights = $check_in->diff($check_out)->days;
                                    ?>
                                    <p class="text-gray-200 font-sans text-lg"><?php echo $nights; ?></p>
                                </div>
                            </div>
                            
                            <div class="flex flex-wrap gap-4 mt-4 relative z-10">
                                <a href="print-itinerary.php?id=<?php echo $booking['id']; ?>" target="_blank" class="text-xs font-sans tracking-widest uppercase px-6 py-3 border border-white/20 text-white hover:bg-white hover:text-black transition-all duration-300 flex items-center hover:shadow-[0_0_15px_rgba(255,255,255,0.3)]">
                                    <i class="fas fa-print mr-2"></i> Print Itinerary
                                </a>
                                <?php if ($booking['status'] == 'pending'): ?>
                                    <button class="text-xs font-sans tracking-widest uppercase px-6 py-3 border border-accent/50 text-accent hover:bg-accent hover:text-black transition-colors flex items-center ml-auto">
                                        Pay Now
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Print Styles -->
<style>
    @media print {
        body * {
            visibility: hidden;
        }
        .max-w-6xl, .max-w-6xl * {
            visibility: visible;
        }
        .max-w-6xl {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        button, a, nav, footer {
            display: none !important;
        }
    }
</style>

<?php include 'includes/footer.php'; ?>