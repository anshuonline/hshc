<?php
include 'config/db.php';

// Get the hotel ID
$stmt = $pdo->query("SELECT id FROM hotels LIMIT 1");
$hotel = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$hotel) {
    echo "No hotel found. Please set up hotel information first.";
    exit;
}

$hotel_id = $hotel['id'];

// Scan the uploads directory for images
$uploadDir = 'uploads/';
$images = glob($uploadDir . "*.{jpg,jpeg,png,gif}", GLOB_BRACE);

$addedCount = 0;
foreach ($images as $imagePath) {
    // Check if image already exists in database
    $stmt = $pdo->prepare("SELECT id FROM hotel_images WHERE image_path = ?");
    $stmt->execute([$imagePath]);
    
    if (!$stmt->fetch()) {
        // Add image to database
        $stmt = $pdo->prepare("INSERT INTO hotel_images (hotel_id, image_path, caption) VALUES (?, ?, ?)");
        $stmt->execute([$hotel_id, $imagePath, 'Hotel Image']);
        $addedCount++;
    }
}

echo "Added $addedCount images to the database.";
?>