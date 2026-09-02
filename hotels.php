<?php
session_start();
include 'config/db.php';
include 'includes/header.php';

// Fetch the hotel information
$stmt = $pdo->query("SELECT * FROM hotels LIMIT 1");
$hotel = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$hotel) {
    $hotel = [
        'name' => 'Grand Luxe Hotel',
        'description' => 'Discover an oasis of serenity and unparalleled elegance. Where every detail is crafted for your perfect stay. The retreat is designed in new-age architecture with a focus on comfort and contemporary trends of living.',
    ];
}

// Fetch hotel gallery images (those not strictly bound to rooms, or all images if preferred. Here we fetch general hotel images)
$images = [];
if ($hotel) {
    $stmt = $pdo->prepare("SELECT * FROM hotel_images WHERE hotel_id = ? ORDER BY created_at DESC");
    $stmt->execute([$hotel['id']]);
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch Rooms
$stmt = $pdo->query("SELECT * FROM rooms ORDER BY price ASC");
$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch Room Images (one per room for the card, and all for the modal)
$room_images = [];
foreach ($rooms as $room) {
    $stmt = $pdo->prepare("SELECT image_path FROM hotel_images WHERE room_id = ? ORDER BY created_at DESC");
    $stmt->execute([$room['id']]);
    $room_images[$room['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<link rel="stylesheet" href="css/hotels.css">

<!-- Hero Section for Retreat -->
<section class="relative h-[60vh] flex items-center justify-center overflow-hidden bg-[#030712] pt-24">
    <div class="absolute inset-0 z-0">
        <div class="absolute inset-0 bg-black/60 z-10 backdrop-blur-[2px]"></div>
        <img src="images/hero_retreat.jpg" class="w-full h-full object-cover" alt="Luxury Resort">
    </div>
    <div class="relative z-20 text-center px-4" data-aos="fade-down" data-aos-duration="1500">
        <p class="text-accent uppercase tracking-[0.4em] text-sm font-medium mb-4 font-sans">The Sanctuary</p>
        <h1 class="text-5xl md:text-6xl font-serif text-white mb-6 tracking-wide drop-shadow-lg">
            Our <span class="font-light italic text-accent">Retreat</span>
        </h1>
        <div class="w-16 h-[1px] bg-accent mx-auto"></div>
    </div>
</section>

<!-- Content Section -->
<section class="py-24 bg-[#030712] relative overflow-hidden">
    <div class="absolute top-0 left-0 w-96 h-96 bg-accent/5 rounded-full blur-[120px] pointer-events-none"></div>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="glass-effect-dark p-10 md:p-16 border border-white/5 shadow-2xl mb-24 relative overflow-hidden" data-aos="fade-up">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-accent to-transparent opacity-50"></div>
            
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
                <div class="lg:col-span-5">
                    <h2 class="text-3xl font-serif text-white mb-6">Welcome to Elegance</h2>
                    <p class="text-gray-400 font-sans text-sm leading-relaxed mb-6 font-light">
                        <?php echo nl2br(htmlspecialchars($hotel['description'])); ?>
                    </p>
                </div>
                
                <div class="lg:col-span-7 grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <h3 class="text-lg font-serif text-accent mb-4 border-b border-white/10 pb-2">Accommodation</h3>
                        <p class="text-gray-400 font-sans text-sm leading-relaxed font-light">
                            Experience exceptional comfort in our meticulously designed Executive and Premium suites, offering sweeping views and lavish furnishings.
                        </p>
                        
                        <h3 class="text-lg font-serif text-accent mb-4 border-b border-white/10 pb-2 mt-8">Gastronomy</h3>
                        <p class="text-gray-400 font-sans text-sm leading-relaxed font-light">
                            Indulge in culinary masterpieces at our multi-cuisine restaurant, or unwind with signature cocktails at our exclusive lounge bar.
                        </p>
                    </div>
                    
                    <div>
                        <h3 class="text-lg font-serif text-accent mb-4 border-b border-white/10 pb-2">Curated Amenities</h3>
                        <ul class="space-y-4 text-gray-400 font-sans text-sm font-light">
                            <li class="flex items-start">
                                <i class="fas fa-check text-accent/70 mt-1 mr-3 text-xs"></i>
                                <span>Magnificent suites with high-speed Wi-Fi</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check text-accent/70 mt-1 mr-3 text-xs"></i>
                                <span>Bespoke laundry & valet services</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check text-accent/70 mt-1 mr-3 text-xs"></i>
                                <span>Seamless, private check-in</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check text-accent/70 mt-1 mr-3 text-xs"></i>
                                <span>24/7 dedicated concierge</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check text-accent/70 mt-1 mr-3 text-xs"></i>
                                <span>Artisan coffee & tea selections</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rooms & Suites Section -->
        <div class="text-center mb-16" data-aos="fade-up">
            <p class="text-accent uppercase tracking-[0.3em] text-sm mb-4 font-sans">Accommodations</p>
            <h2 class="text-4xl font-serif text-white">Our <span class="italic font-light text-accent">Rooms & Suites</span></h2>
        </div>

        <?php if (!empty($rooms)): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-24" data-aos="fade-up" data-aos-delay="200">
                <?php foreach ($rooms as $room): ?>
                    <?php 
                    $main_img = !empty($room_images[$room['id']]) ? $room_images[$room['id']][0]['image_path'] : 'images/suite_interior.jpg';
                    $room_data = htmlspecialchars(json_encode([
                        'id' => $room['id'],
                        'name' => $room['name'],
                        'description' => $room['description'],
                        'price' => $room['price'],
                        'capacity' => $room['capacity'],
                        'amenities' => $room['amenities'],
                        'room_overview_options' => $room['room_overview_options'],
                        'images' => $room_images[$room['id']]
                    ]), ENT_QUOTES, 'UTF-8');
                    ?>
                    <div class="glass-effect-dark border border-white/5 group cursor-pointer overflow-hidden transition-all duration-500 hover:border-accent/50 hover:shadow-[0_0_30px_rgba(212,175,55,0.15)]" onclick="openRoomModal(<?php echo $room_data; ?>)">
                        <div class="relative h-64 overflow-hidden">
                            <img src="<?php echo htmlspecialchars($main_img); ?>" alt="<?php echo htmlspecialchars($room['name']); ?>" class="w-full h-full object-cover transition-transform duration-[2000ms] group-hover:scale-110">
                            <div class="absolute inset-0 bg-gradient-to-t from-[#030712] to-transparent opacity-80"></div>
                            <div class="absolute bottom-4 left-4">
                                <span class="text-accent font-sans text-xs tracking-widest uppercase bg-black/50 px-3 py-1 border border-white/10 backdrop-blur-sm">View Details</span>
                            </div>
                        </div>
                        <div class="p-6">
                            <h3 class="text-2xl font-serif text-white mb-2 group-hover:text-accent transition-colors"><?php echo htmlspecialchars($room['name']); ?></h3>
                            <div class="flex justify-between items-end mt-4 pt-4 border-t border-white/10">
                                <div>
                                    <p class="text-gray-400 font-sans text-xs uppercase tracking-widest mb-1">Starting From</p>
                                    <p class="text-white font-sans"><span class="text-xl font-bold">₹<?php echo number_format($room['price']); ?></span> / night</p>
                                </div>
                                <div class="text-gray-400">
                                    <i class="fas fa-arrow-right group-hover:text-accent transition-colors transform group-hover:translate-x-2 duration-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Gallery Section -->
        <div class="text-center mb-16" data-aos="fade-up">
            <p class="text-accent uppercase tracking-[0.3em] text-sm mb-4 font-sans">Visual Journey</p>
            <h2 class="text-4xl font-serif text-white">The <span class="italic font-light text-accent">Gallery</span></h2>
        </div>

        <?php if (!empty($images)): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6" data-aos="fade-up" data-aos-delay="200">
                <?php foreach ($images as $index => $image): ?>
                    <div class="group relative overflow-hidden h-72 cursor-pointer border border-white/5" onclick="openLightbox('<?php echo htmlspecialchars($image['image_path']); ?>', '<?php echo htmlspecialchars($image['caption'] ?? 'Hotel Image'); ?>')">
                        <img src="<?php echo htmlspecialchars($image['image_path']); ?>" alt="<?php echo htmlspecialchars($image['caption']); ?>" class="w-full h-full object-cover transition-transform duration-[2000ms] group-hover:scale-110">
                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-500 flex flex-col items-center justify-center backdrop-blur-sm">
                            <i class="fas fa-search text-accent text-2xl mb-3 transform -translate-y-4 group-hover:translate-y-0 transition-transform duration-500"></i>
                            <?php if (!empty($image['caption'])): ?>
                                <p class="text-white font-sans text-sm tracking-widest uppercase transform translate-y-4 group-hover:translate-y-0 transition-transform duration-500 delay-75"><?php echo htmlspecialchars($image['caption']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-24 glass-effect-dark border border-white/5">
                <p class="text-gray-500 font-sans tracking-widest uppercase text-sm">Visuals coming soon.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Room Details Modal -->
<div id="room-modal" class="fixed inset-0 bg-black/90 backdrop-blur-md hidden z-[250] items-center justify-center p-4 md:p-8 opacity-0 transition-opacity duration-500 flex">
    <div class="relative w-full max-w-5xl max-h-[90vh] bg-[#0a0a0a] border border-white/10 shadow-[0_0_50px_rgba(0,0,0,0.8)] overflow-hidden flex flex-col md:flex-row" onclick="event.stopPropagation();">
        <button onclick="closeRoomModal()" class="absolute top-4 right-4 text-gray-400 hover:text-accent transition-colors duration-300 z-10 bg-black/50 w-10 h-10 rounded-full flex items-center justify-center backdrop-blur-sm">
            <i class="fas fa-times text-xl"></i>
        </button>
        
        <!-- Room Image Slider -->
        <div class="w-full md:w-1/2 h-64 md:h-auto relative bg-black">
            <img id="rm-image" src="" alt="Room Image" class="w-full h-full object-cover">
            
            <button onclick="prevRmImage(event)" class="absolute left-4 top-1/2 transform -translate-y-1/2 text-white/70 hover:text-accent bg-black/30 p-2 rounded-full transition-all">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button onclick="nextRmImage(event)" class="absolute right-4 top-1/2 transform -translate-y-1/2 text-white/70 hover:text-accent bg-black/30 p-2 rounded-full transition-all">
                <i class="fas fa-chevron-right"></i>
            </button>
            <div id="rm-image-counter" class="absolute bottom-4 right-4 bg-black/60 backdrop-blur-md text-white text-xs font-sans px-3 py-1 rounded-full border border-white/10">
                1 / 1
            </div>
        </div>
        
        <!-- Room Details -->
        <div class="w-full md:w-1/2 p-8 md:p-10 overflow-y-auto max-h-[90vh] custom-scrollbar">
            <h2 id="rm-name" class="text-3xl font-serif text-white mb-2">Room Name</h2>
            <div class="flex items-end gap-2 mb-6 pb-6 border-b border-white/10">
                <span class="text-2xl font-bold text-accent font-sans" id="rm-price">₹0</span>
                <span class="text-gray-400 text-sm font-sans mb-1 uppercase tracking-wider">/ night</span>
            </div>
            
            <p id="rm-desc" class="text-gray-400 font-sans text-sm leading-relaxed font-light mb-8"></p>
            
            <div id="rm-overview-options" class="grid grid-cols-2 gap-4 mb-8">
                <div class="flex items-center text-gray-300 font-sans text-sm">
                    <i class="fas fa-user-friends text-accent w-6 text-center mr-2"></i>
                    <span>Up to <span id="rm-capacity"></span> Guests</span>
                </div>
                <div class="flex items-center text-gray-300 font-sans text-sm">
                    <i class="fas fa-bed text-accent w-6 text-center mr-2"></i>
                    <span>Premium Bedding</span>
                </div>
            </div>
            
            <h3 class="text-lg font-serif text-white mb-4">Room Amenities</h3>
            <div id="rm-amenities" class="grid grid-cols-2 gap-3 mb-10">
                <!-- Amenities injected here -->
            </div>
            
            <a id="rm-book-btn" href="book-now.php" class="inline-block w-full text-center bg-accent hover:bg-accent-light text-[#030712] px-8 py-4 font-bold tracking-[0.2em] uppercase transition-all duration-300 shadow-[0_0_15px_rgba(212,175,55,0.2)] font-sans text-sm">
                Reserve This Room
            </a>
        </div>
    </div>
</div>

<!-- Lightbox Modal (Glassmorphism) -->
<div id="lightbox-modal" class="fixed inset-0 bg-black/90 backdrop-blur-md hidden z-[200] flex items-center justify-center p-4 opacity-0 transition-opacity duration-500" onclick="closeLightbox()">
    <div class="relative w-full max-w-6xl max-h-full flex flex-col items-center" onclick="event.stopPropagation();">
        <button onclick="closeLightbox()" class="absolute -top-12 right-0 text-white hover:text-accent transition-colors duration-300 z-10">
            <i class="fas fa-times text-2xl"></i>
        </button>
        <button id="prev-btn" class="absolute left-4 top-1/2 transform -translate-y-1/2 text-white/50 hover:text-accent transition-colors duration-300 z-10">
            <i class="fas fa-chevron-left text-4xl"></i>
        </button>
        <button id="next-btn" class="absolute right-4 top-1/2 transform -translate-y-1/2 text-white/50 hover:text-accent transition-colors duration-300 z-10">
            <i class="fas fa-chevron-right text-4xl"></i>
        </button>
        
        <div class="relative shadow-[0_0_50px_rgba(0,0,0,0.8)] border border-white/10 bg-black">
            <img id="lightbox-image" src="" alt="" class="max-h-[75vh] max-w-full object-contain">
        </div>
        <p id="lightbox-caption" class="text-gray-300 text-sm tracking-[0.2em] uppercase font-sans mt-8 text-center"></p>
    </div>
</div>

<style>
/* Custom Scrollbar for Modal */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #0a0a0a;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #2a2a2a;
    border-radius: 3px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #d4af37;
}
</style>

<script>
    // --- Room Modal Logic ---
    let currentRoomImages = [];
    let currentRmImageIndex = 0;
    
    function openRoomModal(roomData) {
        document.getElementById('rm-name').textContent = roomData.name;
        document.getElementById('rm-price').textContent = '₹' + parseInt(roomData.price).toLocaleString();
        document.getElementById('rm-desc').innerHTML = roomData.description.replace(/\n/g, '<br>');
        document.getElementById('rm-capacity').textContent = roomData.capacity;
        document.getElementById('rm-book-btn').href = 'book-now.php?room=' + encodeURIComponent(roomData.name);
        
        // Amenities
        const amenitiesContainer = document.getElementById('rm-amenities');
        amenitiesContainer.innerHTML = '';
        let amenities = {};
        try {
            if(roomData.amenities) amenities = JSON.parse(roomData.amenities) || {};
        } catch(e) {}
        
        let hasAmenities = false;
        if (typeof amenities === 'object' && amenities !== null) {
            for (const [key, value] of Object.entries(amenities)) {
                if (value) {
                    hasAmenities = true;
                    amenitiesContainer.innerHTML += `
                        <div class="flex items-center text-gray-400 font-sans text-xs capitalize">
                            <i class="fas fa-check text-accent/50 w-4 mr-2 text-[10px]"></i>
                            <span>${key.replace(/_/g, ' ')}</span>
                        </div>`;
                }
            }
        }
        
        if (!hasAmenities) {
            amenitiesContainer.innerHTML = '<p class="text-gray-500 text-xs italic">No specific amenities listed.</p>';
        }
        
        // Custom Room Overview Options
        const overviewContainer = document.getElementById('rm-overview-options');
        if (overviewContainer) {
            overviewContainer.innerHTML = '';
            let overviewOptions = [];
            try {
                if(roomData.room_overview_options) overviewOptions = JSON.parse(roomData.room_overview_options) || [];
            } catch(e) {}
            
            if (overviewOptions.length > 0) {
                overviewOptions.forEach(opt => {
                    overviewContainer.innerHTML += `
                        <div class="flex items-center text-gray-300 font-sans text-sm">
                            <i class="fas ${opt.icon} text-accent w-6 mr-2"></i>
                            <span>${opt.title}</span>
                        </div>`;
                });
            } else {
                // Default options
                overviewContainer.innerHTML = `
                    <div class="flex items-center text-gray-300 font-sans text-sm">
                        <i class="fas fa-user-friends text-accent w-6 mr-2"></i>
                        <span>Up to ${roomData.capacity} Guests</span>
                    </div>
                    <div class="flex items-center text-gray-300 font-sans text-sm">
                        <i class="fas fa-bed text-accent w-6 mr-2"></i>
                        <span>Premium Bedding</span>
                    </div>
                `;
            }
        }
        
        // Images
        currentRoomImages = roomData.images && roomData.images.length > 0 ? roomData.images : [{image_path: 'images/suite_interior.jpg'}];
        currentRmImageIndex = 0;
        updateRmImageDisplay();
        
        const modal = document.getElementById('room-modal');
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
        }, 10);
        document.body.style.overflow = 'hidden';
    }
    
    function closeRoomModal() {
        const modal = document.getElementById('room-modal');
        modal.classList.add('opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 500);
        document.body.style.overflow = 'auto';
    }
    
    function prevRmImage(e) {
        e.stopPropagation();
        if (currentRoomImages.length <= 1) return;
        currentRmImageIndex = (currentRmImageIndex - 1 + currentRoomImages.length) % currentRoomImages.length;
        updateRmImageDisplay();
    }
    
    function nextRmImage(e) {
        e.stopPropagation();
        if (currentRoomImages.length <= 1) return;
        currentRmImageIndex = (currentRmImageIndex + 1) % currentRoomImages.length;
        updateRmImageDisplay();
    }
    
    function updateRmImageDisplay() {
        const imgEl = document.getElementById('rm-image');
        imgEl.style.opacity = '0';
        setTimeout(() => {
            imgEl.src = currentRoomImages[currentRmImageIndex].image_path;
            document.getElementById('rm-image-counter').textContent = (currentRmImageIndex + 1) + ' / ' + currentRoomImages.length;
            imgEl.style.opacity = '1';
        }, 200);
    }
    document.getElementById('rm-image').style.transition = 'opacity 0.2s ease-in-out';
    
    document.getElementById('room-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeRoomModal();
        }
    });

    // --- Gallery Lightbox Logic ---
    let currentImageIndex = 0;
    const images = <?php echo json_encode(array_values(array_map(function($image) { return ['path' => $image['image_path'], 'caption' => $image['caption'] ?? 'Hotel Image']; }, $images))); ?>;
    
    function openLightbox(imagePath, caption) {
        document.getElementById('lightbox-image').src = imagePath;
        document.getElementById('lightbox-image').alt = caption;
        document.getElementById('lightbox-caption').textContent = caption;
        
        currentImageIndex = images.findIndex(img => img.path === imagePath);
        
        const modal = document.getElementById('lightbox-modal');
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
        }, 10);
        document.body.style.overflow = 'hidden';
    }
    
    function closeLightbox() {
        const modal = document.getElementById('lightbox-modal');
        modal.classList.add('opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 500);
        document.body.style.overflow = 'auto';
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
        const imgEl = document.getElementById('lightbox-image');
        imgEl.style.opacity = '0';
        setTimeout(() => {
            imgEl.src = currentImage.path;
            imgEl.alt = currentImage.caption;
            document.getElementById('lightbox-caption').textContent = currentImage.caption;
            imgEl.style.opacity = '1';
        }, 200);
    }
    
    document.getElementById('lightbox-image').style.transition = 'opacity 0.2s ease-in-out';
    
    document.getElementById('prev-btn').addEventListener('click', function(e) {
        e.stopPropagation();
        showPrevImage();
    });
    
    document.getElementById('next-btn').addEventListener('click', function(e) {
        e.stopPropagation();
        showNextImage();
    });
    
    document.addEventListener('keydown', function(e) {
        if (!document.getElementById('lightbox-modal').classList.contains('hidden')) {
            if (e.key === 'Escape') closeLightbox();
            else if (e.key === 'ArrowLeft') showPrevImage();
            else if (e.key === 'ArrowRight') showNextImage();
        }
        if (!document.getElementById('room-modal').classList.contains('hidden')) {
            if (e.key === 'Escape') closeRoomModal();
            else if (e.key === 'ArrowLeft') prevRmImage({stopPropagation: ()=>{}});
            else if (e.key === 'ArrowRight') nextRmImage({stopPropagation: ()=>{}});
        }
    });
</script>

<?php include 'includes/footer.php'; ?>