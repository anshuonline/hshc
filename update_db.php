<?php
include 'config/db.php';

try {
    // Use the database
    $pdo->exec("USE hotelmanagementsystem");
    
    // Check if the users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() == 0) {
        echo "Users table does not exist. Please run init.php first.\n";
        exit;
    }
    
    // Check if phone column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'phone'");
    if ($stmt->rowCount() == 0) {
        // Add phone column
        $pdo->exec("ALTER TABLE users ADD COLUMN phone VARCHAR(20) AFTER password");
        echo "Added phone column to users table.\n";
    } else {
        echo "Phone column already exists.\n";
    }
    
    // Check if country column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'country'");
    if ($stmt->rowCount() == 0) {
        // Add country column
        $pdo->exec("ALTER TABLE users ADD COLUMN country VARCHAR(100) AFTER phone");
        echo "Added country column to users table.\n";
    } else {
        echo "Country column already exists.\n";
    }
    
    echo "Database update completed successfully!\n";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
?>