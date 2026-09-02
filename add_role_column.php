<?php
include 'config/db.php';

try {
    // Add role column to admins table
    $pdo->exec("ALTER TABLE admins ADD COLUMN role ENUM('admin', 'manager') DEFAULT 'admin'");
    echo "Role column added successfully!";
} catch (PDOException $e) {
    echo "Error adding role column: " . $e->getMessage();
}
?>