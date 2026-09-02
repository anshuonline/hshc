<?php
include 'config/db.php';

try {
    // Add max_adults column
    $pdo->exec("ALTER TABLE rooms ADD COLUMN max_adults INT NOT NULL DEFAULT 2");
    echo "Added max_adults column successfully.\n";
} catch (PDOException $e) {
    echo "Error adding max_adults column: " . $e->getMessage() . "\n";
}

try {
    // Add max_children column
    $pdo->exec("ALTER TABLE rooms ADD COLUMN max_children INT NOT NULL DEFAULT 2");
    echo "Added max_children column successfully.\n";
} catch (PDOException $e) {
    echo "Error adding max_children column: " . $e->getMessage() . "\n";
}
?>