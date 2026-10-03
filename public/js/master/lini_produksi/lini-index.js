/**
 * =========================================================================
 * ERP PT MIRASA - MASTER DATA LINI PRODUKSI INDEX JAVASCRIPT
 * =========================================================================
 */

let activeDropdownMenu = null;
let activeTriggerButton = null;

/**
 * Toggle dropdown Aksi per baris dengan Smart Viewport Positioning
 */
function toggleSmartActionDropdown(button, event, menuId) {
    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }
    const targetMenu = document.getElementById(menuId);
    if (!targetMenu) return;

    if (activeDropdownMenu === targetMenu && targetMenu.style.display === 'block') {
        closeAllActionDropdowns();
        return;
    }

    closeAllActionDropdowns();

    targetMenu.style.display = 'block';
    activeDropdownMenu = targetMenu;
    activeTriggerButton = button;
    button.classList.add('active');

    positionActionDropdown(button, targetMenu);
}

/**
 * Hitung posisi dropdown secara dinamis agar tidak terpotong tepi layar/tabel
 */
function positionActionDropdown(button, menu) {
    if (!button || !menu) return;
    const rect = button.getBoundingClientRect();
    const menuWidth = menu.offsetWidth || 190;
    const menuHeight = menu.offsetHeight || 130;
    const viewportHeight = window.innerHeight;
    const viewportWidth = window.innerWidth;

    const spaceBelow = viewportHeight - rect.bottom;
    const spaceAbove = rect.top;

    if (spaceBelow < menuHeight && spaceAbove > spaceBelow) {
        // Tampilkan di atas tombol (Dropup)
        menu.style.top = `${rect.top - menuHeight - 4}px`;
    } else {
        // Tampilkan di bawah tombol (Dropdown)
        menu.style.top = `${rect.bottom + 4}px`;
    }

    let leftPos = rect.right - menuWidth;
    if (leftPos < 10) leftPos = 10;
    if (leftPos + menuWidth > viewportWidth - 10) {
        leftPos = viewportWidth - menuWidth - 10;
    }
    menu.style.left = `${leftPos}px`;
}

/**
 * Menutup semua dropdown aksi yang sedang aktif
 */
function closeAllActionDropdowns() {
    if (activeDropdownMenu) {
        activeDropdownMenu.style.display = 'none';
        activeDropdownMenu = null;
    }
    if (activeTriggerButton) {
        activeTriggerButton.classList.remove('active');
        activeTriggerButton = null;
    }
}

/**
 * Modal Generic Functions
 */
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
}

/**
 * Konfirmasi Nonaktifkan / Hapus Lini Produksi
 */
function confirmDeleteLini(url, cd, nm) {
    closeAllActionDropdowns();
    const form = document.getElementById('formDeleteLini');
    const cdEl = document.getElementById('deleteLiniCd');
    const nmEl = document.getElementById('deleteLiniNm');

    if (form) form.action = url;
    if (cdEl) cdEl.textContent = `KODE: ${cd}`;
    if (nmEl) nmEl.textContent = nm;

    openModal('modalDeleteLini');
}

// Event Listeners Global
document.addEventListener('click', function (e) {
    if (!e.target.closest('.action-dropdown-menu') && !e.target.closest('.btn-action-trigger')) {
        closeAllActionDropdowns();
    }
});

window.addEventListener('resize', closeAllActionDropdowns);
window.addEventListener('scroll', closeAllActionDropdowns, true);

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        closeAllActionDropdowns();
        document.querySelectorAll('.modal-backdrop.show').forEach(m => m.classList.remove('show'));
        document.body.style.overflow = '';
    }
});
