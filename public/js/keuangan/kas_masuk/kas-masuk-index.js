/**
 * Modul Keuangan: Bukti Kas Masuk (BKM) - Index JS
 * PT Mirasa Food Industry - Enterprise ERP
 */

let activeDropdownMenu = null;

/**
 * Toggle Smart Action Dropdown Menu with fixed positioning
 */
function toggleSmartActionDropdown(button, event, menuId) {
    if (event) {
        event.stopPropagation();
    }

    const menu = document.getElementById(menuId);
    if (!menu) return;

    // If clicking same menu, close it
    if (activeDropdownMenu === menu) {
        menu.classList.remove('show');
        activeDropdownMenu = null;
        return;
    }

    // Close any other open menu
    if (activeDropdownMenu) {
        activeDropdownMenu.classList.remove('show');
    }

    // Position menu relative to button using getBoundingClientRect()
    const rect = button.getBoundingClientRect();
    const menuWidth = 180;
    let top = rect.bottom + 4;
    let left = rect.right - menuWidth;

    // Check window bounds
    if (left < 10) left = 10;
    if (top + 160 > window.innerHeight) {
        top = rect.top - 140; // open upwards if close to bottom
    }

    menu.style.top = `${top}px`;
    menu.style.left = `${left}px`;
    menu.classList.add('show');
    activeDropdownMenu = menu;
}

// Global click listener to close smart action dropdown
document.addEventListener('click', function(e) {
    if (activeDropdownMenu && !activeDropdownMenu.contains(e.target)) {
        activeDropdownMenu.classList.remove('show');
        activeDropdownMenu = null;
    }
});

// Close dropdown on window resize or scroll
window.addEventListener('scroll', function() {
    if (activeDropdownMenu) {
        activeDropdownMenu.classList.remove('show');
        activeDropdownMenu = null;
    }
}, true);

/**
 * Open Void Confirmation Modal
 */
function openModalVoidBkm(bkmId, bkmNo, totalRp) {
    if (activeDropdownMenu) {
        activeDropdownMenu.classList.remove('show');
        activeDropdownMenu = null;
    }

    const modal = document.getElementById('modalVoidBkm');
    const form = document.getElementById('formVoidBkm');
    const labelBkmNo = document.getElementById('voidBkmNo');
    const labelTotal = document.getElementById('voidBkmTotal');
    const inputAlasan = document.getElementById('voidAlasan');

    if (!modal || !form) return;

    form.action = `/keuangan/kas-masuk/${bkmId}/void`;
    if (labelBkmNo) labelBkmNo.textContent = bkmNo;
    if (labelTotal) labelTotal.textContent = totalRp;
    if (inputAlasan) {
        inputAlasan.value = '';
    }

    modal.style.display = 'flex';
    setTimeout(() => {
        if (inputAlasan) inputAlasan.focus();
    }, 150);
}

/**
 * Close Void Confirmation Modal
 */
function closeModalVoidBkm() {
    const modal = document.getElementById('modalVoidBkm');
    if (modal) {
        modal.style.display = 'none';
    }
}
