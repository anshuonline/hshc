<?php
session_start();
include 'config/db.php';
include 'includes/header.php';

// Fetch carousel images - since user reported images aren't working, we use our local generated images
// We'll override the dynamic ones for this premium demo
$images = [
    ['image_path' => 'images/hero_home.jpg'],
    ['image_path' => 'images/hero_retreat.jpg'],
    ['image_path' => 'images/hero_reviews.jpg']
];
?>

<!-- Include page specific CSS -->
<link rel="stylesheet" href="css/home.css">

<!-- Hero Section -->
<section class="relative h-screen flex items-center overflow-hidden bg-[#030712]">
    <!-- Video Background -->
    <div class="absolute inset-0 z-0">
        <div class="absolute inset-0 bg-black/50 z-10"></div>
        <video autoplay loop muted playsinline preload="auto" poster="images/hero_home.jpg" class="w-full h-full object-cover">
            <source src="videos/wshbtaj.mp4" type="video/mp4">
        </video>
    </div>
    
    <!-- Hero Content -->
    <div class="relative z-20 text-left px-8 md:px-16 lg:px-24 max-w-7xl mx-auto w-full" data-aos="fade-up" data-aos-duration="1500">
        <p class="text-accent uppercase tracking-[0.4em] text-sm font-medium mb-6 font-sans">Welcome to</p>
        <h1 class="text-6xl md:text-8xl font-serif text-white mb-8 tracking-wide drop-shadow-2xl">
            Grand<span class="font-light italic text-accent">Luxe</span>
        </h1>
        <p class="text-gray-300 font-sans tracking-widest text-lg max-w-3xl mb-12 leading-relaxed font-light uppercase drop-shadow-lg">
            An Oasis of Serenity and Unparalleled Elegance
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-start gap-8 mt-8">
            <a href="book-now.php" class="bg-accent hover:bg-accent-light text-[#030712] px-12 py-5 font-bold tracking-[0.2em] uppercase transition-all duration-500 w-full sm:w-auto shadow-[0_0_20px_rgba(212,175,55,0.4)] hover:shadow-[0_0_40px_rgba(212,175,55,0.6)] font-sans text-sm text-center">
                Reserve a Suite
            </a>
            <a href="#about" class="text-white border border-white/30 hover:border-accent hover:text-accent px-12 py-5 font-bold tracking-[0.2em] uppercase transition-all duration-500 w-full sm:w-auto bg-white/5 backdrop-blur-sm font-sans text-sm text-center">
                Explore
            </a>
        </div>
    </div>
    
    <!-- Scroll indicator -->
    <div class="absolute bottom-12 left-1/2 transform -translate-x-1/2 z-20 flex flex-col items-center animate-bounce">
        <span class="text-white/70 text-xs uppercase tracking-widest mb-3 font-sans">Scroll to Discover</span>
        <div class="w-[1px] h-16 bg-gradient-to-b from-white/70 to-transparent"></div>
    </div>
</section>

<!-- Featured In Section (Fake Brands) -->
<section class="py-12 bg-[#0a0a0a] border-b border-white/5 relative z-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <p class="text-gray-500 text-xs uppercase tracking-[0.3em] font-sans mb-8">Recognized By</p>
        <div class="flex flex-wrap justify-center items-center gap-12 md:gap-24 opacity-60 grayscale hover:grayscale-0 hover:opacity-100 transition-all duration-700">
            <div class="font-serif text-2xl italic tracking-widest text-white">Vogue Living</div>
            <div class="font-sans text-xl font-light tracking-[0.3em] uppercase text-white">Elite Traveler</div>
            <div class="font-serif text-3xl text-white">Luxe Digest</div>
            <div class="font-sans text-xl tracking-widest font-bold uppercase text-white border-4 border-white p-2">ARCHITECTURAL</div>
            <div class="font-serif text-2xl italic text-white">Condé Luxury</div>
        </div>
    </div>
</section>

<!-- About Section -->
<section id="about" class="py-32 bg-[#030712] relative overflow-hidden">
    <!-- Ambient blur blobs -->
    <div class="absolute top-0 left-0 w-96 h-96 bg-accent/10 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-secondary/30 rounded-full blur-[120px] pointer-events-none"></div>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-20 items-center">
            <div data-aos="fade-right">
                <p class="text-accent uppercase tracking-[0.3em] text-sm mb-6 font-sans">The Experience</p>
                <h2 class="text-5xl md:text-6xl font-serif text-white mb-8 leading-tight">
                    Redefining Modern <span class="italic font-light text-accent">Luxury</span>
                </h2>
                <div class="w-24 h-[1px] bg-accent mb-8"></div>
                <p class="text-gray-400 font-sans leading-relaxed mb-8 font-light text-lg">
                    Immerse yourself in a world of refined elegance. Our retreat is masterfully designed with contemporary architecture that honors classical comfort. Every detail of Grand Luxe has been meticulously curated to provide an unforgettable escape from the ordinary.
                </p>
                <a href="hotels.php" class="inline-flex items-center text-accent tracking-[0.2em] uppercase font-medium hover:text-white transition-colors group font-sans text-sm mt-4">
                    <span class="mr-4 border-b border-accent pb-1">View Accommodations</span>
                    <i class="fas fa-arrow-right transform group-hover:translate-x-2 transition-transform"></i>
                </a>
            </div>
            
            <div class="relative" data-aos="fade-left">
                <div class="glass-effect-dark p-4 border border-white/10 shadow-2xl relative z-10 transform translate-x-4 translate-y-4">
                    <img src="images/suite_interior.jpg" class="w-full h-auto object-cover grayscale-[20%] hover:grayscale-0 transition-all duration-700 aspect-[4/3]" alt="Luxury Suite">
                </div>
                <div class="absolute inset-0 border border-accent/30 -translate-x-4 -translate-y-4 z-0"></div>
            </div>
        </div>
    </div>
