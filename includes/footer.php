<!-- Footer -->
    <footer class="bg-[#030712] text-white pt-20 pb-10 border-t border-white/5 mt-auto relative overflow-hidden">
        
        <!-- Ambient Background for Footer -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden">
            <div class="absolute top-0 right-0 -mr-40 -mt-40 w-96 h-96 rounded-full bg-accent/5 blur-[100px]"></div>
        </div>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-16">
                <div class="lg:col-span-1">
                    <!-- Text Logo -->
                    <a href="index.php" class="inline-block text-3xl font-serif font-bold text-white tracking-[0.2em] uppercase mb-6">
                        Grand<span class="font-light text-accent ml-2">Luxe</span>
                    </a>
                    <p class="text-gray-400 font-sans text-sm leading-relaxed mb-6">
                        Experience the epitome of luxury and tranquility. A sanctuary where modern elegance meets timeless tradition.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="w-10 h-10 rounded-full border border-white/20 flex items-center justify-center text-gray-400 hover:text-accent hover:border-accent transition-all duration-300">
                            <i class="fab fa-facebook-f text-sm"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full border border-white/20 flex items-center justify-center text-gray-400 hover:text-accent hover:border-accent transition-all duration-300">
                            <i class="fab fa-instagram text-sm"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full border border-white/20 flex items-center justify-center text-gray-400 hover:text-accent hover:border-accent transition-all duration-300">
                            <i class="fab fa-twitter text-sm"></i>
                        </a>
                    </div>
                </div>
                
                <div>
                    <h3 class="text-sm font-sans font-medium text-white tracking-[0.2em] uppercase mb-6">Explore</h3>
                    <ul class="space-y-4">
                        <li><a href="index.php" class="text-sm text-gray-400 hover:text-accent transition-colors duration-300 font-sans tracking-wide">Home</a></li>
                        <li><a href="hotels.php" class="text-sm text-gray-400 hover:text-accent transition-colors duration-300 font-sans tracking-wide">Retreat</a></li>
                        <li><a href="index.php#about" class="text-sm text-gray-400 hover:text-accent transition-colors duration-300 font-sans tracking-wide">Facilities</a></li>
                        <li><a href="reviews.php" class="text-sm text-gray-400 hover:text-accent transition-colors duration-300 font-sans tracking-wide">Reviews</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-sm font-sans font-medium text-white tracking-[0.2em] uppercase mb-6">Contact</h3>
                    <ul class="space-y-4 text-gray-400 text-sm">
                        <li class="flex items-start">
                            <i class="fas fa-map-marker-alt mt-1 mr-3 text-accent text-xs"></i>
                            <span class="font-sans tracking-wide leading-relaxed">The Grand Avenue, Luxury District<br>Metropolis City, 10001</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-phone mr-3 text-accent text-xs"></i>
                            <span class="font-sans tracking-wide">1-800-GRAND-LUXE</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-envelope mr-3 text-accent text-xs"></i>
                            <span class="font-sans tracking-wide">inquiries@grandluxe.demo</span>
                        </li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-sm font-sans font-medium text-white tracking-[0.2em] uppercase mb-6">Newsletter</h3>
                    <p class="text-gray-400 mb-6 font-sans text-sm tracking-wide">
                        Join our exclusive mailing list for tailored offers and announcements.
                    </p>
                    <form id="newsletter-form" class="space-y-3 relative">
                        <input type="text" id="subscriber-name" placeholder="Name (optional)" class="w-full bg-white/5 border border-white/10 text-white px-4 py-3 text-sm focus:outline-none focus:border-accent transition-colors font-sans placeholder-gray-500">
                        <input type="email" id="subscriber-email" placeholder="Email Address" required class="w-full bg-white/5 border border-white/10 text-white px-4 py-3 text-sm focus:outline-none focus:border-accent transition-colors font-sans placeholder-gray-500">
                        <button type="submit" class="w-full bg-accent hover:bg-accent-light text-[#030712] py-3 text-sm font-medium tracking-[0.2em] uppercase transition-all duration-300">
                            Subscribe
                        </button>
                        <div id="newsletter-message" class="hidden absolute top-full left-0 mt-2 w-full p-2 text-xs text-center border"></div>
                    </form>
                </div>
            </div>
            
            <div class="border-t border-white/10 pt-8 flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-500 text-xs tracking-widest uppercase font-sans mb-4 md:mb-0">
                    &copy; <?php echo date('Y'); ?> Grand Luxe Hotel. All rights reserved.
                </p>
                <div class="flex space-x-8">
                    <a href="#" class="text-gray-500 hover:text-accent text-xs tracking-widest uppercase transition-colors duration-300 font-sans">Privacy</a>
                    <a href="#" class="text-gray-500 hover:text-accent text-xs tracking-widest uppercase transition-colors duration-300 font-sans">Terms</a>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Newsletter Thank You Popup (Glassmorphism) -->
    <div id="newsletter-popup" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden overflow-y-auto h-full w-full z-[150] transition-opacity duration-300 opacity-0 flex items-center justify-center">
        <div class="relative mx-auto p-8 border border-white/10 shadow-[0_0_50px_rgba(212,175,55,0.15)] bg-[#0a0a0a] max-w-md w-full mx-4 transform transition-transform duration-300 scale-95">
            <button id="close-popup-icon" class="absolute top-4 right-4 text-gray-500 hover:text-accent transition-colors">
                <i class="fas fa-times"></i>
            </button>
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full border-2 border-accent mb-6 bg-accent/5">
                    <i class="fas fa-check text-accent text-2xl"></i>
                </div>
                <h3 class="text-xl font-serif text-white mb-2">Welcome to Grand Luxe</h3>
                <p class="text-gray-400 text-sm font-sans mb-8 leading-relaxed">
                    Thank you for subscribing. We look forward to sharing our latest curations and exclusive offers with you.
                </p>
                <button id="close-popup" class="w-full bg-transparent border border-accent text-accent hover:bg-accent hover:text-[#030712] py-3 text-sm tracking-[0.2em] uppercase transition-all duration-300">
                    Close
                </button>
            </div>
        </div>
    </div>
    
    <script>
        // Newsletter form handling with animation
        document.getElementById('newsletter-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const name = document.getElementById('subscriber-name').value;
            const email = document.getElementById('subscriber-email').value;
            const messageDiv = document.getElementById('newsletter-message');
            
            if (!email) {
                showMessage('Please enter your email.', 'error');
                return;
            }
            
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'subscribe_newsletter.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.success) {
                                const popup = document.getElementById('newsletter-popup');
                                popup.classList.remove('hidden');
                                // Trigger animation
                                setTimeout(() => {
                                    popup.classList.remove('opacity-0');
                                    popup.querySelector('.relative').classList.remove('scale-95');
                                    popup.querySelector('.relative').classList.add('scale-100');
                                }, 10);
                                
                                document.getElementById('subscriber-name').value = '';
                                document.getElementById('subscriber-email').value = '';
                            } else {
                                showMessage(response.message, 'error');
                            }
                        } catch (e) {
                            showMessage('An error occurred.', 'error');
                        }
                    } else {
                        showMessage('An error occurred.', 'error');
                    }
                }
            };
            
            const data = 'email=' + encodeURIComponent(email) + '&name=' + encodeURIComponent(name);
            xhr.send(data);
        });
        
        function closePopupAction() {
            const popup = document.getElementById('newsletter-popup');
            popup.classList.add('opacity-0');
            popup.querySelector('.relative').classList.remove('scale-100');
            popup.querySelector('.relative').classList.add('scale-95');
            setTimeout(() => {
                popup.classList.add('hidden');
            }, 300);
        }

        document.getElementById('close-popup').addEventListener('click', closePopupAction);
        document.getElementById('close-popup-icon').addEventListener('click', closePopupAction);
        
        // Close popup when clicking outside
        document.getElementById('newsletter-popup').addEventListener('click', function(event) {
            if (event.target === this) {
                closePopupAction();
            }
        });
        
        function showMessage(message, type) {
            const messageDiv = document.getElementById('newsletter-message');
            messageDiv.textContent = message;
            
            if (type === 'success') {
                messageDiv.className = 'absolute top-full left-0 mt-2 w-full p-2 text-xs text-center border border-accent text-accent bg-black/50 backdrop-blur-sm transition-all duration-300';
            } else {
                messageDiv.className = 'absolute top-full left-0 mt-2 w-full p-2 text-xs text-center border border-red-500 text-red-500 bg-black/50 backdrop-blur-sm transition-all duration-300';
            }
            
            messageDiv.classList.remove('hidden');
            messageDiv.style.opacity = '1';
            
            // Hide message after 4 seconds
            setTimeout(() => {
                messageDiv.style.opacity = '0';
                setTimeout(() => {
                    messageDiv.classList.add('hidden');
                }, 300);
            }, 4000);
        }
    </script>
    
    <!-- Copyright Ribbon -->
    <div class="fixed bottom-0 right-4 md:right-8 z-[200] bg-accent text-[#030712] px-4 md:px-6 py-2 rounded-t-lg shadow-[0_-5px_20px_rgba(212,175,55,0.3)] transform transition-transform hover:-translate-y-1 flex items-center backdrop-blur-md border-t border-x border-[#aa8c2c]">
        <i class="fas fa-laptop-code mr-3 opacity-80"></i>
        <div class="font-sans text-[0.65rem] md:text-xs font-semibold tracking-wider flex items-center flex-wrap">
            Demo site created by <span class="font-bold tracking-widest ml-1 bg-[#030712] text-accent px-2 py-0.5 rounded-sm mx-1.5 shadow-inner">Hypecrews Software Private Limited</span> Developer - Rajdeep
        </div>
    </div>
    
</body>
</html>