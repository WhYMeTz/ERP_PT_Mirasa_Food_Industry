/**
 * Modul Produksi: Catatan Laporan Operator Packaging (Web Office) - JavaScript
 * PT Mirasa Food Industry
 */

let activeDropdownMenu = null;

function toggleSmartActionDropdown(button, event, menuId) {
    if (event) {
        event.stopPropagation();
    }

    const menu = document.getElementById(menuId);
    if (!menu) return;

    // Tutup menu lain yang sedang aktif
    if (activeDropdownMenu && activeDropdownMenu !== menu) {
        activeDropdownMenu.style.display = 'none';
    }

    const isVisible = menu.style.display === 'block';

    if (isVisible) {
        menu.style.display = 'none';
        activeDropdownMenu = null;
    } else {
        // Hitung posisi button di viewport
        const rect = button.getBoundingClientRect();
        menu.style.display = 'block';
        menu.style.position = 'fixed';

        const menuWidth = 220;
        const menuHeight = menu.offsetHeight || 140;
        const viewportWidth = window.innerWidth;
        const viewportHeight = window.innerHeight;

        // Atur posisi horizontal (default align right ke button)
        let left = rect.right - menuWidth;
        if (left < 10) left = rect.left;
        if (left + menuWidth > viewportWidth - 10) {
            left = viewportWidth - menuWidth - 10;
        }

        // Atur posisi vertikal (default di bawah button)
        let top = rect.bottom + 4;
        if (top + menuHeight > viewportHeight - 10) {
            top = rect.top - menuHeight - 4; // Flip ke atas jika tidak muat
        }

        menu.style.left = `${left}px`;
        menu.style.top = `${top}px`;
        activeDropdownMenu = menu;
    }
}

// Tutup dropdown saat klik di luar area
document.addEventListener('click', function (e) {
    if (activeDropdownMenu && !activeDropdownMenu.contains(e.target)) {
        activeDropdownMenu.style.display = 'none';
        activeDropdownMenu = null;
    }
});

// Tutup dropdown saat scroll
window.addEventListener('scroll', function () {
    if (activeDropdownMenu) {
        activeDropdownMenu.style.display = 'none';
        activeDropdownMenu = null;
    }
}, true);
