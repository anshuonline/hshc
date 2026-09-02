<?php
include 'config/db.php';

try {
    // Check if room_type column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM hotel_images LIKE 'room_type'");
    $roomTypeExists = $stmt->fetch();
    
    // Check if usage_type column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM hotel_images LIKE 'usage_type'");
    $usageTypeExists = $stmt->fetch();
    
    $stmt = $pdo->query("SHOW COLUMNS FROM hotel_images LIKE 'carousel_position'");
    $carouselPositionExists = $stmt->fetch();
    
    // Remove room_type column if it exists
    if ($roomTypeExists) {
        echo "Removing room_type column...\n";
        $pdo->exec("ALTER TABLE hotel_images DROP COLUMN room_type");
        echo "room_type column removed successfully.\n";
    } else {
        echo "room_type column does not exist.\n";
    }
    
    if (!$usageTypeExists) {
        echo "Adding usage_type column...\n";
        $pdo->exec("ALTER TABLE hotel_images ADD COLUMN usage_type ENUM('carousel', 'cover', 'both', 'none') DEFAULT 'none'");
        echo "usage_type column added successfully.\n";
    } else {
        echo "usage_type column already exists.\n";
    }
    
    if (!$carouselPositionExists) {
        echo "Adding carousel_position column...\n";
        $pdo->exec("ALTER TABLE hotel_images ADD COLUMN carousel_position INT NULL DEFAULT NULL");
        echo "carousel_position column added successfully.\n";
    } else {
        echo "carousel_position column already exists.\n";
    }
    
    // Check if rooms table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'rooms'");
    $roomsTableExists = $stmt->fetch();
    
    if (!$roomsTableExists) {
        echo "Creating rooms table...\n";
        $pdo->exec("CREATE TABLE rooms (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            price DECIMAL(10, 2) NOT NULL,
            capacity INT NOT NULL DEFAULT 2,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        echo "rooms table created successfully.\n";
    } else {
        echo "rooms table already exists.\n";
    }
    
    // Check if booking_pause column exists in rooms table
    $stmt = $pdo->query("SHOW COLUMNS FROM rooms LIKE 'booking_pause'");
    $bookingPauseExists = $stmt->fetch();
    
    if (!$bookingPauseExists) {
        echo "Adding booking_pause column to rooms table...\n";
        $pdo->exec("ALTER TABLE rooms ADD COLUMN booking_pause TINYINT(1) DEFAULT 0");
        echo "booking_pause column added successfully.\n";
    } else {
        echo "booking_pause column already exists.\n";
    }
    
    // Check if room_overview_options column exists in rooms table
    $stmt = $pdo->query("SHOW COLUMNS FROM rooms LIKE 'room_overview_options'");
    $roomOverviewOptionsExists = $stmt->fetch();
    
    if (!$roomOverviewOptionsExists) {
        echo "Adding room_overview_options column to rooms table...\n<br>";
        $pdo->exec("ALTER TABLE rooms ADD COLUMN room_overview_options TEXT NULL DEFAULT NULL");
        echo "room_overview_options column added successfully.\n<br>";
    } else {
        echo "room_overview_options column already exists.\n<br>";
    }
    
    // Check if additional_charges column exists in rooms table
    $stmt = $pdo->query("SHOW COLUMNS FROM rooms LIKE 'additional_charges'");
    $additionalChargesExists = $stmt->fetch();
    
    if (!$additionalChargesExists) {
        echo "Adding additional_charges column to rooms table...\n<br>";
        $pdo->exec("ALTER TABLE rooms ADD COLUMN additional_charges TEXT NULL AFTER extra_child_charge");
        echo "additional_charges column added successfully.\n<br>";
    } else {
        echo "additional_charges column already exists.\n<br>";
    }
    
    // Update existing images to have default values
    echo "Setting default values for existing images...\n";
    $pdo->exec("UPDATE hotel_images SET usage_type = 'carousel' WHERE usage_type IS NULL");
    
    echo "Database update completed successfully!\n";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
?>