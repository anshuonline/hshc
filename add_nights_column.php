<?php
include 'config/db.php';

try {
    // Check if the nights column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM bookings LIKE 'nights'");
    $column = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($column) {
        echo "The nights column already exists in the bookings table.\n";
    } else {
        // Add the nights column
        $pdo->exec("ALTER TABLE bookings ADD COLUMN nights INT NULL DEFAULT NULL AFTER check_out_time");
        echo "Successfully added the nights column to the bookings table.\n";
    }
    
    // Verify the column was added
    $stmt = $pdo->query("DESCRIBE bookings");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nBookings table structure:\n";
    foreach ($columns as $column) {
        echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
?>