</section>

<!-- Gastronomy & Spa Sections -->
<section class="py-20 bg-[#0a0a0a] relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Gastronomy -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center mb-32">
            <div class="order-2 lg:order-1 relative" data-aos="fade-right">
                <div class="absolute inset-0 border border-accent/30 translate-x-4 translate-y-4 z-0"></div>
                <img src="images/fine_dining.jpg" class="w-full h-auto object-cover relative z-10 shadow-2xl aspect-[4/3]" alt="Fine Dining">
            </div>
            <div class="order-1 lg:order-2 pl-0 lg:pl-12" data-aos="fade-left">
                <p class="text-accent uppercase tracking-[0.3em] text-sm mb-4 font-sans">Michelin Star Experience</p>
                <h3 class="text-4xl md:text-5xl font-serif text-white mb-6">Culinary <span class="italic font-light text-accent">Excellence</span></h3>
                <p class="text-gray-400 font-sans leading-relaxed font-light text-lg mb-8">
                    Savor exquisite culinary creations crafted by our executive chef. Our signature restaurant offers a curated journey through local and international flavors, perfectly paired with selections from our extensive wine cellar.
                </p>
                <button class="text-sm font-sans tracking-[0.2em] uppercase px-8 py-3 border border-white/30 text-white hover:bg-white hover:text-black transition-colors">
                    Reserve a Table
                </button>
            </div>
        </div>

        <!-- Spa -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
            <div class="pr-0 lg:pr-12" data-aos="fade-right">
                <p class="text-accent uppercase tracking-[0.3em] text-sm mb-4 font-sans">Holistic Wellness</p>
                <h3 class="text-4xl md:text-5xl font-serif text-white mb-6">The <span class="italic font-light text-accent">Sanctuary</span> Spa</h3>
                <p class="text-gray-400 font-sans leading-relaxed font-light text-lg mb-8">
                    Rejuvenate your mind, body, and soul in our award-winning wellness center. Experience bespoke treatments, hydrotherapy pools, and serene meditation lounges designed for ultimate relaxation.
                </p>
                <button class="text-sm font-sans tracking-[0.2em] uppercase px-8 py-3 border border-white/30 text-white hover:bg-white hover:text-black transition-colors">
                    Explore Treatments
                </button>
            </div>
            <div class="relative" data-aos="fade-left">
                <div class="absolute inset-0 border border-accent/30 -translate-x-4 translate-y-4 z-0"></div>
                <img src="images/wellness_spa.jpg" class="w-full h-auto object-cover relative z-10 shadow-2xl aspect-[4/3]" alt="Wellness Spa">
            </div>
        </div>

    </div>
</section>

