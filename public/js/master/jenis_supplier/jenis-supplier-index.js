/**
 * =========================================================================
 * ERP PT MIRASA - MASTER DATA JENIS SUPPLIER INDEX JAVASCRIPT
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
    const menuWidth = menu.offsetWidth || 180;
    const menuHeight = menu.offsetHeight || 120;
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
 * Tutup seluruh dropdown aksi yang sedang aktif
 */
function closeAllActionDropdowns() {
    document.querySelectorAll('.action-dropdown-menu').forEach(m => m.style.display = 'none');
    document.querySelectorAll('.btn-action-trigger').forEach(b => b.classList.remove('active'));
    activeDropdownMenu = null;
    activeTriggerButton = null;
}

// Tutup dropdown jika user klik di luar area menu
window.addEventListener('click', function(e) {
    if (!e.target.closest('.action-dropdown-menu') && !e.target.closest('.btn-action-trigger')) {
        closeAllActionDropdowns();
    }
});

// Sesuaikan posisi jika window discroll
window.addEventListener('scroll', function() {
    if (activeDropdownMenu && activeTriggerButton) {
        positionActionDropdown(activeTriggerButton, activeDropdownMenu);
    }
}, true);

// Tutup dropdown jika ukuran jendela diubah
window.addEventListener('resize', closeAllActionDropdowns);

/**
 * Buka modal edit jenis supplier dan isi formulirnya
 */
function editJenisSupplier(id, kode, nama) {
    closeAllActionDropdowns();
    const elCd = document.getElementById('edit_jenis_supplier_cd');
    const elNm = document.getElementById('edit_jenis_supplier_nm');
    const form = document.getElementById('formEditJenisSupplier');

    if (elCd) elCd.value = kode;
    if (elNm) elNm.value = nama;

    const baseUrl = (window.masterJenisSupplierConfig && window.masterJenisSupplierConfig.baseUrl) 
        ? window.masterJenisSupplierConfig.baseUrl 
        : '/master-jenis-supplier';

    if (form) {
        form.action = baseUrl.replace(/\/$/, '') + '/' + id;
    }

    if (typeof openModal === 'function') {
        openModal('modalEditJenisSupplier');
    }
}

/**
 * Buka modal konfirmasi nonaktifkan / hapus jenis supplier
 */
function openDeleteJenisSupplierModal(id, kode, nama) {
    closeAllActionDropdowns();
    const cdElem = document.getElementById('deleteJenisSupplierCd');
    const nmElem = document.getElementById('deleteJenisSupplierNm');
    const formElem = document.getElementById('formDeleteJenisSupplier');

    if (cdElem) cdElem.innerText = kode;
    if (nmElem) nmElem.innerText = nama;

    const baseUrl = (window.masterJenisSupplierConfig && window.masterJenisSupplierConfig.baseUrl) 
        ? window.masterJenisSupplierConfig.baseUrl 
        : '/master-jenis-supplier';

    if (formElem) {
        formElem.action = baseUrl.replace(/\/$/, '') + '/' + id;
    }

    if (typeof openModal === 'function') {
        openModal('modalDeleteJenisSupplier');
    }
}

// Expose fungsi ke window
window.toggleSmartActionDropdown = toggleSmartActionDropdown;
window.closeAllActionDropdowns = closeAllActionDropdowns;
window.editJenisSupplier = editJenisSupplier;
window.openDeleteJenisSupplierModal = openDeleteJenisSupplierModal;
