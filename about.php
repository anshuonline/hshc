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
        'description' => 'Demo Hotel & Resort is located at 123 Demo Street along the national highway, just five minutes away from M.G. Marg, the heart of Demo City. Our strategic location makes travelling within the city easy for visitors.
Demo City has something for everyone; it has places of interest for the holiday-makers, fine restaurants for the foodies, gorgeous nature for the nature-lovers and fashion streets for the fashionistas.

The retreat is designed in new-age architecture with a focus on comfort and contemporary trends of living, all at an affordable tariff. It offers world-class facilities and brilliant services with modern amenities.

Our retreats in Demo City are located around these cultural and commercial places, making travelling within the city easy for visitors. Our retreats ensure an enjoyable stay for its patrons by making available rooms with the best amenities and a hospitable staff. The retreat is not only offer exceptional comfort and convenience, but also brilliant retreat deals that make accommodation easy and affordable.

Our retreat in Demo City that you must choose for a successful Demo City trip.',
        'address' => '123 Demo Street, Demo City, Demo State',
        'phone' => '+12345678900',
        'email' => 'reservations@demohotel.com'
    ];
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="text-center mb-12">
        <h1 class="text-3xl font-serif font-bold text-gray-900">About Demo Hotel & Resort</h1>
        <p class="mt-4 text-lg text-gray-600">Discover our story and what makes us unique</p>
    </div>

    <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-12">
        <div class="p-6 md:p-8">
            <div class="prose prose-lg max-w-none">
                <h2 class="text-2xl font-serif font-bold text-gray-900">Welcome To Demo Hotel & Resort, Demo City</h2>
                <p class="text-gray-700 mb-6 leading-relaxed font-sans">
                    <?php echo nl2br(htmlspecialchars($hotel['description'])); ?>
                </p>
                
                <h2 class="text-2xl font-serif font-bold text-gray-900 mt-10">Our Location</h2>
                <p class="text-gray-700 mb-6 font-sans">
                    Demo Hotel & Resort is located at 123 Demo Street along the national highway, just five minutes away from M.G. Marg, the heart of Demo City. Our strategic location makes travelling within the city easy for visitors.
                </p>
                
                <h2 class="text-2xl font-serif font-bold text-gray-900 mt-10">Local Attractions</h2>
                <p class="text-gray-700 mb-6 font-sans">
                    Our prime location offers easy access to many of Demo City's most famous attractions. Here are some key places of interest nearby:
                </p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
                    <div class="bg-gradient-to-br from-green-50 to-green-100 p-6 rounded-xl border border-green-200">
                        <h3 class="text-xl font-serif font-bold text-gray-900 mb-4">Nearby Attractions (0.5 km)</h3>
                        <ul class="space-y-3">
                            <li class="flex items-start">
                                <i class="fas fa-mountain text-accent mt-1 mr-3"></i>
                                <span class="text-gray-700 font-sans">Rope Way</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-book text-accent mt-1 mr-3"></i>
                                <span class="text-gray-700 font-sans">Tibetology</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-place-of-worship text-accent mt-1 mr-3"></i>
                                <span class="text-gray-700 font-sans">Do Drul Chorten Monastery</span>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="bg-gradient-to-br from-green-50 to-green-100 p-6 rounded-xl border border-green-200">
                        <h3 class="text-xl font-serif font-bold text-gray-900 mb-4">Other Attractions</h3>
                        <ul class="space-y-3">
                            <li class="flex items-start">
                                <i class="fas fa-road text-accent mt-1 mr-3"></i>
                                <span class="text-gray-700 font-sans">M.G. Marg - 1.5 km</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-water text-accent mt-1 mr-3"></i>
                                <span class="text-gray-700 font-sans">Ban Jhakri Waterfall - 3 km</span>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 my-8">
                    <div class="bg-gradient-to-br from-green-50 to-green-100 p-6 rounded-xl border border-green-200">
                        <h3 class="text-xl font-serif font-bold text-gray-900 mb-4">Accommodation</h3>
                        <p class="text-gray-700 mb-4 font-sans">
                            The retreat is not only offer exceptional comfort and convenience, but also brilliant retreat deals that make accommodation easy and affordable. We have 18 Executive rooms all designed with a focus on comfort and contemporary trends of living.
                        </p>
                    </div>
                    
                    <div class="bg-gradient-to-br from-green-50 to-green-100 p-6 rounded-xl border border-green-200">
                        <h3 class="text-xl font-serif font-bold text-gray-900 mb-4">Food And Dining</h3>
                        <p class="text-gray-700 font-sans">
                            Relish your evenings at our well-stocked bar, take gastronomic delights at our multi cuisine restaurant serving North Indian/Punjabi/Chinese and local dishes. Our restaurant offers a delightful culinary experience for all our guests.
                        </p>
                    </div>
                </div>
                
                <h2 class="text-2xl font-serif font-bold text-gray-900 mt-10">Amenities</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6 mt-6">
                    <div class="bg-gray-50 p-5 rounded-lg border border-gray-200">
                        <h3 class="text-lg font-serif font-semibold text-gray-900 mb-3">Room Features</h3>
                        <ul class="space-y-2">
                            <li class="flex items-start">
                                <i class="fas fa-bed text-accent mt-1 mr-2"></i>
                                <span class="text-gray-700 font-sans">Executive Room Category</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-mountain text-accent mt-1 mr-2"></i>
                                <span class="text-gray-700 font-sans">A Breath-taking View of Darjeeling Himalayan Mountain</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-utensils text-accent mt-1 mr-2"></i>
                                <span class="text-gray-700 font-sans">Buffet Breakfast Available</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-tv text-accent mt-1 mr-2"></i>
                                <span class="text-gray-700 font-sans">All Rooms with LCD Television</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-coffee text-accent mt-1 mr-2"></i>
                                <span class="text-gray-700 font-sans">Tea Coffee Maker in All Rooms</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-wifi text-accent mt-1 mr-2"></i>
                                <span class="text-gray-700 font-sans">24 Hours Internet Access via WiFi Connection</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-concierge-bell text-accent mt-1 mr-2"></i>
                                <span class="text-gray-700 font-sans">Daily Housekeeping</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-bed text-accent mt-1 mr-2"></i>
                                <span class="text-gray-700 font-sans">Rollaway Beds Available</span>
                            </li>
                        </ul>
                    </div>
                    <div class="bg-gray-50 p-5 rounded-lg border border-gray-200">
                        <h3 class="text-lg font-serif font-semibold text-gray-900 mb-3">Services</h3>
                        <ul class="space-y-2 mb-4">
                            <li class="flex items-start">
                                <i class="fas fa-utensils text-accent mt-1 mr-2"></i>
                                <span class="text-gray-700 font-sans">Multi-Cuisine Restaurant</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-coffee text-accent mt-1 mr-2"></i>
                                <span class="text-gray-700 font-sans">Open Air Cafe</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-tshirt text-accent mt-1 mr-2"></i>
                                <span class="text-gray-700 font-sans">Dry Cleaning Services</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-suitcase text-accent mt-1 mr-2"></i>
                                <span class="text-gray-700 font-sans">Luggage Hold</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-couch text-accent mt-1 mr-2"></i>
                                <span class="text-gray-700 font-sans">Guest Waiting Area</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-car text-accent mt-1 mr-2"></i>
                                <span class="text-gray-700 font-sans">Parking Space</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-concierge-bell text-accent mt-1 mr-2"></i>
                                <span class="text-gray-700 font-sans">Concierge Service</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-calendar-alt text-accent mt-1 mr-2"></i>
                                <span class="text-gray-700 font-sans">Event Planning Services</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-car text-accent mt-1 mr-2"></i>
                                <span class="text-gray-700 font-sans">Car Rental Desk</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-ticket-alt text-accent mt-1 mr-2"></i>
                                <span class="text-gray-700 font-sans">Tour / Ticket Assistance</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-shuttle-van text-accent mt-1 mr-2"></i>
                                <span class="text-gray-700 font-sans">Pick-up & Drop Facility</span>
                            </li>
                        </ul>
                        
                        <h3 class="text-lg font-serif font-semibold text-gray-900 mb-3">Special Services</h3>
                        <ul class="space-y-2">
                            <li class="flex items-start">
                                <i class="fas fa-hair-dryer text-accent mt-1 mr-2"></i>
                                <span class="text-gray-700 font-sans">Hair Dryer</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-tshirt text-accent mt-1 mr-2"></i>
                                <span class="text-gray-700 font-sans">On Call Iron Service</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-fire text-accent mt-1 mr-2"></i>
                                <span class="text-gray-700 font-sans">On Call Room Heater Service (Chargeable)</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-sign-in-alt text-accent mt-1 mr-2"></i>
                                <span class="text-gray-700 font-sans">Express Check-In</span>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <h2 class="text-2xl font-serif font-bold text-gray-900 mt-10">Why Choose Us</h2>
                <p class="text-gray-700 mb-4 font-sans">
                    Our retreats ensure an enjoyable stay for its patrons by making available rooms with the best amenities and a hospitable staff. We offer:
                </p>
                <ul class="list-disc pl-5 space-y-2 text-gray-700 mb-6">
                    <li class="font-sans">World-class facilities and brilliant services with modern amenities</li>
                    <li class="font-sans">New-age architecture with a focus on comfort and contemporary trends</li>
                    <li class="font-sans">Affordable tariff without compromising on quality</li>
                    <li class="font-sans">Prime location just 5 minutes from M.G. Marg</li>
                    <li class="font-sans">Hospitable staff dedicated to making your stay memorable</li>
                </ul>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="p-6 md:p-8">
            <h2 class="text-2xl font-serif font-bold text-gray-900 mb-8">Contact Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="flex items-start p-5 bg-gradient-to-br from-green-50 to-green-100 rounded-xl border border-green-200">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-14 w-14 rounded-full bg-accent text-white">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                    </div>
                    <div class="ml-5">
                        <h3 class="text-lg font-serif font-medium text-gray-900">Address</h3>
                        <p class="mt-2 text-gray-700 font-sans"><?php echo htmlspecialchars($hotel['address']); ?></p>
                    </div>
                </div>
                
                <div class="flex items-start p-5 bg-gradient-to-br from-green-50 to-green-100 rounded-xl border border-green-200">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-14 w-14 rounded-full bg-accent text-white">
                            <i class="fas fa-phone"></i>
                        </div>
                    </div>
                    <div class="ml-5">
                        <h3 class="text-lg font-serif font-medium text-gray-900">Phone</h3>
                        <p class="mt-2 text-gray-700 font-sans"><?php echo htmlspecialchars($hotel['phone']); ?></p>
                    </div>
                </div>
                
                <div class="flex items-start p-5 bg-gradient-to-br from-green-50 to-green-100 rounded-xl border border-green-200">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-14 w-14 rounded-full bg-accent text-white">
                            <i class="fas fa-envelope"></i>
                        </div>
                    </div>
                    <div class="ml-5">
                        <h3 class="text-lg font-serif font-medium text-gray-900">Email</h3>
                        <p class="mt-2 text-gray-700 font-sans"><?php echo htmlspecialchars($hotel['email']); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>