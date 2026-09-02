<?php
session_start();
include 'config/db.php';

// Redirect if already logged in
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']) {
    header('Location: profile.php');
    exit;
}

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']) {
    header('Location: admin/dashboard.php');
    exit;
}

$error = '';
$success = '';

// List of countries for the dropdown
$countries = [
    "Afghanistan", "Albania", "Algeria", "Andorra", "Angola", "Antigua and Barbuda", "Argentina", "Armenia", "Australia", "Austria",
    "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bhutan",
    "Bolivia", "Bosnia and Herzegovina", "Botswana", "Brazil", "Brunei", "Bulgaria", "Burkina Faso", "Burundi", "Cabo Verde", "Cambodia",
    "Cameroon", "Canada", "Central African Republic", "Chad", "Chile", "China", "Colombia", "Comoros", "Congo (Congo-Brazzaville)", "Costa Rica",
    "Croatia", "Cuba", "Cyprus", "Czechia (Czech Republic)", "Democratic Republic of the Congo", "Denmark", "Djibouti", "Dominica", "Dominican Republic", "Ecuador",
    "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Eswatini (fmr. Swaziland)", "Ethiopia", "Fiji", "Finland", "France",
    "Gabon", "Gambia", "Georgia", "Germany", "Ghana", "Greece", "Grenada", "Guatemala", "Guinea", "Guinea-Bissau",
    "Guyana", "Haiti", "Holy See", "Honduras", "Hungary", "Iceland", "India", "Indonesia", "Iran", "Iraq",
    "Ireland", "Israel", "Italy", "Jamaica", "Japan", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Kuwait",
    "Kyrgyzstan", "Laos", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libya", "Liechtenstein", "Lithuania", "Luxembourg",
    "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Mauritania", "Mauritius", "Mexico",
    "Micronesia", "Moldova", "Monaco", "Mongolia", "Montenegro", "Morocco", "Mozambique", "Myanmar (formerly Burma)", "Namibia", "Nauru",
    "Nepal", "Netherlands", "New Zealand", "Nicaragua", "Niger", "Nigeria", "North Korea", "North Macedonia", "Norway", "Oman",
    "Pakistan", "Palau", "Palestine State", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Poland", "Portugal",
    "Qatar", "Romania", "Russia", "Rwanda", "Saint Kitts and Nevis", "Saint Lucia", "Saint Vincent and the Grenadines", "Samoa", "San Marino", "Sao Tome and Principe",
    "Saudi Arabia", "Senegal", "Serbia", "Seychelles", "Sierra Leone", "Singapore", "Slovakia", "Slovenia", "Solomon Islands", "Somalia",
    "South Africa", "South Korea", "South Sudan", "Spain", "Sri Lanka", "Sudan", "Suriname", "Sweden", "Switzerland", "Syria",
    "Tajikistan", "Tanzania", "Thailand", "Timor-Leste", "Togo", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan",
    "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States of America", "Uruguay", "Uzbekistan", "Vanuatu", "Venezuela",
    "Vietnam", "Yemen", "Zambia", "Zimbabwe"
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $country = trim($_POST['country']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($name) || empty($email) || empty($phone) || empty($country) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (!preg_match("/^[0-9+\-\s\(\)]+$/", $phone)) {
        $error = 'Please enter a valid phone number.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'An account with this email already exists.';
        } else {
            // Register the user
            try {
                $hashed_password = md5($password); // Using MD5 as requested
                
                $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, country, password) VALUES (?, ?, ?, ?, ?)");
                $result = $stmt->execute([$name, $email, $phone, $country, $hashed_password]);
                
                if ($result) {
                    // Get the inserted user ID
                    $user_id = $pdo->lastInsertId();
                    
                    // Automatically log in the user
                    $_SESSION['user_logged_in'] = true;
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_email'] = $email;
                    
                    // Redirect to profile page
                    header('Location: profile.php');
                    exit;
                } else {
                    $error = 'Registration failed. Database operation failed.';
                }
            } catch (PDOException $e) {
                $error = 'Registration failed. Database error: ' . $e->getMessage();
            }
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<!-- Register Section -->
<section class="min-h-screen flex items-center justify-center bg-[#030712] pt-32 pb-20 relative overflow-hidden">
    <div class="absolute top-0 right-0 w-96 h-96 bg-accent/10 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-96 h-96 bg-secondary/30 rounded-full blur-[120px] pointer-events-none"></div>
    
    <div class="max-w-2xl w-full px-4 sm:px-6 relative z-10" data-aos="zoom-in" data-aos-duration="1000">
        <div class="glass-effect-dark p-8 md:p-12 border border-white/10 shadow-[0_0_50px_rgba(0,0,0,0.5)]">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-serif text-white tracking-wide">Become a <span class="italic font-light text-accent">Member</span></h2>
                <div class="w-12 h-[1px] bg-accent mx-auto mt-4 mb-4"></div>
                <p class="text-gray-400 font-sans text-sm font-light">Join us for exclusive benefits and personalized experiences</p>
            </div>
            
            <?php if ($error): ?>
                <div class="mb-6 bg-red-900/30 border-l-2 border-red-500 p-4">
                    <p class="text-sm text-red-200 font-sans font-light"><?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="mb-6 bg-green-900/30 border-l-2 border-green-500 p-4">
                    <p class="text-sm text-green-200 font-sans font-light"><?php echo htmlspecialchars($success); ?></p>
                </div>
            <?php endif; ?>
            
            <form class="space-y-6" method="POST">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-xs uppercase tracking-widest text-gray-400 font-sans mb-2">Full Name</label>
                        <input type="text" id="name" name="name" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" class="w-full bg-transparent border-b border-white/20 text-white px-0 py-3 focus:outline-none focus:border-accent transition-colors font-sans placeholder-gray-600">
                    </div>
                    
                    <div>
                        <label for="email" class="block text-xs uppercase tracking-widest text-gray-400 font-sans mb-2">Email Address</label>
                        <input type="email" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" class="w-full bg-transparent border-b border-white/20 text-white px-0 py-3 focus:outline-none focus:border-accent transition-colors font-sans placeholder-gray-600">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="phone" class="block text-xs uppercase tracking-widest text-gray-400 font-sans mb-2">Phone Number</label>
                        <input type="tel" id="phone" name="phone" required value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" class="w-full bg-transparent border-b border-white/20 text-white px-0 py-3 focus:outline-none focus:border-accent transition-colors font-sans placeholder-gray-600" placeholder="+91 XXX XXX XXXX">
                    </div>
                    
                    <div>
                        <label for="country" class="block text-xs uppercase tracking-widest text-gray-400 font-sans mb-2">Country</label>
                        <select id="country" name="country" required class="w-full bg-transparent border-b border-white/20 text-white px-0 py-3 focus:outline-none focus:border-accent transition-colors font-sans appearance-none">
                            <option value="" class="bg-[#0a0a0a] text-gray-400">Select Country</option>
                            <?php foreach ($countries as $country_name): ?>
                                <option value="<?php echo htmlspecialchars($country_name); ?>" class="bg-[#0a0a0a] text-white" <?php echo (isset($_POST['country']) && $_POST['country'] === $country_name) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($country_name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="password" class="block text-xs uppercase tracking-widest text-gray-400 font-sans mb-2">Password</label>
                        <input type="password" id="password" name="password" required minlength="6" class="w-full bg-transparent border-b border-white/20 text-white px-0 py-3 focus:outline-none focus:border-accent transition-colors font-sans placeholder-gray-600">
                    </div>
                    
                    <div>
                        <label for="confirm_password" class="block text-xs uppercase tracking-widest text-gray-400 font-sans mb-2">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="6" class="w-full bg-transparent border-b border-white/20 text-white px-0 py-3 focus:outline-none focus:border-accent transition-colors font-sans placeholder-gray-600">
                    </div>
                </div>
                
                <div class="pt-6">
                    <button type="submit" class="w-full bg-accent hover:bg-accent-light text-[#030712] px-8 py-4 font-bold tracking-[0.2em] uppercase transition-all duration-300 shadow-[0_0_15px_rgba(212,175,55,0.2)] font-sans text-sm">
                        Create Account
                    </button>
                </div>
            </form>
            
            <div class="mt-8 text-center border-t border-white/10 pt-6">
                <p class="text-sm text-gray-400 font-sans font-light">
                    Already a member? 
                    <a href="login.php" class="text-accent hover:text-white transition-colors uppercase tracking-widest text-xs ml-2">Sign In</a>
                </p>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>