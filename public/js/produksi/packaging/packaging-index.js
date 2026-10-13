/**
 * =========================================================================
 * ERP PT MIRASA - PENGEMASAN BARANG JADI & HPP (PACKAGING) INDEX JS
 * Standard Separation of Concerns & Clean DOM Handling
 * =========================================================================
 */

document.addEventListener('DOMContentLoaded', function () {
    // Tutup dropdown cerdas jika mengklik di luar area
    window.addEventListener('click', function (e) {
        if (!e.target.closest('.btn-action-trigger') && !e.target.closest('.action-dropdown-menu')) {
            document.querySelectorAll('.action-dropdown-menu.show').forEach(function (menu) {
                menu.classList.remove('show');
            });
        }
    });

    // Tutup dropdown dengan tombol keyboard Escape
    window.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.action-dropdown-menu.show').forEach(function (menu) {
                menu.classList.remove('show');
            });
            closeVoidPackagingModal();
        }
    });
});

/**
 * Smart Action Dropdown Trigger (Position Fixed untuk mencegah overflow/scroll clipping)
 */
function toggleSmartActionDropdown(btn, event, menuId) {
    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }

    const menu = document.getElementById(menuId);
    if (!menu) return;

    const isShown = menu.classList.contains('show');

    // Tutup semua menu dropdown aktif lainnya
    document.querySelectorAll('.action-dropdown-menu.show').forEach(function (m) {
        m.classList.remove('show');
    });

    if (!isShown) {
        const rect = btn.getBoundingClientRect();
        const menuWidth = 190;
        let left = rect.right - menuWidth;
        let top = rect.bottom + 4;

        if (left < 10) left = 10;
        if (left + menuWidth > window.innerWidth - 10) {
            left = window.innerWidth - menuWidth - 10;
        }

        if (top + 160 > window.innerHeight) {
            top = rect.top - 140;
        }

        menu.style.left = left + 'px';
        menu.style.top = top + 'px';
        menu.classList.add('show');
    }
}

/**
 * Buka Modal Konfirmasi Pembatalan Dokumen Packaging (Void)
 */
function openVoidPackagingModal(packId, packNo, targetNm) {
    const modal = document.getElementById('modalVoidPackaging');
    if (!modal) return;

    const form = document.getElementById('formVoidPackaging');
    if (form) {
        form.action = '/produksi/packaging/' + packId + '/void';
    }

    const labelNo = document.getElementById('voidPackNoLabel');
    if (labelNo) {
        labelNo.textContent = packNo + ' (' + targetNm + ')';
    }

    const inputAlasan = document.getElementById('inputAlasanVoid');
    if (inputAlasan) {
        inputAlasan.value = '';
        setTimeout(() => inputAlasan.focus(), 150);
    }

    modal.style.display = 'flex';
}

/**
 * Tutup Modal Konfirmasi Pembatalan Packaging
 */
function closeVoidPackagingModal() {
    const modal = document.getElementById('modalVoidPackaging');
    if (modal) {
        modal.style.display = 'none';
    }
}
