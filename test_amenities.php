<?php
include 'config/db.php';

try {
    $stmt = $pdo->prepare('INSERT INTO rooms (name, description, amenities, price, capacity) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([
        'Test Room', 
        'A beautiful test room', 
        '{"wifi": true, "ac": true, "breakfast": false, "pool": true}', 
        5000, 
        2
    ]);
    echo "Test room created successfully!\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>