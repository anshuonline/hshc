<?php
include 'config/db.php';

try {
    // Add arrival_status column to bookings table
    $pdo->exec("ALTER TABLE bookings ADD COLUMN arrival_status ENUM('not_arrived', 'arrived', 'checked_out') DEFAULT 'not_arrived' AFTER payment_status");
    
    echo "<h1>Database Update Successful</h1>";
    echo "<p>Added arrival_status column to bookings table.</p>";
    
    // Verify the column was added
    $stmt = $pdo->query("DESCRIBE bookings");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Bookings Table Structure:</h2>";
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . $column['Field'] . "</td>";
        echo "<td>" . $column['Type'] . "</td>";
        echo "<td>" . $column['Null'] . "</td>";
        echo "<td>" . $column['Key'] . "</td>";
        echo "<td>" . $column['Default'] . "</td>";
        echo "<td>" . $column['Extra'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "<h1>Database Update Information</h1>";
        echo "<p>Column already exists in the bookings table.</p>";
    } else {
        echo "<h1>Database Update Error</h1>";
        echo "<p>Error: " . $e->getMessage() . "</p>";
    }
}
?>