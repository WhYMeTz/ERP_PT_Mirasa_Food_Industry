/**
 * =========================================================================
 * ERP PT MIRASA - MASTER DATA KATEGORI PENGELUARAN JAVASCRIPT
 * WaveDash Clean Enterprise Minimalism
 * =========================================================================
 */

let activeDropdownMenu = null;
let activeTriggerButton = null;
let isManualKodeEdited = false;

/**
 * Buka modal berdasarkan ID elemen
 */
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    // Focus input pertama
    setTimeout(() => {
        const firstInput = modal.querySelector('input:not([type="hidden"]), select, textarea');
        if (firstInput) firstInput.focus();
    }, 100);
}

/**
 * Tutup modal berdasarkan ID elemen
 */
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;
    modal.style.display = 'none';
    document.body.style.overflow = '';
}

/**
 * Tutup modal jika user klik di backdrop luar dialog
 */
document.addEventListener('click', function(e) {
    if (e.target && e.target.classList.contains('modal-overlay')) {
        e.target.style.display = 'none';
        document.body.style.overflow = '';
    }
});

/**
 * Tutup modal dengan tombol Escape
 */
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay').forEach(modal => {
            modal.style.display = 'none';
        });
        document.body.style.overflow = '';
        closeAllActionDropdowns();
    }
});

/**
 * =========================================================================
 * SMART ACTION DROPDOWN (AGENTS.md MANDATORY)
 * =========================================================================
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

function positionActionDropdown(button, menu) {
    if (!button || !menu) return;
    const rect = button.getBoundingClientRect();
    const menuWidth = menu.offsetWidth || 185;
    const menuHeight = menu.offsetHeight || 130;
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

function closeAllActionDropdowns() {
    document.querySelectorAll('.action-dropdown-menu').forEach(m => m.style.display = 'none');
    document.querySelectorAll('.btn-action-trigger').forEach(b => b.classList.remove('active'));
    activeDropdownMenu = null;
    activeTriggerButton = null;
}

window.addEventListener('click', function(e) {
    if (!e.target.closest('.action-dropdown-menu') && !e.target.closest('.btn-action-trigger')) {
        closeAllActionDropdowns();
    }
});

window.addEventListener('scroll', function() {
    if (activeDropdownMenu && activeTriggerButton) {
        positionActionDropdown(activeTriggerButton, activeDropdownMenu);
    }
}, true);

window.addEventListener('resize', closeAllActionDropdowns);

/**
 * =========================================================================
 * AUTO GENERATE KODE KATEGORI
 * =========================================================================
 */
function handleNamaKategoriInput(event) {
    const namaInput = event.target;
    const kodeInput = document.getElementById('create_kode_kategori');
    if (!kodeInput) return;

    if (!isManualKodeEdited || kodeInput.value.trim() === '') {
        const val = namaInput.value.trim();
        // Convert to uppercase slug with underscore: "Listrik & Air PLN" -> "LISTRIK_AIR_PLN"
        const slug = val
            .toUpperCase()
            .replace(/[^A-Z0-9\s]/g, ' ')
            .trim()
            .replace(/\s+/g, '_')
            .substring(0, 30);
        kodeInput.value = slug;
    }
}

function handleManualKodeChange() {
    const kodeInput = document.getElementById('create_kode_kategori');
    if (kodeInput && kodeInput.value.trim() !== '') {
        isManualKodeEdited = true;
    }
}

/**
 * =========================================================================
 * MODAL EDIT & DELETE HANDLERS
 * =========================================================================
 */
async function openEditKategoriModal(id) {
    closeAllActionDropdowns();

    const baseUrl = (window.appConfig && window.appConfig.baseUrl) 
        ? window.appConfig.baseUrl 
        : '/master-kategori-pengeluaran';

    try {
        const response = await fetch(`${baseUrl}/${id}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok) {
            throw new Error('Gagal mengambil data kategori.');
        }

        const res = await response.json();
        const data = res.data;

        document.getElementById('edit_nama_kategori').value = data.nama_kategori || '';
        document.getElementById('edit_kode_kategori').value = data.kode_kategori || '';
        document.getElementById('edit_coa_id_default').value = data.coa_id_default || '';
        document.getElementById('edit_keterangan').value = data.keterangan || '';
        document.getElementById('edit_active_st').checked = Boolean(data.active_st);

        const formEdit = document.getElementById('formEditKategori');
        if (formEdit) {
            formEdit.action = `${baseUrl}/${id}`;
        }

        openModal('modalEditKategori');
    } catch (err) {
        console.error(err);
        alert('Terjadi kesalahan saat memuat data kategori: ' + err.message);
    }
}

function openDeleteKategoriModal(id, kode, nama) {
    closeAllActionDropdowns();

    const baseUrl = (window.appConfig && window.appConfig.baseUrl) 
        ? window.appConfig.baseUrl 
        : '/master-kategori-pengeluaran';

    document.getElementById('delete_kategori_kode').textContent = kode;
    document.getElementById('delete_kategori_nama').textContent = nama;

    const formDelete = document.getElementById('formDeleteKategori');
    if (formDelete) {
        formDelete.action = `${baseUrl}/${id}`;
    }

    openModal('modalDeleteKategori');
}

function submitToggleStatus(id) {
    closeAllActionDropdowns();

    const baseUrl = (window.appConfig && window.appConfig.baseUrl) 
        ? window.appConfig.baseUrl 
        : '/master-kategori-pengeluaran';

    const form = document.getElementById('formToggleStatus');
    if (form) {
        form.action = `${baseUrl}/${id}/toggle-status`;
        form.submit();
    }
}

// Inisialisasi Event Listener saat DOM Content Loaded
document.addEventListener('DOMContentLoaded', function() {
    const namaInput = document.getElementById('create_nama_kategori');
    if (namaInput) {
        namaInput.addEventListener('input', handleNamaKategoriInput);
    }

    const kodeInput = document.getElementById('create_kode_kategori');
    if (kodeInput) {
        kodeInput.addEventListener('change', handleManualKodeChange);
    }
});
