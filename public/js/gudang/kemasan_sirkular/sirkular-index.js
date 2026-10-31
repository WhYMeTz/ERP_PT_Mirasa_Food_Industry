/**
 * Logika Interaktif Indeks Penerimaan Kemasan Sirkular - ERP PT Mirasa
 * Standar Smart Action Dropdown Fixed Viewport
 */

let activeDropdownMenu = null;
let activeTriggerButton = null;

function toggleSmartActionDropdown(button, event, menuId) {
    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }

    const targetMenu = document.getElementById(menuId);
    if (!targetMenu) return;

    if (activeDropdownMenu === targetMenu && targetMenu.style.display === 'block') {
        closeAllActionDropdowns();
        return;
    }

    closeAllActionDropdowns();

    targetMenu.style.display = 'block';
    activeDropdownMenu = targetMenu;
    activeTriggerButton = button;
    button.classList.add('active');

    positionActionDropdown(button, targetMenu);
}

function positionActionDropdown(button, menu) {
    if (!button || !menu) return;

    const rect = button.getBoundingClientRect();
    const menuWidth = menu.offsetWidth || 170;
    const menuHeight = menu.offsetHeight || 120;
    const viewportHeight = window.innerHeight;
    const viewportWidth = window.innerWidth;

    const spaceBelow = viewportHeight - rect.bottom;
    const spaceAbove = rect.top;

    if (spaceBelow < menuHeight && spaceAbove > spaceBelow) {
        menu.style.top = `${rect.top - menuHeight - 4}px`;
    } else {
        menu.style.top = `${rect.bottom + 4}px`;
    }

    let leftPos = rect.right - menuWidth;
    if (leftPos + menuWidth > viewportWidth - 10) {
        leftPos = viewportWidth - menuWidth - 10;
    }
    if (leftPos < 10) {
        leftPos = 10;
    }

    menu.style.left = `${leftPos}px`;
    menu.style.position = 'fixed';
}

function closeAllActionDropdowns() {
    document.querySelectorAll('.action-dropdown-menu').forEach(m => m.style.display = 'none');
    document.querySelectorAll('.btn-action-trigger').forEach(b => b.classList.remove('active'));
    activeDropdownMenu = null;
    activeTriggerButton = null;
}

document.addEventListener('DOMContentLoaded', function () {
    document.addEventListener('click', closeAllActionDropdowns);
    window.addEventListener('scroll', closeAllActionDropdowns, true);
    window.addEventListener('resize', closeAllActionDropdowns);
});

