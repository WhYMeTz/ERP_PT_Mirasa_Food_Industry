/**
 * JavaScript Modul Rekap Produksi Harian & HPP
 * Path: public/js/produksi/produksi-index.js
 * PT Mirasa Food Industry
 */

let activeDropdownMenu = null;

/**
 * Smart Action Dropdown dengan Fixed Positioning
 * Mencegah menu terpotong overflow scroll horizontal tabel
 */
function toggleSmartActionDropdown(button, event, menuId) {
    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }

    const menu = document.getElementById(menuId);
    if (!menu) return;

    // Jika menu ini sudah terbuka, tutup
    if (activeDropdownMenu === menu) {
        menu.classList.remove('show');
        activeDropdownMenu = null;
        return;
    }

    // Tutup menu lain yang sedang aktif
    if (activeDropdownMenu) {
        activeDropdownMenu.classList.remove('show');
    }

    // Hitung posisi tombol di layar
    const rect = button.getBoundingClientRect();
    const menuWidth = 190;
    const windowWidth = window.innerWidth;
    const windowHeight = window.innerHeight;

    // Tentukan posisi horizontal (utamakan rata kanan tombol)
    let left = rect.right - menuWidth;
    if (left < 10) left = 10;
    if (left + menuWidth > windowWidth - 10) {
        left = windowWidth - menuWidth - 10;
    }

    // Tentukan posisi vertikal (bawah tombol, atau atas jika mepet bawah)
    let top = rect.bottom + 4;
    const estimatedHeight = 120;
    if (top + estimatedHeight > windowHeight - 10 && rect.top > estimatedHeight) {
        top = rect.top - estimatedHeight - 4;
    }

    menu.style.top = top + 'px';
    menu.style.left = left + 'px';
    menu.classList.add('show');
    activeDropdownMenu = menu;
}

// Tutup dropdown jika klik di luar atau saat scroll window
document.addEventListener('click', function (e) {
    if (activeDropdownMenu && !activeDropdownMenu.contains(e.target)) {
        activeDropdownMenu.classList.remove('show');
        activeDropdownMenu = null;
    }
});

window.addEventListener('resize', function () {
    if (activeDropdownMenu) {
        activeDropdownMenu.classList.remove('show');
        activeDropdownMenu = null;
    }
});

/**
 * Buka modal konfirmasi hapus lembar produksi harian
 */
function openDeleteProduksiModal(id, no, tgl, shift, batch) {
    const modal = document.getElementById('modalDeleteProduksi');
    const form = document.getElementById('formDeleteProduksi');
    const txtNo = document.getElementById('deleteProduksiNo');
    const txtTgl = document.getElementById('deleteProduksiTgl');
    const txtShift = document.getElementById('deleteProduksiShift');
    const txtBatch = document.getElementById('deleteProduksiBatch');

    if (!modal || !form) return;

    // Buat route URL hapus
    form.action = `/produksi/${id}`;

    if (txtNo) txtNo.textContent = no || '-';
    if (txtTgl) txtTgl.textContent = tgl || '-';
    if (txtShift) txtShift.textContent = shift ? `Shift ${shift}` : '-';
    if (txtBatch) txtBatch.textContent = batch || '-';

    // Tutup dropdown jika masih terbuka
    if (activeDropdownMenu) {
        activeDropdownMenu.classList.remove('show');
        activeDropdownMenu = null;
    }

    modal.classList.add('show');
}

/**
 * Tutup modal konfirmasi hapus
 */
function closeDeleteProduksiModal() {
    const modal = document.getElementById('modalDeleteProduksi');
    if (modal) {
        modal.classList.remove('show');
    }
}

/**
 * Tab Switching: Tab 1 (Hasil Produksi) vs Tab 2 (Rekap HPP)
 */
function switchProduksiTab(tabName) {
    const btnHasil = document.getElementById('btn-tab-hasil');
    const btnRekap = document.getElementById('btn-tab-rekap');
    const paneHasil = document.getElementById('pane-tab-hasil');
    const paneRekap = document.getElementById('pane-tab-rekap');

    if (!paneHasil || !paneRekap) return;

    if (tabName === 'hasil') {
        btnHasil?.classList.add('active');
        btnRekap?.classList.remove('active');
        paneHasil.classList.add('active');
        paneRekap.classList.remove('active');
    } else {
        btnRekap?.classList.add('active');
        btnHasil?.classList.remove('active');
        paneRekap.classList.add('active');
        paneHasil.classList.remove('active');
    }

    // Perbarui URL tanpa reload penuh agar bookmark/refresh konsisten
    const url = new URL(window.location);
    url.searchParams.set('tab', tabName);
    window.history.replaceState({}, '', url);
}

/**
 * Kontrol Modal Import Excel Hasil Produksi
 */
function openModalImportHasil() {
    const modal = document.getElementById('modal-import-hasil');
    if (modal) modal.style.display = 'flex';
}

function closeModalImportHasil() {
    const modal = document.getElementById('modal-import-hasil');
    if (modal) modal.style.display = 'none';
}

/**
 * Kontrol Modal Import Excel Rekap HPP
 */
function openModalImportRekap() {
    const modal = document.getElementById('modal-import-rekap');
    if (modal) modal.style.display = 'flex';
}

function closeModalImportRekap() {
    const modal = document.getElementById('modal-import-rekap');
    if (modal) modal.style.display = 'none';
}


