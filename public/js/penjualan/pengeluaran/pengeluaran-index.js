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

// Toggle Ready-to-Ship Queue List display
window.toggleQueueList = function() {
    const list = document.getElementById('sjQueueList');
    const label = document.getElementById('sjQueueToggleText');
    const icon = document.getElementById('sjQueueToggleIcon');
    if (!list) return;

    const isHidden = list.style.display === 'none';
    if (isHidden) {
        list.style.display = 'grid';
        if (label) label.textContent = 'Sembunyikan';
        if (icon) icon.style.transform = 'rotate(180deg)';
    } else {
        list.style.display = 'none';
        if (label) label.textContent = 'Tampilkan';
        if (icon) icon.style.transform = 'rotate(0deg)';
    }
};

