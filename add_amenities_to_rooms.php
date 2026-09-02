<?php
include 'config/db.php';

try {
    // Check if amenities column already exists
    $stmt = $pdo->query("SHOW COLUMNS FROM rooms LIKE 'amenities'");
    $column = $stmt->fetch();
    
    if (!$column) {
        // Add amenities column to rooms table
        $stmt = $pdo->prepare("ALTER TABLE rooms ADD COLUMN amenities TEXT NULL DEFAULT NULL AFTER description");
        $stmt->execute();
        echo "Successfully added amenities column to rooms table.\n";
    } else {
        echo "Amenities column already exists in rooms table.\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>