<!-- Amenities Grid -->
<section class="py-32 bg-[#030712] border-y border-white/5 relative">
    <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-full h-full max-w-4xl bg-accent/5 rounded-full blur-[150px] pointer-events-none"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center mb-20" data-aos="fade-up">
            <p class="text-accent uppercase tracking-[0.3em] text-sm mb-4 font-sans">Bespoke Services</p>
            <h2 class="text-4xl md:text-5xl font-serif text-white">Exceptional <span class="italic font-light text-accent">Amenities</span></h2>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
            <div class="glass-effect-dark p-12 text-center group hover:-translate-y-4 transition-all duration-500 border border-white/10 hover:border-accent/50 shadow-[0_10px_30px_rgba(0,0,0,0.5)] hover:shadow-[0_20px_40px_rgba(212,175,55,0.15)] relative overflow-hidden rounded-sm" data-aos="fade-up" data-aos-delay="100">
                <div class="absolute inset-0 bg-gradient-to-b from-accent/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
                <div class="w-24 h-24 mx-auto border border-accent/30 rounded-full flex items-center justify-center mb-8 group-hover:border-accent group-hover:scale-110 group-hover:bg-accent/10 transition-all duration-500 bg-black/50 relative z-10">
                    <i class="fas fa-gem text-4xl text-accent group-hover:drop-shadow-[0_0_10px_rgba(212,175,55,0.8)] transition-all"></i>
                </div>
                <h3 class="text-2xl font-serif text-white mb-4 tracking-wide relative z-10 group-hover:text-accent transition-colors">Elite Concierge</h3>
                <p class="text-gray-400 font-sans text-sm leading-relaxed font-light relative z-10">Experience flawless, personalized service catering to your every whim at any hour.</p>
            </div>
            
            <div class="glass-effect-dark p-12 text-center group hover:-translate-y-4 transition-all duration-500 border border-white/10 hover:border-accent/50 shadow-[0_10px_30px_rgba(0,0,0,0.5)] hover:shadow-[0_20px_40px_rgba(212,175,55,0.15)] relative overflow-hidden rounded-sm" data-aos="fade-up" data-aos-delay="200">
                <div class="absolute inset-0 bg-gradient-to-b from-accent/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
                <div class="w-24 h-24 mx-auto border border-accent/30 rounded-full flex items-center justify-center mb-8 group-hover:border-accent group-hover:scale-110 group-hover:bg-accent/10 transition-all duration-500 bg-black/50 relative z-10">
                    <i class="fas fa-car-side text-4xl text-accent group-hover:drop-shadow-[0_0_10px_rgba(212,175,55,0.8)] transition-all"></i>
                </div>
                <h3 class="text-2xl font-serif text-white mb-4 tracking-wide relative z-10 group-hover:text-accent transition-colors">Chauffeur Service</h3>
                <p class="text-gray-400 font-sans text-sm leading-relaxed font-light relative z-10">Complimentary luxury transfers in our fleet of premium vehicles.</p>
            </div>
            
            <div class="glass-effect-dark p-12 text-center group hover:-translate-y-4 transition-all duration-500 border border-white/10 hover:border-accent/50 shadow-[0_10px_30px_rgba(0,0,0,0.5)] hover:shadow-[0_20px_40px_rgba(212,175,55,0.15)] relative overflow-hidden rounded-sm" data-aos="fade-up" data-aos-delay="300">
                <div class="absolute inset-0 bg-gradient-to-b from-accent/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
                <div class="w-24 h-24 mx-auto border border-accent/30 rounded-full flex items-center justify-center mb-8 group-hover:border-accent group-hover:scale-110 group-hover:bg-accent/10 transition-all duration-500 bg-black/50 relative z-10">
                    <i class="fas fa-wine-glass-alt text-4xl text-accent group-hover:drop-shadow-[0_0_10px_rgba(212,175,55,0.8)] transition-all"></i>
                </div>
                <h3 class="text-2xl font-serif text-white mb-4 tracking-wide relative z-10 group-hover:text-accent transition-colors">Private Bar</h3>
                <p class="text-gray-400 font-sans text-sm leading-relaxed font-light relative z-10">Exclusive access to our rare spirits collection and private tasting rooms.</p>
            </div>
        </div>
    </div>
</section>

<!-- Luxury Contact Form Section -->
<section class="py-32 bg-[#0a0a0a] relative overflow-hidden">
    <div class="absolute right-0 top-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-accent/5 rounded-full blur-[150px] pointer-events-none"></div>
    
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="glass-effect-dark p-12 md:p-20 border border-white/10 shadow-[0_0_50px_rgba(0,0,0,0.5)] text-center relative overflow-hidden" data-aos="zoom-in">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-accent to-transparent"></div>
            
            <p class="text-accent uppercase tracking-[0.3em] text-sm mb-4 font-sans">Connect</p>
            <h2 class="text-4xl md:text-5xl font-serif text-white mb-6">Private <span class="italic font-light text-accent">Inquiry</span></h2>
            <p class="text-gray-400 font-sans mb-12 font-light text-lg">
                Our reservation specialists are at your disposal to tailor your perfect stay.
            </p>
            
            <form class="space-y-8 text-left max-w-3xl mx-auto">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <input type="text" placeholder="First Name" class="w-full bg-transparent border-b border-white/30 text-white px-0 py-4 focus:outline-none focus:border-accent transition-colors font-sans placeholder-gray-500 text-lg">
                    </div>
                    <div>
                        <input type="text" placeholder="Last Name" class="w-full bg-transparent border-b border-white/30 text-white px-0 py-4 focus:outline-none focus:border-accent transition-colors font-sans placeholder-gray-500 text-lg">
                    </div>
                </div>
                <div>
                    <input type="email" placeholder="Email Address" class="w-full bg-transparent border-b border-white/30 text-white px-0 py-4 focus:outline-none focus:border-accent transition-colors font-sans placeholder-gray-500 text-lg">
                </div>
                <div>
                    <textarea placeholder="Your Request" rows="3" class="w-full bg-transparent border-b border-white/30 text-white px-0 py-4 focus:outline-none focus:border-accent transition-colors font-sans placeholder-gray-500 resize-none text-lg"></textarea>
                </div>
                <div class="pt-8 text-center">
                    <button type="button" class="bg-transparent border border-accent text-accent hover:bg-accent hover:text-[#030712] px-16 py-5 font-bold tracking-[0.2em] uppercase transition-all duration-500 font-sans text-sm shadow-[0_0_15px_rgba(212,175,55,0.2)]">
                        Send Inquiry
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Include page specific JS -->
<script src="js/home.js"></script>

<?php include 'includes/footer.php'; ?>