<?php
/**
 * Booking utilities for generating unique booking numbers
 */

/**
 * Generate a unique booking number
 * 
 * @param PDO $pdo Database connection
 * @return string Unique booking number
 */
function generateUniqueBookingNumber($pdo) {
    $maxAttempts = 10; // Limit the number of attempts to prevent infinite loops
    $attempt = 0;
    $bookingNumber = '';
    
    do {
        // Generate booking number: BK + year + month + day + 6-digit random number
        $bookingNumber = 'BK' . date('Ymd') . rand(100000, 999999);
        
        // Check if this booking number already exists
        $checkStmt = $pdo->prepare("SELECT id FROM bookings WHERE booking_number = ?");
        $checkStmt->execute([$bookingNumber]);
        $attempt++;
    } while ($checkStmt->fetch() && $attempt < $maxAttempts);
    
    // If we've exhausted our attempts, generate a timestamp-based number
    if ($attempt >= $maxAttempts) {
        $bookingNumber = 'BK' . date('YmdHis') . rand(100, 999);
        // Verify this one more time
        $checkStmt = $pdo->prepare("SELECT id FROM bookings WHERE booking_number = ?");
        $checkStmt->execute([$bookingNumber]);
        if ($checkStmt->fetch()) {
            // If still not unique, use a microtime-based approach
            $bookingNumber = 'BK' . substr(str_replace('.', '', microtime(true)), -12);
        }
    }
    
    return $bookingNumber;
}
?>