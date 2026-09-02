<?php

session_start();
include 'config/db.php';
include 'includes/header.php';


error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fetch the hotel information
$stmt = $pdo->query("SELECT * FROM hotels LIMIT 1");
$hotel = $stmt->fetch(PDO::FETCH_ASSOC);

// If no hotel data in database, use the real information you provided
if (!$hotel) {
    $hotel = [
        'name' => 'Demo Hotel & Resort',
        'description' => 'Demo Hotel & Resort is located at 123 Demo Street along the national highway, just five minutes away from M.G. Marg, the heart of Demo City. Demo City has something for everyone; it has places of interest for the holiday-makers, fine restaurants for the foodies, gorgeous nature for the nature-lovers and fashion streets for the fashionistas.

The retreat is designed in new-age architecture with a focus on comfort and contemporary trends of living, all at an affordable tariff. It offers world-class facilities and brilliant services with modern amenities.

Our retreats in Demo City are located around these cultural and commercial places, making travelling within the city easy for visitors. Our retreats ensure an enjoyable stay for its patrons by making available rooms with the best amenities and a hospitable staff. The retreat is not only offer exceptional comfort and convenience, but also brilliant retreat deals that make accommodation easy and affordable.

Our retreat in Demo City that you must choose for a successful Demo City trip.',
        'address' => '123 Demo Street, Demo City, Demo State',
        'phone' => '+12345678900',
        'email' => 'reservations@demohotel.com'
    ];
}

