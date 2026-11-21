/**
 * JavaScript Riwayat Pengeluaran Kas Operasional (BKK Non-Tagihan) - ERP PT Mirasa
 * Sesuai Standar AGENTS.md (Smart Action Dropdown dengan position: fixed & Danger Modal)
 */

let activeKasKeluarDropdown = null;
let lastVoidTriggerEl = null;

document.addEventListener('DOMContentLoaded', function () {
    // Tutup dropdown saat klik di luar
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.btn-action-trigger') && !e.target.closest('.action-dropdown-menu')) {
            closeAllActionDropdowns();
        }

        const modal = document.getElementById('modalVoidKasKeluar');
        if (modal && modal.style.display === 'flex' && e.target === modal) {
            closeModalVoidKasKeluar();
        }
    });

    // Tutup dengan Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeAllActionDropdowns();
            const modal = document.getElementById('modalVoidKasKeluar');
            if (modal && modal.style.display === 'flex') {
                closeModalVoidKasKeluar();
            }
        }
    });

    // Tutup dropdown saat scroll tabel
    window.addEventListener('scroll', closeAllActionDropdowns, true);
});

/**
 * Toggle Smart Action Dropdown dengan position: fixed agar tidak terpotong container tabel
 */
window.toggleSmartActionDropdown = function (button, event, menuId) {
    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }

    const menu = document.getElementById(menuId);
    if (!menu) return;

    if (activeKasKeluarDropdown && activeKasKeluarDropdown === menu) {
        closeAllActionDropdowns();
        return;
    }

    closeAllActionDropdowns();

    const rect = button.getBoundingClientRect();
    menu.style.display = 'block';
    menu.style.position = 'fixed';

    const menuWidth = 195;
    const menuHeight = menu.offsetHeight || 120;
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

    activeKasKeluarDropdown = menu;
};

function closeAllActionDropdowns() {
    document.querySelectorAll('.action-dropdown-menu').forEach(function (m) {
        m.style.display = 'none';
    });
    activeKasKeluarDropdown = null;
}

/**
 * Modal Konfirmasi Pembatalan (VOID) Kas Keluar
 */
window.openModalVoidKasKeluar = function (bkkId, bkkNo, totalRupiah, kasName, triggerEl) {
    closeAllActionDropdowns();
    lastVoidTriggerEl = triggerEl || document.activeElement;

    const modal = document.getElementById('modalVoidKasKeluar');
    const form = document.getElementById('formVoidKasKeluar');
    const noEl = document.getElementById('voidBkkNoText');
    const nominalEl = document.getElementById('voidBkkNominalText');
    const kasEl = document.getElementById('voidBkkKasText');
    const alasanInput = document.getElementById('alasanVoidKasKeluar');

    if (!modal || !form) return;

    form.action = `${window.appConfig.baseUrl}/keuangan/kas-keluar/${bkkId}/void`;
    if (noEl) noEl.textContent = bkkNo;
    if (nominalEl) nominalEl.textContent = totalRupiah;
    if (kasEl) kasEl.textContent = kasName || 'Kas/Bank';
    if (alasanInput) {
        alasanInput.value = '';
    }

    modal.style.display = 'flex';
    setTimeout(() => {
        if (alasanInput) alasanInput.focus();
    }, 50);
};

window.closeModalVoidKasKeluar = function () {
    const modal = document.getElementById('modalVoidKasKeluar');
    if (modal) {
        modal.style.display = 'none';
    }
    if (lastVoidTriggerEl) {
        lastVoidTriggerEl.focus();
        lastVoidTriggerEl = null;
    }
};

/**
 * Switch Quick Status Tab
 */
window.switchStatusFilter = function (status) {
    const form = document.getElementById('formBkkFilter');
    const statusInput = document.getElementById('inputFilterStatusCd');
    if (form && statusInput) {
        statusInput.value = status || '';
        form.submit();
    }
};
