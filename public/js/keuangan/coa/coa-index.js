/**
 * JavaScript Logika Bagan Akun (COA) - ERP PT Mirasa
 * Path: public/js/keuangan/coa/coa-index.js
 */

let activeDropdownMenu = null;
let currentCategoryFilter = 'ALL';

document.addEventListener('DOMContentLoaded', function () {
    // Tutup dropdown saat klik di luar
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.btn-action-trigger') && !e.target.closest('.action-dropdown-menu')) {
            closeAllActionDropdowns();
        }
    });

    // Close on Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeAllActionDropdowns();
            closeCreateModal();
            closeEditModal();
            closeDeleteModal();
        }
    });

    // Reposition on scroll / resize
    window.addEventListener('scroll', closeAllActionDropdowns, true);
    window.addEventListener('resize', closeAllActionDropdowns);

    // Filter pencarian tabel instan
    const searchInput = document.getElementById('coaSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', filterCoaTable);
    }
});

/* ========================================================
   SMART ACTION DROPDOWN (Fixed Viewport Positioning)
   ======================================================== */
window.toggleSmartActionDropdown = function (btn, event, menuId) {
    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }

    const menu = document.getElementById(menuId);
    if (!menu) return;

    if (activeDropdownMenu === menu) {
        closeAllActionDropdowns();
        return;
    }

    closeAllActionDropdowns();

    const rect = btn.getBoundingClientRect();
    const menuWidth = 150;
    const menuHeight = 100;

    let left = rect.right - menuWidth;
    let top = rect.bottom + 4;

    // Boundary check viewport
    if (left < 10) left = rect.left;
    if (top + menuHeight > window.innerHeight) {
        top = rect.top - menuHeight - 4;
    }

    menu.style.position = 'fixed';
    menu.style.top = `${top}px`;
    menu.style.left = `${left}px`;
    menu.style.display = 'block';

    activeDropdownMenu = menu;
};

window.closeAllActionDropdowns = function () {
    if (activeDropdownMenu) {
        activeDropdownMenu.style.display = 'none';
        activeDropdownMenu = null;
    }
    document.querySelectorAll('.action-dropdown-menu').forEach(m => {
        m.style.display = 'none';
    });
};

/* ========================================================
   FILTER KATEGORI & SEARCH REALTIME
   ======================================================== */
window.switchCoaCategory = function (category) {
    currentCategoryFilter = category;

    // Update active tab button style
    document.querySelectorAll('.coa-tab-btn').forEach(btn => {
        const cat = btn.getAttribute('data-category');
        btn.classList.toggle('active', cat === category);
    });

    filterCoaTable();
};

