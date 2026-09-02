<?php
session_start();
include 'config/db.php';
include 'includes/booking_utils.php';

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
            
            $success = 'Booking submitted successfully!';
        } catch (PDOException $e) {
            $error = 'Failed to submit booking. Please try again.';
        }
    }
} // This closes the else block and the POST if block
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Now - Demo Hotel & Resort</title>
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
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 font-sans">
    <?php include 'includes/header.php'; ?>
    
    <!-- Booking Popup Modal -->
    <div id="bookingModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                    <i class="fas fa-check text-green-600 text-xl"></i>
                </div>
                <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">Booking Submitted!</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-gray-700">Thank you for your booking. Our team will contact you shortly to confirm your reservation.</p>
                    <?php if ($bookingNumber): ?>
                        <p class="text-gray-700 mt-2"><strong>Booking Number:</strong> <span class="font-semibold text-green-600"><?php echo htmlspecialchars($bookingNumber); ?></span></p>
                    <?php endif; ?>
                    <p class="text-gray-700 mt-2">Payment status: <span class="font-semibold">Pending</span>. You will receive payment instructions via phone call.</p>
                </div>
                <div class="items-center px-4 py-3">
                    <button id="closeModal" class="px-4 py-2 bg-green-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="max-w-4xl mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h1 class="text-2xl font-bold text-gray-800">Book Your Stay</h1>
                <p class="text-gray-600 mt-1">Reserve your room at Demo Hotel & Resort</p>
            </div>
            
            <?php if ($error): ?>
                <div class="mx-6 mt-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>
            
            <form class="p-6" method="POST">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" readonly class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 bg-gray-100">
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 bg-gray-100">
                    </div>
                    
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" readonly class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 bg-gray-100">
                    </div>
                    
                    <div>
                        <label for="check_in" class="block text-sm font-medium text-gray-700">Check-in Date</label>
                        <input type="date" id="check_in" name="check_in" required min="<?php echo date('Y-m-d'); ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-green-500 focus:border-green-500">
                    </div>
                    
                    <div>
                        <label for="check_out" class="block text-sm font-medium text-gray-700">Check-out Date</label>
                        <input type="date" id="check_out" name="check_out" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-green-500 focus:border-green-500">
                    </div>
                    
                    <div>
                        <label for="adults" class="block text-sm font-medium text-gray-700">Adults</label>
                        <select id="adults" name="adults" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-green-500 focus:border-green-500">
                            <!-- Options will be populated by JavaScript based on room selection -->
                        </select>
                    </div>
                    
                    <div>
                        <label for="children" class="block text-sm font-medium text-gray-700">Children</label>
                        <select id="children" name="children" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-green-500 focus:border-green-500">
                            <!-- Options will be populated by JavaScript based on room selection -->
                        </select>
                    </div>
                    
                    <div>
                        <label for="rooms" class="block text-sm font-medium text-gray-700">Number of Rooms</label>
                        <select id="rooms" name="rooms" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-green-500 focus:border-green-500" disabled>
                            <option value="1" selected>1 Room</option>
                        </select>
                        <input type="hidden" name="rooms" value="1">
                    </div>
                    
                    <div>
                        <label for="room_id" class="block text-sm font-medium text-gray-700">Room Type</label>
                        <select id="room_id" name="room_id" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-green-500 focus:border-green-500">
                            <option value="">Select a room type</option>
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
                                    <?php echo (isset($room['booking_pause']) && $room['booking_pause'] == 1) ? ' (Booking Paused)' : ''; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="mt-1 text-sm text-gray-500">Price per room per night</p>
                    </div>
                </div>
                
                <!-- Booking Summary -->
                <div class="mt-6 bg-gray-50 p-4 rounded-md">
                    <h3 class="text-lg font-medium text-gray-900 mb-3">Booking Summary</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span>Base Price:</span>
                            <span id="base-price">₹0.00</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Nights:</span>
                            <span id="nights">0</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Rooms:</span>
                            <span id="room-count">0</span>
                        </div>
                        <div id="extra-charges-row" class="hidden">
                            <div class="flex justify-between">
                                <span>Extra Guest Charges:</span>
                                <span id="extra-charges">₹0.00</span>
                            </div>
                            <div id="extra-charges-details" class="text-sm text-gray-500 ml-4"></div>
                        </div>
                        <div id="additional-charges-row" class="hidden">
                            <div class="flex justify-between">
                                <span>Additional Charges:</span>
                                <span id="additional-charges">₹0.00</span>
                            </div>
                            <div id="additional-charges-details" class="text-sm text-gray-500 ml-4"></div>
                        </div>
                        <div class="border-t border-gray-300 pt-2 mt-2">
                            <div class="flex justify-between font-semibold">
                                <span>Total Amount:</span>
                                <span id="total-amount">₹0.00</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6">
                    <label for="special_requests" class="block text-sm font-medium text-gray-700">Special Requests</label>
                    <textarea id="special_requests" name="special_requests" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-green-500 focus:border-green-500" placeholder="Any special requests or requirements..."></textarea>
                </div>
                
                <div class="mt-6 bg-blue-50 p-4 rounded-md">
                    <h3 class="text-sm font-medium text-blue-800">Payment Information</h3>
                    <p class="text-sm text-blue-700 mt-1">Your booking will be confirmed after payment. You will receive payment instructions via phone call after submitting this form.</p>
                    <?php if ($bookingNumber): ?>
                        <p class="text-sm text-blue-700 mt-1"><strong>Booking Number:</strong> <span class="font-semibold"><?php echo htmlspecialchars($bookingNumber); ?></span></p>
                    <?php endif; ?>
                    <p class="text-sm text-blue-700 mt-1">Current payment status: <span class="font-semibold">Pending</span></p>
                </div>
                
                <div class="mt-8">
                    <button type="submit" class="w-full bg-green-500 text-white py-3 px-4 rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 font-medium">
                        Submit Booking Request
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
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