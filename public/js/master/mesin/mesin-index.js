/**
 * =========================================================================
 * ERP PT MIRASA FOOD INDUSTRY - MASTER DATA MESIN PABRIK INDEX JS
 * =========================================================================
 */

let activeDropdownMenu = null;
let activeTriggerButton = null;

/**
 * Toggle Smart Action Dropdown dengan perhitungan posisi viewport dinamis (AGENTS.md)
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
 * Hitung posisi dropdown secara dinamis agar tidak terpotong oleh overflow scroll tabel
 */
function positionActionDropdown(button, menu) {
    if (!button || !menu) return;
    const rect = button.getBoundingClientRect();
    const menuWidth = menu.offsetWidth || 190;
    const menuHeight = menu.offsetHeight || 135;
    const viewportHeight = window.innerHeight;
    const viewportWidth = window.innerWidth;

    const spaceBelow = viewportHeight - rect.bottom;
    const spaceAbove = rect.top;

    if (spaceBelow < menuHeight && spaceAbove > spaceBelow) {
        menu.style.top = `${rect.top - menuHeight - 4}px`;
    } else {
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
 * Tutup semua dropdown aksi yang sedang terbuka
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
 * Modal Dialog Handlers
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
 * Isi form modal Edit Mesin dan buka modal
 */
function openEditMesin(data) {
    closeAllActionDropdowns();

    const form = document.getElementById('formEditMesin');
    if (!form || !data) return;

    // Set action URL
    form.action = `${window.appConfig.baseUrl}/master-mesin/${data.mesin_id}`;

    document.getElementById('edit_mesin_id').value = data.mesin_id;
    document.getElementById('edit_mesin_cd').value = data.mesin_cd || '';
    document.getElementById('edit_mesin_nm').value = data.mesin_nm || '';
    document.getElementById('edit_kategori_mesin').value = data.kategori_mesin || 'PRODUKSI';
    document.getElementById('edit_jenis_proses').value = data.jenis_proses || '';
    document.getElementById('edit_kapasitas_per_jam').value = data.kapasitas_per_jam !== null ? data.kapasitas_per_jam : '';
    document.getElementById('edit_satuan_kapasitas').value = data.satuan_kapasitas || 'KG/JAM';
    document.getElementById('edit_tipe_energi').value = data.tipe_energi || '';
    document.getElementById('edit_status_kondisi').value = data.status_kondisi || 'SIAP';
    document.getElementById('edit_keterangan').value = data.keterangan || '';

    // Status aktif
    const activeCheckbox = document.getElementById('edit_active_st');
    if (activeCheckbox) {
        activeCheckbox.checked = !!data.active_st;
    }

    openModal('modalEditMesin');
}

/**
 * Konfirmasi Hapus / Nonaktifkan Mesin
 */
function confirmDeleteMesin(url, cd, nm) {
    closeAllActionDropdowns();

    const form = document.getElementById('formDeleteMesin');
    const cdEl = document.getElementById('deleteMesinCd');
    const nmEl = document.getElementById('deleteMesinNm');

    if (form) form.action = url;
    if (cdEl) cdEl.textContent = `KODE: ${cd}`;
    if (nmEl) nmEl.textContent = nm;

    openModal('modalDeleteMesin');
}

/**
 * Dinamika pilihan kategori mesin (PRODUKSI vs PACKAGING)
 * Menyesuaikan opsi default satuan kapasitas dan jenis proses
 */
function onKategoriMesinChanged(kategoriVal, prefix) {
    const satuanEl = document.getElementById(`${prefix}_satuan_kapasitas`);
    const jenisEl = document.getElementById(`${prefix}_jenis_proses`);
    if (!satuanEl) return;

    if (kategoriVal === 'PACKAGING') {
        if (!satuanEl.value || satuanEl.value === 'KG/JAM') {
            satuanEl.value = 'KEMASAN/MENIT';
        }
    } else {
        if (!satuanEl.value || satuanEl.value === 'KEMASAN/MENIT' || satuanEl.value === 'KARTON/MENIT') {
            satuanEl.value = 'KG/JAM';
        }
    }
}

// Global Event Listeners
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

// Expose fungsi ke scope global window
window.toggleSmartActionDropdown = toggleSmartActionDropdown;
window.closeAllActionDropdowns = closeAllActionDropdowns;
window.openModal = openModal;
window.closeModal = closeModal;
window.openEditMesin = openEditMesin;
window.confirmDeleteMesin = confirmDeleteMesin;
window.onKategoriMesinChanged = onKategoriMesinChanged;

