<?php
session_start();
include 'config/db.php';
include 'includes/header.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';

// Fetch user's completed bookings (confirmed and checked out)
$stmt = $pdo->prepare("SELECT b.*, h.name as hotel_name FROM bookings b JOIN hotels h ON b.id = h.id WHERE b.user_id = ? AND b.status = 'confirmed' AND b.check_out < CURDATE() AND b.id NOT IN (SELECT booking_id FROM reviews WHERE user_id = ?) ORDER BY b.check_out DESC");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = intval($_POST['booking_id']);
    $rating = intval($_POST['rating']);
    $comment = trim($_POST['comment']);
    
    // Validation
    if (empty($booking_id)) {
        $error = 'Please select a booking to review.';
    } elseif ($rating < 1 || $rating > 5) {
        $error = 'Please select a rating between 1 and 5 stars.';
    } else {
        // Verify that the booking belongs to the user and is eligible for review
        $stmt = $pdo->prepare("SELECT id FROM bookings WHERE id = ? AND user_id = ? AND status = 'confirmed' AND check_out < CURDATE()");
        $stmt->execute([$booking_id, $_SESSION['user_id']]);
        $booking = $stmt->fetch();
        
        if (!$booking) {
            $error = 'Invalid booking selected.';
        } else {
            // Check if user has already reviewed this booking
            $stmt = $pdo->prepare("SELECT id FROM reviews WHERE user_id = ? AND booking_id = ?");
            $stmt->execute([$_SESSION['user_id'], $booking_id]);
            if ($stmt->fetch()) {
                $error = 'You have already reviewed this booking.';
            } else {
                // Insert review
                try {
                    $stmt = $pdo->prepare("INSERT INTO reviews (user_id, booking_id, rating, comment) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$_SESSION['user_id'], $booking_id, $rating, $comment]);
                    $success = 'Thank you for your review! It will be visible after admin approval.';
                    
                    // Refresh bookings list
                    $stmt = $pdo->prepare("SELECT b.*, h.name as hotel_name FROM bookings b JOIN hotels h ON b.id = h.id WHERE b.user_id = ? AND b.status = 'confirmed' AND b.check_out < CURDATE() AND b.id NOT IN (SELECT booking_id FROM reviews WHERE user_id = ?) ORDER BY b.check_out DESC");
                    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
                    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } catch (PDOException $e) {
                    $error = 'Failed to submit review. Please try again.';
                }
            }
        }
    }
}
?>

<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h1 class="text-2xl font-bold text-gray-800">Write a Review</h1>
            <p class="text-gray-600 mt-1">Share your experience with other guests</p>
        </div>
        
        <?php if ($error): ?>
            <div class="mx-6 mt-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="mx-6 mt-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($success); ?></span>
            </div>
        <?php endif; ?>
        
        <?php if (empty($bookings)): ?>
            <div class="p-6 text-center">
                <i class="fas fa-calendar-check text-gray-400 text-4xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No eligible bookings for review</h3>
                <p class="text-gray-500 mb-6">You can only review bookings that are confirmed and have a check-out date in the past.</p>
                <a href="my-bookings.php" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-accent hover:bg-secondary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent">
                    View Your Bookings
                </a>
            </div>
        <?php else: ?>
            <form class="p-6" method="POST">
                <div class="mb-6">
                    <label for="booking_id" class="block text-sm font-medium text-gray-700 mb-2">Select Booking</label>
                    <select id="booking_id" name="booking_id" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-accent focus:border-accent">
                        <option value="">Choose a booking to review</option>
                        <?php foreach ($bookings as $booking): ?>
                            <option value="<?php echo $booking['id']; ?>">
                                <?php echo htmlspecialchars($booking['hotel_name']); ?> - 
                                <?php echo date('M j, Y', strtotime($booking['check_in'])); ?> to 
                                <?php echo date('M j, Y', strtotime($booking['check_out'])); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="mt-2 text-sm text-gray-500">You can only review bookings that are confirmed and have a check-out date in the past.</p>
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Rating</label>
                    <div class="flex items-center">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <input type="radio" id="rating<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" class="hidden" required>
                            <label for="rating<?php echo $i; ?>" class="cursor-pointer text-3xl text-gray-300 hover:text-yellow-400 mr-1">
                                <i class="fas fa-star"></i>
                            </label>
                        <?php endfor; ?>
                    </div>
                    <p class="mt-2 text-sm text-gray-500">Select your rating (1-5 stars)</p>
                </div>
                
                <div class="mb-6">
                    <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">Review</label>
                    <textarea id="comment" name="comment" rows="5" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-accent focus:border-accent" placeholder="Share your experience..."></textarea>
                </div>
                
                <div class="flex items-center justify-between">
                    <a href="reviews.php" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to Reviews
                    </a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-accent hover:bg-secondary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent">
                        Submit Review
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
// Handle star rating selection
document.querySelectorAll('input[name="rating"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const rating = this.value;
        document.querySelectorAll('label[for^="rating"]').forEach((label, index) => {
            const starIcon = label.querySelector('i');
            if (index < rating) {
                starIcon.classList.remove('far', 'text-gray-300');
                starIcon.classList.add('fas', 'text-yellow-400');
            } else {
                starIcon.classList.remove('fas', 'text-yellow-400');
                starIcon.classList.add('far', 'text-gray-300');
            }
        });
    });
});

// Set initial state for stars
document.querySelectorAll('label[for^="rating"]').forEach((label, index) => {
    label.addEventListener('mouseover', function() {
        const rating = index + 1;
        document.querySelectorAll('label[for^="rating"]').forEach((l, i) => {
            const starIcon = l.querySelector('i');
            if (i < rating) {
                starIcon.classList.remove('far', 'text-gray-300');
                starIcon.classList.add('fas', 'text-yellow-400');
            } else {
                starIcon.classList.remove('fas', 'text-yellow-400');
                starIcon.classList.add('far', 'text-gray-300');
            }
        });
    });
});

// Reset stars when mouse leaves rating section
document.querySelector('.flex.items-center').addEventListener('mouseleave', function() {
    const selectedRating = document.querySelector('input[name="rating"]:checked');
    const rating = selectedRating ? selectedRating.value : 0;
    
    document.querySelectorAll('label[for^="rating"]').forEach((label, index) => {
        const starIcon = label.querySelector('i');
        if (index < rating) {
            starIcon.classList.remove('far', 'text-gray-300');
            starIcon.classList.add('fas', 'text-yellow-400');
        } else {
            starIcon.classList.remove('fas', 'text-yellow-400');
            starIcon.classList.add('far', 'text-gray-300');
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>