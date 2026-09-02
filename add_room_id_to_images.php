<?php
include 'config/db.php';

try {
    // Add room_id column to hotel_images table
    $stmt = $pdo->prepare("ALTER TABLE hotel_images ADD COLUMN room_id INT NULL DEFAULT NULL AFTER hotel_id");
    $stmt->execute();
    
    // Add foreign key constraint
    $stmt = $pdo->prepare("ALTER TABLE hotel_images ADD CONSTRAINT fk_hotel_images_room FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE");
    $stmt->execute();
    
    echo "Successfully added room_id column to hotel_images table.\n";
    
    // Verify the column was added
    $stmt = $pdo->query("SHOW COLUMNS FROM hotel_images LIKE 'room_id'");
    $column = $stmt->fetch();
    
    if ($column) {
        echo "Column verification successful.\n";
    } else {
        echo "Warning: Column verification failed.\n";
    }
    
} catch (PDOException $e) {
    // Check if the column already exists
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column room_id already exists in hotel_images table.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
        exit(1);
    }
}
?>