function filterCoaTable() {
    const searchVal = (document.getElementById('coaSearchInput')?.value || '').toLowerCase().trim();
    const rows = document.querySelectorAll('.coa-table-row');
    let visibleCount = 0;

    rows.forEach(row => {
        const code = (row.getAttribute('data-code') || '').toLowerCase();
        const name = (row.getAttribute('data-name') || '').toLowerCase();
        const category = row.getAttribute('data-category') || '';
        const isKasBank = row.getAttribute('data-kas-bank') === '1';

        const matchSearch = (!searchVal || code.includes(searchVal) || name.includes(searchVal));

        let matchCategory = false;
        if (currentCategoryFilter === 'ALL') {
            matchCategory = true;
        } else if (currentCategoryFilter === 'KAS_BANK') {
            matchCategory = isKasBank;
        } else {
            matchCategory = (category === currentCategoryFilter);
        }

        if (matchSearch && matchCategory) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    const emptyRow = document.getElementById('coaEmptyRow');
    if (emptyRow) {
        emptyRow.style.display = visibleCount === 0 ? '' : 'none';
    }
}

/* ========================================================
   MODAL CREATE
   ======================================================== */
window.openCreateModal = function () {
    closeAllActionDropdowns();
    const modal = document.getElementById('modalCreateCoa');
    if (!modal) return;
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    // Reset input
    const form = document.getElementById('formCreateCoa');
    if (form) form.reset();

    setTimeout(() => {
        document.getElementById('create_kode_akun')?.focus();
    }, 100);
};

window.closeCreateModal = function () {
    const modal = document.getElementById('modalCreateCoa');
    if (!modal) return;
    modal.style.display = 'none';
    document.body.style.overflow = '';
};

/* ========================================================
   MODAL EDIT
   ======================================================== */
window.openEditModal = function (id, kode, nama, kategori, subKategori, posisi, saldoNormal, saldoAwal, isKasBank, keterangan) {
    closeAllActionDropdowns();
    const modal = document.getElementById('modalEditCoa');
    if (!modal) return;

    const form = document.getElementById('formEditCoa');
    if (form) {
        form.action = `${window.appConfig.baseUrl}/keuangan/coa/${id}`;
    }

    document.getElementById('edit_coa_id').value = id;
    document.getElementById('edit_kode_akun').value = kode || '';
    document.getElementById('edit_nama_akun').value = nama || '';
    document.getElementById('edit_kategori_akun').value = kategori || 'AKTIVA';
    document.getElementById('edit_sub_kategori').value = subKategori || '';
    document.getElementById('edit_posisi_keuangan').value = posisi || 'NERACA';
    document.getElementById('edit_saldo_normal').value = saldoNormal || 'DEBET';
    document.getElementById('edit_saldo_awal').value = parseFloat(saldoAwal || 0);
    document.getElementById('edit_is_kas_bank').checked = (isKasBank == 1 || isKasBank === true);
    document.getElementById('edit_keterangan').value = keterangan || '';

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
};

window.closeEditModal = function () {
    const modal = document.getElementById('modalEditCoa');
    if (!modal) return;
    modal.style.display = 'none';
    document.body.style.overflow = '';
};

/* ========================================================
   MODAL DELETE
   ======================================================== */
window.openDeleteModal = function (id, kode, nama) {
    closeAllActionDropdowns();
    const modal = document.getElementById('modalDeleteCoa');
    if (!modal) return;

    const form = document.getElementById('formDeleteCoa');
    if (form) {
        form.action = `${window.appConfig.baseUrl}/keuangan/coa/${id}`;
    }

    document.getElementById('deleteAccountName').textContent = `${kode} - ${nama}`;

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
};

window.closeDeleteModal = function () {
    const modal = document.getElementById('modalDeleteCoa');
    if (!modal) return;
    modal.style.display = 'none';
    document.body.style.overflow = '';
};

/* ========================================================
   SMART DEFAULTS KETIKA PILIH KATEGORI AKUN
   ======================================================== */
window.onKategoriChange = function (selectEl, prefix) {
    const val = selectEl.value;
    const posisiEl = document.getElementById(`${prefix}_posisi_keuangan`);
    const saldoNormalEl = document.getElementById(`${prefix}_saldo_normal`);
    const isKasBankWrap = document.getElementById(`${prefix}_kas_bank_group`);

    if (val === 'AKTIVA') {
        if (posisiEl) posisiEl.value = 'NERACA';
        if (saldoNormalEl) saldoNormalEl.value = 'DEBET';
        if (isKasBankWrap) isKasBankWrap.style.display = 'block';
    } else if (val === 'PASIVA') {
        if (posisiEl) posisiEl.value = 'NERACA';
        if (saldoNormalEl) saldoNormalEl.value = 'KREDIT';
        if (isKasBankWrap) isKasBankWrap.style.display = 'none';
    } else if (val === 'MODAL') {
        if (posisiEl) posisiEl.value = 'NERACA';
        if (saldoNormalEl) saldoNormalEl.value = 'KREDIT';
        if (isKasBankWrap) isKasBankWrap.style.display = 'none';
    } else if (val === 'PENDAPATAN') {
        if (posisiEl) posisiEl.value = 'LABA/RUGI';
        if (saldoNormalEl) saldoNormalEl.value = 'KREDIT';
        if (isKasBankWrap) isKasBankWrap.style.display = 'none';
    } else if (val === 'HPP' || val === 'BEBAN') {
        if (posisiEl) posisiEl.value = 'LABA/RUGI';
        if (saldoNormalEl) saldoNormalEl.value = 'DEBET';
        if (isKasBankWrap) isKasBankWrap.style.display = 'none';
    }
};
