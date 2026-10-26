/**
 * ==============================================================
 * PENGELUARAN BARANG / SURAT JALAN - INDEX JAVASCRIPT
 * PT Mirasa Food Industry (Cap Payung)
 * ==============================================================
 */

// Toggle Smart Action Dropdown (AGENTS.md Standard)
window.toggleSmartActionDropdown = function(button, event, menuId) {
    if (event) {
        event.stopPropagation();
    }

    const targetMenu = document.getElementById(menuId);
    if (!targetMenu) return;

    const isCurrentlyShown = targetMenu.classList.contains('show');

    // Close all other dropdowns
    document.querySelectorAll('.action-dropdown-menu').forEach(m => {
        m.classList.remove('show');
    });

    if (!isCurrentlyShown) {
        const rect = button.getBoundingClientRect();
        targetMenu.style.position = 'fixed';
        targetMenu.style.top = (rect.bottom + 4) + 'px';
        
        // Align right
        const menuWidth = 190;
        let leftPos = rect.right - menuWidth;
        if (leftPos < 10) leftPos = 10;
        targetMenu.style.left = leftPos + 'px';

        targetMenu.classList.add('show');
    }
};

// Global click to close dropdowns
document.addEventListener('click', function(e) {
    if (!e.target.closest('.action-dropdown-menu') && !e.target.closest('.btn-action-trigger')) {
        document.querySelectorAll('.action-dropdown-menu').forEach(m => {
            m.classList.remove('show');
        });
    }
});

// Close dropdowns on table scroll
window.addEventListener('scroll', function() {
    document.querySelectorAll('.action-dropdown-menu').forEach(m => {
        m.classList.remove('show');
    });
}, true);

// Open Modal Void Surat Jalan
window.openVoidModal = function(pengeluaranId, pengeluaranNo) {
    const modal = document.getElementById('modalVoidPengeluaran');
    if (!modal) return;

    const form = document.getElementById('formVoidPengeluaran');
    if (form) {
        form.action = `/penjualan/pengeluaran/${pengeluaranId}/void`;
    }

    const noSpan = document.getElementById('voidSuratJalanNoSpan');
    if (noSpan) {
        noSpan.textContent = pengeluaranNo;
    }

    const reasonInput = document.getElementById('voidReasonInput');
    if (reasonInput) {
        reasonInput.value = '';
    }

    modal.style.display = 'flex';
};

// Close Modal Void
window.closeVoidModal = function() {
    const modal = document.getElementById('modalVoidPengeluaran');
    if (modal) {
        modal.style.display = 'none';
    }
};

// Switch Tab (Antrean PO Siap Kirim vs Daftar Dokumen Surat Jalan)
window.switchSjTab = function(tabName) {
    const paneSj = document.getElementById('paneSj');
    const paneAntrean = document.getElementById('paneAntrean');
    const btnSj = document.getElementById('tabBtnSj');
    const btnAntrean = document.getElementById('tabBtnAntrean');

    if (!paneSj || !paneAntrean) return;

    if (tabName === 'sj') {
        paneSj.style.display = 'block';
        paneAntrean.style.display = 'none';
        if (btnSj) btnSj.classList.add('active');
        if (btnAntrean) btnAntrean.classList.remove('active');

        // Update URL query string with ?tab=sj
        const url = new URL(window.location);
        url.searchParams.set('tab', 'sj');
        window.history.replaceState({}, '', url);
    } else {
        paneSj.style.display = 'none';
        paneAntrean.style.display = 'block';
        if (btnSj) btnSj.classList.remove('active');
        if (btnAntrean) btnAntrean.classList.add('active');

        // Default tab is antrean, so remove tab parameter
        const url = new URL(window.location);
        url.searchParams.delete('tab');
        window.history.replaceState({}, '', url);
    }
};


