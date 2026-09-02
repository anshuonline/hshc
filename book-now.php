<?php
session_start();
include 'config/db.php';
include 'includes/booking_utils.php';
include 'includes/mailer.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
    header('Location: login.php');
    exit;
}

// Get room type from URL parameter
$selected_room_id = isset($_GET['room_id']) ? intval($_GET['room_id']) : 0;

// Fetch all rooms
$stmt = $pdo->query("SELECT * FROM rooms ORDER BY name");
$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Find selected room
$selected_room = null;
if ($selected_room_id > 0) {
    foreach ($rooms as $room) {
        if ($room['id'] == $selected_room_id) {
            $selected_room = $room;
            break;
        }
    }
}

// Fetch user details
$stmt = $pdo->prepare("SELECT name, email, phone FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    // If user not found, logout
    session_destroy();
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';
$bookingNumber = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $check_in = trim($_POST['check_in']);
    $check_out = trim($_POST['check_out']);
    $adults = intval($_POST['adults']);
    $children = intval($_POST['children']);
    $rooms_count = intval($_POST['rooms']);
    $special_requests = trim($_POST['special_requests']);
    $room_id = intval($_POST['room_id']);
    
    // Find the selected room
    $selected_room = null;
    foreach ($rooms as $room) {
        if ($room['id'] == $room_id) {
            $selected_room = $room;
            break;
        }
    }
    
    // Validation
    if (empty($check_in) || empty($check_out)) {
        $error = 'Please select check-in and check-out dates.';
    } elseif (strtotime($check_in) >= strtotime($check_out)) {
        $error = 'Check-out date must be after check-in date.';
    } elseif ($adults < 1) {
        $error = 'Please select at least 1 adult.';
    } elseif ($rooms_count < 1) {
        $error = 'Please select at least 1 room.';
    } elseif (!$selected_room) {
        $error = 'Please select a valid room type.';
    } elseif (isset($selected_room['booking_pause']) && $selected_room['booking_pause'] == 1) {
        $error = 'Booking for this room is currently paused. Please select another room.';
    } else {
        // Calculate nights
        $checkInDate = new DateTime($check_in);
        $checkOutDate = new DateTime($check_out);
        $interval = $checkInDate->diff($checkOutDate);
        $nights = $interval->days;
        
        // Calculate total price
        $basePrice = $selected_room['price'] * $rooms_count * $nights;
        
        // Calculate extra guest charges
        $extraCharges = 0;
        $totalGuests = $adults + $children;
        $baseGuests = $selected_room['capacity'] * $rooms_count;
        
        if ($totalGuests > $baseGuests) {
            // Calculate extra adults and children
            $extraAdults = max(0, $adults - ($selected_room['capacity'] * $rooms_count));
            $extraChildren = max(0, $children);
            
            // Adjust if adults are less than capacity
            if ($adults < ($selected_room['capacity'] * $rooms_count)) {
                $extraChildren = max(0, $children - max(0, ($selected_room['capacity'] * $rooms_count) - $adults));
            }
            
            $extraAdultCharges = $extraAdults * $selected_room['extra_adult_charge'] * $nights * $rooms_count;
            $extraChildCharges = $extraChildren * $selected_room['extra_child_charge'] * $nights * $rooms_count;
            
            $extraCharges = $extraAdultCharges + $extraChildCharges;
        }
        
        // Calculate additional charges
        $additionalCharges = 0;
        if (!empty($selected_room['additional_charges'])) {
            $additionalChargesObj = json_decode($selected_room['additional_charges'], true);
            if (is_array($additionalChargesObj)) {
                foreach ($additionalChargesObj as $chargeName => $chargeData) {
                    if (is_array($chargeData) && isset($chargeData['type']) && isset($chargeData['amount'])) {
                        if ($chargeData['type'] === 'percentage') {
                            $percentage = floatval(str_replace('%', '', $chargeData['amount']));
                            $additionalCharges += ($basePrice * $percentage / 100);
                        } else {
                            $amount = floatval($chargeData['amount']);
                            $additionalCharges += $amount * $rooms_count * $nights;
                        }
                    } else {
                        // Old format - assume fixed amount
                        $amount = floatval($chargeData);
                        $additionalCharges += $amount * $rooms_count * $nights;
                    }
                }
            }
        }
        
        $totalPrice = $basePrice + $extraCharges + $additionalCharges;
        
        // Generate a unique booking number using our utility function
        $bookingNumber = generateUniqueBookingNumber($pdo);
        
        // Save booking to database with default payment status 'pending'
        try {
            $stmt = $pdo->prepare("INSERT INTO bookings (user_id, name, email, phone, check_in, check_out, adults, children, rooms, special_requests, booking_number, room_id, total_price) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $_SESSION['user_id'],
                $user['name'],
                $user['email'],
                $user['phone'],
                $check_in,
                $check_out,
                $adults,
                $children,
                $rooms_count,
                $special_requests,
                $bookingNumber,
                $room_id,
                $totalPrice
            ]);
            
            $bookingId = $pdo->lastInsertId();
            
            // Prepare booking details array for the email
            $bookingDetails = [
                'id' => $bookingId,
                'booking_number' => $bookingNumber,
                'check_in' => $check_in,
                'check_out' => $check_out,
                'adults' => $adults,
                'children' => $children,
                'total_price' => $totalPrice
            ];
            
            // Send Confirmation Email
            sendBookingConfirmationEmail($user['email'], $user['name'], $bookingDetails);
            
            $success = 'Booking submitted successfully!';
        } catch (PDOException $e) {
            $error = 'Failed to submit booking. Please try again.';
        }
    }
} // This closes the else block and the POST if block
?>

