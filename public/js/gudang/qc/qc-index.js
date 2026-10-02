/**
 * QC INBOUND INDEX JAVASCRIPT - ERP PT MIRASA FOOD INDUSTRY
 * Menangani interaksi UI mobile, modal delete, dan filter cepat
 */

document.addEventListener('DOMContentLoaded', function () {
    // Backdrop click close modal
    const modalDelete = document.getElementById('modalDeleteQc');
    if (modalDelete) {
        modalDelete.addEventListener('click', function (e) {
            if (e.target === this) {
                closeDeleteQcModal();
            }
        });
    }

    // Keyboard ESC to close
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeDeleteQcModal();
        }
    });
});

/**
 * Membuka Modal Konfirmasi Pembatalan Tiket QC
 */
function openDeleteQcModal(qcId, qcNo) {
    const modal = document.getElementById('modalDeleteQc');
    const labelNo = document.getElementById('deleteQcNo');
    const form = document.getElementById('formDeleteQc');

    if (!modal || !form) return;

    if (labelNo) {
        labelNo.textContent = qcNo || `#QC-${qcId}`;
    }

    // Setup action URL
    form.action = `${window.location.origin}/qc/inbound/${qcId}`;
    modal.style.display = 'flex';
}

/**
 * Menutup Modal Konfirmasi Pembatalan Tiket QC
 */
function closeDeleteQcModal() {
    const modal = document.getElementById('modalDeleteQc');
    if (modal) {
        modal.style.display = 'none';
    }
}
