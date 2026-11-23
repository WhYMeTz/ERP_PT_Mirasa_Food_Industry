/**
 * PENJUALAN BAHAN PENOLONG & BUMBU - INDEX SCRIPT
 * ERP PT Mirasa Food Industry
 */

document.addEventListener('DOMContentLoaded', function () {
    // Tutup dropdown saat klik di luar
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.btn-action-trigger') && !e.target.closest('.action-dropdown-menu')) {
            closeAllActionDropdowns();
        }
    });

    // Tutup saat window scroll atau resize
    window.addEventListener('scroll', function () {
        closeAllActionDropdowns();
    }, true);

    window.addEventListener('resize', function () {
        closeAllActionDropdowns();
    });
});

function closeAllActionDropdowns() {
    document.querySelectorAll('.action-dropdown-menu').forEach(function (menu) {
        menu.style.display = 'none';
    });
    document.querySelectorAll('.btn-action-trigger').forEach(function (btn) {
        btn.classList.remove('active');
    });
}

function toggleSmartActionDropdown(triggerBtn, event, menuId) {
    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }

    const menu = document.getElementById(menuId);
    if (!menu) return;

    const isVisible = menu.style.display === 'block';

    closeAllActionDropdowns();

    if (!isVisible) {
        triggerBtn.classList.add('active');
        positionActionDropdown(triggerBtn, menu);
        menu.style.display = 'block';
    }
}

function positionActionDropdown(triggerBtn, menu) {
    const rect = triggerBtn.getBoundingClientRect();
    const menuWidth = 220;
    const menuHeight = 120;
    const windowWidth = window.innerWidth;
    const windowHeight = window.innerHeight;

    let leftPos = rect.right - menuWidth;
    if (leftPos < 10) leftPos = 10;
    if (leftPos + menuWidth > windowWidth - 10) {
        leftPos = windowWidth - menuWidth - 10;
    }

    let topPos = rect.bottom + 4;
    if (topPos + menuHeight > windowHeight && rect.top > menuHeight) {
        topPos = rect.top - menuHeight - 4;
    }

    menu.style.position = 'fixed';
    menu.style.top = topPos + 'px';
    menu.style.left = leftPos + 'px';
    menu.style.zIndex = '999999';
}
