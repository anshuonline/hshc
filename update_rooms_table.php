<?php
include 'config/db.php';

try {
    // Add extra_adult_charge column if it doesn't exist
    $stmt = $pdo->prepare("ALTER TABLE rooms ADD COLUMN extra_adult_charge DECIMAL(10, 2) NOT NULL DEFAULT 0.00");
    $stmt->execute();
    echo "Added extra_adult_charge column successfully.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column extra_adult_charge already exists.\n";
    } else {
        echo "Error adding extra_adult_charge column: " . $e->getMessage() . "\n";
    }
}

try {
    // Add extra_child_charge column if it doesn't exist
    $stmt = $pdo->prepare("ALTER TABLE rooms ADD COLUMN extra_child_charge DECIMAL(10, 2) NOT NULL DEFAULT 0.00");
    $stmt->execute();
    echo "Added extra_child_charge column successfully.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column extra_child_charge already exists.\n";
    } else {
        echo "Error adding extra_child_charge column: " . $e->getMessage() . "\n";
    }
}

echo "Database update completed.\n";
?>