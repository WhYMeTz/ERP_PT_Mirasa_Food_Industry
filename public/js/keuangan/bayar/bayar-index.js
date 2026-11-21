/**
 * JavaScript Riwayat Pembayaran Hutang / BKK - ERP PT Mirasa
 * Sesuai Standar AGENTS.md (Smart Action Dropdown dengan position: fixed)
 */

let activeDropdownMenu = null;
let lastVoidBayarTriggerEl = null;

document.addEventListener('DOMContentLoaded', function () {
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.btn-action-trigger') && !e.target.closest('.action-dropdown-menu')) {
            closeAllActionDropdowns();
        }

        const modal = document.getElementById('modalVoidBayar');
        if (modal && modal.style.display === 'flex' && e.target === modal) {
            closeModalVoid();
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeAllActionDropdowns();
            const modal = document.getElementById('modalVoidBayar');
            if (modal && modal.style.display === 'flex') {
                closeModalVoid();
            }
        }
    });

    window.addEventListener('scroll', closeAllActionDropdowns, true);
});

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

window.openModalVoid = function (bayarId, bayarNo, totalRupiah) {
    closeAllActionDropdowns();
    lastVoidBayarTriggerEl = document.activeElement;

    const modal = document.getElementById('modalVoidBayar');
    const form = document.getElementById('formVoidBayar');
    const noEl = document.getElementById('voidBayarNoText');
    const nominalEl = document.getElementById('voidNominalText');

    if (!modal || !form) return;

    form.action = `${window.appConfig.baseUrl}/keuangan/bayar/${bayarId}/void`;
    if (noEl) noEl.textContent = bayarNo;
    if (nominalEl) nominalEl.textContent = totalRupiah;

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
};

window.closeModalVoid = function () {
    const modal = document.getElementById('modalVoidBayar');
    if (!modal) return;
    modal.style.display = 'none';
    document.body.style.overflow = '';

    if (lastVoidBayarTriggerEl && typeof lastVoidBayarTriggerEl.focus === 'function') {
        lastVoidBayarTriggerEl.focus();
        lastVoidBayarTriggerEl = null;
    }
};
