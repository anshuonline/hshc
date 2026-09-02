<?php
include 'config/db.php';

try {
    // First, let's see what images are currently marked as cover
    $stmt = $pdo->query("SELECT id, usage_type FROM hotel_images WHERE usage_type IN ('cover', 'both') ORDER BY created_at ASC");
    $coverImages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Current Cover Images:</h2>";
    echo "<ul>";
    foreach ($coverImages as $image) {
        echo "<li>Image ID: " . $image['id'] . " - Usage Type: " . $image['usage_type'] . "</li>";
    }
    echo "</ul>";
    
    // If there are multiple cover images, keep only the most recent one as cover, rest as carousel only
    if (count($coverImages) > 1) {
        echo "<h2>Fixing cover images...</h2>";
        
        // Get the most recent cover image (last one in the array)
        $mostRecentCover = end($coverImages);
        
        // Update all other cover images to be carousel only
        $stmt = $pdo->prepare("UPDATE hotel_images SET usage_type = CASE WHEN usage_type = 'cover' THEN 'carousel' WHEN usage_type = 'both' THEN 'carousel' ELSE usage_type END WHERE id != ? AND usage_type IN ('cover', 'both')");
        $stmt->execute([$mostRecentCover['id']]);
        
        echo "<p>Fixed! Only image ID " . $mostRecentCover['id'] . " is now the cover image.</p>";
    } else {
        echo "<p>No issues found. Only one cover image exists.</p>";
    }
    
    // Verify the fix
    $stmt = $pdo->query("SELECT id, usage_type FROM hotel_images WHERE usage_type IN ('cover', 'both') ORDER BY created_at ASC");
    $coverImages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>After Fix - Current Cover Images:</h2>";
    echo "<ul>";
    foreach ($coverImages as $image) {
        echo "<li>Image ID: " . $image['id'] . " - Usage Type: " . $image['usage_type'] . "</li>";
    }
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
<a href="admin/images.php">Back to Admin Panel</a>