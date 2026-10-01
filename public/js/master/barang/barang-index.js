/**
 * =========================================================================
 * ERP PT MIRASA - MASTER DATA BARANG INDEX JAVASCRIPT
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
    const menuWidth = menu.offsetWidth || 195;
    const menuHeight = menu.offsetHeight || 150;
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
 * Buka modal konfirmasi hapus / nonaktifkan barang
 */
function openDeleteBarangModal(id, code, name) {
    const cdElem = document.getElementById('deleteBarangCd');
    const nmElem = document.getElementById('deleteBarangNm');
    const formElem = document.getElementById('formDeleteBarang');
    
    if (cdElem) cdElem.innerText = code;
    if (nmElem) nmElem.innerText = name;
    
    const baseUrl = window.masterBarangBaseUrl || '/master-barang';
    if (formElem) formElem.action = baseUrl.replace(/\/$/, '') + '/' + id;
    
    openModal('modalDeleteBarang');
}

/**
 * Buka modal edit barang dan isi nilainya
 */
function editBarang(id, kode, nama, jenisId, satuanDasarId, satuanBesarId, konversi, batasMin, hargaStandar) {
    const elKode = document.getElementById('edit_barang_cd');
    const elNama = document.getElementById('edit_barang_nm');
    const elJenis = document.getElementById('edit_jenis_barang_id');
    const elSatuanDasar = document.getElementById('edit_satuan_dasar_id');
    const elSatuanBesar = document.getElementById('edit_satuan_besar_id');
    const elKonversi = document.getElementById('edit_konversi_qty');
    const elBatasMin = document.getElementById('edit_batas_minimum_qty');
    const elHargaStandar = document.getElementById('edit_harga_beli_standar');
    const elId = document.getElementById('edit_barang_id');
    const formEdit = document.getElementById('formEditBarang');

    if (elKode) elKode.value = kode;
    if (elNama) elNama.value = nama;
    if (elJenis) elJenis.value = jenisId;
    if (elSatuanDasar) elSatuanDasar.value = satuanDasarId;
    if (elSatuanBesar) elSatuanBesar.value = satuanBesarId || '';
    if (elKonversi) elKonversi.value = konversi;
    if (elBatasMin) elBatasMin.value = batasMin || 0;
    if (elHargaStandar) elHargaStandar.value = hargaStandar || 0;
    if (elId) elId.value = id;

    const baseUrl = window.masterBarangBaseUrl || '/master-barang';
    if (formEdit) formEdit.action = baseUrl.replace(/\/$/, '') + '/' + id;

    openModal('modalEditBarang');
}

// Expose fungsi ke scope global window
window.toggleSmartActionDropdown = toggleSmartActionDropdown;
window.closeAllActionDropdowns = closeAllActionDropdowns;
window.openDeleteBarangModal = openDeleteBarangModal;
window.editBarang = editBarang;
