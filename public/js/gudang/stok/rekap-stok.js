/**
 * ═══════════════════════════════════════════════════════════════
 * JAVASCRIPT: REKAPITULASI STOK & VALUASI PERSEDIAAN GUDANG
 * ERP PT Mirasa Food Industry (Blueprint 10 - Rekap Stok)
 * ═══════════════════════════════════════════════════════════════
 */

(function () {
    'use strict';

    let activeMenu = null;

    // Smart Action Dropdown (Posisi Fixed agar tidak terpotong oleh overflow tabel)
    window.toggleSmartActionDropdown = function (triggerEl, event, menuId) {
        event.stopPropagation();

        const menuEl = document.getElementById(menuId);
        if (!menuEl) return;

        // Tutup menu sebelumnya jika ada
        if (activeMenu && activeMenu !== menuEl) {
            activeMenu.style.display = 'none';
        }

        if (menuEl.style.display === 'block') {
            menuEl.style.display = 'none';
            activeMenu = null;
            return;
        }

        // Hitung posisi tombol trigger di viewport
        const rect = triggerEl.getBoundingClientRect();
        const menuWidth = 175;
        const menuHeight = 90;

        let left = rect.right - menuWidth;
        if (left < 10) left = 10;

        let top = rect.bottom + 4;
        if (top + menuHeight > window.innerHeight) {
            top = rect.top - menuHeight - 4;
        }

        menuEl.style.position = 'fixed';
        menuEl.style.top = `${top}px`;
        menuEl.style.left = `${left}px`;
        menuEl.style.display = 'block';

        activeMenu = menuEl;
    };

    // Tutup dropdown saat klik di luar
    document.addEventListener('click', function () {
        if (activeMenu) {
            activeMenu.style.display = 'none';
            activeMenu = null;
        }
    });

    // Tutup dropdown saat scroll halaman
    window.addEventListener('scroll', function () {
        if (activeMenu) {
            activeMenu.style.display = 'none';
            activeMenu = null;
        }
    }, true);

})();
