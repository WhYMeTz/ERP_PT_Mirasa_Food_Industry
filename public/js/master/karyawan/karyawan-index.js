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
 * Escape teks agar aman disisipkan ke innerHTML
 */
function escapeHtml(value) {
    const div = document.createElement('div');
    div.textContent = value ?? '';
    return div.innerHTML;
}

function getKaryawanBaseUrl() {
    return (window.appConfig && window.appConfig.karyawanBaseUrl)
        ? window.appConfig.karyawanBaseUrl
        : '/master-karyawan';
}

/**
 * Buka modal edit karyawan dan isi datanya (data berupa object dari Blade)
 */
function editKaryawan(data) {
    closeAllActionDropdowns();

    const form = document.getElementById('formEditKaryawan');
    if (form) {
        form.action = getKaryawanBaseUrl() + '/' + data.karyawan_id;
    }

    const fields = {
        edit_nik: data.nik,
        edit_id_absensi: data.id_absensi,
        edit_karyawan_nm: data.karyawan_nm,
        edit_departemen_cd: data.departemen_cd,
        edit_jabatan_nm: data.jabatan_nm,
        edit_kelompok: data.kelompok,
        edit_shift: data.shift,
        edit_status_kerja: data.status_kerja || 'Tetap',
        edit_tgl_masuk: data.tgl_masuk,
        edit_telepon_no: data.telepon_no,
        edit_email: data.email,
        edit_bank_name: data.bank_name,
        edit_no_rekening: data.no_rekening,
        edit_alamat_txt: data.alamat_txt,
    };

    Object.entries(fields).forEach(([id, value]) => {
        const el = document.getElementById(id);
        if (el) el.value = value ?? '';
    });

    openModal('modalEditKaryawan');
}

/**
 * Buka modal konfirmasi hapus karyawan
 */
function openDeleteKaryawanModal(data) {
    closeAllActionDropdowns();

    const form = document.getElementById('formDeleteKaryawan');
    if (form) {
        form.action = getKaryawanBaseUrl() + '/' + data.karyawan_id;
    }

    const elInfo = document.getElementById('deleteKaryawanInfo');
    if (elInfo) {
        elInfo.innerHTML = `<strong>${escapeHtml(data.karyawan_nm)}</strong> <span style="font-family: monospace; color: #0284c7;">(${escapeHtml(data.nik)})</span><br><span style="color: #64748b; font-size: 0.8rem;">Departemen: ${escapeHtml(data.departemen_nm)} | Divisi: ${escapeHtml(data.jabatan_nm)}</span>`;
    }

    openModal('modalDeleteKaryawan');
}

/**
 * Reset NIK otomatis di modal tambah
 */
function resetNikOtomatis() {
    const defaultNik = (window.appConfig && window.appConfig.nextNIK) ? window.appConfig.nextNIK : '';
    const elNik = document.getElementById('create_nik');
    if (elNik && defaultNik) {
        elNik.value = defaultNik;
    }
}

// Tutup dropdown aksi dengan tombol ESC (modal sudah ditangani layout)
window.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeAllActionDropdowns();
});