<?php include 'includes/header.php'; ?>

<!-- Link specific CSS -->
<link rel="stylesheet" href="css/book-now.css">

<!-- Hero Section -->
<section class="relative h-[40vh] flex items-center justify-center overflow-hidden bg-[#030712] pt-24 border-b border-white/5">
    <div class="absolute inset-0 z-0">
        <div class="absolute inset-0 bg-black/70 z-10 backdrop-blur-[2px]"></div>
        <img src="images/hero_book_now.jpg" class="w-full h-full object-cover" alt="Luxury Suite">
    </div>
    
    <div class="relative z-20 text-center px-4" data-aos="fade-down" data-aos-duration="1500">
        <p class="text-accent uppercase tracking-[0.4em] text-sm font-medium mb-4 font-sans">Reservations</p>
        <h1 class="text-4xl md:text-5xl font-serif text-white mb-6 tracking-wide drop-shadow-lg">
            Secure Your <span class="font-light italic text-accent">Stay</span>
        </h1>
        <div class="w-16 h-[1px] bg-accent mx-auto"></div>
    </div>
</section>

<!-- Content Section -->
<section class="py-24 bg-[#030712] relative overflow-hidden">
    <div class="absolute top-0 right-0 w-96 h-96 bg-accent/5 rounded-full blur-[120px] pointer-events-none"></div>
    
    <!-- Booking Popup Modal (Glassmorphism) -->
    <div id="bookingModal" class="fixed inset-0 bg-black/90 backdrop-blur-sm hidden overflow-y-auto h-full w-full z-[100]">
        <div class="relative top-20 mx-auto p-8 border border-white/10 shadow-[0_0_50px_rgba(212,175,55,0.15)] bg-[#0a0a0a] max-w-lg w-full">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full border border-accent bg-accent/10">
                    <i class="fas fa-check text-accent text-2xl"></i>
                </div>
                <h3 class="text-2xl font-serif text-white mt-6 mb-2">Reservation Secured</h3>
                <div class="mt-4">
                    <p class="text-gray-400 font-sans text-sm font-light leading-relaxed">Thank you for your reservation. Our concierge will contact you shortly to confirm details.</p>
                    <?php if ($bookingNumber): ?>
                        <p class="text-gray-400 font-sans mt-4 text-sm tracking-widest uppercase">Booking Reference: <br><span class="text-xl text-accent font-serif tracking-widest mt-2 inline-block"><?php echo htmlspecialchars($bookingNumber); ?></span></p>
                    <?php endif; ?>
                    
                    <!-- Email Notification Notice -->
                    <div class="mt-5 bg-accent/10 border border-accent/30 px-4 py-3 text-left">
                        <div class="flex items-start gap-3">
                            <i class="fas fa-envelope text-accent mt-0.5 text-sm flex-shrink-0"></i>
                            <div>
                                <p class="text-accent font-sans text-xs font-semibold uppercase tracking-widest mb-1">Confirmation Email Sent</p>
                                <p class="text-gray-300 font-sans text-xs font-light leading-relaxed">We've sent a booking confirmation with your e-ticket to your registered email address.</p>
                                <p class="text-yellow-400/80 font-sans text-xs mt-2 flex items-center gap-1">
                                    <i class="fas fa-exclamation-triangle text-[10px]"></i>
                                    If you don't see it, please check your <strong>Spam / Junk</strong> folder.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <p class="text-gray-500 font-sans mt-4 text-xs tracking-widest uppercase border-t border-white/10 pt-4">Status: Pending <br> (Payment details via phone)</p>
                </div>
                <div class="mt-8">
                    <button id="closeModal" class="w-full bg-accent text-[#030712] py-3 text-sm tracking-[0.2em] uppercase transition-colors hover:bg-accent-light">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="max-w-4xl mx-auto px-4 relative z-10" data-aos="fade-up">
        <div class="glass-effect-dark border border-white/5 shadow-2xl relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-accent to-transparent opacity-50"></div>
            
            <div class="px-8 py-6 border-b border-white/10">
                <h2 class="text-2xl font-serif text-white">Guest Information</h2>
                <p class="text-gray-400 font-sans text-sm tracking-wide mt-1">Please provide details for your reservation</p>
            </div>
            
            <?php if ($error): ?>
                <div class="mx-8 mt-6 bg-red-900/30 border border-red-500/50 text-red-200 px-4 py-3 text-sm font-sans tracking-wide" role="alert">
                    <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>
            
            <form class="p-8" method="POST">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Readonly User Details -->
                    <div class="col-span-1 md:col-span-2 grid grid-cols-1 md:grid-cols-3 gap-6 border-b border-white/10 pb-8 mb-4">
                        <div>
                            <label class="block text-xs font-sans tracking-[0.2em] uppercase text-gray-400 mb-2">Guest Name</label>
                            <input type="text" value="<?php echo htmlspecialchars($user['name']); ?>" readonly class="w-full bg-white/5 border border-white/10 text-gray-300 px-4 py-3 font-sans text-sm cursor-not-allowed">
                        </div>
                        <div>
                            <label class="block text-xs font-sans tracking-[0.2em] uppercase text-gray-400 mb-2">Email</label>
                            <input type="text" value="<?php echo htmlspecialchars($user['email']); ?>" readonly class="w-full bg-white/5 border border-white/10 text-gray-300 px-4 py-3 font-sans text-sm cursor-not-allowed">
                        </div>
                        <div>
                            <label class="block text-xs font-sans tracking-[0.2em] uppercase text-gray-400 mb-2">Phone</label>
                            <input type="text" value="<?php echo htmlspecialchars($user['phone']); ?>" readonly class="w-full bg-white/5 border border-white/10 text-gray-300 px-4 py-3 font-sans text-sm cursor-not-allowed">
                        </div>
                    </div>

                    <!-- Booking Details -->
                    <div>
                        <label for="check_in" class="block text-xs font-sans tracking-[0.2em] uppercase text-gray-400 mb-2">Arrival</label>
                        <input type="date" id="check_in" name="check_in" required min="<?php echo date('Y-m-d'); ?>" class="w-full bg-transparent border-b border-white/20 text-white px-0 py-3 focus:outline-none focus:border-accent transition-colors font-sans color-scheme-dark">
                    </div>
                    
                    <div>
                        <label for="check_out" class="block text-xs font-sans tracking-[0.2em] uppercase text-gray-400 mb-2">Departure</label>
                        <input type="date" id="check_out" name="check_out" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" class="w-full bg-transparent border-b border-white/20 text-white px-0 py-3 focus:outline-none focus:border-accent transition-colors font-sans color-scheme-dark">
                    </div>
                    
                    <div>
                        <label for="room_id" class="block text-xs font-sans tracking-[0.2em] uppercase text-gray-400 mb-2">Suite Selection</label>
                        <select id="room_id" name="room_id" required class="w-full bg-[#0a0a0a] border-b border-white/20 text-white px-0 py-3 focus:outline-none focus:border-accent transition-colors font-sans appearance-none rounded-none">
                            <option value="">Select Suite</option>
                            <?php foreach ($rooms as $room): ?>
                                <option value="<?php echo $room['id']; ?>" 
                                        data-price="<?php echo $room['price']; ?>" 
                                        data-capacity="<?php echo $room['capacity']; ?>" 
                                        data-max-adults="<?php echo $room['max_adults']; ?>" 
                                        data-max-children="<?php echo $room['max_children']; ?>" 
                                        data-extra-adult="<?php echo $room['extra_adult_charge']; ?>" 
                                        data-extra-child="<?php echo $room['extra_child_charge']; ?>"
                                        data-additional-charges="<?php echo htmlspecialchars($room['additional_charges'] ?? '{}'); ?>"
                                        <?php echo (isset($room['booking_pause']) && $room['booking_pause'] == 1) ? 'disabled' : ''; ?>
                                        <?php echo ($selected_room && $selected_room['id'] == $room['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($room['name']); ?> - ₹<?php echo number_format($room['price']); ?>/night
                                    <?php echo (isset($room['booking_pause']) && $room['booking_pause'] == 1) ? ' (Paused)' : ''; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="rooms" class="block text-xs font-sans tracking-[0.2em] uppercase text-gray-400 mb-2">Number of Suites</label>
                        <select id="rooms" name="rooms" class="w-full bg-[#0a0a0a] border-b border-white/20 text-white px-0 py-3 focus:outline-none focus:border-accent transition-colors font-sans appearance-none rounded-none cursor-not-allowed" disabled>
                            <option value="1" selected>1 Suite</option>
                        </select>
                        <input type="hidden" name="rooms" value="1">
                    </div>
                    
                    <div>
                        <label for="adults" class="block text-xs font-sans tracking-[0.2em] uppercase text-gray-400 mb-2">Adults</label>
                        <select id="adults" name="adults" class="w-full bg-[#0a0a0a] border-b border-white/20 text-white px-0 py-3 focus:outline-none focus:border-accent transition-colors font-sans appearance-none rounded-none">
                        </select>
                    </div>
                    
                    <div>
                        <label for="children" class="block text-xs font-sans tracking-[0.2em] uppercase text-gray-400 mb-2">Children</label>
                        <select id="children" name="children" class="w-full bg-[#0a0a0a] border-b border-white/20 text-white px-0 py-3 focus:outline-none focus:border-accent transition-colors font-sans appearance-none rounded-none">
                        </select>
                    </div>
                </div>
                
                <!-- Booking Summary -->
                <div class="mt-12 relative overflow-hidden" style="border: 1px solid rgba(212,175,55,0.2); background: linear-gradient(135deg, rgba(212,175,55,0.05) 0%, rgba(255,255,255,0.03) 100%); backdrop-filter: blur(10px);">
                    <!-- Gold top accent line -->
                    <div style="height: 2px; background: linear-gradient(90deg, transparent, #d4af37, transparent);"></div>
                    
                    <div class="p-6">
                        <!-- Header -->
                        <div class="flex items-center gap-3 mb-6">
                            <div style="width:32px;height:32px;background:rgba(212,175,55,0.15);border:1px solid rgba(212,175,55,0.3);display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-receipt text-accent text-xs"></i>
                            </div>
                            <h3 class="text-xs font-sans tracking-[0.3em] uppercase text-white">Reservation Summary</h3>
                        </div>

                        <!-- Line Items -->
                        <div class="space-y-3 font-sans text-sm">

                            <!-- Base Rate -->
                            <div class="flex justify-between items-center py-2" style="border-bottom: 1px solid rgba(255,255,255,0.06);">
                                <div class="flex items-center gap-2 text-gray-400">
                                    <i class="fas fa-bed text-accent/50 w-4 text-center text-xs"></i>
                                    <span class="tracking-wide text-xs uppercase">Base Rate</span>
                                </div>
                                <span id="base-price" class="text-white font-medium">₹0</span>
                            </div>

                            <!-- Duration -->
                            <div class="flex justify-between items-center py-2" style="border-bottom: 1px solid rgba(255,255,255,0.06);">
                                <div class="flex items-center gap-2 text-gray-400">
                                    <i class="fas fa-moon text-accent/50 w-4 text-center text-xs"></i>
                                    <span class="tracking-wide text-xs uppercase">Duration</span>
                                </div>
                                <span class="text-white font-medium"><span id="nights">0</span> Night(s)</span>
                            </div>

                            <!-- Suites -->
                            <div class="flex justify-between items-center py-2" style="border-bottom: 1px solid rgba(255,255,255,0.06);">
                                <div class="flex items-center gap-2 text-gray-400">
                                    <i class="fas fa-door-open text-accent/50 w-4 text-center text-xs"></i>
                                    <span class="tracking-wide text-xs uppercase">Suites</span>
                                </div>
                                <span class="text-white font-medium"><span id="room-count">0</span> Room(s)</span>
                            </div>

                            <!-- Additional Guests (hidden by default) -->
                            <div id="extra-charges-row" class="hidden">
                                <div class="flex justify-between items-center py-2" style="border-bottom: 1px solid rgba(255,255,255,0.06);">
                                    <div class="flex items-center gap-2">
                                        <i class="fas fa-user-plus text-accent/50 w-4 text-center text-xs"></i>
                                        <span class="tracking-wide text-xs uppercase text-accent">Additional Guests</span>
                                    </div>
                                    <span id="extra-charges" class="text-accent font-medium">₹0</span>
                                </div>
                                <div id="extra-charges-details" class="text-xs text-gray-500 mt-1 pl-6 leading-relaxed"></div>
                            </div>

                            <!-- Taxes & Fees (hidden by default) -->
                            <div id="additional-charges-row" class="hidden">
                                <div class="flex justify-between items-center py-2" style="border-bottom: 1px solid rgba(255,255,255,0.06);">
                                    <div class="flex items-center gap-2">
                                        <i class="fas fa-percent text-accent/50 w-4 text-center text-xs"></i>
                                        <span class="tracking-wide text-xs uppercase text-accent">Taxes & Fees</span>
                                    </div>
                                    <span id="additional-charges" class="text-accent font-medium">₹0</span>
                                </div>
                                <div id="additional-charges-details" class="text-xs text-gray-500 mt-1 pl-6 leading-relaxed space-y-1"></div>
                            </div>
                        </div>

                        <!-- Total -->
                        <div class="mt-6 pt-4" style="border-top: 1px solid rgba(212,175,55,0.3);">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-xs tracking-[0.25em] uppercase text-gray-400 font-sans">Total Estimate</p>
                                    <p class="text-xs text-gray-600 font-sans mt-0.5">Incl. all taxes & charges</p>
                                </div>
                                <div class="text-right">
                                    <span id="total-amount" class="text-3xl font-serif" style="color: #d4af37; text-shadow: 0 0 20px rgba(212,175,55,0.3);">₹0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-8">
                    <label for="special_requests" class="block text-xs font-sans tracking-[0.2em] uppercase text-gray-400 mb-2">Special Requests</label>
                    <textarea id="special_requests" name="special_requests" rows="2" class="w-full bg-transparent border-b border-white/20 text-white px-0 py-3 focus:outline-none focus:border-accent transition-colors font-sans placeholder-gray-600 resize-none" placeholder="Dietary requirements, celebrations, arrival time..."></textarea>
                </div>
                
                <div class="mt-12 text-center">
                    <button type="submit" class="bg-accent hover:bg-accent-light text-[#030712] px-12 py-4 text-sm font-sans tracking-[0.2em] uppercase transition-all duration-300 shadow-[0_0_20px_rgba(212,175,55,0.2)] hover:shadow-[0_0_30px_rgba(212,175,55,0.4)]">
                        Confirm Reservation
                    </button>
                    <p class="text-gray-500 font-sans text-xs mt-6 tracking-widest uppercase font-light">Payment will be arranged securely via concierge upon confirmation.</p>
                </div>
            </form>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
<style>
/* Adjust input date color picker icon for dark theme */
input[type="date"]::-webkit-calendar-picker-indicator {
    filter: invert(1);
    opacity: 0.5;
    cursor: pointer;
}
input[type="date"]::-webkit-calendar-picker-indicator:hover {
    opacity: 0.8;
}
</style>
    
    <script>
        // Show modal if booking was successful
        <?php if ($success): ?>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('bookingModal').classList.remove('hidden');
        });
        <?php endif; ?>
        
        // Close modal
        document.getElementById('closeModal').addEventListener('click', function() {
            document.getElementById('bookingModal').classList.add('hidden');
        });
        
        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('bookingModal');
            if (event.target == modal) {
                modal.classList.add('hidden');
            }
        });
        
        // Set minimum check-out date based on check-in date
        document.getElementById('check_in').addEventListener('change', function() {
            const checkInDate = new Date(this.value);
            const checkOutDate = new Date(checkInDate);
            checkOutDate.setDate(checkOutDate.getDate() + 1);
            
            const minCheckOut = checkOutDate.toISOString().split('T')[0];
            document.getElementById('check_out').min = minCheckOut;
            
            // If check-out date is before new minimum, update it
            if (document.getElementById('check_out').value < minCheckOut) {
                document.getElementById('check_out').value = minCheckOut;
            }
            
            // Update booking summary
            updateBookingSummary();
        });
        
        // Update booking summary when check-out date changes
        document.getElementById('check_out').addEventListener('change', function() {
            updateBookingSummary();
        });
        
        // Function to populate adults dropdown based on room selection
        function populateAdultsDropdown(maxAdults) {
            const adultsSelect = document.getElementById('adults');
            const currentAdults = adultsSelect.value;
            
            // Clear existing options
            adultsSelect.innerHTML = '';
            
            // Populate with options up to max adults
            for (let i = 1; i <= maxAdults; i++) {
                const option = document.createElement('option');
                option.value = i;
                option.textContent = i + ' Adult' + (i > 1 ? 's' : '');
                if (i == 2) { // Default to 2 adults
                    option.selected = true;
                }
                adultsSelect.appendChild(option);
            }
            
            // If current selection is higher than max, select the max
            if (currentAdults > maxAdults) {
                adultsSelect.value = maxAdults;
            }
        }
        
        // Function to populate children dropdown based on room selection
        function populateChildrenDropdown(maxChildren) {
            const childrenSelect = document.getElementById('children');
            const currentChildren = childrenSelect.value;
            
            // Clear existing options
            childrenSelect.innerHTML = '';
            
            // Populate with options from 0 to max children
            for (let i = 0; i <= maxChildren; i++) {
                const option = document.createElement('option');
                option.value = i;
                option.textContent = i + ' Child' + (i !== 1 ? 'ren' : '');
                if (i == 0) { // Default to 0 children
                    option.selected = true;
                }
                childrenSelect.appendChild(option);
            }
            
            // If current selection is higher than max, select the max
            if (currentChildren > maxChildren) {
                childrenSelect.value = maxChildren;
            }
        }
        
        // Function to update dropdowns when room selection changes
        function updateRoomBasedDropdowns() {
            const roomId = document.getElementById('room_id').value;
            
            if (!roomId) {
                // Reset to default values if no room selected
                populateAdultsDropdown(10);
                populateChildrenDropdown(10);
                return;
            }
            
            // Get room data from selected option
            const selectedOption = document.getElementById('room_id').selectedOptions[0];
            const maxAdults = parseInt(selectedOption.getAttribute('data-max-adults')) || 10;
            const maxChildren = parseInt(selectedOption.getAttribute('data-max-children')) || 10;
            
            // Update dropdowns
            populateAdultsDropdown(maxAdults);
            populateChildrenDropdown(maxChildren);
            
            // Update booking summary
            updateBookingSummary();
        }
        
        // Function to calculate number of nights
        function calculateNights() {
            const checkIn = document.getElementById('check_in').value;
            const checkOut = document.getElementById('check_out').value;
            
            if (!checkIn || !checkOut) return 0;
            
            const startDate = new Date(checkIn);
            const endDate = new Date(checkOut);
            
            // Calculate the time difference in milliseconds
            const timeDiff = endDate.getTime() - startDate.getTime();
            
            // Convert milliseconds to days
            const daysDiff = timeDiff / (1000 * 3600 * 24);
            
            return Math.max(0, Math.round(daysDiff));
        }
        
        // Function to update booking summary
        function updateBookingSummary() {
            const roomId = document.getElementById('room_id').value;
            const rooms = 1; // Always use 1 room since the dropdown is disabled
            const adults = parseInt(document.getElementById('adults').value) || 1;
            const children = parseInt(document.getElementById('children').value) || 0;
            const nights = calculateNights();
            
            // Update display elements
            document.getElementById('nights').textContent = nights;
            document.getElementById('room-count').textContent = rooms;
            
            if (!roomId) {
                document.getElementById('base-price').textContent = '₹0.00';
                document.getElementById('extra-charges').textContent = '₹0.00';
                document.getElementById('additional-charges').textContent = '₹0.00';
                document.getElementById('total-amount').textContent = '₹0.00';
                document.getElementById('extra-charges-row').classList.add('hidden');
                document.getElementById('additional-charges-row').classList.add('hidden');
                return;
            }
            
            // Get room data from selected option
            const selectedOption = document.getElementById('room_id').selectedOptions[0];
            const roomPrice = parseFloat(selectedOption.getAttribute('data-price')) || 0;
            const roomCapacity = parseInt(selectedOption.getAttribute('data-capacity')) || 1;
            const extraAdultCharge = parseFloat(selectedOption.getAttribute('data-extra-adult')) || 0;
            const extraChildCharge = parseFloat(selectedOption.getAttribute('data-extra-child')) || 0;
            
            // Calculate base price
            const basePrice = roomPrice * rooms * nights;
            document.getElementById('base-price').textContent = '₹' + basePrice.toFixed(2);
            
            // Calculate extra charges
            let extraCharges = 0;
            let extraChargesDetails = '';
            
            if (adults > 0 || children > 0) {
                const totalGuests = adults + children;
                const baseGuests = roomCapacity * rooms;
                
                if (totalGuests > baseGuests) {
                    // Calculate extra adults and children
                    let extraAdults = Math.max(0, adults - (roomCapacity * rooms));
                    let extraChildren = Math.max(0, children);
                    
                    // Adjust if adults are less than capacity
                    if (adults < (roomCapacity * rooms)) {
                        extraChildren = Math.max(0, children - Math.max(0, (roomCapacity * rooms) - adults));
                    }
                    
                    const extraAdultCharges = extraAdults * extraAdultCharge * nights * rooms;
                    const extraChildCharges = extraChildren * extraChildCharge * nights * rooms;
                    
                    extraCharges = extraAdultCharges + extraChildCharges;
                    
                    if (extraCharges > 0) {
                        extraChargesDetails = '<ul class="list-disc pl-5 space-y-1 mt-1">';
                        if (extraAdultCharges > 0) {
                            extraChargesDetails += `<li>${extraAdults} extra adult(s) × ₹${extraAdultCharge.toFixed(2)} × ${nights} night(s) × ${rooms} room(s) = ₹${extraAdultCharges.toFixed(2)}</li>`;
                        }
                        if (extraChildCharges > 0) {
                            extraChargesDetails += `<li>${extraChildren} extra child(ren) × ₹${extraChildCharge.toFixed(2)} × ${nights} night(s) × ${rooms} room(s) = ₹${extraChildCharges.toFixed(2)}</li>`;
                        }
                        extraChargesDetails += '</ul>';
                        
                        document.getElementById('extra-charges-details').innerHTML = extraChargesDetails;
                        document.getElementById('extra-charges-row').classList.remove('hidden');
                    } else {
                        document.getElementById('extra-charges-row').classList.add('hidden');
                    }
                } else {
                    document.getElementById('extra-charges-row').classList.add('hidden');
                }
            }
            
            document.getElementById('extra-charges').textContent = '₹' + extraCharges.toFixed(2);
            
            // Calculate additional charges
            let additionalCharges = 0;
            let additionalChargesDetails = '';
            
            // Get additional charges from data attribute (stored as JSON)
            const additionalChargesData = selectedOption.getAttribute('data-additional-charges');
            if (additionalChargesData) {
                try {
                    const additionalChargesObj = JSON.parse(additionalChargesData);
                    if (additionalChargesObj && typeof additionalChargesObj === 'object') {
                        additionalChargesDetails = '<ul class="list-disc pl-5 space-y-1 mt-1">';
                        
                        for (const [chargeName, chargeData] of Object.entries(additionalChargesObj)) {
                            let chargeTotal = 0;
                            
                            if (typeof chargeData === 'object' && chargeData.type) {
                                // New format with type information
                                if (chargeData.type === 'percentage') {
                                    const percentage = parseFloat(chargeData.amount);
                                    chargeTotal = (basePrice * percentage / 100);
                                    additionalChargesDetails += `<li>${chargeName}: ${percentage}% of ₹${basePrice.toFixed(2)} = ₹${chargeTotal.toFixed(2)}</li>`;
                                } else {
                                    // Fixed amount per room per night
                                    const amount = parseFloat(chargeData.amount);
                                    chargeTotal = amount * rooms * nights;
                                    additionalChargesDetails += `<li>${chargeName}: ₹${amount.toFixed(2)} × ${rooms} room(s) × ${nights} night(s) = ₹${chargeTotal.toFixed(2)}</li>`;
                                }
                            } else {
                                // Old format - assume fixed amount
                                const amount = typeof chargeData === 'object' ? parseFloat(chargeData.amount) : parseFloat(chargeData);
                                chargeTotal = amount * rooms * nights;
                                additionalChargesDetails += `<li>${chargeName}: ₹${amount.toFixed(2)} × ${rooms} room(s) × ${nights} night(s) = ₹${chargeTotal.toFixed(2)}</li>`;
                            }
                            
                            additionalCharges += chargeTotal;
                        }
                        
                        additionalChargesDetails += '</ul>';
                        
                        document.getElementById('additional-charges-details').innerHTML = additionalChargesDetails;
                        document.getElementById('additional-charges-row').classList.remove('hidden');
                    }
                } catch (e) {
                    console.error('Error parsing additional charges:', e);
                }
            } else {
                document.getElementById('additional-charges-row').classList.add('hidden');
            }
            
            document.getElementById('additional-charges').textContent = '₹' + additionalCharges.toFixed(2);
            
            // Calculate total amount
            const totalAmount = basePrice + extraCharges + additionalCharges;
            document.getElementById('total-amount').textContent = '₹' + totalAmount.toFixed(2);
        }
        
        // Add event listeners
        document.getElementById('room_id').addEventListener('change', updateRoomBasedDropdowns);
        document.getElementById('adults').addEventListener('change', updateBookingSummary);
        document.getElementById('children').addEventListener('change', updateBookingSummary);
        
        // Initialize form
        document.addEventListener('DOMContentLoaded', function() {
            // Set default dates
            const today = new Date();
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            
            document.getElementById('check_in').valueAsDate = today;
            document.getElementById('check_out').valueAsDate = tomorrow;
            
            // Set min dates
            document.getElementById('check_in').min = today.toISOString().split('T')[0];
            document.getElementById('check_out').min = tomorrow.toISOString().split('T')[0];
            
            // Initialize dropdowns
            populateAdultsDropdown(10);
            populateChildrenDropdown(10);
            
            // Update based on room selection
            updateRoomBasedDropdowns();
        });
    </script>
</body>
</html>