<?php
include 'config/db.php';

try {
    // Set all images with empty usage_type to 'carousel'
    $stmt = $pdo->prepare("UPDATE hotel_images SET usage_type = 'carousel' WHERE usage_type = '' OR usage_type IS NULL");
    $stmt->execute();
    $updatedCount = $stmt->rowCount();
    
    echo "Updated $updatedCount images to have usage_type = 'carousel'\n";
    
    // Assign carousel positions to all carousel images
    $stmt = $pdo->prepare("SELECT id FROM hotel_images WHERE usage_type IN ('carousel', 'both') ORDER BY created_at ASC");
    $stmt->execute();
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $position = 1;
    foreach ($images as $image) {
        $stmt = $pdo->prepare("UPDATE hotel_images SET carousel_position = ? WHERE id = ?");
        $stmt->execute([$position, $image['id']]);
        echo "Set image ID {$image['id']} to carousel position $position\n";
        $position++;
        
        // Limit to 10 images as per carousel limit
        if ($position > 10) {
            break;
        }
    }
    
    // If there are more than 10 carousel images, set the rest to 'none'
    if (count($images) > 10) {
        $stmt = $pdo->prepare("UPDATE hotel_images SET usage_type = 'none' WHERE usage_type IN ('carousel', 'both') AND carousel_position IS NULL");
        $stmt->execute();
        $removedCount = $stmt->rowCount();
        echo "Set $removedCount excess images to usage_type = 'none'\n";
    }
    
    echo "Carousel images fixed successfully!\n";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
?>