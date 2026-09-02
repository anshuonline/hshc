<?php
include 'config/db.php';

try {
    // Add room_type column to hotel_images table
    $pdo->exec("ALTER TABLE hotel_images ADD COLUMN room_type VARCHAR(50) DEFAULT 'general' AFTER caption");
    echo "room_type column added successfully to hotel_images table!\n";
    
    // Add index for better performance on room_type queries
    $pdo->exec("ALTER TABLE hotel_images ADD INDEX idx_room_type (room_type)");
    echo "Index added for room_type column!\n";
    
    echo "Database update completed successfully!\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "room_type column already exists in hotel_images table.\n";
    } else {
        echo "Error adding room_type column: " . $e->getMessage() . "\n";
    }
}
?>