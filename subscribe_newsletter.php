<?php
// Newsletter subscription handler
include 'config/db.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    
    // Validate email
    if (empty($email)) {
        $response['message'] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Please enter a valid email address.';
    } else {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM newsletter_subscribers WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $response['message'] = 'This email is already subscribed to our newsletter.';
            } else {
                // Insert new subscriber
                $stmt = $pdo->prepare("INSERT INTO newsletter_subscribers (email, name) VALUES (?, ?)");
                $stmt->execute([$email, $name]);
                $response['success'] = true;
                $response['message'] = 'Thank you for subscribing to our newsletter!';
            }
        } catch (PDOException $e) {
            $response['message'] = 'An error occurred. Please try again later.';
        }
    }
} else {
    $response['message'] = 'Invalid request method.';
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>