<?php
session_start();
include 'config/db.php';
include 'includes/authy.php';

// Redirect if already logged in
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']) {
    header('Location: profile.php');
    exit;
}

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']) {
    // Check if 2FA is set up and verified
    $stmt = $pdo->prepare("SELECT authy_setup_complete FROM admins WHERE id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin && $admin['authy_setup_complete'] == 1) {
        if (!isset($_SESSION['authy_verified']) || $_SESSION['authy_verified'] !== true) {
            header('Location: admin/verify_2fa.php');
            exit;
        }
    }
    
    header('Location: admin/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $user_type = $_POST['user_type'] ?? 'user';
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        if ($user_type === 'admin') {
            // Check admin credentials
            $stmt = $pdo->prepare("SELECT id, username, password, email, authy_setup_complete FROM admins WHERE email = ? OR username = ?");
            $stmt->execute([$email, $email]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Use md5 for password verification (as requested)
            if ($admin && $admin['password'] === md5($password)) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_email'] = $admin['email'];
                
                // Check if 2FA is set up
                if ($admin['authy_setup_complete'] == 1) {
                    // Redirect to 2FA verification
                    header('Location: admin/verify_2fa.php');
                    exit;
                } else {
                    // Redirect to 2FA setup
                    header('Location: admin/setup_2fa.php');
                    exit;
                }
            } else {
                $error = 'Invalid admin credentials.';
            }
        } else {
            // Check user credentials
            $stmt = $pdo->prepare("SELECT id, name, email, password FROM users WHERE email = ? OR name = ?");
            $stmt->execute([$email, $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Use md5 for password verification (as requested)
            if ($user && $user['password'] === md5($password)) {
                $_SESSION['user_logged_in'] = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                
                header('Location: profile.php');
                exit;
            } else {
                $error = 'Invalid user credentials.';
            }
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<!-- Login Section -->
<section class="min-h-screen flex items-center justify-center bg-[#030712] pt-32 pb-20 relative overflow-hidden">
    <div class="absolute top-0 right-0 w-96 h-96 bg-accent/10 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-96 h-96 bg-secondary/30 rounded-full blur-[120px] pointer-events-none"></div>
    
    <div class="max-w-md w-full px-4 sm:px-6 relative z-10" data-aos="zoom-in" data-aos-duration="1000">
        <div class="glass-effect-dark p-8 md:p-12 border border-white/10 shadow-[0_0_50px_rgba(0,0,0,0.5)]">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-serif text-white tracking-wide">Sign <span class="italic font-light text-accent">In</span></h2>
                <div class="w-12 h-[1px] bg-accent mx-auto mt-4 mb-4"></div>
                <p class="text-gray-400 font-sans text-sm font-light">Access your Grand Luxe account</p>
            </div>
            
            <?php if ($error): ?>
                <div class="mb-6 bg-red-900/30 border-l-2 border-red-500 p-4">
                    <p class="text-sm text-red-200 font-sans font-light"><?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php endif; ?>
            
            <form class="space-y-6" method="POST">
                <div>
                    <label for="email" class="block text-xs uppercase tracking-widest text-gray-400 font-sans mb-2">Email or Username</label>
                    <input id="email" name="email" type="text" required class="w-full bg-transparent border-b border-white/20 text-white px-0 py-3 focus:outline-none focus:border-accent transition-colors font-sans placeholder-gray-600">
                </div>
                
                <div>
                    <label for="password" class="block text-xs uppercase tracking-widest text-gray-400 font-sans mb-2">Password</label>
                    <input id="password" name="password" type="password" required class="w-full bg-transparent border-b border-white/20 text-white px-0 py-3 focus:outline-none focus:border-accent transition-colors font-sans placeholder-gray-600">
                </div>
                
                <div>
                    <label for="user_type" class="block text-xs uppercase tracking-widest text-gray-400 font-sans mb-2">Login As</label>
                    <select id="user_type" name="user_type" class="w-full bg-transparent border-b border-white/20 text-white px-0 py-3 focus:outline-none focus:border-accent transition-colors font-sans appearance-none">
                        <option value="user" class="bg-[#0a0a0a] text-white">Guest</option>
                        <option value="admin" class="bg-[#0a0a0a] text-white">Administrator</option>
                    </select>
                </div>
                
                <div class="pt-6">
                    <button type="submit" class="w-full bg-accent hover:bg-accent-light text-[#030712] px-8 py-4 font-bold tracking-[0.2em] uppercase transition-all duration-300 shadow-[0_0_15px_rgba(212,175,55,0.2)] font-sans text-sm">
                        Authenticate
                    </button>
                </div>
            </form>
            
            <div class="mt-8 text-center border-t border-white/10 pt-6">
                <p class="text-sm text-gray-400 font-sans font-light">
                    Don't have an account? 
                    <a href="register.php" class="text-accent hover:text-white transition-colors uppercase tracking-widest text-xs ml-2">Register Now</a>
                </p>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>