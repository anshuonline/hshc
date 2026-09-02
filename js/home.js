document.addEventListener('DOMContentLoaded', function() {
    // Carousel Logic
    const carousel = document.getElementById('heroCarousel');
    if (carousel) {
        const items = carousel.querySelectorAll('[data-carousel-item]');
        let currentIndex = 0;
        const intervalTime = 6000; // 6 seconds for slow luxury feel
        let carouselInterval;
        
        function showSlide(index) {
            items[currentIndex].classList.remove('opacity-100');
            items[currentIndex].classList.add('opacity-0');
            
            currentIndex = index;
            
            items[currentIndex].classList.remove('opacity-0');
            items[currentIndex].classList.add('opacity-100');
        }
        
        function nextSlide() {
            const nextIndex = (currentIndex + 1) % items.length;
            showSlide(nextIndex);
        }
        
        function startAutoplay() {
            carouselInterval = setInterval(nextSlide, intervalTime);
        }
        
        startAutoplay();
    }
});
