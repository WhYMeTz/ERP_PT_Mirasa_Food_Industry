/**
 * =========================================================================
 * ERP PT MIRASA - TERIMA BARANG (GRN) INDEX JAVASCRIPT
 * =========================================================================
 */

let activeDropdownMenu = null;
let activeTriggerButton = null;

function toggleSmartActionDropdown(button, event, menuId) {
    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }

    const targetMenu = document.getElementById(menuId);
    if (!targetMenu) return;

    // Jika dropdown yang sama sedang terbuka, tutup
    if (activeDropdownMenu === targetMenu && targetMenu.style.display === 'block') {
        closeAllActionDropdowns();
        return;
    }

    // Tutup dropdown lain yang sedang terbuka
    closeAllActionDropdowns();

    // Tampilkan menu dan hitung posisinya secara pintar (Fixed Viewport)
    targetMenu.style.display = 'block';
    activeDropdownMenu = targetMenu;
    activeTriggerButton = button;
    button.classList.add('active');

    positionActionDropdown(button, targetMenu);
}

function positionActionDropdown(button, menu) {
    if (!button || !menu) return;

    const rect = button.getBoundingClientRect();
    const menuWidth = menu.offsetWidth || 195;
    const menuHeight = menu.offsetHeight || 150;
    const viewportHeight = window.innerHeight;
    const viewportWidth = window.innerWidth;

    // Cek ruang vertikal: jika ruang bawah tidak cukup, letakkan di atas tombol (Dropup)
    const spaceBelow = viewportHeight - rect.bottom;
    const spaceAbove = rect.top;

    if (spaceBelow < menuHeight && spaceAbove > spaceBelow) {
        // Dropup (di atas tombol)
        menu.style.top = `${rect.top - menuHeight - 4}px`;
    } else {
        // Dropdown (di bawah tombol)
        menu.style.top = `${rect.bottom + 4}px`;
    }

    // Cek ruang horizontal: posisikan rata kanan tombol
    let leftPos = rect.right - menuWidth;
    if (leftPos < 8) {
        leftPos = 8;
    }
    if (leftPos + menuWidth > viewportWidth - 8) {
        leftPos = viewportWidth - menuWidth - 8;
    }

    menu.style.left = `${leftPos}px`;
}

function closeAllActionDropdowns() {
    document.querySelectorAll('.action-dropdown-menu').forEach(menu => {
        menu.style.display = 'none';
    });
    if (activeTriggerButton) {
        activeTriggerButton.classList.remove('active');
        activeTriggerButton = null;
    }
    activeDropdownMenu = null;
}

// Handler Modal Delete Terima
function openDeleteTerimaModal(terimaId, terimaNo, supplierNm) {
    closeAllActionDropdowns();
    const modal = document.getElementById('modal-delete-terima');
    const noEl = document.getElementById('delete-terima-no');
    const supEl = document.getElementById('delete-terima-supplier');
    const formEl = document.getElementById('form-delete-terima');

    if (modal && noEl && formEl) {
        noEl.textContent = terimaNo || '-';
        if (supEl) supEl.textContent = supplierNm ? `Supplier: ${supplierNm}` : '';
        formEl.action = `/gudang/terima/${terimaId}`;
        modal.style.display = 'flex';
    }
}

function closeDeleteTerimaModal() {
    const modal = document.getElementById('modal-delete-terima');
    if (modal) modal.style.display = 'none';
}

// Global Event Listeners
document.addEventListener('click', function(e) {
    if (activeDropdownMenu && !activeDropdownMenu.contains(e.target)) {
        closeAllActionDropdowns();
    }
});

window.addEventListener('scroll', function() {
    if (activeDropdownMenu && activeTriggerButton) {
        positionActionDropdown(activeTriggerButton, activeDropdownMenu);
    }
}, true);

window.addEventListener('resize', function() {
    if (activeDropdownMenu && activeTriggerButton) {
        positionActionDropdown(activeTriggerButton, activeDropdownMenu);
    }
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAllActionDropdowns();
        closeDeleteTerimaModal();
        const importModal = document.getElementById('modal-import-excel');
        if (importModal) importModal.style.display = 'none';
    }
});

// Import Excel submit listener
document.addEventListener('DOMContentLoaded', function() {
    const importForm = document.querySelector('#modal-import-excel form');
    if (importForm) {
        importForm.addEventListener('submit', function() {
            const btn = document.getElementById('btn-import-submit');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<svg class="animate-spin" width="16" height="16" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-dasharray="30 10"/></svg> Memproses...';
            }
        });
    }
});
