/**
 * =========================================================================
 * ERP PT MIRASA - MASTER DATA CUSTOMER INDEX JAVASCRIPT
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
 * Buka modal edit customer dan isi formulirnya
 */
function editCustomer(id, kode, nama, kontak, alamat, klasifikasi, wilayah, kodePelanggan, topTxt, defaultPpn) {
    closeAllActionDropdowns();
    const elCd = document.getElementById('edit_customer_cd');
    const elNm = document.getElementById('edit_customer_nm');
    const elKontak = document.getElementById('edit_customer_kontak');
    const elAlamat = document.getElementById('edit_customer_alamat');
    const elKlas = document.getElementById('edit_klasifikasi');
    const elWil = document.getElementById('edit_wilayah');
    const elRef = document.getElementById('edit_kode_pelanggan');
    const elTop = document.getElementById('edit_top_txt');
    const elPpn = document.getElementById('edit_default_ppn');
    const form = document.getElementById('formEditCustomer');
    
    if (elCd) elCd.value = kode;
    if (elNm) elNm.value = nama;
    if (elKontak) elKontak.value = kontak || '';
    if (elAlamat) elAlamat.value = alamat || '';
    if (elKlas) elKlas.value = klasifikasi || 'MANUAL';
    if (elWil) elWil.value = wilayah || '';
    if (elRef) elRef.value = kodePelanggan || '';
    if (elTop) elTop.value = topTxt || '';
    if (elPpn) elPpn.value = defaultPpn || 'NON_PPN';

    handleKlasifikasiChange('edit');
    
    const baseUrl = (window.masterCustomerConfig && window.masterCustomerConfig.baseUrl) 
        ? window.masterCustomerConfig.baseUrl 
        : '/master-customer';
        
    if (form) {
        form.action = baseUrl.replace(/\/$/, '') + '/' + id;
    }
    
    if (typeof openModal === 'function') {
        openModal('modalEditCustomer');
    }
}

/**
 * Handle perubahan klasifikasi customer pada form tambah/edit
 */
function handleKlasifikasiChange(prefix) {
    const klasifikasiElem = document.getElementById(prefix + '_klasifikasi');
    const topElem = document.getElementById(prefix + '_top_txt');
    const ppnGroup = document.getElementById('group_' + prefix + '_default_ppn');
    const ppnElem = document.getElementById(prefix + '_default_ppn');
    const refElem = document.getElementById(prefix + '_kode_pelanggan');

    if (!klasifikasiElem) return;
    const val = klasifikasiElem.value;

    if (val === 'IFM') {
        if (topElem && (!topElem.value || topElem.value === 'Tinggal 1 Nota')) {
            topElem.value = '7 Hari';
        }
        if (refElem && !refElem.value) {
            refElem.placeholder = 'Contoh: IFM01.012007';
        }
        if (ppnGroup) ppnGroup.style.display = 'none';
    } else if (val === 'IDM_ALFA') {
        if (topElem) {
            topElem.value = '';
            topElem.placeholder = 'Tidak ada TOP (Kosong / -)';
        }
        if (refElem && !refElem.value) {
            refElem.placeholder = 'Kosong';
        }
        if (ppnGroup) {
            ppnGroup.style.display = 'none';
        }
    } else { // MANUAL
        if (topElem && (!topElem.value || topElem.value === '7 Hari' || topElem.value === '14 Hari')) {
            topElem.value = 'Tinggal 1 Nota';
        }
        if (refElem && !refElem.value) {
            refElem.placeholder = 'Kode Sales / Toko (Opsional)';
        }
        if (ppnGroup) ppnGroup.style.display = 'block';
    }
}

/**
 * Buka modal konfirmasi nonaktifkan / hapus customer
 */
function openDeleteCustomerModal(id, kode, nama) {
    closeAllActionDropdowns();
    const cdElem = document.getElementById('deleteCustomerCd');
    const nmElem = document.getElementById('deleteCustomerNm');
    const formElem = document.getElementById('formDeleteCustomer');
    
    if (cdElem) cdElem.innerText = kode;
    if (nmElem) nmElem.innerText = nama;
    
    const baseUrl = (window.masterCustomerConfig && window.masterCustomerConfig.baseUrl) 
        ? window.masterCustomerConfig.baseUrl 
        : '/master-customer';
        
    if (formElem) {
        formElem.action = baseUrl.replace(/\/$/, '') + '/' + id;
    }
    
    if (typeof openModal === 'function') {
        openModal('modalDeleteCustomer');
    }
}

// Expose fungsi ke window
window.toggleSmartActionDropdown = toggleSmartActionDropdown;
window.closeAllActionDropdowns = closeAllActionDropdowns;
window.editCustomer = editCustomer;
window.openDeleteCustomerModal = openDeleteCustomerModal;
window.handleKlasifikasiChange = handleKlasifikasiChange;
