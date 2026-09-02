<?php
session_start();
include 'config/db.php';
include 'includes/header.php';

// Fetch the hotel information
$stmt = $pdo->query("SELECT * FROM hotels LIMIT 1");
$hotel = $stmt->fetch(PDO::FETCH_ASSOC);

// If no hotel found, redirect to hotels page
if (!$hotel) {
    header('Location: hotels.php');
    exit;
}

// Fetch hotel images
$stmt = $pdo->prepare("SELECT * FROM hotel_images WHERE hotel_id = ? ORDER BY created_at DESC");
$stmt->execute([$hotel['id']]);
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="p-6">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Demo Hotel & Resort Demo City</h1>
            <p class="text-gray-600 mb-8">Experience luxury and tranquility in the heart of Sikkim</p>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">About Our Retreat</h2>
                    <p class="text-gray-600 mb-6"><?php echo nl2br(htmlspecialchars($hotel['description'])); ?></p>
                    
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Location & Contact</h2>
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <i class="fas fa-map-marker-alt text-green-500 mt-1 mr-3"></i>
                            <span><?php echo htmlspecialchars($hotel['address']); ?></span>
                        </div>
                        <div class="flex items-start">
                            <i class="fas fa-phone text-green-500 mt-1 mr-3"></i>
                            <span><?php echo htmlspecialchars($hotel['phone']); ?></span>
                        </div>
                        <div class="flex items-start">
                            <i class="fas fa-envelope text-green-500 mt-1 mr-3"></i>
                            <span><?php echo htmlspecialchars($hotel['email']); ?></span>
                        </div>
                    </div>
                </div>
                
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Gallery</h2>
                    <?php if (!empty($images)): ?>
                        <div class="grid grid-cols-2 gap-4">
                            <?php foreach (array_slice($images, 0, 4) as $image): ?>
                                <div class="overflow-hidden rounded-lg shadow-md transform transition duration-500 hover:scale-105">
                                    <img src="<?php echo htmlspecialchars($image['image_path']); ?>" alt="<?php echo htmlspecialchars($image['caption']); ?>" class="w-full h-40 object-cover">
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if (count($images) > 4): ?>
                            <div class="mt-4 text-center">
                                <a href="hotels.php#gallery" class="text-green-500 hover:text-green-600 font-medium">View All Images</a>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="overflow-hidden rounded-lg shadow-md">
                                <img src="https://images.unsplash.com/photo-1611892440504-42a792e24d32?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80" alt="Hotel Room" class="w-full h-40 object-cover">
                            </div>
                            <div class="overflow-hidden rounded-lg shadow-md">
                                <img src="https://images.unsplash.com/photo-1566665797739-1674de7a421a?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80" alt="Restaurant" class="w-full h-40 object-cover">
                            </div>
                            <div class="overflow-hidden rounded-lg shadow-md">
                                <img src="https://images.unsplash.com/photo-1561501900-3701fa6a0864?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80" alt="Pool" class="w-full h-40 object-cover">
                            </div>
                            <div class="overflow-hidden rounded-lg shadow-md">
                                <img src="https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80" alt="View" class="w-full h-40 object-cover">
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="bg-gray-50 rounded-lg p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Facilities & Amenities</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                        <span>Luxuriously appointed rooms with mountain views</span>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                        <span>Spa and wellness center featuring traditional treatments</span>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                        <span>Multi-cuisine restaurant with local and international dishes</span>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                        <span>Infinity pool overlooking the Himalayan range</span>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                        <span>Conference and event facilities</span>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                        <span>Guided tours and outdoor activities</span>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                        <span>24-hour room service</span>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                        <span>Complimentary Wi-Fi throughout the property</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>