<?php
// Migration script to create rooms table
include 'config/db.php';

try {
    // Create rooms table
    $sql = "CREATE TABLE IF NOT EXISTS rooms (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        price DECIMAL(10, 2) NOT NULL,
        capacity INT NOT NULL DEFAULT 2,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($sql);
    echo "Rooms table created successfully.\n";
    
    // Insert default rooms if they don't exist
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM rooms");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['count'] == 0) {
        // Insert default rooms
        $stmt = $pdo->prepare("INSERT INTO rooms (name, description, price, capacity) VALUES (?, ?, ?, ?)");
        $stmt->execute(['Premium Room', 'Luxurious room with mountain view', 3500.00, 2]);
        $stmt->execute(['Executive Room', 'Spacious room with premium amenities', 3500.00, 2]);
        echo "Default rooms inserted.\n";
    }
    
    echo "Migration completed successfully!\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>