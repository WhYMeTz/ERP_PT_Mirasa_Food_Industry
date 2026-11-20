/* ==========================================================================
   LOGIN PAGE - JAVASCRIPT
   ========================================================================== */

document.addEventListener('DOMContentLoaded', function() {
    
    // Password Toggle
    const toggleBtn = document.getElementById('togglePasswordBtn');
    const passwordInput = document.getElementById('passwordInput');
    
    if (toggleBtn && passwordInput) {
        toggleBtn.addEventListener('click', function() {
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
        });
    }
    
    // Slideshow Auto-play
    const slideImages = document.querySelectorAll('.slide-img');
    const slideDots = document.querySelectorAll('.slide-dot');
    let currentSlide = 0;
    
    function showSlide(index) {
        slideImages.forEach(img => img.classList.remove('active'));
        slideDots.forEach(dot => dot.classList.remove('active'));
        
        if (slideImages[index]) slideImages[index].classList.add('active');
        if (slideDots[index]) slideDots[index].classList.add('active');
    }
    
    function nextSlide() {
        currentSlide = (currentSlide + 1) % slideImages.length;
        showSlide(currentSlide);
    }
    
    // Auto change every 6 seconds
    if (slideImages.length > 1) {
        setInterval(nextSlide, 6000);
    }
    
    // Manual control
    slideDots.forEach((dot, index) => {
        dot.addEventListener('click', function() {
            currentSlide = index;
            showSlide(currentSlide);
        });
    });

    // Demo Accounts Toggle
    const demoToggle = document.getElementById('demoToggleTrigger');
    const demoGrid = document.getElementById('demoGridWrapper');
    
    if (demoToggle && demoGrid) {
        demoToggle.addEventListener('click', function() {
            this.classList.toggle('expanded');
            demoGrid.classList.toggle('open');
        });
    }
});
