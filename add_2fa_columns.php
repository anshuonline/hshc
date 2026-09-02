<?php
include 'config/db.php';

try {
    // Add 2FA columns to admins table
    $pdo->exec("ALTER TABLE admins ADD COLUMN authy_id VARCHAR(50) NULL DEFAULT NULL");
    $pdo->exec("ALTER TABLE admins ADD COLUMN authy_secret VARCHAR(255) NULL DEFAULT NULL");
    $pdo->exec("ALTER TABLE admins ADD COLUMN authy_enabled TINYINT(1) DEFAULT 0");
    $pdo->exec("ALTER TABLE admins ADD COLUMN authy_setup_complete TINYINT(1) DEFAULT 0");
    
    echo "2FA columns added successfully!\n";
    
    // Verify the columns were added
    $stmt = $pdo->query("DESCRIBE admins");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Admins table structure:\n";
    foreach ($columns as $column) {
        echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
    }
} catch (PDOException $e) {
    echo "Error adding 2FA columns: " . $e->getMessage() . "\n";
}
?>