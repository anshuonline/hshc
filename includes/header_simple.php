<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demo Hotel & Resort Demo City</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#059669', // green-600
                        secondary: '#047857', // green-700
                        accent: '#10b981', // green-500
                        light: '#ecfdf5', // green-50
                        dark: '#065f46', // green-800
                    },
                    fontFamily: {
                        'serif': ['Playfair Display', 'serif'],
                        'sans': ['Poppins', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <style>
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 12px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #059669;
            border-radius: 6px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #047857;
        }
        
        /* Enhanced navigation styles */
        .nav-link {
            position: relative;
            transition: all 0.3s ease;
            padding-bottom: 5px;
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: #059669;
            transition: width 0.3s ease;
        }
        
        .nav-link:hover::after {
            width: 100%;
        }
        
        .nav-link:hover {
            color: #059669 !important;
        }
        
        /* Active link styling */
        .active-link {
            color: #059669 !important;
        }
        
        .active-link::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: #059669;
        }
        
        /* Mobile menu animation */
        .mobile-menu {
            transition: all 0.3s ease-in-out;
            transform-origin: top;
        }
        
        /* Enhanced button styles */
        .btn-primary {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            box-shadow: 0 4px 6px rgba(5, 150, 105, 0.2);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(5, 150, 105, 0.3);
        }
        
        /* Admin button styles */
        .btn-admin {
            transition: all 0.3s ease;
        }
        
        .btn-admin:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        /* User profile dropdown */
        .user-dropdown {
            position: relative;
        }
        
        .dropdown-menu {
            position: absolute;
            right: 0;
            top: 100%;
            margin-top: 0.5rem;
            width: 200px;
            background-color: white;
            border-radius: 0.375rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            z-index: 50;
            display: none;
        }
        
        .dropdown-menu.show {
            display: block;
        }
        
        .dropdown-item {
            display: block;
            padding: 0.75rem 1rem;
            color: #4b5563;
            text-decoration: none;
            transition: background-color 0.2s;
        }
        
        .dropdown-item:hover {
            background-color: #f3f4f6;
            color: #059669;
        }
        
        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: #059669;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
    </style>
