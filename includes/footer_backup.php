    <!-- Footer -->
    <footer class="bg-gradient-to-r from-gray-800 to-gray-900 text-white pt-16 pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">
                <div>
                    <h3 class="text-xl font-serif font-bold mb-4">Demo Hotel & Resort</h3>
                    <p class="text-gray-300 mb-4 font-sans">
                        Experience luxury and tranquility in the heart of Demo City, just 5 minutes from M.G. Marg.
                    </p>
                    <div class="flex space-x-4">
                        <a href="https://www.facebook.com/share/16wFVgoNbp/" target="_blank" class="text-gray-400 hover:text-white transition-colors duration-300">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors duration-300">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors duration-300">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </div>
                </div>
                
                <div>
                    <h3 class="text-xl font-serif font-bold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="index.php" class="text-gray-300 hover:text-white transition-colors duration-300 font-sans">Home</a></li>
                        <li><a href="index.php#about" class="text-gray-300 hover:text-white transition-colors duration-300 font-sans">About Us</a></li>
                        <li><a href="hotels.php" class="text-gray-300 hover:text-white transition-colors duration-300 font-sans">Retreat</a></li>
                        <li><a href="about.php" class="text-gray-300 hover:text-white transition-colors duration-300 font-sans">Facilities</a></li>
                        <li><a href="contact.php" class="text-gray-300 hover:text-white transition-colors duration-300 font-sans">Contact</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-xl font-serif font-bold mb-4">Contact Info</h3>
                    <ul class="space-y-3 text-gray-300">
                        <li class="flex items-start">
                            <i class="fas fa-map-marker-alt mt-1 mr-3 text-accent"></i>
                            <span class="font-sans">NH-31A, Amdogolai, near RBI, Demo City, Demo State 737102</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-phone mr-3 text-accent"></i>
                            <span class="font-sans">+911234567890</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-envelope mr-3 text-accent"></i>
                            <span class="font-sans">info@demohotel.com</span>
                        </li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-xl font-serif font-bold mb-4">Newsletter</h3>
                    <p class="text-gray-300 mb-4 font-sans">
                        Subscribe to our newsletter for special offers and updates.
                    </p>
                    <form id="newsletter-form" class="space-y-3">
                        <input type="text" id="subscriber-name" placeholder="Your name (optional)" class="px-4 py-2 w-full rounded-lg focus:outline-none text-gray-800 font-sans">
                        <input type="email" id="subscriber-email" placeholder="Your email" required class="px-4 py-2 w-full rounded-lg focus:outline-none text-gray-800 font-sans">
                        <button type="submit" class="bg-accent hover:bg-secondary px-4 py-2 rounded-lg transition-colors duration-300 w-full font-sans font-medium">
                            Subscribe
                        </button>
                        <div id="newsletter-message" class="hidden p-2 rounded text-sm"></div>
                    </form>
                </div>
            </div>
            
            <div class="border-t border-gray-700 pt-8">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <p class="text-gray-400 mb-4 md:mb-0 font-sans">
                        &copy; <?php echo date('Y'); ?> Demo Hotel & Resort. All rights reserved.
                    </p>
                    <div class="flex space-x-6">
                        <a href="#" class="text-gray-400 hover:text-white transition-colors duration-300 font-sans">Privacy Policy</a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors duration-300 font-sans">Terms of Service</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Newsletter Thank You Popup -->
    <div id="newsletter-popup" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                    <i class="fas fa-check text-green-600 text-xl"></i>
                </div>
                <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">Thank You for Subscribing!</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-gray-700">We're excited to have you join our newsletter. You'll receive our latest updates and special offers soon.</p>
                </div>
                <div class="items-center px-4 py-3">
                    <button id="close-popup" class="px-4 py-2 bg-green-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Newsletter subscription form handling
        document.getElementById('newsletter-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const name = document.getElementById('subscriber-name').value;
            const email = document.getElementById('subscriber-email').value;
            const messageDiv = document.getElementById('newsletter-message');
            
            // Basic validation
            if (!email) {
                showMessage('Please enter your email address.', 'error');
                return;
            }
            
            // Send AJAX request
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'subscribe_newsletter.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.success) {
                                // Show thank you popup
                                document.getElementById('newsletter-popup').classList.remove('hidden');
                                document.getElementById('subscriber-name').value = '';
                                document.getElementById('subscriber-email').value = '';
                            } else {
                                showMessage(response.message, 'error');
                            }
                        } catch (e) {
                            showMessage('An error occurred. Please try again.', 'error');
                        }
                    } else {
                        showMessage('An error occurred. Please try again.', 'error');
                    }
                }
            };
            
            const data = 'email=' + encodeURIComponent(email) + '&name=' + encodeURIComponent(name);
            xhr.send(data);
        });
        
        // Close popup when clicking the close button
        document.getElementById('close-popup').addEventListener('click', function() {
            document.getElementById('newsletter-popup').classList.add('hidden');
        });
        
        // Close popup when clicking outside
        window.addEventListener('click', function(event) {
            const popup = document.getElementById('newsletter-popup');
            if (event.target == popup) {
                popup.classList.add('hidden');
            }
        });
        
        function showMessage(message, type) {
            const messageDiv = document.getElementById('newsletter-message');
            messageDiv.textContent = message;
            messageDiv.className = 'p-2 rounded text-sm ';
            
            if (type === 'success') {
                messageDiv.classList.add('bg-green-100', 'text-green-800');
            } else {
                messageDiv.classList.add('bg-red-100', 'text-red-800');
            }
            
            messageDiv.classList.remove('hidden');
            
            // Hide message after 5 seconds
            setTimeout(() => {
                messageDiv.classList.add('hidden');
            }, 5000);
        }
    </script>
</body>
</html>