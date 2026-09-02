<?php
session_start();
include 'config/db.php';
include 'includes/header.php';

// Fetch the hotel information
$stmt = $pdo->query("SELECT * FROM hotels LIMIT 1");
$hotel = $stmt->fetch(PDO::FETCH_ASSOC);

// If no hotel data in database, use the real information you provided
if (!$hotel) {
    $hotel = [
        'name' => 'Demo Hotel & Resort',
        'description' => 'Demo Hotel & Resort is located at 123 Demo Street along the national highway, just five minutes away from M.G. Marg, the heart of Demo City. Demo City has something for everyone; it has places of interest for the holiday-makers, fine restaurants for the foodies, gorgeous nature for the nature-lovers and fashion streets for the fashionistas.

The retreat is designed in new-age architecture with a focus on comfort and contemporary trends of living, all at an affordable tariff. It offers exceptional facilities and brilliant services with modern amenities.

Our retreats in Demo City are located around these cultural and commercial places, making travelling within the city easy for visitors. Our retreats ensure an enjoyable stay for its patrons by making available rooms with the best amenities and a hospitable staff. The retreat is not only offer exceptional comfort and convenience, but also brilliant retreat deals that make accommodation easy and affordable.

Our retreat in Demo City that you must choose for a successful Demo City trip.',
        'address' => '123 Demo Street, Demo City, Demo State',
        'phone' => '+12345678900',
        'email' => 'reservations@demohotel.com'
    ];
}

