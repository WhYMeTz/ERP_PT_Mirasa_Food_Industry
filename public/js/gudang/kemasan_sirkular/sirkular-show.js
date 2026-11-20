/**
 * Logika Interaktif Tampilan Detail Kemasan Sirkular - ERP PT Mirasa
 */

function openModalVoid() {
    const m = document.getElementById('modalVoid');
    if (m) {
        m.style.display = 'flex';
    }
}

function closeModalVoid() {
    const m = document.getElementById('modalVoid');
    if (m) {
        m.style.display = 'none';
    }
}

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        closeModalVoid();
    }
});

