<?php
session_start();
include 'includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="text-center mb-12">
        <h1 class="text-3xl font-serif font-bold text-gray-900">Contact Demo Hotel & Resort</h1>
        <p class="mt-4 text-lg text-gray-600">Get in touch with our team</p>
    </div>

    <div class="mt-10 grid grid-cols-1 lg:grid-cols-2 gap-10">
        <div class="bg-white rounded-2xl shadow-xl p-6 md:p-8">
            <h2 class="text-2xl font-serif font-bold text-gray-900 mb-6">Get in Touch</h2>
            <p class="text-gray-700 mb-8 font-sans">
                Have questions or want to make a reservation? Reach out to us using the contact information below.
            </p>
            
            <div class="space-y-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-14 w-14 rounded-full bg-accent text-white">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                    </div>
                    <div class="ml-5">
                        <h3 class="text-lg font-serif font-medium text-gray-900">Address</h3>
                        <p class="mt-1 text-gray-700 font-sans">123 Demo Street<br>Demo City, Demo State</p>
                    </div>
                </div>
                
                <div class="flex">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-14 w-14 rounded-full bg-accent text-white">
                            <i class="fas fa-phone"></i>
                        </div>
                    </div>
                    <div class="ml-5">
                        <h3 class="text-lg font-serif font-medium text-gray-900">Phone</h3>
                        <p class="mt-1 text-gray-700 font-sans">+12345678900</p>
                    </div>
                </div>
                
                <div class="flex">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-14 w-14 rounded-full bg-accent text-white">
                            <i class="fas fa-envelope"></i>
                        </div>
                    </div>
                    <div class="ml-5">
                        <h3 class="text-lg font-serif font-medium text-gray-900">Email</h3>
                        <p class="mt-1 text-gray-700 font-sans">reservations@demohotel.com</p>
                    </div>
                </div>
                
                <div class="flex">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-14 w-14 rounded-full bg-accent text-white">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                    <div class="ml-5">
                        <h3 class="text-lg font-serif font-medium text-gray-900">Location</h3>
                        <p class="mt-1 text-gray-700 font-sans">Just 5 minutes away from M.G. Marg, the heart of Demo City</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-2xl shadow-xl p-6 md:p-8">
            <h2 class="text-2xl font-serif font-bold text-gray-900 mb-6">Send us a Message</h2>
            <form class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2 font-sans">Name</label>
                    <input type="text" id="name" name="name" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent focus:border-accent transition duration-300 font-sans">
                </div>
                
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2 font-sans">Email</label>
                    <input type="email" id="email" name="email" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent focus:border-accent transition duration-300 font-sans">
                </div>
                
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2 font-sans">Phone</label>
                    <input type="tel" id="phone" name="phone" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent focus:border-accent transition duration-300 font-sans">
                </div>
                
                <div>
                    <label for="subject" class="block text-sm font-medium text-gray-700 mb-2 font-sans">Subject</label>
                    <input type="text" id="subject" name="subject" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent focus:border-accent transition duration-300 font-sans">
                </div>
                
                <div>
                    <label for="message" class="block text-sm font-medium text-gray-700 mb-2 font-sans">Message</label>
                    <textarea id="message" name="message" rows="5" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent focus:border-accent transition duration-300 font-sans"></textarea>
                </div>
                
                <div>
                    <button type="submit" class="w-full bg-gradient-to-r from-accent to-secondary hover:from-secondary hover:to-dark text-white py-3 px-6 rounded-lg font-medium transition duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl font-sans">
                        Send Message
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="mt-12 bg-white rounded-2xl shadow-xl p-6 md:p-8">
        <h2 class="text-2xl font-serif font-bold text-gray-900 mb-6">Location</h2>
        <p class="text-gray-700 mb-6 font-sans">
            Demo Hotel & Resort is located at 123 Demo Street along the national highway, just five minutes away from M.G. Marg, the heart of Demo City.
        </p>
        <div class="rounded-xl overflow-hidden h-96">
            <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d3544.7225822509045!2d88.604474!3d27.3218695!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x39e6a50698dfd307%3A0x71a1abcf2ac2587b!2sThe%20Khurana%20Group%20Dewachen%20Retreat-%20Hotel!5e0!3m2!1sen!2sin!4v1762073618748!5m2!1sen!2sin" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>