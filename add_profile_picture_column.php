<?php
include 'config/db.php';

try {
    // Add profile_picture column to admins table
    $pdo->exec("ALTER TABLE admins ADD COLUMN profile_picture VARCHAR(255) NULL DEFAULT NULL");
    echo "Profile picture column added successfully!";
} catch (PDOException $e) {
    echo "Error adding profile picture column: " . $e->getMessage();
}
?>