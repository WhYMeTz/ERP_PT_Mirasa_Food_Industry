/**
 * =========================================================================
 * ERP PT MIRASA - PENGEMASAN BARANG JADI & HPP (PACKAGING) SHOW JS
 * Handlers Tab Navigation, Cetak Dokumen & Modal Pembatalan (Void)
 * =========================================================================
 */

/**
 * Tab Navigation Controls (Output FG | Kalkulasi Biaya Kemas | Riwayat Audit)
 */
function switchProdTab(tabName, event) {
    if (event) {
        event.preventDefault();
    }
    
    // Update tab buttons
    const buttons = document.querySelectorAll('.prod-tab-btn');
    buttons.forEach(btn => {
        if (btn.getAttribute('data-tab') === tabName) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    // Update tab panes
    const panes = document.querySelectorAll('.prod-tab-pane');
    panes.forEach(pane => {
        if (pane.id === 'pane-' + tabName) {
            pane.classList.add('active');
        } else {
            pane.classList.remove('active');
        }
    });

    // Update URL hash without jumping
    if (history.replaceState) {
        history.replaceState(null, null, '#' + tabName);
    }
}
window.switchProdTab = switchProdTab;

document.addEventListener('DOMContentLoaded', function () {
    // Add click listeners to tab buttons
    document.querySelectorAll('.prod-tab-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            const tab = this.getAttribute('data-tab');
            if (tab) {
                switchProdTab(tab, e);
            }
        });
    });

    // Check hash on load
    const hash = window.location.hash.replace('#', '');
    if (hash && ['output', 'biaya', 'audit'].includes(hash)) {
        switchProdTab(hash);
    }

    // Tombol Escape menutup modal void
    window.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeVoidPackagingModal();
        }
    });

    // Delegated click handler untuk tombol void
    const btnVoid = document.getElementById('btnVoidPackaging');
    if (btnVoid) {
        btnVoid.addEventListener('click', function (e) {
            e.preventDefault();
            const packId = this.getAttribute('data-pack-id');
            const packNo = this.getAttribute('data-pack-no');
            const targetNm = this.getAttribute('data-target-nm');
            openVoidPackagingModal(packId, packNo, targetNm);
        });
    }
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
function openVoidPackagingModal(packId, packNo, targetNm) {
    const modal = document.getElementById('modalVoidPackaging');
    if (!modal) return;

    const form = document.getElementById('formVoidPackaging');
    if (form && packId) {
        form.action = '/produksi/packaging/' + packId + '/void';
    }

    const labelNo = document.getElementById('voidPackNoLabel');
    if (labelNo && packNo) {
        labelNo.textContent = packNo + (targetNm ? ' (' + targetNm + ')' : '');
    }

    const inputAlasan = document.getElementById('inputAlasanVoid');
    if (inputAlasan) {
        inputAlasan.value = '';
        setTimeout(() => inputAlasan.focus(), 150);
    }

    modal.style.display = 'flex';
}

/**
 * Tutup Modal Void
 */
function closeVoidPackagingModal() {
    const modal = document.getElementById('modalVoidPackaging');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Pasang secara eksplisit ke window global
window.printPackagingDoc = printPackagingDoc;
window.openVoidPackagingModal = openVoidPackagingModal;
window.closeVoidPackagingModal = closeVoidPackagingModal;
