<?php
include 'config/db.php';

try {
    $stmt = $pdo->query("DESCRIBE rooms");
    echo "Rooms table structure:\n";
    while ($row = $stmt->fetch()) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>