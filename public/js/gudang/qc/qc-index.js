/**
 * QC INBOUND INDEX JAVASCRIPT - ERP PT MIRASA FOOD INDUSTRY
 * Menangani dual-view switcher (Card HP vs Table Gudang PC), modal delete, dan filter
 */

document.addEventListener('DOMContentLoaded', function () {
    // Inisialisasi Tampilan Berdasarkan Layar atau Preferensi Tersimpan
    initViewMode();

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
 * Inisialisasi Mode Tampilan (Card vs Table)
 */
function initViewMode() {
    const savedMode = localStorage.getItem('mirasa_qc_view_mode');
    if (savedMode) {
        switchQcView(savedMode, false);
    } else {
        // Otomatis: jika layar lebar >= 992px gunakan Table Gudang, jika mobile gunakan Card
        if (window.innerWidth >= 992) {
            switchQcView('table', false);
        } else {
            switchQcView('card', false);
        }
    }
}

/**
 * Mengubah Mode Tampilan: 'card' (HP) atau 'table' (PC Gudang)
 */
function switchQcView(mode, save = true) {
    const cardContainer = document.getElementById('qcCardContainer');
    const tableContainer = document.getElementById('qcTableContainer');
    const mainWrapper = document.getElementById('qcIndexWrapper');
    const btnCard = document.getElementById('btnViewCard');
    const btnTable = document.getElementById('btnViewTable');

    if (!cardContainer || !tableContainer) return;

    if (mode === 'table') {
        cardContainer.style.display = 'none';
        tableContainer.style.display = 'block';
        if (btnCard) btnCard.classList.remove('active');
        if (btnTable) btnTable.classList.add('active');
        if (mainWrapper) mainWrapper.style.maxWidth = '1200px';
    } else {
        cardContainer.style.display = 'flex';
        tableContainer.style.display = 'none';
        if (btnCard) btnCard.classList.add('active');
        if (btnTable) btnTable.classList.remove('active');
        if (mainWrapper) mainWrapper.style.maxWidth = '800px';
    }

    if (save) {
        localStorage.setItem('mirasa_qc_view_mode', mode);
    }
}

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
