<?php
// Migration script to remove room_type column from hotel_images table
include 'config/db.php';

try {
    // Check if room_type column exists
    $stmt = $pdo->prepare("SHOW COLUMNS FROM hotel_images LIKE 'room_type'");
    $stmt->execute();
    $columnExists = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($columnExists) {
        // Remove room_type column
        $sql = "ALTER TABLE hotel_images DROP COLUMN room_type";
        $pdo->exec($sql);
        echo "Room type column removed successfully.\n";
    } else {
        echo "Room type column does not exist.\n";
    }
    
    echo "Migration completed successfully!\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>