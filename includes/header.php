<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Management System</title>
    
    <!-- Tailwind CSS (via CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts: Cormorant Garamond (Luxury Serif) & Montserrat (Clean Sans) -->
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Tailwind Config (Must be before custom css if custom css uses tailwind classes, but config is JS) -->
    <script src="js/tailwind.config.js"></script>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/global.css?v=<?php echo time(); ?>">
</head>
<body class="font-sans antialiased bg-[#030712] text-white selection:bg-accent selection:text-white">

<!-- Page Loader -->
<div id="page-loader">
    <div class="loader-spinner"></div>
</div>

<!-- Top Gradient for Header Readability -->
<div class="fixed top-0 left-0 w-full h-40 bg-gradient-to-b from-black/80 to-transparent z-[90] pointer-events-none"></div>

<!-- Luxury Navigation Bar -->
<nav class="fixed w-full z-[100] transition-all duration-500 bg-transparent" id="navbar">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-32 transition-all duration-500" id="nav-container">
            
            <!-- Logo (Text Only, Luxury Feel) -->
            <div class="flex-shrink-0 flex items-center">
                <a href="index.php" class="text-4xl font-serif font-bold text-white tracking-[0.2em] uppercase hover:text-accent transition-colors duration-300">
                    Grand<span class="font-light text-accent ml-2">Luxe</span>
                </a>
            </div>
            
            <!-- Desktop Menu -->
            <div class="hidden md:flex items-center space-x-10">
                <a href="index.php" class="text-base font-bold text-white tracking-[0.2em] uppercase hover:text-accent transition-colors duration-300 nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active-link' : ''; ?>">Home</a>
                <a href="index.php#about" class="text-base font-bold text-white tracking-[0.2em] uppercase hover:text-accent transition-colors duration-300 nav-link">About</a>
                <a href="hotels.php" class="text-base font-bold text-white tracking-[0.2em] uppercase hover:text-accent transition-colors duration-300 nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'hotels.php' ? 'active-link' : ''; ?>">Retreat</a>
                <a href="reviews.php" class="text-base font-bold text-white tracking-[0.2em] uppercase hover:text-accent transition-colors duration-300 nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'reviews.php' ? 'active-link' : ''; ?>">Reviews</a>
                
                <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']): ?>
                    <a href="book-now.php" class="text-base font-bold text-[#030712] bg-accent hover:bg-accent-light px-6 py-3 tracking-[0.2em] uppercase transition-colors duration-300 <?php echo basename($_SERVER['PHP_SELF']) == 'book-now.php' ? 'ring-2 ring-white ring-offset-2 ring-offset-black' : ''; ?>">Book Now</a>
                    
                    <!-- User Dropdown -->
                    <div class="relative user-dropdown ml-6">
                        <button type="button" class="flex items-center space-x-2 text-white hover:text-accent focus:outline-none transition-colors duration-300" id="user-menu-button">
                            <span class="w-12 h-12 rounded-full border border-accent flex items-center justify-center font-serif text-xl bg-black/30">
                                <?php 
                                $name = $_SESSION['user_name'] ?? '';
                                echo !empty($name) ? strtoupper($name[0]) : 'U';
                                ?>
                            </span>
                        </button>
                        <div class="dropdown-menu border border-white/10 glass-effect-dark" id="user-dropdown-menu">
                            <div class="px-6 py-4 border-b border-white/10">
                                <p class="text-xs text-gray-400 uppercase tracking-widest">Welcome,</p>
                                <p class="text-sm font-medium text-white truncate font-serif italic mt-1"><?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
                            </div>
                            <div class="py-3">
                                <a href="profile.php" class="block px-6 py-3 text-sm text-gray-300 hover:bg-white/5 hover:text-accent transition-colors">Profile</a>
                                <a href="my-bookings.php" class="block px-6 py-3 text-sm text-gray-300 hover:bg-white/5 hover:text-accent transition-colors">My Bookings</a>
                                <a href="logout.php" class="block px-6 py-3 text-sm text-gray-300 hover:bg-white/5 hover:text-accent transition-colors">Sign out</a>
                            </div>
                        </div>
                    </div>
                <?php elseif (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']): ?>
                    <!-- Admin Dropdown -->
                    <div class="relative user-dropdown ml-6">
                        <button type="button" class="flex items-center space-x-2 text-white hover:text-accent focus:outline-none transition-colors duration-300" id="admin-menu-button">
                            <span class="w-12 h-12 rounded-full border border-accent flex items-center justify-center font-serif text-xl bg-accent/10 text-accent">
                                <?php 
                                $name = $_SESSION['admin_username'] ?? '';
                                echo !empty($name) ? strtoupper($name[0]) : 'A';
                                ?>
                            </span>
                        </button>
                        <div class="dropdown-menu border border-white/10 glass-effect-dark" id="admin-dropdown-menu">
                            <div class="px-6 py-4 border-b border-white/10">
                                <p class="text-xs text-gray-400 uppercase tracking-widest">Admin</p>
                                <p class="text-sm font-medium text-white truncate font-serif italic mt-1"><?php echo htmlspecialchars($_SESSION['admin_username']); ?></p>
                            </div>
                            <div class="py-3">
                                <a href="admin/dashboard.php" class="block px-6 py-3 text-sm text-gray-300 hover:bg-white/5 hover:text-accent transition-colors">Dashboard</a>
                                <a href="admin/logout.php" class="block px-6 py-3 text-sm text-gray-300 hover:bg-white/5 hover:text-accent transition-colors">Sign out</a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="flex items-center space-x-8 ml-6 border-l border-white/20 pl-8">
                        <a href="login.php" class="text-base font-bold text-white tracking-[0.2em] uppercase hover:text-accent transition-colors duration-300">Sign In</a>
                        <a href="register.php" class="text-base font-bold text-[#030712] bg-accent hover:bg-accent-light px-8 py-4 tracking-[0.2em] uppercase transition-all duration-300 shadow-[0_0_15px_rgba(212,175,55,0.2)]">Reserve</a>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Mobile menu button -->
            <div class="flex items-center md:hidden">
                <button type="button" id="mobile-menu-button" class="inline-flex items-center justify-center p-2 text-white hover:text-accent focus:outline-none transition-colors">
                    <svg class="h-8 w-8 font-light" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Mobile Menu -->
    <div class="hidden md:hidden glass-effect-dark border-t border-white/10 absolute w-full left-0 mobile-menu" id="mobile-menu">
        <div class="px-2 pt-2 pb-6 space-y-1 sm:px-3 text-center">
            <a href="index.php" class="block px-3 py-4 text-sm tracking-[0.2em] uppercase font-medium text-white hover:bg-white/5 hover:text-accent transition-all">Home</a>
            <a href="hotels.php" class="block px-3 py-4 text-sm tracking-[0.2em] uppercase font-medium text-white hover:bg-white/5 hover:text-accent transition-all">Retreat</a>
            <a href="reviews.php" class="block px-3 py-4 text-sm tracking-[0.2em] uppercase font-medium text-white hover:bg-white/5 hover:text-accent transition-all">Reviews</a>
            
            <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']): ?>
                <a href="book-now.php" class="block px-3 py-4 text-sm tracking-[0.2em] uppercase font-medium text-white hover:bg-white/5 hover:text-accent transition-all">Book Now</a>
                <div class="mt-4 pt-4 border-t border-white/10">
                    <a href="profile.php" class="block px-3 py-3 text-sm tracking-[0.1em] font-medium text-gray-300 hover:text-white hover:bg-white/5 transition-all">Profile</a>
                    <a href="my-bookings.php" class="block px-3 py-3 text-sm tracking-[0.1em] font-medium text-gray-300 hover:text-white hover:bg-white/5 transition-all">My Bookings</a>
                    <a href="logout.php" class="block px-3 py-3 text-sm tracking-[0.1em] font-medium text-accent hover:text-white hover:bg-white/5 transition-all">Sign out</a>
                </div>
            <?php elseif (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']): ?>
                <div class="mt-4 pt-4 border-t border-white/10">
                    <a href="admin/dashboard.php" class="block px-3 py-3 text-sm tracking-[0.1em] font-medium text-gray-300 hover:text-white hover:bg-white/5 transition-all">Admin Dashboard</a>
                    <a href="admin/logout.php" class="block px-3 py-3 text-sm tracking-[0.1em] font-medium text-accent hover:text-white hover:bg-white/5 transition-all">Sign out</a>
                </div>
            <?php else: ?>
                <div class="mt-6 pt-6 border-t border-white/10 flex flex-col space-y-4 px-6">
                    <a href="login.php" class="block text-center py-3 text-sm tracking-[0.2em] uppercase font-medium text-white border border-white/20 hover:bg-white/5 transition-all">Sign In</a>
                    <a href="register.php" class="block text-center py-3 text-sm tracking-[0.2em] uppercase font-medium text-primary bg-accent hover:bg-accent-light transition-all">Reserve</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</nav>

<script>
    // Page Loader
    window.addEventListener('load', function() {
        const loader = document.getElementById('page-loader');
        if (loader) {
            loader.style.opacity = '0';
            setTimeout(() => {
                loader.style.display = 'none';
            }, 800);
        }
    });

    // Mobile menu toggle
    document.getElementById('mobile-menu-button').addEventListener('click', function() {
        var mobileMenu = document.getElementById('mobile-menu');
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
        
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            const navContainer = document.getElementById('nav-container');
            if (window.scrollY > 50) {
                navbar.classList.add('shadow-2xl', 'glass-effect-dark');
                navbar.classList.remove('bg-transparent');
                navbar.style.background = '';
                navContainer.classList.replace('h-32', 'h-24');
            } else {
                navbar.classList.remove('shadow-2xl', 'glass-effect-dark');
                navbar.classList.add('bg-transparent');
                navbar.style.background = '';
                navContainer.classList.replace('h-24', 'h-32');
            }
        });
    });
</script>

<!-- AOS Script -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
      AOS.init({
          duration: 1200,
          once: true,
          offset: 50,
          easing: 'ease-out-quart'
      });
  });
</script>
