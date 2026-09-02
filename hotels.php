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

// Fetch hotel images
$images = [];
if ($hotel) {
    $stmt = $pdo->prepare("SELECT * FROM hotel_images WHERE hotel_id = ? ORDER BY created_at DESC");
    $stmt->execute([$hotel['id']]);
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

<!-- Lightbox Modal (Glassmorphism) -->
<div id="lightbox-modal" class="fixed inset-0 bg-black/90 backdrop-blur-md hidden z-[200] flex items-center justify-center p-4 opacity-0 transition-opacity duration-500">
    <div class="relative w-full max-w-6xl max-h-full flex flex-col items-center">
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

<script>
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
    
    document.getElementById('lightbox-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeLightbox();
        }
    });
    
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
    });
</script>

<?php include 'includes/footer.php'; ?>