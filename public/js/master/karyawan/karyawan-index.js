/**
 * =========================================================================
 * ERP PT MIRASA - MASTER DATA KARYAWAN INDEX JAVASCRIPT
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
 * Buka modal edit karyawan dan isi datanya
 */
function editKaryawan(id, nik, nama, departemen, jabatan, telp, email, alamat) {
    closeAllActionDropdowns();

    const baseUrl = (window.appConfig && window.appConfig.karyawanBaseUrl) 
        ? window.appConfig.karyawanBaseUrl 
        : '/master-karyawan';

    const form = document.getElementById('formEditKaryawan');
    if (form) {
        form.action = baseUrl + '/' + id;
    }

    const elNik = document.getElementById('edit_nik');
    const elNama = document.getElementById('edit_karyawan_nm');
    const elDept = document.getElementById('edit_departemen_cd');
    const elJabatan = document.getElementById('edit_jabatan_nm');
    const elTelp = document.getElementById('edit_telepon_no');
    const elEmail = document.getElementById('edit_email');
    const elAlamat = document.getElementById('edit_alamat_txt');

    if (elNik) elNik.value = nik || '';
    if (elNama) elNama.value = nama || '';
    if (elDept) elDept.value = departemen || '';
    if (elJabatan) elJabatan.value = jabatan || '';
    if (elTelp) elTelp.value = telp || '';
    if (elEmail) elEmail.value = email || '';
    if (elAlamat) elAlamat.value = alamat || '';

    openModal('modalEditKaryawan');
}

/**
 * Buka modal konfirmasi hapus karyawan
 */
function openDeleteKaryawanModal(id, nik, nama, departemen, jabatan) {
    closeAllActionDropdowns();

    const baseUrl = (window.appConfig && window.appConfig.karyawanBaseUrl) 
        ? window.appConfig.karyawanBaseUrl 
        : '/master-karyawan';

    const form = document.getElementById('formDeleteKaryawan');
    if (form) {
        form.action = baseUrl + '/' + id;
    }

    const elInfo = document.getElementById('deleteKaryawanInfo');
    if (elInfo) {
        elInfo.innerHTML = `<strong>${nama}</strong> <span style="font-family: monospace; color: #0284c7;">(${nik})</span><br><span style="color: #64748b; font-size: 0.8rem;">Departemen: ${departemen} | Jabatan: ${jabatan}</span>`;
    }

    openModal('modalDeleteKaryawan');
}

/**
 * Reset NIK otomatis di modal tambah
 */
function resetNikOtomatis() {
    const defaultNik = (window.appConfig && window.appConfig.nextNik) ? window.appConfig.nextNik : '';
    const elNik = document.getElementById('create_nik');
    if (elNik && defaultNik) {
        elNik.value = defaultNik;
    }
}

/**
 * Kontrol modal global
 */
function openModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

// Menutup modal dengan tombol ESC
window.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-backdrop.active').forEach(modal => {
            modal.classList.remove('active');
        });
        document.body.style.overflow = '';
        closeAllActionDropdowns();
    }
});
