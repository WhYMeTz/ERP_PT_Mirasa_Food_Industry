/**
 * Desktop Login Script - PT Mirasa Food Industry
 * Features: Ken Burns Slideshow Crossfade, Password Toggle, Demo Switcher Accordion
 */
document.addEventListener('DOMContentLoaded', function () {
    // 1. Password Visibility Toggle
    const toggleBtn = document.getElementById('togglePasswordBtn');
    const pwdInput = document.getElementById('passwordInput');
    const eyeIcon = document.getElementById('eyeIcon');

    if (toggleBtn && pwdInput) {
        toggleBtn.addEventListener('click', function () {
            const isPassword = pwdInput.type === 'password';
            pwdInput.type = isPassword ? 'text' : 'password';

            if (eyeIcon) {
                if (isPassword) {
                    // Eye slash (hide password)
                    eyeIcon.innerHTML = `
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                    `;
                } else {
                    // Eye open (show password)
                    eyeIcon.innerHTML = `
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    `;
                }
            }
        });
    }

    // 2. Ken Burns Factory Showcase Slideshow
    const slides = document.querySelectorAll('.slide-img');
    const dots = document.querySelectorAll('.slide-dot');
    let currentSlide = 0;
    let slideInterval = null;

    function goToSlide(index) {
        if (!slides.length) return;
        slides.forEach(s => s.classList.remove('active'));
        dots.forEach(d => d.classList.remove('active'));

        currentSlide = (index + slides.length) % slides.length;
        slides[currentSlide].classList.add('active');
        if (dots[currentSlide]) {
            dots[currentSlide].classList.add('active');
        }
    }

    function nextSlide() {
        goToSlide(currentSlide + 1);
    }

    if (slides.length > 1) {
        slideInterval = setInterval(nextSlide, 7000);

        dots.forEach((dot, idx) => {
            dot.addEventListener('click', function () {
                clearInterval(slideInterval);
                goToSlide(idx);
                slideInterval = setInterval(nextSlide, 7000);
            });
        });
    }

    // 3. Demo Accounts Accordion Toggle
    const demoTrigger = document.getElementById('demoToggleTrigger');
    const demoWrapper = document.getElementById('demoGridWrapper');

    if (demoTrigger && demoWrapper) {
        demoTrigger.addEventListener('click', function () {
            const isExpanded = demoTrigger.classList.contains('expanded');
            if (isExpanded) {
                demoTrigger.classList.remove('expanded');
                demoWrapper.classList.remove('open');
            } else {
                demoTrigger.classList.add('expanded');
                demoWrapper.classList.add('open');
            }
        });
    }

    // 4. Focus email field if empty
    const emailField = document.getElementById('emailInput');
    if (emailField && !emailField.value) {
        emailField.focus();
    }
});
