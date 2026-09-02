<?php
session_start();
include '../config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get image ID from POST data
$input = json_decode(file_get_contents('php://input'), true);
$image_id = isset($input['image_id']) ? intval($input['image_id']) : 0;

if ($image_id <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid image ID']);
    exit;
}

try {
    // First, get the image path to delete the file
    $stmt = $pdo->prepare("SELECT image_path FROM hotel_images WHERE id = ?");
    $stmt->execute([$image_id]);
    $image = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$image) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Image not found']);
        exit;
    }
    
    // Delete the image file from the server
    $file_path = '../' . $image['image_path'];
    if (file_exists($file_path)) {
        unlink($file_path);
    }
    
    // Delete the record from the database
    $stmt = $pdo->prepare("DELETE FROM hotel_images WHERE id = ?");
    $stmt->execute([$image_id]);
    
    // Check if the deletion was successful
    if ($stmt->rowCount() > 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Image deleted successfully']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Failed to delete image from database']);
    }
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>