// Fetch hotel images for carousel
$images = [];
if ($hotel) {
    $stmt = $pdo->prepare("SELECT * FROM hotel_images WHERE hotel_id = ? AND usage_type IN ('carousel', 'both') ORDER BY CASE WHEN carousel_position IS NULL THEN 999 ELSE carousel_position END ASC, created_at ASC LIMIT 10");
    $stmt->execute([$hotel['id']]);
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// If no carousel images found, get any images
if (empty($images) && $hotel) {
    $stmt = $pdo->prepare("SELECT * FROM hotel_images WHERE hotel_id = ? ORDER BY created_at DESC LIMIT 10");
    $stmt->execute([$hotel['id']]);
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// If no database images, use uploaded images from folder
if (empty($images)) {
    $uploadDir = 'uploads/';
    $uploadedImages = glob($uploadDir . "*.jpg");
    if (empty($uploadedImages)) {
        $uploadedImages = glob($uploadDir . "*.jpeg");
    }
    if (empty($uploadedImages)) {
        $uploadedImages = glob($uploadDir . "*.png");
    }
    
    // Create image array from uploaded files
    foreach (array_slice($uploadedImages, 0, 10) as $imagePath) {
        $images[] = ['image_path' => $imagePath];
    }
}

// If still no images, use placeholder images
if (empty($images)) {
    $images = [
        ['image_path' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1200&q=80'],
        ['image_path' => 'https://images.unsplash.com/photo-1611892440504-42a792e24d32?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1200&q=80'],
        ['image_path' => 'https://images.unsplash.com/photo-1566665797739-1674de7a421a?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1200&q=80']
    ];
}
?>

<!-- Hero Carousel Section -->
<div class="relative">
    <!-- Carousel wrapper -->
    <div id="heroCarousel" class="relative h-screen overflow-hidden">
        <?php foreach ($images as $index => $image): ?>
            <div class="absolute inset-0 opacity-0 transition-opacity duration-1000 ease-in-out <?php echo $index === 0 ? 'opacity-100' : ''; ?>" data-carousel-item>
                <img src="<?php echo htmlspecialchars($image['image_path']); ?>" 
                     alt="Hotel Demo Hotel & Resort Image <?php echo $index + 1; ?>" 
                     class="w-full h-full object-cover"
                     onerror="this.src='https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1200&q=80';">
            </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Slider indicators -->
    <div class="absolute z-30 flex space-x-3 -translate-x-1/2 bottom-5 left-1/2">
        <?php foreach ($images as $index => $image): ?>
            <button type="button" class="w-3 h-3 rounded-full <?php echo $index === 0 ? 'bg-white' : 'bg-gray-400'; ?>" aria-current="<?php echo $index === 0 ? 'true' : 'false'; ?>" aria-label="Slide <?php echo $index + 1; ?>" data-carousel-slide-to="<?php echo $index; ?>"></button>
        <?php endforeach; ?>
    </div>
    
    <!-- Slider controls -->
    <button type="button" class="absolute top-0 left-0 z-30 flex items-center justify-center h-full px-4 cursor-pointer group focus:outline-none" data-carousel-prev>
        <span class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-white/30 dark:bg-gray-800/30 group-hover:bg-white/50 dark:group-hover:bg-gray-800/60 group-focus:ring-4 group-focus:ring-white dark:group-focus:ring-gray-800/70 group-focus:outline-none transition-all duration-300">
            <svg class="w-6 h-6 text-white dark:text-gray-800" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 1 1 5l4 4"/>
            </svg>
            <span class="sr-only">Previous</span>
        </span>
    </button>
    <button type="button" class="absolute top-0 right-0 z-30 flex items-center justify-center h-full px-4 cursor-pointer group focus:outline-none" data-carousel-next>
        <span class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-white/30 dark:bg-gray-800/30 group-hover:bg-white/50 dark:group-hover:bg-gray-800/60 group-focus:ring-4 group-focus:ring-white dark:group-focus:ring-gray-800/70 group-focus:outline-none transition-all duration-300">
            <svg class="w-6 h-6 text-white dark:text-gray-800" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
            </svg>
            <span class="sr-only">Next</span>
        </span>
    </button>
    
    <!-- Enhanced Hero Content -->
    <div class="absolute inset-0 flex items-center justify-center">
        <div class="text-center px-4 max-w-4xl mx-auto">
            <div class="bg-black/40 backdrop-blur-sm rounded-3xl p-8 md:p-12 shadow-2xl border border-white/20">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-serif font-bold tracking-tight text-white mb-4 animate-fade-in-down">
                    <span class="block">Demo Hotel & Resort</span>
                    <span class="block text-2xl md:text-3xl lg:text-4xl font-light mt-2 text-green-300">Demo City, Demo State</span>
                </h1>
                <p class="mt-6 text-lg md:text-xl text-gray-200 max-w-2xl mx-auto leading-relaxed animate-fade-in-up font-sans">
                    Experience unparalleled luxury and tranquility in the heart of the Himalayas. 
                    Just 5 minutes from M.G. Marg, our retreat offers breathtaking views and exceptional amenities.
                </p>
                <div class="mt-10 flex flex-col sm:flex-row justify-center gap-4 animate-fade-in-up delay-300">
                    <a href="#about" class="inline-flex items-center justify-center bg-gradient-to-r from-accent to-secondary hover:from-secondary hover:to-dark text-white px-8 py-4 rounded-full text-lg font-medium transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl font-sans">
                        <span>Explore Retreat</span>
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                        </svg>
                    </a>
                    <a href="select-room.php" class="inline-flex items-center justify-center bg-transparent border-2 border-white text-white hover:bg-white hover:text-gray-900 px-8 py-4 rounded-full text-lg font-medium transition-all duration-300 transform hover:scale-105 font-sans">
                        <span>Book Now</span>
                    </a>
                </div>
                <div class="mt-8 flex justify-center animate-bounce">
                    <a href="#about" class="text-white/80 hover:text-white">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- About Section -->
<div id="about" class="py-16 bg-gradient-to-b from-white to-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="lg:text-center mb-12">
            <h2 class="text-base text-accent font-semibold tracking-wide uppercase font-sans">About Our Retreat</h2>
            <p class="mt-2 text-3xl font-serif font-bold text-gray-900 sm:text-4xl">Demo Hotel & Resort</p>
            <p class="mt-4 max-w-2xl text-xl text-gray-600 lg:mx-auto font-sans">
                Located just 5 minutes from M.G. Marg, the heart of Demo City
            </p>
        </div>

        <div class="mt-10">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div class="order-2 lg:order-1">
                    <h3 class="text-2xl font-serif font-bold text-gray-900 mb-6">Welcome To Demo Hotel & Resort, Demo City</h3>
                    <?php if ($hotel): ?>
                        <p class="text-gray-700 mb-6 leading-relaxed font-sans text-lg">
                            <?php 
                            $description = htmlspecialchars($hotel['description']);
                            $sentences = explode('.', $description);
                            echo nl2br(substr($description, 0, 300)) . '...';
                            ?>
                        </p>
                        <a href="hotels.php" class="inline-flex items-center text-accent hover:text-secondary font-medium font-sans">
                            Read More
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    <?php else: ?>
                        <p class="text-gray-700 mb-6 leading-relaxed font-sans text-lg">
                            Demo Hotel & Resort is located at 123 Demo Street along the national highway, just five minutes away from M.G. Marg, the heart of Demo City. 
                            Demo City has something for everyone; it has places of interest for the holiday-makers, fine restaurants for the foodies, gorgeous nature for the nature-lovers and fashion streets for the fashionistas.
                        </p>
                        <p class="text-gray-700 leading-relaxed font-sans text-lg">
                            The hotel is designed in new-age architecture with a focus on comfort and contemporary trends of living, all at an affordable tariff.
                        </p>
                    <?php endif; ?>
                </div>
                <div class="order-1 lg:order-2">
                    <div class="relative">
                        <?php
                        // Fetch a random hotel image
                        $randomImage = null;
                        if ($hotel) {
                            $stmt = $pdo->prepare("SELECT image_path FROM hotel_images WHERE hotel_id = ? ORDER BY RAND() LIMIT 1");
                            $stmt->execute([$hotel['id']]);
                            $randomImage = $stmt->fetch(PDO::FETCH_ASSOC);
                        }
                        
                        // If no database image, use a default
                        if (!$randomImage) {
                            $randomImage = ['image_path' => 'https://images.unsplash.com/photo-1611892440504-42a792e24d32?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1200&q=80'];
                        }
                        ?>
                        <img src="<?php echo htmlspecialchars($randomImage['image_path']); ?>" alt="Demo Hotel & Resort" class="rounded-xl w-full h-96 object-cover shadow-lg">
                        <div class="absolute -bottom-6 -left-6 w-32 h-32 bg-accent rounded-lg shadow-xl"></div>
                        <div class="absolute -top-6 -right-6 w-24 h-24 bg-secondary rounded-lg shadow-xl"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Features Section -->
<div class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl font-serif font-bold text-gray-900">Why Choose Demo Hotel & Resort</h2>
            <p class="mt-4 text-lg text-gray-600 max-w-2xl mx-auto font-sans">
                Experience the perfect blend of luxury and comfort
            </p>
        </div>

        <div class="mt-10">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center group">
                    <div class="flex justify-center">
                        <div class="flex items-center justify-center h-20 w-20 rounded-full bg-accent text-white transition-all duration-300 group-hover:bg-secondary group-hover:scale-110">
                            <i class="fas fa-map-marker-alt text-3xl"></i>
                        </div>
                    </div>
                    <h3 class="mt-6 text-xl font-serif font-bold text-gray-900">Prime Location</h3>
                    <p class="mt-4 text-gray-600 font-sans">
                        Just 5 minutes from M.G. Marg, near Deorali Stand along the national highway
                    </p>
                </div>

                <div class="text-center group">
                    <div class="flex justify-center">
                        <div class="flex items-center justify-center h-20 w-20 rounded-full bg-accent text-white transition-all duration-300 group-hover:bg-secondary group-hover:scale-110">
                            <i class="fas fa-bed text-3xl"></i>
                        </div>
                    </div>
                    <h3 class="mt-6 text-xl font-serif font-bold text-gray-900">Comfortable Accommodation</h3>
                    <p class="mt-4 text-gray-600 font-sans">
                        14 Executive rooms with modern amenities and exceptional comfort
                    </p>
                </div>

                <div class="text-center group">
                    <div class="flex justify-center">
                        <div class="flex items-center justify-center h-20 w-20 rounded-full bg-accent text-white transition-all duration-300 group-hover:bg-secondary group-hover:scale-110">
                            <i class="fas fa-utensils text-3xl"></i>
                        </div>
                    </div>
                    <h3 class="mt-6 text-xl font-serif font-bold text-gray-900">Delicious Dining</h3>
                    <p class="mt-4 text-gray-600 font-sans">
                        Multi-cuisine restaurant serving North Indian, Punjabi, Chinese and local dishes
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Gallery Section -->
<div class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl font-serif font-bold text-gray-900">Gallery</h2>
            <p class="mt-4 text-lg text-gray-600 font-sans">
                A glimpse of our beautiful retreat
            </p>
        </div>

        <?php 
        // Fetch more images for gallery
        $galleryImages = [];
        if ($hotel) {
            $stmt = $pdo->prepare("SELECT * FROM hotel_images WHERE hotel_id = ? AND usage_type IN ('carousel', 'both') ORDER BY CASE WHEN carousel_position IS NULL THEN 999 ELSE carousel_position END ASC, created_at ASC");
            $stmt->execute([$hotel['id']]);
            $galleryImages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        // If no carousel images found, get any images
        if (empty($galleryImages) && $hotel) {
            $stmt = $pdo->prepare("SELECT * FROM hotel_images WHERE hotel_id = ? ORDER BY created_at DESC");
            $stmt->execute([$hotel['id']]);
            $galleryImages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        // If no database images, use uploaded images from folder
        if (empty($galleryImages)) {
            $uploadDir = 'uploads/';
            $uploadedImages = glob($uploadDir . "*.jpg");
            if (empty($uploadedImages)) {
                $uploadedImages = glob($uploadDir . "*.jpeg");
            }
            if (empty($uploadedImages)) {
                $uploadedImages = glob($uploadDir . "*.png");
            }
            
            // Create image array from uploaded files
            foreach ($uploadedImages as $imagePath) {
                $galleryImages[] = ['image_path' => $imagePath];
            }
        }
        ?>
        
        <?php if (!empty($galleryImages)): ?>
            <div class="mt-10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php foreach (array_slice($galleryImages, 0, 8) as $image): ?>
                    <div class="overflow-hidden rounded-2xl shadow-lg transform transition duration-500 hover:scale-105 group cursor-pointer" onclick="openLightbox('<?php echo htmlspecialchars($image['image_path']); ?>', '<?php echo isset($image['caption']) ? htmlspecialchars($image['caption']) : 'Hotel Image'; ?>')">
                        <div class="relative">
                            <img src="<?php echo htmlspecialchars($image['image_path']); ?>" alt="<?php echo isset($image['caption']) ? htmlspecialchars($image['caption']) : 'Hotel Image'; ?>" class="w-full h-64 object-cover transition-transform duration-500 group-hover:scale-110">
                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all duration-300 flex items-center justify-center">
                                <i class="fas fa-search-plus text-white text-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></i>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="mt-10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="overflow-hidden rounded-2xl shadow-lg cursor-pointer" onclick="openLightbox('https://images.unsplash.com/photo-1611892440504-42a792e24d32?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80', 'Hotel Room')">
                    <img src="https://images.unsplash.com/photo-1611892440504-42a792e24d32?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80" alt="Hotel Room" class="w-full h-64 object-cover">
                </div>
                <div class="overflow-hidden rounded-2xl shadow-lg cursor-pointer" onclick="openLightbox('https://images.unsplash.com/photo-1566665797739-1674de7a421a?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80', 'Restaurant')">
                    <img src="https://images.unsplash.com/photo-1566665797739-1674de7a421a?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80" alt="Restaurant" class="w-full h-64 object-cover">
                </div>
                <div class="overflow-hidden rounded-2xl shadow-lg cursor-pointer" onclick="openLightbox('https://images.unsplash.com/photo-1561501900-3701fa6a0864?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80', 'Pool')">
                    <img src="https://images.unsplash.com/photo-1561501900-3701fa6a0864?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80" alt="Pool" class="w-full h-64 object-cover">
                </div>
                <div class="overflow-hidden rounded-2xl shadow-lg cursor-pointer" onclick="openLightbox('https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80', 'View')">
                    <img src="https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80" alt="View" class="w-full h-64 object-cover">
                </div>
            </div>
        <?php endif; ?>
        
        <div class="mt-12 text-center">
            <a href="hotels.php" class="inline-block bg-gradient-to-r from-accent to-secondary hover:from-secondary hover:to-dark text-white px-8 py-4 rounded-full text-lg font-medium hover:bg-green-600 transition duration-300 shadow-lg hover:shadow-xl font-sans">
                View More Photos
            </a>
        </div>
    </div>
</div>

<!-- Lightbox Modal -->
<div id="lightbox-modal" class="fixed inset-0 bg-black bg-opacity-90 hidden z-50 flex items-center justify-center p-4">
    <div class="relative max-w-6xl max-h-full">
        <button onclick="closeLightbox()" class="absolute top-4 right-4 text-white text-3xl bg-black bg-opacity-50 rounded-full w-10 h-10 flex items-center justify-center hover:bg-opacity-75 transition-all duration-300 z-10">
            <i class="fas fa-times"></i>
        </button>
        <button id="prev-btn" class="absolute left-4 top-1/2 transform -translate-y-1/2 text-white text-2xl bg-black bg-opacity-50 rounded-full w-10 h-10 flex items-center justify-center hover:bg-opacity-75 transition-all duration-300 z-10">
            <i class="fas fa-chevron-left"></i>
        </button>
        <button id="next-btn" class="absolute right-4 top-1/2 transform -translate-y-1/2 text-white text-2xl bg-black bg-opacity-50 rounded-full w-10 h-10 flex items-center justify-center hover:bg-opacity-75 transition-all duration-300 z-10">
            <i class="fas fa-chevron-right"></i>
        </button>
        <div class="flex flex-col items-center">
            <img id="lightbox-image" src="" alt="" class="max-h-[80vh] max-w-full object-contain">
            <p id="lightbox-caption" class="text-white text-lg mt-4 text-center"></p>
        </div>
    </div>
</div>

<script>
    let currentImageIndex = 0;
    const images = <?php echo json_encode(array_values(array_map(function($image) { 
        return [
            'path' => $image['image_path'] ?? (isset($image['image']) ? $image['image'] : ''), 
            'caption' => $image['caption'] ?? 'Hotel Image'
        ]; 
    }, $galleryImages))); ?>;
    
    function openLightbox(imagePath, caption) {
        document.getElementById('lightbox-image').src = imagePath;
        document.getElementById('lightbox-image').alt = caption;
        document.getElementById('lightbox-caption').textContent = caption;
        
        // Find the current image index
        currentImageIndex = images.findIndex(img => img.path === imagePath);
        
        document.getElementById('lightbox-modal').classList.remove('hidden');
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
    }
    
    function closeLightbox() {
        document.getElementById('lightbox-modal').classList.add('hidden');
        document.body.style.overflow = 'auto'; // Enable background scrolling
    }
    
    function showPrevImage() {
        currentImageIndex = (currentImageIndex - 1 + images.length) % images.length;
        updateLightbox();
    }
    
    function showNextImage() {
        currentImageIndex = (currentImageIndex + 1) % images.length;
        updateLightbox();
    }
    
    function updateLightbox() {
        const currentImage = images[currentImageIndex];
        document.getElementById('lightbox-image').src = currentImage.path;
        document.getElementById('lightbox-image').alt = currentImage.caption;
        document.getElementById('lightbox-caption').textContent = currentImage.caption;
    }
    
    // Close lightbox when clicking outside the image
    document.getElementById('lightbox-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeLightbox();
        }
    });
    
    // Navigation buttons
    document.getElementById('prev-btn').addEventListener('click', function(e) {
        e.stopPropagation();
        showPrevImage();
    });
    
    document.getElementById('next-btn').addEventListener('click', function(e) {
        e.stopPropagation();
        showNextImage();
    });
    
    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (!document.getElementById('lightbox-modal').classList.contains('hidden')) {
            if (e.key === 'Escape') {
                closeLightbox();
            } else if (e.key === 'ArrowLeft') {
                showPrevImage();
            } else if (e.key === 'ArrowRight') {
                showNextImage();
            }
        }
    });
</script>

<?php include 'includes/footer.php'; ?>

<!-- Carousel Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const carousel = document.getElementById('heroCarousel');
    const items = carousel.querySelectorAll('[data-carousel-item]');
    const indicators = document.querySelectorAll('[data-carousel-slide-to]');
    const prevButton = document.querySelector('[data-carousel-prev]');
    const nextButton = document.querySelector('[data-carousel-next]');
    
    let currentIndex = 0;
    const intervalTime = 5000; // Reset to 5 seconds for better fade effect
    let carouselInterval;
    
    // Show slide function with fade effect
    function showSlide(index) {
        // Fade out current slide
        items[currentIndex].classList.remove('opacity-100');
        items[currentIndex].classList.add('opacity-0');
        
        // Update indicators
        indicators[currentIndex].classList.replace('bg-white', 'bg-gray-400');
        
        // Set new index
        currentIndex = index;
        
        // Fade in new slide
        items[currentIndex].classList.remove('opacity-0');
        items[currentIndex].classList.add('opacity-100');
        
        // Update indicators
        indicators[currentIndex].classList.replace('bg-gray-400', 'bg-white');
    }
    
    // Next slide function
    function nextSlide() {
        const nextIndex = (currentIndex + 1) % items.length;
        showSlide(nextIndex);
    }
    
    // Previous slide function
    function prevSlide() {
        const prevIndex = (currentIndex - 1 + items.length) % items.length;
        showSlide(prevIndex);
    }
    
    // Start autoplay
    function startAutoplay() {
        carouselInterval = setInterval(nextSlide, intervalTime);
    }
    
    // Stop autoplay
    function stopAutoplay() {
        clearInterval(carouselInterval);
    }
    
    // Event listeners for buttons
    if (prevButton) {
        prevButton.addEventListener('click', function() {
            prevSlide();
            stopAutoplay();
            startAutoplay();
        });
    }
    
    if (nextButton) {
        nextButton.addEventListener('click', function() {
            nextSlide();
            stopAutoplay();
            startAutoplay();
        });
    }
    
    // Event listeners for indicators
    indicators.forEach((indicator, index) => {
        indicator.addEventListener('click', function() {
            showSlide(index);
            stopAutoplay();
            startAutoplay();
        });
    });
    
    // Start autoplay initially
    startAutoplay();
    
    // Pause autoplay on hover
    if (carousel) {
        carousel.addEventListener('mouseenter', stopAutoplay);
        carousel.addEventListener('mouseleave', startAutoplay);
    }
});

