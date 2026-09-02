<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Modern Responsive Navigation -->
<nav class="bg-white shadow-md">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <div class="flex-shrink-0 flex items-center">
                    <span class="text-primary font-bold text-xl">Demo Hotel & Resort</span>
                    <span class="ml-2 text-gray-500 text-sm">Admin</span>
                </div>
            </div>
            
            <!-- Desktop Menu -->
            <div class="hidden md:flex items-center space-x-1">
                <a href="dashboard.php" class="<?php echo $current_page == 'dashboard.php' ? 'bg-primary text-white' : 'text-gray-700 hover:text-primary hover:bg-gray-100'; ?> px-4 py-2 rounded-lg text-sm font-medium">Dashboard</a>
                
                <div class="relative" id="manage-dropdown">
                    <button class="<?php echo in_array($current_page, ['hotels.php', 'images.php', 'rooms.php', 'bookings.php', 'subscribers.php', 'reviews.php', 'admins.php']) ? 'bg-primary text-white' : 'text-gray-700 hover:text-primary'; ?> px-4 py-2 rounded-lg text-sm font-medium flex items-center" id="manage-button">
                        Manage <i class="fas fa-chevron-down ml-1 text-xs"></i>
                    </button>
                    <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 hidden z-50" id="manage-menu">
                        <a href="hotels.php" class="block px-4 py-2 text-sm <?php echo $current_page == 'hotels.php' ? 'bg-gray-100 text-primary font-bold' : 'text-gray-700 hover:bg-gray-100'; ?>">Retreat</a>
                        <a href="images.php" class="block px-4 py-2 text-sm <?php echo $current_page == 'images.php' ? 'bg-gray-100 text-primary font-bold' : 'text-gray-700 hover:bg-gray-100'; ?>">Images</a>
                        <a href="rooms.php" class="block px-4 py-2 text-sm <?php echo $current_page == 'rooms.php' ? 'bg-gray-100 text-primary font-bold' : 'text-gray-700 hover:bg-gray-100'; ?>">Rooms</a>
                        <a href="bookings.php" class="block px-4 py-2 text-sm <?php echo $current_page == 'bookings.php' ? 'bg-gray-100 text-primary font-bold' : 'text-gray-700 hover:bg-gray-100'; ?>">Bookings</a>
                        <a href="subscribers.php" class="block px-4 py-2 text-sm <?php echo $current_page == 'subscribers.php' ? 'bg-gray-100 text-primary font-bold' : 'text-gray-700 hover:bg-gray-100'; ?>">Subscribers</a>
                        <a href="reviews.php" class="block px-4 py-2 text-sm <?php echo $current_page == 'reviews.php' ? 'bg-gray-100 text-primary font-bold' : 'text-gray-700 hover:bg-gray-100'; ?>">Reviews</a>
                        <a href="admins.php" class="block px-4 py-2 text-sm <?php echo $current_page == 'admins.php' ? 'bg-gray-100 text-primary font-bold' : 'text-gray-700 hover:bg-gray-100'; ?>">Admins</a>
                    </div>
                </div>
                
                <span class="text-gray-700 px-4 py-2 rounded-lg text-sm font-medium flex items-center">
                    <i class="fas fa-user-circle mr-1"></i><?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?>
                </span>
                <a href="logout.php" class="text-gray-700 hover:text-primary px-4 py-2 rounded-lg text-sm font-medium flex items-center">
                    <i class="fas fa-sign-out-alt mr-1"></i>Logout
                </a>
            </div>
            
            <!-- Mobile menu button -->
            <div class="md:hidden flex items-center">
                <button id="mobile-menu-button" class="text-gray-700 hover:text-primary focus:outline-none">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Mobile Menu -->
    <div id="mobile-menu" class="md:hidden hidden bg-white border-t">
        <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
            <a href="dashboard.php" class="<?php echo $current_page == 'dashboard.php' ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100'; ?> block px-3 py-2 rounded-md text-base font-medium">Dashboard</a>
            <a href="hotels.php" class="<?php echo $current_page == 'hotels.php' ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100'; ?> block px-3 py-2 rounded-md text-base font-medium">Hotels</a>
            <a href="images.php" class="<?php echo $current_page == 'images.php' ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100'; ?> block px-3 py-2 rounded-md text-base font-medium">Images</a>
            <a href="rooms.php" class="<?php echo $current_page == 'rooms.php' ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100'; ?> block px-3 py-2 rounded-md text-base font-medium">Rooms</a>
            <a href="bookings.php" class="<?php echo $current_page == 'bookings.php' ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100'; ?> block px-3 py-2 rounded-md text-base font-medium">Bookings</a>
            <a href="subscribers.php" class="<?php echo $current_page == 'subscribers.php' ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100'; ?> block px-3 py-2 rounded-md text-base font-medium">Subscribers</a>
            <a href="reviews.php" class="<?php echo $current_page == 'reviews.php' ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100'; ?> block px-3 py-2 rounded-md text-base font-medium">Reviews</a>
            <a href="admins.php" class="<?php echo $current_page == 'admins.php' ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100'; ?> block px-3 py-2 rounded-md text-base font-medium">Admins</a>
            <div class="px-3 py-2 text-gray-700 text-base font-medium border-t border-gray-200 mt-2 pt-2">
                Welcome, <?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?>!
            </div>
            <a href="logout.php" class="text-gray-700 hover:bg-gray-100 block px-3 py-2 rounded-md text-base font-medium">Logout</a>
        </div>
    </div>
</nav>

<script>
    // Navbar JavaScript
    document.addEventListener('DOMContentLoaded', function() {
        const manageBtn = document.getElementById('manage-button');
        const manageMenu = document.getElementById('manage-menu');
        const mobileMenuBtn = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        
        if (manageBtn && manageMenu) {
            manageBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                manageMenu.classList.toggle('hidden');
            });
            
            document.addEventListener('click', function(e) {
                if (!manageBtn.contains(e.target) && !manageMenu.contains(e.target)) {
                    manageMenu.classList.add('hidden');
                }
            });
        }
        
        if (mobileMenuBtn && mobileMenu) {
            mobileMenuBtn.addEventListener('click', function() {
                mobileMenu.classList.toggle('hidden');
            });
        }
    });
</script>
