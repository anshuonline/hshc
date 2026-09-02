<?php
include 'config/db.php';

try {
    // Add extra_adult_charge column
    $pdo->exec("ALTER TABLE rooms ADD COLUMN extra_adult_charge DECIMAL(10, 2) NOT NULL DEFAULT 0.00");
    echo "Added extra_adult_charge column successfully.\n";
} catch (PDOException $e) {
    echo "Error adding extra_adult_charge column: " . $e->getMessage() . "\n";
}

try {
    // Add extra_child_charge column
    $pdo->exec("ALTER TABLE rooms ADD COLUMN extra_child_charge DECIMAL(10, 2) NOT NULL DEFAULT 0.00");
    echo "Added extra_child_charge column successfully.\n";
} catch (PDOException $e) {
    echo "Error adding extra_child_charge column: " . $e->getMessage() . "\n";
}
?>