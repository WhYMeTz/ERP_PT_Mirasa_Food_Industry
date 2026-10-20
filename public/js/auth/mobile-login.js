/**
 * Mobile Shopfloor Login Script - PT Mirasa Food Industry
 */
document.addEventListener('DOMContentLoaded', function () {
    // 1. Toggle Password Visibility
    const toggleBtn = document.getElementById('togglePasswordBtn');
    const pwdInput = document.getElementById('passwordInput');
    const eyeIcon = document.getElementById('eyeIcon');

    if (toggleBtn && pwdInput) {
        toggleBtn.addEventListener('click', function () {
            const isPassword = pwdInput.type === 'password';
            pwdInput.type = isPassword ? 'text' : 'password';

            if (eyeIcon) {
                if (isPassword) {
                    // Tampilkan ikon mata dicoret (sembunyikan)
                    eyeIcon.innerHTML = `
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                    `;
                } else {
                    // Tampilkan ikon mata terbuka
                    eyeIcon.innerHTML = `
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    `;
                }
            }
        });
    }

    // 2. Autofocus email field on load
    const emailField = document.getElementById('emailInput');
    if (emailField && !emailField.value) {
        emailField.focus();
    }
});

// Quick fill credentials for local test
function selectQuickOperator(email, defaultPwd) {
    const emailInput = document.getElementById('emailInput');
    const pwdInput = document.getElementById('passwordInput');
    if (emailInput) emailInput.value = email;
    if (pwdInput) pwdInput.value = defaultPwd || 'password';

    const form = document.getElementById('mobileLoginForm');
    if (form) form.submit();
}
