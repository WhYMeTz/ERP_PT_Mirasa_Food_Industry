/**
 * Modul Keuangan: Bukti Kas Masuk (BKM) - Index JS
 * PT Mirasa Food Industry - Enterprise ERP (Standard PO Dropdown & Modals)
 */

let activeDropdownMenu = null;

/**
 * Toggle Smart Action Dropdown Menu with fixed positioning to prevent overflow clippings
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
    const menuWidth = 195;
    let top = rect.bottom + 4;
    let left = rect.right - menuWidth;

    // Boundary checks
    if (left < 10) left = 10;
    if (top + 180 > window.innerHeight) {
        top = rect.top - 160; // open upwards if close to viewport bottom
    }

    menu.style.top = `${top}px`;
    menu.style.left = `${left}px`;
    menu.classList.add('show');
    activeDropdownMenu = menu;
}

// Global click listener to close smart action dropdown
document.addEventListener('click', function (e) {
    if (activeDropdownMenu && !activeDropdownMenu.contains(e.target)) {
        activeDropdownMenu.classList.remove('show');
        activeDropdownMenu = null;
    }
});

// Close dropdown on window resize or scroll
window.addEventListener('scroll', function () {
    if (activeDropdownMenu) {
        activeDropdownMenu.classList.remove('show');
        activeDropdownMenu = null;
    }
}, true);

// Close dropdown on Escape key
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        if (activeDropdownMenu) {
            activeDropdownMenu.classList.remove('show');
            activeDropdownMenu = null;
        }
        closeModalVerifikasiBkm();
        closeModalVoidBkm();
    }
});

/**
 * Open Modal Verifikasi & Terbitkan Jurnal (Tahap 2 Akuntansi)
 */
function openModalVerifikasiBkm(bkmId, bkmNo, penyetor, kasBank, totalRp, tglTerima) {
    if (activeDropdownMenu) {
        activeDropdownMenu.classList.remove('show');
        activeDropdownMenu = null;
    }

    const modal = document.getElementById('modalVerifikasiBkm');
    const form = document.getElementById('formVerifikasiBkm');
    const labelBkmNo = document.getElementById('verifBkmNo');
    const labelTgl = document.getElementById('verifBkmTgl');
    const labelPenyetor = document.getElementById('verifBkmPenyetor');
    const labelKasBank = document.getElementById('verifBkmKasBank');
    const labelTotal = document.getElementById('verifBkmTotal');
    const selectCoa = document.getElementById('verifCoaId');
    const inputTgl = document.getElementById('verifTglJurnal');

    if (!modal || !form) return;

    form.action = `/keuangan/kas-masuk/${bkmId}/verifikasi-jurnal`;

    if (labelBkmNo) labelBkmNo.textContent = bkmNo;
    if (labelTgl) labelTgl.textContent = tglTerima || '-';
    if (labelPenyetor) labelPenyetor.textContent = penyetor || '-';
    if (labelKasBank) labelKasBank.textContent = kasBank || '-';
    if (labelTotal) labelTotal.textContent = totalRp;

    if (selectCoa) {
        selectCoa.value = '';
    }
    if (inputTgl) {
        inputTgl.value = '';
    }

    modal.style.display = 'flex';
    setTimeout(() => {
        if (selectCoa) selectCoa.focus();
    }, 150);
}

/**
 * Close Modal Verifikasi BKM
 */
function closeModalVerifikasiBkm() {
    const modal = document.getElementById('modalVerifikasiBkm');
    if (modal) {
        modal.style.display = 'none';
    }
}

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
