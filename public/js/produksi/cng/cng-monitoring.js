/**
 * ═════════════════════════════════════════════════════════════════
 * JAVASCRIPT: MONITORING & REKONSILIASI PENGGUNAAN GAS CNG
 * ERP PT Mirasa Food Industry - Modul Produksi
 * ═════════════════════════════════════════════════════════════════
 */

document.addEventListener('DOMContentLoaded', function () {
    // Inisialisasi tab berdasarkan URL query param atau default
    const urlParams = new URLSearchParams(window.location.search);
    const initialTab = urlParams.get('tab') || 'rangkuman';
    switchCngTab(initialTab, false);

    // Inisialisasi kalkulasi jika tab balancing aktif
    if (typeof recalculateCngBalancing === 'function') {
        recalculateCngBalancing();
    }
});

/**
 * Pindah Tab Segmented Secara Interaktif & Responsif
 */
function switchCngTab(tabName, updateUrl = true) {
    const validTabs = ['rangkuman', 'harian', 'bulanan'];
    if (!validTabs.includes(tabName)) {
        tabName = 'rangkuman';
    }

    // Update Tab Buttons
    document.querySelectorAll('.cng-tab-btn').forEach(btn => {
        if (btn.dataset.tab === tabName) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    // Update Tab Panes
    document.querySelectorAll('.cng-tab-pane').forEach(pane => {
        if (pane.id === 'pane_' + tabName) {
            pane.classList.add('active');
        } else {
            pane.classList.remove('active');
        }
    });

    // Update URL query parameter tanpa reload halaman
    if (updateUrl && window.history && window.history.replaceState) {
        const url = new URL(window.location);
        url.searchParams.set('tab', tabName);
        window.history.replaceState({}, '', url);
    }

    // Trigger recalculate jika masuk ke tab harian
    if (tabName === 'harian' && typeof recalculateCngBalancing === 'function') {
        setTimeout(recalculateCngBalancing, 50);
    }
}

/**
 * Cetak Dokumen Rangkuman Pemakaian Gas Harian
 */
function printCngSummary() {
    window.print();
}
