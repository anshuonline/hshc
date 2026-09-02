<?php
// Script to add demo reviews to the database
include 'config/db.php';

try {
    echo "Adding demo data...\n";
    
    // Create demo users
    $demo_users = [
        ['John Doe', 'john.doe@example.com', '1234567890', 'United States', 'password123'],
        ['Jane Smith', 'jane.smith@example.com', '0987654321', 'United Kingdom', 'password123'],
        ['Robert Johnson', 'robert.j@example.com', '1112223333', 'Canada', 'password123'],
        ['Emily Davis', 'emily.davis@example.com', '4445556666', 'Australia', 'password123'],
        ['Michael Wilson', 'michael.w@example.com', '7778889999', 'Germany', 'password123']
    ];
    
    $user_ids = [];
    foreach ($demo_users as $user) {
        // Check if user already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$user[1]]);
        $existing_user = $stmt->fetch();
        
        if ($existing_user) {
            $user_ids[] = $existing_user['id'];
            echo "User {$user[0]} already exists with ID: {$existing_user['id']}\n";
        } else {
            // Insert new user
            $hashed_password = md5($user[4]);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, country, password) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$user[0], $user[1], $user[2], $user[3], $hashed_password]);
            $user_id = $pdo->lastInsertId();
            $user_ids[] = $user_id;
            echo "Created user {$user[0]} with ID: $user_id\n";
        }
    }
    
    // Create demo bookings for these users
    $demo_bookings = [
        // User 1 bookings
        [$user_ids[0], 'John Doe', 'john.doe@example.com', '1234567890', '2025-10-01', '2025-10-05', 2, 0, 1, 'Near window room preferred', 'BK20251001001'],
        [$user_ids[0], 'John Doe', 'john.doe@example.com', '1234567890', '2025-11-10', '2025-11-15', 2, 1, 1, 'Birthday celebration', 'BK20251110001'],
        
        // User 2 bookings
        [$user_ids[1], 'Jane Smith', 'jane.smith@example.com', '0987654321', '2025-09-15', '2025-09-20', 1, 0, 1, '', 'BK20250915001'],
        [$user_ids[1], 'Jane Smith', 'jane.smith@example.com', '0987654321', '2025-10-25', '2025-10-30', 1, 0, 1, 'Quiet room needed', 'BK20251025001'],
        
        // User 3 bookings
        [$user_ids[2], 'Robert Johnson', 'robert.j@example.com', '1112223333', '2025-08-20', '2025-08-25', 2, 2, 2, 'Family vacation', 'BK20250820001'],
        
        // User 4 bookings
        [$user_ids[3], 'Emily Davis', 'emily.davis@example.com', '4445556666', '2025-07-10', '2025-07-15', 1, 0, 1, 'Honeymoon suite', 'BK20250710001'],
        [$user_ids[3], 'Emily Davis', 'emily.davis@example.com', '4445556666', '2025-11-05', '2025-11-10', 2, 0, 1, '', 'BK20251105001'],
        
        // User 5 bookings
        [$user_ids[4], 'Michael Wilson', 'michael.w@example.com', '7778889999', '2025-06-05', '2025-06-10', 1, 0, 1, 'Business trip', 'BK20250605001'],
        [$user_ids[4], 'Michael Wilson', 'michael.w@example.com', '7778889999', '2025-09-30', '2025-10-05', 1, 0, 1, 'Conference attendance', 'BK20250930001'],
        [$user_ids[4], 'Michael Wilson', 'michael.w@example.com', '7778889999', '2025-12-15', '2025-12-20', 1, 0, 1, 'Christmas vacation', 'BK20251215001']
    ];
    
    $booking_ids = [];
    foreach ($demo_bookings as $booking) {
        // Check if booking already exists
        $stmt = $pdo->prepare("SELECT id FROM bookings WHERE booking_number = ?");
        $stmt->execute([$booking[10]]);
        $existing_booking = $stmt->fetch();
        
        if ($existing_booking) {
            $booking_ids[] = $existing_booking['id'];
            echo "Booking {$booking[10]} already exists with ID: {$existing_booking['id']}\n";
        } else {
            // Insert new booking
            $stmt = $pdo->prepare("INSERT INTO bookings (user_id, name, email, phone, check_in, check_out, adults, children, rooms, special_requests, booking_number, status, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'confirmed', 'paid')");
            $stmt->execute($booking);
            $booking_id = $pdo->lastInsertId();
            $booking_ids[] = $booking_id;
            echo "Created booking {$booking[10]} with ID: $booking_id\n";
        }
    }
    
    // Create demo reviews for these bookings
    $demo_reviews = [
        // Reviews for User 1
        [$user_ids[0], $booking_ids[0], 5, 'Absolutely wonderful experience! The staff was incredibly friendly and the room was spotless. Will definitely be coming back.', 'approved'],
        [$user_ids[0], $booking_ids[1], 4, 'Great hotel with beautiful views. The service was good, though the breakfast could be improved.', 'approved'],
        
        // Reviews for User 2
        [$user_ids[1], $booking_ids[2], 5, 'Perfect location and amazing hospitality. The hotel exceeded all our expectations. Highly recommended!', 'approved'],
        [$user_ids[1], $booking_ids[3], 3, 'Good hotel but a bit noisy. The room was clean but could use some modernization.', 'approved'],
        
        // Reviews for User 3
        [$user_ids[2], $booking_ids[4], 4, 'Family-friendly hotel with great amenities. The kids loved the pool area. Staff was very accommodating.', 'approved'],
        
        // Reviews for User 4
        [$user_ids[3], $booking_ids[5], 5, 'Romantic getaway was perfect. The honeymoon suite was beautiful and the service was impeccable.', 'approved'],
        [$user_ids[3], $booking_ids[6], 4, 'Comfortable stay with excellent views. The staff was helpful with our special requests.', 'pending'],
        
        // Reviews for User 5
        [$user_ids[4], $booking_ids[7], 4, 'Good business hotel with reliable Wi-Fi and comfortable rooms. Convenient location for meetings.', 'approved'],
        [$user_ids[4], $booking_ids[8], 5, 'Excellent service during our conference stay. The hotel staff went above and beyond to accommodate our needs.', 'approved'],
        [$user_ids[4], $booking_ids[9], 2, 'Disappointing experience. Room was not cleaned properly and the air conditioning was broken.', 'rejected']
    ];
    
    foreach ($demo_reviews as $review) {
        // Check if review already exists
        $stmt = $pdo->prepare("SELECT id FROM reviews WHERE user_id = ? AND booking_id = ?");
        $stmt->execute([$review[0], $review[1]]);
        $existing_review = $stmt->fetch();
        
        if ($existing_review) {
            echo "Review for user {$review[0]} and booking {$review[1]} already exists with ID: {$existing_review['id']}\n";
        } else {
            // Insert new review
            $stmt = $pdo->prepare("INSERT INTO reviews (user_id, booking_id, rating, comment, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute($review);
            $review_id = $pdo->lastInsertId();
            echo "Created review with ID: $review_id for user {$review[0]} and booking {$review[1]}\n";
        }
    }
    
    echo "Demo data added successfully!\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>