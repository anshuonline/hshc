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

// Check if 2FA is already set up
if ($admin['authy_setup_complete'] == 1) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

// Initialize Google Authenticator
$ga = new GoogleAuthenticator();

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['generate_secret'])) {
        // Generate a new secret
        $secret = $ga->generateSecret();
        
        // Update the admin record with the secret
        $stmt = $pdo->prepare("UPDATE admins SET authy_secret = ? WHERE id = ?");
        $stmt->execute([$secret, $_SESSION['admin_id']]);
        
        // Update the admin variable with the new secret for immediate display
        $admin['authy_secret'] = $secret;
        
        // Refresh admin data
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
        $stmt->execute([$_SESSION['admin_id']]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    } elseif (isset($_POST['verify_token'])) {
        $token = trim($_POST['token']);
        
        if (empty($token)) {
            $error = 'Please enter the 6-digit code from your Google Authenticator app.';
        } elseif (strlen($token) != 6 || !is_numeric($token)) {
            $error = 'Please enter a valid 6-digit code.';
        } else {
            // Verify the token
            if (!empty($admin['authy_secret'])) {
                if ($ga->verifyToken($admin['authy_secret'], $token)) {
                    // Mark 2FA as set up
                    $stmt = $pdo->prepare("UPDATE admins SET authy_setup_complete = 1, authy_enabled = 1 WHERE id = ?");
                    $stmt->execute([$_SESSION['admin_id']]);
                    
                    $success = 'Two-factor authentication has been successfully set up!';
                    
                    // Redirect to dashboard after a short delay
                    header("refresh:3;url=dashboard.php");
                } else {
                    $error = 'Invalid verification code. Please try again.';
                }
            } else {
                $error = 'Please generate a secret first.';
            }
        }
    }
}

// If we don't have a secret, generate one
if (empty($admin['authy_secret'])) {
    // Generate a new secret for initial display
    $secret = $ga->generateSecret();
    
    // Update the admin record with the secret
    $stmt = $pdo->prepare("UPDATE admins SET authy_secret = ? WHERE id = ?");
    $stmt->execute([$secret, $_SESSION['admin_id']]);
    
    // Refresh admin data
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup 2FA - Demo Hotel & Resort</title>
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
        
        // Function to copy secret to clipboard
        function copyToClipboard() {
            const secretElement = document.getElementById('secret-code');
            const secret = secretElement.textContent;
            
            navigator.clipboard.writeText(secret).then(() => {
                // Show success message
                const button = document.getElementById('copy-button');
                const originalText = button.innerHTML;
                button.innerHTML = '<i class="fas fa-check mr-2"></i>Copied!';
                button.classList.remove('bg-gradient-to-r', 'from-primary', 'to-secondary');
                button.classList.add('bg-green-500');
                
                // Reset button after 2 seconds
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.classList.remove('bg-green-500');
                    button.classList.add('bg-gradient-to-r', 'from-primary', 'to-secondary');
                }, 2000);
            }).catch(err => {
                console.error('Failed to copy: ', err);
                alert('Failed to copy secret to clipboard. Please select and copy manually.');
            });
        }
    </script>
