(function () {
    'use strict';

    let activeMenu = null;

    window.toggleSmartActionDropdown = function (triggerEl, event, menuId) {
        event.stopPropagation();

        const menuEl = document.getElementById(menuId);
        if (!menuEl) return;

        if (activeMenu && activeMenu !== menuEl) {
            activeMenu.style.display = 'none';
        }

        if (menuEl.style.display === 'block') {
            menuEl.style.display = 'none';
            activeMenu = null;
            return;
        }

        const rect = triggerEl.getBoundingClientRect();
        const menuWidth = 195;
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

    document.addEventListener('click', function () {
        if (activeMenu) {
            activeMenu.style.display = 'none';
            activeMenu = null;
        }
    });

    window.addEventListener('scroll', function () {
        if (activeMenu) {
            activeMenu.style.display = 'none';
            activeMenu = null;
        }
    }, true);

    // Auto navigate on date picker change
    document.addEventListener('DOMContentLoaded', function () {
        const datePicker = document.getElementById('rekapDatePicker');
        if (datePicker) {
            datePicker.addEventListener('change', function () {
                const targetDate = this.value;
                if (!targetDate) return;

                const url = new URL(window.location.href);
                url.searchParams.set('tanggal', targetDate);
                url.searchParams.delete('page'); // Reset pagination to page 1
                window.location.href = url.toString();
            });
        }
    });

})();
