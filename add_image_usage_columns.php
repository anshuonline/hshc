<?php
include 'config/db.php';

try {
    // Add columns for image usage
    $pdo->exec("ALTER TABLE hotel_images ADD COLUMN usage_type ENUM('carousel', 'cover', 'both') DEFAULT 'carousel'");
    $pdo->exec("ALTER TABLE hotel_images ADD COLUMN carousel_position INT NULL DEFAULT NULL");
    
    echo "Successfully added image usage columns to hotel_images table.\n";
    
    // Verify the columns were added
    $stmt = $pdo->query("DESCRIBE hotel_images");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nHotel images table structure:\n";
    foreach ($columns as $column) {
        echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
    }
    
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Image usage columns already exist in the hotel_images table.\n";
    } else {
        echo "Database error: " . $e->getMessage() . "\n";
    }
}
?>