</head>
<body class="bg-gray-50 min-h-screen font-sans">
    <!-- Navigation -->
    <nav class="bg-gradient-to-r from-primary to-secondary shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <span class="text-white font-bold text-xl">Demo Hotel & Resort Admin</span>
                    </div>
                </div>
                <div class="hidden md:block">
                    <div class="ml-4 flex items-center md:ml-6">
                        <span class="text-blue-100 mr-4">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!</span>
                        <a href="logout.php" class="text-blue-100 hover:bg-blue-600 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition duration-200">
                            <i class="fas fa-sign-out-alt mr-1"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-200">
                <h1 class="text-2xl font-bold text-gray-900">Two-Factor Authentication Setup</h1>
                <p class="text-gray-600 mt-1">Secure your account with an additional layer of protection</p>
            </div>
            
            <div class="p-6">
                <?php if ($success): ?>
                    <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-check-circle text-green-500 text-xl"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-green-700"><?php echo htmlspecialchars($success); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded">
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
                
                <?php if ($admin['authy_setup_complete'] == 1): ?>
                    <div class="text-center py-8">
                        <i class="fas fa-check-circle text-green-500 text-5xl mb-4"></i>
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">2FA Already Set Up</h2>
                        <p class="text-gray-600 mb-6">Your account already has two-factor authentication enabled.</p>
                        <a href="dashboard.php" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gradient-to-r from-primary to-secondary hover:from-secondary hover:to-primary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                            Go to Dashboard
                        </a>
                    </div>
                <?php else: ?>
                    <div class="space-y-8">
                        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-info-circle text-blue-500 text-xl"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-blue-700">
                                        Two-factor authentication (2FA) adds an extra layer of security to your account. 
                                        In addition to your password, you'll need to enter a code from your authenticator app.
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="border border-gray-200 rounded-lg p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Step 1: Install an Authenticator App</h2>
                            <p class="text-gray-600 mb-4">
                                Download and install an authenticator app on your smartphone:
                            </p>
                            <ul class="list-disc list-inside text-gray-600 space-y-2">
                                <li>Google Authenticator (Android & iOS)</li>
                                <li>Authy (Android & iOS)</li>
                                <li>Microsoft Authenticator (Android & iOS)</li>
                                <li>Any other TOTP-compatible authenticator app</li>
                            </ul>
                        </div>
                        
                        <div class="border border-gray-200 rounded-lg p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Step 2: Set Up Your Account</h2>
                            <p class="text-gray-600 mb-4">
                                Open your authenticator app and add a new account manually using the secret code below.
                            </p>
                            
                            <div class="bg-gray-50 rounded-lg p-6 mb-6">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm text-gray-500 mb-1">Secret Key</p>
                                        <p id="secret-code" class="text-xl font-mono font-bold text-gray-900 tracking-wider"><?php echo htmlspecialchars($admin['authy_secret']); ?></p>
                                        <p class="text-sm text-gray-500 mt-2">Account: <?php echo htmlspecialchars($admin['email']); ?></p>
                                        <p class="text-sm text-gray-500">Issuer: Demo Hotel & Resort</p>
                                    </div>
                                    <button id="copy-button" onclick="copyToClipboard()" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gradient-to-r from-primary to-secondary hover:from-secondary hover:to-primary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                                        <i class="fas fa-copy mr-2"></i>Copy
                                    </button>
                                </div>
                            </div>
                            
                            <form method="POST" class="flex justify-end">
                                <button type="submit" name="generate_secret" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                                    <i class="fas fa-sync-alt mr-2"></i>Generate New Secret
                                </button>
                            </form>
                        </div>
                        
                        <div class="border border-gray-200 rounded-lg p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Step 3: Verify and Activate</h2>
                            <p class="text-gray-600 mb-4">
                                Enter the 6-digit code from your Google Authenticator app to complete the setup.
                            </p>
                            
                            <form method="POST" class="space-y-4">
                                <div>
                                    <label for="token" class="block text-sm font-medium text-gray-700">6-Digit Code</label>
                                    <input type="text" id="token" name="token" maxlength="6" pattern="\d{6}" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
                                    <p class="mt-2 text-sm text-gray-500">Enter the code generated by your Google Authenticator app.</p>
                                </div>
                                
                                <div>
                                    <button type="submit" name="verify_token" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                        <i class="fas fa-check mr-2"></i>Verify and Activate
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        // Add fallback for clipboard API
        if (!navigator.clipboard) {
            document.getElementById('copy-button').addEventListener('click', function() {
                const secretElement = document.getElementById('secret-code');
                const range = document.createRange();
                range.selectNode(secretElement);
                window.getSelection().removeAllRanges();
                window.getSelection().addRange(range);
                try {
                    document.execCommand('copy');
                    const button = this;
                    const originalText = button.innerHTML;
                    button.innerHTML = '<i class="fas fa-check mr-2"></i>Copied!';
                    button.classList.remove('bg-gradient-to-r', 'from-primary', 'to-secondary');
                    button.classList.add('bg-green-500');
                    
                    setTimeout(() => {
                        button.innerHTML = originalText;
                        button.classList.remove('bg-green-500');
                        button.classList.add('bg-gradient-to-r', 'from-primary', 'to-secondary');
                        window.getSelection().removeAllRanges();
                    }, 2000);
                } catch (err) {
                    alert('Failed to copy secret to clipboard. Please select and copy manually.');
                }
            });
        }
    </script>
</body>
</html>