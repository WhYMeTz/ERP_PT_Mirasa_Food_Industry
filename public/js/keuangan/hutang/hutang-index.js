/**
 * JavaScript Logika Interaktif - Hutang Usaha & Tagihan Supplier (AP)
 */

document.addEventListener('DOMContentLoaded', function () {
    // Tutup dropdown saat klik di luar
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.btn-action-trigger') && !e.target.closest('.action-dropdown-menu')) {
            document.querySelectorAll('.action-dropdown-menu').forEach(menu => {
                menu.style.display = 'none';
            });
            document.querySelectorAll('.btn-action-trigger').forEach(btn => {
                btn.classList.remove('active');
            });
        }
    });

    // Tutup modal saat klik tombol close atau di luar dialog
    document.querySelectorAll('.modal-backdrop').forEach(modal => {
        modal.addEventListener('click', function (e) {
            if (e.target === this) {
                closeModal(this.id);
            }
        });
    });
});

/**
 * Toggle Smart Action Dropdown dengan posisi fixed (anti terpotong table overflow)
 */
function toggleSmartActionDropdown(button, event, menuId) {
    if (event) {
        event.stopPropagation();
    }

    const menu = document.getElementById(menuId);
    if (!menu) return;

    const isVisible = menu.style.display === 'block';

    // Tutup menu lain yang sedang terbuka
    document.querySelectorAll('.action-dropdown-menu').forEach(m => {
        m.style.display = 'none';
    });
    document.querySelectorAll('.btn-action-trigger').forEach(b => {
        b.classList.remove('active');
    });

    if (!isVisible) {
        const rect = button.getBoundingClientRect();
        menu.style.display = 'block';
        button.classList.add('active');

        // Posisikan menu tepat di bawah button
        menu.style.top = `${rect.bottom + 4}px`;
        menu.style.right = `${window.innerWidth - rect.right}px`;
        menu.style.left = 'auto';

        // Deteksi jika menu keluar layar bawah
        const menuRect = menu.getBoundingClientRect();
        if (menuRect.bottom > window.innerHeight) {
            menu.style.top = `${rect.top - menuRect.height - 4}px`;
        }
    }
}

/**
 * Buka Modal Pembatalan Tagihan
 */
function openDeleteHutangModal(hutangId, hutangNo, totalTagihan) {
    const modal = document.getElementById('modalDeleteHutang');
    if (!modal) return;

    document.getElementById('deleteHutangNoText').textContent = hutangNo;
    document.getElementById('deleteHutangValText').textContent = totalTagihan;
    document.getElementById('formDeleteHutang').action = `${window.appConfig.baseUrl}/keuangan/hutang/${hutangId}`;

    modal.classList.add('show');
}

/**
 * Tutup Modal Dialog
 */
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');
    }
}
