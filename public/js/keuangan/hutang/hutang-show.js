/**
 * Logika Interaktif Halaman Detail Tagihan Hutang (show.blade.php)
 * ERP PT Mirasa Food Industry
 */

function openEditTempoModal() {
    const modal = document.getElementById('modalEditTempo');
    if (modal) {
        modal.style.display = 'flex';
    }
}

function closeEditTempoModal() {
    const modal = document.getElementById('modalEditTempo');
    if (modal) {
        modal.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('modalEditTempo');
    if (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                closeEditTempoModal();
            }
        });
    }
});
