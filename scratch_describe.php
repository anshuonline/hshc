<?php
include 'C:\xampp\htdocs\hotel-management\config\db.php';
$stmt = $pdo->query("SELECT id, name, amenities, room_overview_options FROM rooms");
$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rooms as $room) {
    echo "Room ID: " . $room['id'] . " - " . $room['name'] . "\n";
    echo "  Amenities (raw): " . ($room['amenities'] ?: 'NULL') . "\n";
    echo "  Overview Options (raw): " . ($room['room_overview_options'] ?: 'NULL') . "\n";
    echo "---\n";
}
?>
