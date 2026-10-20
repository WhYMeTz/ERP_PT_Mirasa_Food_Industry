/**
 * ══════════════════════════════════════════════════════════════════════════
 * PO PENJUALAN (SALES ORDER) - INDEX SCRIPT
 * ERP PT Mirasa Food Industry
 * ══════════════════════════════════════════════════════════════════════════
 */

let activeDropdownMenu = null;

/**
 * Toggle Smart Action Dropdown dengan position: fixed
 * Memastikan menu tidak terpotong oleh overflow scroll tabel (Aturan Wajib AGENTS.md)
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
        closeSmartActionDropdown();
        return;
    }

    // Tutup menu lain yang sedang aktif
    closeSmartActionDropdown();

    // Hitung posisi fixed relatif terhadap viewport
    const rect = triggerBtn.getBoundingClientRect();
    const menuWidth = 220;
    let left = rect.right - menuWidth;
    let top = rect.bottom + 4;

    // Cegah keluar layar kiri
    if (left < 10) left = 10;

    // Cegah keluar layar bawah
    const windowHeight = window.innerHeight;
    if (top + 200 > windowHeight) {
        top = rect.top - 180;
    }

    menu.style.position = 'fixed';
    menu.style.top = top + 'px';
    menu.style.left = left + 'px';
    menu.style.display = 'block';

    activeDropdownMenu = menu;
}

function closeSmartActionDropdown() {
    if (activeDropdownMenu) {
        activeDropdownMenu.style.display = 'none';
        activeDropdownMenu = null;
    }
}

// Tutup dropdown saat klik di luar
document.addEventListener('click', function(e) {
    if (activeDropdownMenu && !activeDropdownMenu.contains(e.target)) {
        closeSmartActionDropdown();
    }
});

// Tutup saat window scroll/resize
window.addEventListener('scroll', closeSmartActionDropdown, true);
window.addEventListener('resize', closeSmartActionDropdown);

/**
 * Expand/Collapse Sub-Row Rincian Item Barang
 */
function toggleSoRow(rowId, btn) {
    const row = document.getElementById(rowId);
    if (!row) return;

    const icon = btn.querySelector('.chevron-icon');
    if (row.style.display === 'none' || row.style.display === '') {
        row.style.display = 'table-row';
        if (icon) icon.style.transform = 'rotate(90deg)';
    } else {
        row.style.display = 'none';
        if (icon) icon.style.transform = 'rotate(0deg)';
    }
}

/**
 * Modal Dialog Handlers
 */
function openConfirmDeliveryModal(soId, soNo, customerNm) {
    closeSmartActionDropdown();
    const modal = document.getElementById('modalConfirmDelivery');
    if (!modal) return;

    const form = document.getElementById('formConfirmDelivery');
    if (form) {
        form.action = `/penjualan/so/${soId}/confirm-delivery`;
    }

    document.getElementById('modalDeliverySoNo').textContent = soNo;
    document.getElementById('modalDeliveryCustomer').textContent = customerNm;
    document.getElementById('deliveryTglInput').value = new Date().toISOString().split('T')[0];

    modal.style.display = 'flex';
}

function closeConfirmDeliveryModal() {
    const modal = document.getElementById('modalConfirmDelivery');
    if (modal) modal.style.display = 'none';
}

function openCancelSoModal(soId, soNo) {
    closeSmartActionDropdown();
    const modal = document.getElementById('modalCancelSo');
    if (!modal) return;

    const form = document.getElementById('formCancelSo');
    if (form) {
        form.action = `/penjualan/so/${soId}/cancel`;
    }

    document.getElementById('modalCancelSoNo').textContent = soNo;
    modal.style.display = 'flex';
}

function closeCancelSoModal() {
    const modal = document.getElementById('modalCancelSo');
    if (modal) modal.style.display = 'none';
}