// Fetch hotel images
$images = [];
if ($hotel) {
    $stmt = $pdo->prepare("SELECT * FROM hotel_images WHERE hotel_id = ? ORDER BY created_at DESC");
    $stmt->execute([$hotel['id']]);
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="text-center mb-12">
        <h1 class="text-3xl font-serif font-bold text-gray-900">Demo Hotel & Resort</h1>
        <p class="mt-4 text-lg text-gray-600">Experience luxury and tranquility in the heart of Demo City</p>
    </div>

    <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-12">
        <div class="p-6 md:p-8">
            <h2 class="text-2xl font-serif font-bold text-gray-900 mb-6">Welcome To Demo Hotel & Resort, Demo City</h2>
            <div class="prose prose-lg max-w-none">
                <p class="text-gray-700 mb-6 leading-relaxed font-sans">
                    <?php echo nl2br(htmlspecialchars($hotel['description'])); ?>
                </p>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 mt-10">
                <div>
                    <h3 class="text-xl font-serif font-bold text-gray-900 mb-5 pb-2 border-b border-gray-200">Accommodation</h3>
                    <p class="text-gray-700 mb-6 font-sans">
                        The retreat is not only offer exceptional comfort and convenience, but also brilliant retreat deals. It's 14 rooms Executive and Premium.
                    </p>
                    
                    <h3 class="text-xl font-serif font-bold text-gray-900 mb-5 pb-2 border-b border-gray-200 mt-8">Food And Dining</h3>
                    <p class="text-gray-700 font-sans">
                        Relish your evenings at our well-stocked bar, take gastronomic delights at our multi cuisine restaurant serving North Indian/South Indian/Chinese and local dishes.
                    </p>
                </div>
                
                <div>
                    <h3 class="text-xl font-serif font-bold text-gray-900 mb-5 pb-2 border-b border-gray-200">Retreat Amenities</h3>
                    <ul class="space-y-3 mb-8">
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-accent mt-1 mr-3"></i>
                            <span class="text-gray-700 font-sans">Maginificent Executive rooms with TV in all rooms and Wi-Fi Connection</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-accent mt-1 mr-3"></i>
                            <span class="text-gray-700 font-sans">Laundry and Other services</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-accent mt-1 mr-3"></i>
                            <span class="text-gray-700 font-sans">Seamless Check-in</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-accent mt-1 mr-3"></i>
                            <span class="text-gray-700 font-sans">Concierge Service</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-accent mt-1 mr-3"></i>
                            <span class="text-gray-700 font-sans">Tea coffee maker in all rooms</span>
                        </li>
                    </ul>
                    
                    <h3 class="text-xl font-serif font-bold text-gray-900 mb-5 pb-2 border-b border-gray-200 mt-8">Contact Information</h3>
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <i class="fas fa-map-marker-alt text-accent mt-1 mr-3"></i>
                            <span class="text-gray-700 font-sans"><?php echo htmlspecialchars($hotel['address']); ?></span>
                        </div>
                        <div class="flex items-start">
                            <i class="fas fa-phone text-accent mt-1 mr-3"></i>
                            <span class="text-gray-700 font-sans"><?php echo htmlspecialchars($hotel['phone']); ?></span>
                        </div>
                        <div class="flex items-start">
                            <i class="fas fa-envelope text-accent mt-1 mr-3"></i>
                            <span class="text-gray-700 font-sans"><?php echo htmlspecialchars($hotel['email']); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Services and Amenities Section -->
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-12">
        <div class="p-6 md:p-8">
            <h2 class="text-2xl font-serif font-bold text-gray-900 mb-6">Services & Amenities</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <h3 class="text-xl font-serif font-bold text-gray-900 mb-4">Room Features</h3>
                    <ul class="space-y-3">
                        <li class="flex items-start">
                            <i class="fas fa-bed text-accent mt-1 mr-3"></i>
                            <span class="text-gray-700 font-sans">Executive Room Category</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-mountain text-accent mt-1 mr-3"></i>
                            <span class="text-gray-700 font-sans">A Breath-taking valley view of Himalayas</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-utensils text-accent mt-1 mr-3"></i>
                            <span class="text-gray-700 font-sans">Buffet Breakfast Available</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-tv text-accent mt-1 mr-3"></i>
                            <span class="text-gray-700 font-sans">All Rooms with LED Smart TV</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-coffee text-accent mt-1 mr-3"></i>
                            <span class="text-gray-700 font-sans">Tea Coffee Maker in All Rooms</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-wifi text-accent mt-1 mr-3"></i>
                            <span class="text-gray-700 font-sans">24 Hours Internet Access via WiFi Connection</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-concierge-bell text-accent mt-1 mr-3"></i>
                            <span class="text-gray-700 font-sans">Daily Housekeeping</span>
                        </li>
                    
                        <li class="flex items-start">
                            <i class="fas fa-couch text-accent mt-1 mr-3"></i>
                            <span class="text-gray-700 font-sans">Sofa Cum Beds Available</span>
                        </li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-xl font-serif font-bold text-gray-900 mb-4">Retreat Services</h3>
                    <ul class="space-y-3 mb-6">
                        <li class="flex items-start">
                            <i class="fas fa-utensils text-accent mt-1 mr-3"></i>
                            <span class="text-gray-700 font-sans">Multi-Cuisine Restaurant</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-coffee text-accent mt-1 mr-3"></i>
                            <span class="text-gray-700 font-sans">Open Air Cafe</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-tshirt text-accent mt-1 mr-3"></i>
                            <span class="text-gray-700 font-sans">Dry Cleaning Services</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-suitcase text-accent mt-1 mr-3"></i>
                            <span class="text-gray-700 font-sans">Luggage Hold</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-couch text-accent mt-1 mr-3"></i>
                            <span class="text-gray-700 font-sans">Guest Waiting Area</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-car text-accent mt-1 mr-3"></i>
                            <span class="text-gray-700 font-sans">Parking Space</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-concierge-bell text-accent mt-1 mr-3"></i>
                            <span class="text-gray-700 font-sans">Concierge Service</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-calendar-alt text-accent mt-1 mr-3"></i>
                            <span class="text-gray-700 font-sans">Event Planning Services</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-car text-accent mt-1 mr-3"></i>
                            <span class="text-gray-700 font-sans">Car Rental Desk</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-ticket-alt text-accent mt-1 mr-3"></i>
                            <span class="text-gray-700 font-sans">Tour / Ticket Assistance</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-shuttle-van text-accent mt-1 mr-3"></i>
                            <span class="text-gray-700 font-sans">Pick-up & Drop Facility</span>
                        </li>
                    </ul>
                    
                    <h3 class="text-xl font-serif font-bold text-gray-900 mb-4">Special Services</h3>
                    <ul class="space-y-3">
                        <li class="flex items-start">
                            <i class="fas fa-wind text-accent mt-1 mr-3"></i>
                            <span class="text-gray-700 font-sans">Hair Dryer</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-tshirt text-accent mt-1 mr-3"></i>
                            <span class="text-gray-700 font-sans">On Call Iron Service</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-fire text-accent mt-1 mr-3"></i>
                            <span class="text-gray-700 font-sans">On Call Room Heater Service (Chargeable)</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-sign-in-alt text-accent mt-1 mr-3"></i>
                            <span class="text-gray-700 font-sans">Seamless Check-in</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Local Attractions Section -->
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-12">
        <div class="p-6 md:p-8">
            <h2 class="text-2xl font-serif font-bold text-gray-900 mb-6">Local Attractions</h2>
            <p class="text-gray-700 mb-8 font-sans">
                Our prime location offers easy access to many of Demo City's most famous attractions. Here are some key places of interest nearby:
            </p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <h3 class="text-xl font-serif font-bold text-gray-900 mb-4">Nearby Attractions (0.5 km)</h3>
                    <ul class="space-y-4">
                        <li class="flex items-start">
                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-accent flex items-center justify-center">
                                <i class="fas fa-mountain text-white"></i>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-medium text-gray-900 font-sans">Rope Way</h4>
                                <p class="text-gray-600 font-sans">Experience breathtaking views of the Himalayas</p>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-accent flex items-center justify-center">
                                <i class="fas fa-book text-white"></i>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-medium text-gray-900 font-sans">Tibetology</h4>
                                <p class="text-gray-600 font-sans">Explore Tibetan culture and artifacts</p>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-accent flex items-center justify-center">
                                <i class="fas fa-place-of-worship text-white"></i>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-medium text-gray-900 font-sans">Do Drul Chorten Monastery</h4>
                                <p class="text-gray-600 font-sans">Visit this important Buddhist monastery</p>
                            </div>
                        </li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-xl font-serif font-bold text-gray-900 mb-4">Other Attractions</h3>
                    <ul class="space-y-4">
                        <li class="flex items-start">
                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-accent flex items-center justify-center">
                                <i class="fas fa-road text-white"></i>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-medium text-gray-900 font-sans">M.G. Marg</h4>
                                <p class="text-gray-600 font-sans">1.5 km - The heart of Demo City with shopping and dining</p>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-accent flex items-center justify-center">
                                <i class="fas fa-water text-white"></i>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-medium text-gray-900 font-sans">Ban Jhakri Waterfall</h4>
                                <p class="text-gray-600 font-sans">3 km - Beautiful waterfall with cultural significance</p>
                            </div>
                        </li>
                </div>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="p-6 md:p-8">
            <h2 class="text-2xl font-serif font-bold text-gray-900 mb-6">Gallery</h2>
            
            <?php if (!empty($images)): ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    <?php foreach ($images as $image): ?>
                        <div class="overflow-hidden rounded-xl shadow-lg transform transition duration-500 hover:scale-105 group cursor-pointer" onclick="openLightbox('<?php echo htmlspecialchars($image['image_path']); ?>', '<?php echo htmlspecialchars($image['caption'] ?? 'Hotel Image'); ?>')">
                            <div class="relative">
                                <img src="<?php echo htmlspecialchars($image['image_path']); ?>" alt="<?php echo htmlspecialchars($image['caption']); ?>" class="w-full h-64 object-cover transition-transform duration-500 group-hover:scale-110">
                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all duration-300 flex items-center justify-center">
                                    <i class="fas fa-search-plus text-white text-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></i>
                                </div>
                            </div>
                            <?php if (!empty($image['caption'])): ?>
                                <div class="p-3 bg-gray-50 text-center">
                                    <p class="text-sm text-gray-600 font-sans"><?php echo htmlspecialchars($image['caption']); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-12">
                    <p class="text-gray-500 font-sans">No images available at the moment.</p>
                </div>
            <?php endif; ?>
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
    const images = <?php echo json_encode(array_values(array_map(function($image) { return ['path' => $image['image_path'], 'caption' => $image['caption'] ?? 'Hotel Image']; }, $images))); ?>;
    
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