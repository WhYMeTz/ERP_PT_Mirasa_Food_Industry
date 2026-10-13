/**
 * =========================================================================
 * ERP PT MIRASA - PENGEMASAN BARANG JADI & HPP (PACKAGING) SHOW JS
 * Handlers Cetak Dokumen & Modal Pembatalan (Void)
 * =========================================================================
 */

document.addEventListener('DOMContentLoaded', function () {
    // Tombol Escape menutup modal
    window.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeVoidShowModal();
        }
    });
});

/**
 * Trigger cetak berita acara pengemasan
 */
function printPackagingDoc() {
    window.print();
}

/**
 * Buka Modal Void pada halaman detail
 */
function openVoidShowModal() {
    const modal = document.getElementById('modalVoidPackagingShow');
    if (modal) {
        modal.style.display = 'flex';
        const input = document.getElementById('inputAlasanVoidShow');
        if (input) {
            input.value = '';
            setTimeout(() => input.focus(), 150);
        }
    }
}

/**
 * Tutup Modal Void
 */
function closeVoidShowModal() {
    const modal = document.getElementById('modalVoidPackagingShow');
    if (modal) {
        modal.style.display = 'none';
    }
}
