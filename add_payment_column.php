<?php
include 'config/db.php';

try {
    // Use the database
    $pdo->exec("USE hotelmanagementsystem");
    
    // Check if the payment_status column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM bookings LIKE 'payment_status'");
    $column = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($column) {
        echo "The payment_status column already exists in the bookings table.\n";
    } else {
        // Add the payment_status column
        $pdo->exec("ALTER TABLE bookings ADD COLUMN payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending' AFTER status");
        echo "Successfully added the payment_status column to the bookings table.\n";
        echo "Column details:\n";
        echo "- Name: payment_status\n";
        echo "- Type: ENUM('pending', 'paid', 'failed')\n";
        echo "- Default: pending\n";
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
?>