// Add animation classes for hero content
document.addEventListener('DOMContentLoaded', function() {
    // Add animation classes after a short delay
    setTimeout(function() {
        const heroContent = document.querySelector('.animate-fade-in-down');
        if (heroContent) {
            heroContent.classList.add('animate-fade-in-down-visible');
        }
    }, 100);
});
</script>

<style>
@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translate3d(0, -20px, 0);
    }
    to {
        opacity: 1;
        transform: translate3d(0, 0, 0);
    }
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translate3d(0, 20px, 0);
    }
    to {
        opacity: 1;
        transform: translate3d(0, 0, 0);
    }
}

.animate-fade-in-down {
    animation: fadeInDown 1s ease-out forwards;
    opacity: 0;
}

.animate-fade-in-down-visible {
    opacity: 1;
}

.animate-fade-in-up {
    animation: fadeInUp 1s ease-out 0.3s forwards;
    opacity: 0;
}

.delay-300 {
    animation-delay: 0.6s;
}

/* Custom cursor styles */
* {
    cursor: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32"><circle cx="16" cy="16" r="10" fill="white" opacity="0.7"/><circle cx="16" cy="16" r="6" fill="black" opacity="0.7"/></svg>') 16 16, auto;
}

/* Special cursor for links and buttons */
a, button, .cursor-pointer {
    cursor: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32"><circle cx="16" cy="16" r="12" fill="white" opacity="0.8"/><circle cx="16" cy="16" r="8" fill="black" opacity="0.8"/><circle cx="16" cy="16" r="4" fill="green" opacity="0.9"/></svg>') 16 16, pointer;
}
</style>