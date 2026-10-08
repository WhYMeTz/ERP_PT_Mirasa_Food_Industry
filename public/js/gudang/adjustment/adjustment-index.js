/**
 * JAVASCRIPT INDEX PENYESUAIAN STOK (STOCK ADJUSTMENT)
 * ERP PT Mirasa Food Industry - Standar Separation of Concerns
 */

document.addEventListener('DOMContentLoaded', function () {
    // Tutup smart dropdown jika klik di luar
    window.addEventListener('click', function (e) {
        if (!e.target.closest('.btn-action-trigger') && !e.target.closest('.action-dropdown-menu')) {
            document.querySelectorAll('.action-dropdown-menu.show').forEach(function (menu) {
                menu.classList.remove('show');
            });
        }
    });

    // Tutup dropdown dengan tombol Escape
    window.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.action-dropdown-menu.show').forEach(function (menu) {
                menu.classList.remove('show');
            });
        }
    });
});

/**
 * Smart Action Dropdown Trigger (Position Fixed agar tidak terpotong overflow scroll)
 */
function toggleSmartActionDropdown(btn, event, menuId) {
    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }

    const menu = document.getElementById(menuId);
    if (!menu) return;

    const isShown = menu.classList.contains('show');

    // Tutup seluruh dropdown lain yang aktif
    document.querySelectorAll('.action-dropdown-menu.show').forEach(function (m) {
        m.classList.remove('show');
    });

    if (!isShown) {
        const rect = btn.getBoundingClientRect();
        const menuWidth = 170;
        let left = rect.right - menuWidth;
        let top = rect.bottom + 4;

        if (left < 10) left = 10;
        if (top + 160 > window.innerHeight) {
            top = rect.top - 140;
        }

        menu.style.left = left + 'px';
        menu.style.top = top + 'px';
        menu.classList.add('show');
    }
}

/**
 * Buka Modal Konfirmasi Pembatalan (Void)
 */
function openVoidModal(adjId, adjNo) {
    const modal = document.getElementById('modalVoidAdjustment');
    if (!modal) return;

    const form = document.getElementById('formVoidAdjustment');
    if (form) {
        form.action = '/gudang/adjustment/' + adjId + '/void';
    }

    const labelNo = document.getElementById('voidAdjNoLabel');
    if (labelNo) {
        labelNo.textContent = adjNo;
    }

    modal.style.display = 'flex';
}

/**
 * Tutup Modal Konfirmasi Pembatalan
 */
function closeVoidModal() {
    const modal = document.getElementById('modalVoidAdjustment');
    if (modal) {
        modal.style.display = 'none';
    }
}
