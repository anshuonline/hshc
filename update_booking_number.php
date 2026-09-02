<?php
// Script to add booking_number column to existing bookings table
include 'config/db.php';
include 'includes/booking_utils.php';

try {
    // Check if booking_number column exists
    $stmt = $pdo->prepare("SHOW COLUMNS FROM bookings LIKE 'booking_number'");
    $stmt->execute();
    $columnExists = $stmt->fetch();
    
    if (!$columnExists) {
        // Add booking_number column
        $pdo->exec("ALTER TABLE bookings ADD COLUMN booking_number VARCHAR(20) UNIQUE");
        echo "booking_number column added successfully!\n";
        
        // Generate booking numbers for existing bookings
        $stmt = $pdo->query("SELECT id FROM bookings WHERE booking_number IS NULL OR booking_number = ''");
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($bookings as $booking) {
            // Generate a unique booking number using our utility function
            $bookingNumber = generateUniqueBookingNumber($pdo);
            
            // Update the booking with the generated booking number
            $updateStmt = $pdo->prepare("UPDATE bookings SET booking_number = ? WHERE id = ?");
            $updateStmt->execute([$bookingNumber, $booking['id']]);
        }
        
        echo "Booking numbers generated for " . count($bookings) . " existing bookings!\n";
    } else {
        echo "booking_number column already exists!\n";
    }
    
    echo "Update completed successfully!";
} catch (PDOException $e) {
    echo "Error updating database: " . $e->getMessage();
}
?>