</head>
<body class="bg-gray-50 font-sans">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between">
                <div class="flex space-x-7">
                    <div>
                        <a href="index.php" class="flex items-center py-4 px-2">
                            <img src="includes/Images/logo.jpg" alt="Demo Hotel & Resort Logo" class="h-10 rounded-full mr-2">
                            <span class="font-serif text-gray-800 text-xl font-bold">Serin Dewachen<span class="text-accent">Retreat</span></span>
                        </a>
                    </div>
                </div>
                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center">
                    <button id="mobile-menu-button" class="outline-none mobile-menu-button text-gray-600 hover:text-accent transition-colors duration-300">
                        <i class="fas fa-bars text-2xl"></i>
                    </button>
                </div>
                <div class="hidden md:flex items-center space-x-1">
                    <a href="index.php" class="py-4 px-3 text-gray-700 font-medium nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' && !isset($_GET['page']) ? 'active-link' : ''; ?>">Home</a>
                    <a href="index.php#about" class="py-4 px-3 text-gray-700 font-medium nav-link">About</a>
                    <a href="hotels.php" class="py-4 px-3 text-gray-700 font-medium nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'hotels.php' ? 'active-link' : ''; ?>">Hotel</a>
                    <a href="reviews.php" class="py-4 px-3 text-gray-700 font-medium nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'reviews.php' ? 'active-link' : ''; ?>">Reviews</a>
                    <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']): ?>
                        <a href="book-now.php" class="py-4 px-3 text-gray-700 font-medium nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'book-now.php' ? 'active-link' : ''; ?>">Book Now</a>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']): ?>
                        <div class="user-dropdown relative">
                            <button id="user-menu-button" class="flex items-center space-x-2 py-4 px-3 text-gray-700 font-medium hover:text-accent transition-colors duration-300">
                                <div class="user-avatar">
                                    <?php 
                                    $name = $_SESSION['user_name'] ?? '';
                                    $initial = !empty($name) ? strtoupper($name[0]) : 'U';
                                    echo $initial;
                                    ?>
                                </div>
                                <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                            <div id="user-dropdown-menu" class="dropdown-menu">
                                <a href="profile.php" class="dropdown-item">
                                    <i class="fas fa-user mr-2"></i>Profile
                                </a>
                                <a href="my-bookings.php" class="dropdown-item">
                                    <i class="fas fa-calendar-check mr-2"></i>My Bookings
                                </a>
                                <a href="reviews.php" class="dropdown-item">
                                    <i class="fas fa-comments mr-2"></i>Reviews
                                </a>
                                <a href="logout.php" class="dropdown-item">
                                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                                </a>
                            </div>
                        </div>
                    <?php elseif (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']): ?>
                        <div class="user-dropdown relative">
                            <button id="admin-menu-button" class="flex items-center space-x-2 py-4 px-3 text-gray-700 font-medium hover:text-accent transition-colors duration-300">
                                <div class="user-avatar bg-secondary">
                                    <?php 
                                    $name = $_SESSION['admin_username'] ?? '';
                                    $initial = !empty($name) ? strtoupper($name[0]) : 'A';
                                    echo $initial;
                                    ?>
                                </div>
                                <span><?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                            <div id="admin-dropdown-menu" class="dropdown-menu">
                                <a href="admin/dashboard.php" class="dropdown-item">
                                    <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                                </a>
                                <a href="admin/logout.php" class="dropdown-item">
                                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="register.php" class="py-2 px-4 bg-accent hover:bg-secondary text-white rounded-lg transition duration-300 btn-admin">Register</a>
                        <a href="login.php" class="py-2 px-4 bg-white border border-accent text-accent hover:bg-light rounded-lg transition duration-300 btn-admin">Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <!-- Mobile Menu -->
        <div class="hidden mobile-menu md:hidden bg-white shadow-lg">
            <ul class="py-3 space-y-1">
                <li><a href="index.php" class="block px-4 py-3 text-gray-800 font-medium hover:bg-light transition-colors duration-300 <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' && !isset($_GET['page']) ? 'bg-light border-l-4 border-accent' : ''; ?>">Home</a></li>
                <li><a href="index.php#about" class="block px-4 py-3 text-gray-700 font-medium hover:bg-light transition-colors duration-300">About</a></li>
                <li><a href="hotels.php" class="block px-4 py-3 text-gray-700 font-medium hover:bg-light transition-colors duration-300 <?php echo basename($_SERVER['PHP_SELF']) == 'hotels.php' ? 'bg-light border-l-4 border-accent' : ''; ?>">Hotel</a></li>
                <li><a href="reviews.php" class="block px-4 py-3 text-gray-700 font-medium hover:bg-light transition-colors duration-300 <?php echo basename($_SERVER['PHP_SELF']) == 'reviews.php' ? 'bg-light border-l-4 border-accent' : ''; ?>">Reviews</a></li>
                <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']): ?>
                    <li><a href="book-now.php" class="block px-4 py-3 text-gray-700 font-medium hover:bg-light transition-colors duration-300 <?php echo basename($_SERVER['PHP_SELF']) == 'book-now.php' ? 'bg-light border-l-4 border-accent' : ''; ?>">Book Now</a></li>
                <?php endif; ?>
                <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']): ?>
                    <li class="border-t border-gray-200 pt-2">
                        <div class="px-4 py-2 text-gray-700 font-medium">
                            <div class="flex items-center space-x-2">
                                <div class="user-avatar">
                                    <?php 
                                    $name = $_SESSION['user_name'] ?? '';
                                    $initial = !empty($name) ? strtoupper($name[0]) : 'U';
                                    echo $initial;
                                    ?>
                                </div>
                                <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                            </div>
                        </div>
                    </li>
                    <li><a href="profile.php" class="block px-4 py-3 text-gray-700 font-medium hover:bg-light transition-colors duration-300">Profile</a></li>
                    <li><a href="my-bookings.php" class="block px-4 py-3 text-gray-700 font-medium hover:bg-light transition-colors duration-300">My Bookings</a></li>
                    <li><a href="reviews.php" class="block px-4 py-3 text-gray-700 font-medium hover:bg-light transition-colors duration-300">Reviews</a></li>
                    <li><a href="logout.php" class="block px-4 py-3 text-gray-700 font-medium hover:bg-light transition-colors duration-300">Logout</a></li>
                <?php elseif (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']): ?>
                    <li class="border-t border-gray-200 pt-2">
                        <div class="px-4 py-2 text-gray-700 font-medium">
                            <div class="flex items-center space-x-2">
                                <div class="user-avatar bg-secondary">
                                    <?php 
                                    $name = $_SESSION['admin_username'] ?? '';
                                    $initial = !empty($name) ? strtoupper($name[0]) : 'A';
                                    echo $initial;
                                    ?>
                                </div>
                                <span><?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                            </div>
                        </div>
                    </li>
                    <li><a href="admin/dashboard.php" class="block px-4 py-3 text-gray-700 font-medium hover:bg-light transition-colors duration-300">Admin Dashboard</a></li>
                    <li><a href="admin/logout.php" class="block px-4 py-3 text-gray-700 font-medium hover:bg-light transition-colors duration-300">Logout</a></li>
                <?php else: ?>
                    <li><a href="register.php" class="block px-4 py-3 text-gray-700 font-medium hover:bg-light transition-colors duration-300">Register</a></li>
                    <li><a href="login.php" class="block px-4 py-3 text-gray-700 font-medium hover:bg-light transition-colors duration-300">Login</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
    
    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            var mobileMenu = document.querySelector('.mobile-menu');
            mobileMenu.classList.toggle('hidden');
        });
        
        // User dropdown toggle
        document.addEventListener('DOMContentLoaded', function() {
            const userMenuButton = document.getElementById('user-menu-button');
            const userDropdownMenu = document.getElementById('user-dropdown-menu');
            
            const adminMenuButton = document.getElementById('admin-menu-button');
            const adminDropdownMenu = document.getElementById('admin-dropdown-menu');
            
            function toggleDropdown(button, menu) {
                if (button && menu) {
                    button.addEventListener('click', function(e) {
                        e.stopPropagation();
                        menu.classList.toggle('show');
                    });
                }
            }
            
            toggleDropdown(userMenuButton, userDropdownMenu);
            toggleDropdown(adminMenuButton, adminDropdownMenu);
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function() {
                if (userDropdownMenu) userDropdownMenu.classList.remove('show');
                if (adminDropdownMenu) adminDropdownMenu.classList.remove('show');
            });
        });
        
        // Smooth scroll for all anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                    
                    // Close mobile menu if open
                    const mobileMenu = document.querySelector('.mobile-menu');
                    if (!mobileMenu.classList.contains('hidden')) {
                        mobileMenu.classList.add('hidden');
                    }
                }
            });
        });
        
        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            const mobileMenu = document.querySelector('.mobile-menu');
            const menuButton = document.getElementById('mobile-menu-button');
            
            if (mobileMenu && !mobileMenu.classList.contains('hidden') && 
                !mobileMenu.contains(event.target) && 
                !menuButton.contains(event.target)) {
                mobileMenu.classList.add('hidden');
            }
        });
    </script>
</body>
</html>