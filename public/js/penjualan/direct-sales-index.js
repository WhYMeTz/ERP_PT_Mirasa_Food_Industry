/**
 * ══════════════════════════════════════════════════════════════════════════
 * PENJUALAN LANGSUNG / TUNAI (DIRECT SALES) - INDEX JAVASCRIPT
 * ERP PT Mirasa Food Industry
 * ══════════════════════════════════════════════════════════════════════════
 */

let activeDropdownMenu = null;
let activeTriggerButton = null;

function positionActionDropdown(button, menu) {
    const rect = button.getBoundingClientRect();
    const menuWidth = 190;
    const menuHeight = menu.offsetHeight || 120;
    const windowWidth = window.innerWidth;
    const windowHeight = window.innerHeight;

    let left = rect.right - menuWidth;
    if (left < 10) left = 10;
    if (left + menuWidth > windowWidth - 10) {
        left = windowWidth - menuWidth - 10;
    }

    const spaceBelow = windowHeight - rect.bottom;
    const spaceAbove = rect.top;

    let top;
    if (spaceBelow < menuHeight && spaceAbove > spaceBelow) {
        top = rect.top - menuHeight - 4;
    } else {
        top = rect.bottom + 4;
    }

    menu.style.top = top + 'px';
    menu.style.left = left + 'px';
}

function closeAllActionDropdowns() {
    if (activeDropdownMenu) {
        activeDropdownMenu.classList.remove('show');
        activeDropdownMenu = null;
    }
    if (activeTriggerButton) {
        activeTriggerButton.classList.remove('active');
        activeTriggerButton = null;
    }
    document.querySelectorAll('.action-dropdown-menu.show').forEach(m => m.classList.remove('show'));
    document.querySelectorAll('.btn-action-trigger.active').forEach(b => b.classList.remove('active'));
}

function toggleSmartActionDropdown(button, event, menuId) {
    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }

    const menu = document.getElementById(menuId);
    if (!menu) return;

    if (activeDropdownMenu === menu) {
        closeAllActionDropdowns();
        return;
    }

    closeAllActionDropdowns();

    activeDropdownMenu = menu;
    activeTriggerButton = button;
    button.classList.add('active');

    menu.classList.add('show');
    positionActionDropdown(button, menu);
}

// Global click & scroll listeners
document.addEventListener('click', function (e) {
    if (!e.target.closest('.action-dropdown-menu') && !e.target.closest('.btn-action-trigger')) {
        closeAllActionDropdowns();
    }
});

window.addEventListener('scroll', function () {
    if (activeDropdownMenu && activeTriggerButton) {
        const rect = activeTriggerButton.getBoundingClientRect();
        if (rect.bottom < 50 || rect.top > window.innerHeight - 30) {
            closeAllActionDropdowns();
        } else {
            positionActionDropdown(activeTriggerButton, activeDropdownMenu);
        }
    }
}, true);

window.addEventListener('resize', function () {
    closeAllActionDropdowns();
});

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        closeAllActionDropdowns();
    }
});
