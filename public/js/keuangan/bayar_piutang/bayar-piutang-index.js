/**
 * JavaScript Riwayat Terima Pembayaran Customer (BKM AR) - ERP PT Mirasa
 * Sesuai Standar AGENTS.md (Smart Action Dropdown dengan position: fixed)
 */

let activeDropdownMenu = null;

document.addEventListener('DOMContentLoaded', function () {
    // Click outside handler
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.btn-action-trigger') && !e.target.closest('.action-dropdown-menu')) {
            closeAllActionDropdowns();
        }

        const modal = document.getElementById('modalVoidBayarPiutang');
        if (modal && modal.style.display === 'flex' && e.target === modal) {
            closeModalVoid();
        }
    });

    // Escape key handler
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeAllActionDropdowns();
            const modal = document.getElementById('modalVoidBayarPiutang');
            if (modal && modal.style.display === 'flex') {
                closeModalVoid();
            }
        }
    });

    // Close dropdowns on scroll
    window.addEventListener('scroll', closeAllActionDropdowns, true);
});

// Quick Filter Tab Switcher
window.switchStatusFilter = function (status) {
    const inputStatus = document.getElementById('inputFilterStatusCd');
    const form = document.getElementById('formBayarPiutangFilter');
    if (inputStatus && form) {
        inputStatus.value = status;
        form.submit();
    }
};

// Smart Action Dropdown (Position: Fixed)
window.toggleSmartActionDropdown = function (button, event, menuId) {
    event.stopPropagation();
    event.preventDefault();

    const menu = document.getElementById(menuId);
    if (!menu) return;

    if (activeDropdownMenu && activeDropdownMenu === menu) {
        closeAllActionDropdowns();
        return;
    }

    closeAllActionDropdowns();

    const rect = button.getBoundingClientRect();
    menu.style.display = 'block';
    menu.style.position = 'fixed';

    const menuWidth = 180;
    const menuHeight = menu.offsetHeight || 100;
    const windowHeight = window.innerHeight;

    let top = rect.bottom + 4;
    let left = rect.right - menuWidth;

    if (top + menuHeight > windowHeight - 10) {
        top = rect.top - menuHeight - 4;
    }

    if (left < 10) {
        left = 10;
    }

    menu.style.top = `${top}px`;
    menu.style.left = `${left}px`;

    activeDropdownMenu = menu;
};

function closeAllActionDropdowns() {
    document.querySelectorAll('.action-dropdown-menu').forEach(m => {
        m.style.display = 'none';
    });
    activeDropdownMenu = null;
}

// Modal Konfirmasi Void (Standar AGENTS.md)
window.openModalVoid = function (bayarId, bkmNo, totalRupiah) {
    closeAllActionDropdowns();

    const modal = document.getElementById('modalVoidBayarPiutang');
    const form = document.getElementById('formVoidBayarPiutang');
    const noEl = document.getElementById('voidBkmNoText');
    const nominalEl = document.getElementById('voidNominalText');

    if (!modal || !form) return;

    form.action = `${window.appConfig.baseUrl}/keuangan/bayar-piutang/${bayarId}/void`;
    if (noEl) noEl.textContent = bkmNo;
    if (nominalEl) nominalEl.textContent = totalRupiah;

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
};

window.closeModalVoid = function () {
    const modal = document.getElementById('modalVoidBayarPiutang');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
};
