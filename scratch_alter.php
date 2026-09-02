<?php
include 'C:\xampp\htdocs\hotel-management\config\db.php';
try {
    $pdo->exec('ALTER TABLE rooms ADD COLUMN additional_charges TEXT NULL AFTER extra_child_charge');
    echo 'Column added successfully';
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
}
?>
