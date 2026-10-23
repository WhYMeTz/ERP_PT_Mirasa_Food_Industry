/**
 * FAKTUR PENJUALAN - INDEX JAVASCRIPT
 * PT Mirasa Food Industry
 */

let activeDropdownMenu = null;

/**
 * Smart Action Dropdown dengan posisi fixed agar tidak terpotong overflow scroll tabel.
 * Standar wajib AGENTS.md ERP Mirasa.
 */
function toggleSmartActionDropdown(triggerBtn, event, menuId) {
    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }

    const menu = document.getElementById(menuId);
    if (!menu) return;

    // Jika menu yang sama sedang terbuka, tutup
    if (activeDropdownMenu === menu) {
        menu.style.display = 'none';
        activeDropdownMenu = null;
        return;
    }

    // Tutup menu lain jika ada
    if (activeDropdownMenu) {
        activeDropdownMenu.style.display = 'none';
    }

    // Hitung posisi trigger button terhadap viewport
    const rect = triggerBtn.getBoundingClientRect();
    const menuWidth = 190;
    
    // Tampilkan sementara untuk mendapatkan tinggi aktual
    menu.style.visibility = 'hidden';
    menu.style.display = 'block';
    const menuHeight = menu.offsetHeight || 120;
    menu.style.visibility = 'visible';

    let top = rect.bottom + 4;
    let left = rect.right - menuWidth;

    // Jika melebihi batas bawah layar, munculkan ke atas tombol
    if (top + menuHeight > window.innerHeight) {
        top = Math.max(10, rect.top - menuHeight - 4);
    }

    // Pastikan tidak keluar batas kiri layar
    if (left < 10) {
        left = 10;
    }

    menu.style.position = 'fixed';
    menu.style.top = top + 'px';
    menu.style.left = left + 'px';
    menu.style.display = 'block';

    activeDropdownMenu = menu;
}

// Tutup dropdown jika klik di luar atau saat window discroll
document.addEventListener('click', function (e) {
    if (activeDropdownMenu && !activeDropdownMenu.contains(e.target)) {
        activeDropdownMenu.style.display = 'none';
        activeDropdownMenu = null;
    }
});

window.addEventListener('scroll', function () {
    if (activeDropdownMenu) {
        activeDropdownMenu.style.display = 'none';
        activeDropdownMenu = null;
    }
}, true);

/**
 * Modal Void Faktur Penjualan (Bahaya / Critical Action)
 */
function openVoidModal(fakturId, fakturNo) {
    const modal = document.getElementById('modalVoidFaktur');
    const form = document.getElementById('formVoidFaktur');
    const labelFakturNo = document.getElementById('voidFakturNoText');

    if (modal && form) {
        form.action = `/penjualan/faktur/${fakturId}/void`;
        if (labelFakturNo) {
            labelFakturNo.textContent = fakturNo;
        }
        modal.classList.add('active');
    }
}

function closeVoidModal() {
    const modal = document.getElementById('modalVoidFaktur');
    if (modal) {
        modal.classList.remove('active');
    }
}
