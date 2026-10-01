/**
 * =========================================================================
 * ERP PT MIRASA - TERIMA BARANG (GRN) SHOW JAVASCRIPT
 * =========================================================================
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
    const menuWidth = menu.offsetWidth || 195;
    const menuHeight = menu.offsetHeight || 150;
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
    if (leftPos < 8) leftPos = 8;
    if (leftPos + menuWidth > viewportWidth - 8) leftPos = viewportWidth - menuWidth - 8;

    menu.style.left = `${leftPos}px`;
}

function closeAllActionDropdowns() {
    document.querySelectorAll('.action-dropdown-menu').forEach(menu => {
        menu.style.display = 'none';
    });
    if (activeTriggerButton) {
        activeTriggerButton.classList.remove('active');
        activeTriggerButton = null;
    }
    activeDropdownMenu = null;
}

// Handler Modal Delete Terima
function openDeleteTerimaModal(terimaId, terimaNo, supplierNm) {
    closeAllActionDropdowns();
    const modal = document.getElementById('modal-delete-terima');
    const noEl = document.getElementById('delete-terima-no');
    const supEl = document.getElementById('delete-terima-supplier');
    const formEl = document.getElementById('form-delete-terima');

    if (modal && noEl && formEl) {
        noEl.textContent = terimaNo || '-';
        if (supEl) supEl.textContent = supplierNm ? `Supplier: ${supplierNm}` : '';
        formEl.action = `/gudang/terima/${terimaId}`;
        modal.style.display = 'flex';
    }
}

function closeDeleteTerimaModal() {
    const modal = document.getElementById('modal-delete-terima');
    if (modal) modal.style.display = 'none';
}

document.addEventListener('click', function(e) {
    if (activeDropdownMenu && !activeDropdownMenu.contains(e.target)) {
        closeAllActionDropdowns();
    }
});

window.addEventListener('scroll', function() {
    if (activeDropdownMenu && activeTriggerButton) {
        positionActionDropdown(activeTriggerButton, activeDropdownMenu);
    }
}, true);

window.addEventListener('resize', function() {
    if (activeDropdownMenu && activeTriggerButton) {
        positionActionDropdown(activeTriggerButton, activeDropdownMenu);
    }
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAllActionDropdowns();
        closeDeleteTerimaModal();
    }
});
