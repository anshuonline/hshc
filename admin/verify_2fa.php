<?php
session_start();
include '../config/db.php';
include '../includes/authy.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

// Get admin details
$stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Check if 2FA is set up
if ($admin['authy_setup_complete'] != 1) {
    header('Location: setup_2fa.php');
    exit;
}

// Check if 2FA is already verified for this session
if (isset($_SESSION['authy_verified']) && $_SESSION['authy_verified'] === true) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

// Initialize Google Authenticator
$ga = new GoogleAuthenticator();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = trim($_POST['token']);
    
    if (empty($token)) {
        $error = 'Please enter the 6-digit code from your Google Authenticator app.';
    } elseif (strlen($token) != 6 || !is_numeric($token)) {
        $error = 'Please enter a valid 6-digit code.';
    } else {
        // Verify the token
        if ($ga->verifyToken($admin['authy_secret'], $token)) {
            // Mark 2FA as verified for this session
            $_SESSION['authy_verified'] = true;
            
            // Redirect to dashboard
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid verification code. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify 2FA - Demo Hotel & Resort</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#0ea5e9',
                        secondary: '#0284c7',
                        accent: '#06b6d4',
                        dark: '#0c4a6e',
                        light: '#f0f9ff'
                    },
                    fontFamily: {
                        'sans': ['Inter', 'sans-serif']
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 min-h-screen font-sans">
    <div class="min-h-screen flex items-center justify-center px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full bg-white rounded-xl shadow-md overflow-hidden">
            <div class="px-6 py-8">
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center">
                        <div class="bg-gradient-to-r from-primary to-secondary rounded-full p-3">
                            <i class="fas fa-shield-alt text-white text-2xl"></i>
                        </div>
                    </div>
                    <h2 class="mt-6 text-3xl font-extrabold text-gray-900">Two-Factor Authentication</h2>
                    <p class="mt-2 text-sm text-gray-600">
                        Enter the verification code from your Google Authenticator app
                    </p>
                </div>
                
                <?php if ($error): ?>
                    <div class="mt-6 bg-red-50 border-l-4 border-red-500 p-4 rounded">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-circle text-red-500 text-xl"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-red-700"><?php echo htmlspecialchars($error); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <form class="mt-8 space-y-6" method="POST">
                    <div>
                        <label for="token" class="block text-sm font-medium text-gray-700">6-Digit Code</label>
                        <div class="mt-1">
                            <input id="token" name="token" type="text" maxlength="6" pattern="\d{6}" required class="appearance-none block w-full px-3 py-4 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary text-center text-2xl tracking-widest">
                        </div>
                        <p class="mt-2 text-sm text-gray-500">
                            Open your Google Authenticator app and enter the 6-digit code
                        </p>
                    </div>
                    
                    <div>
                        <button type="submit" class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-gradient-to-r from-primary to-secondary hover:from-secondary hover:to-primary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                                <i class="fas fa-lock text-blue-300 group-hover:text-blue-200"></i>
                            </span>
                            Verify and Continue
                        </button>
                    </div>
                </form>
                
                <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-blue-400"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Need help?</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <p>
                                    If you're having trouble, contact your system administrator.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>