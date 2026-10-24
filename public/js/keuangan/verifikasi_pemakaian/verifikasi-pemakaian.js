/**
 * JAVASCRIPT LOGIC - MODUL VERIFIKASI PEMAKAIAN BAHAN & POSTING JURNAL HPP
 * ERP PT Mirasa Food Industry
 */

let activeDropdownMenu = null;

function toggleSmartActionDropdown(btn, event, menuId) {
    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }

    const menu = document.getElementById(menuId);
    if (!menu) return;

    if (activeDropdownMenu && activeDropdownMenu !== menu) {
        activeDropdownMenu.style.display = 'none';
    }

    if (menu.style.display === 'block') {
        menu.style.display = 'none';
        activeDropdownMenu = null;
        return;
    }

    const rect = btn.getBoundingClientRect();
    menu.style.display = 'block';

    const menuHeight = menu.offsetHeight || 120;
    const menuWidth = menu.offsetWidth || 190;
    const windowHeight = window.innerHeight;
    const windowWidth = window.innerWidth;

    let top = rect.bottom + 4;
    let left = rect.right - menuWidth;

    if (top + menuHeight > windowHeight - 10) {
        top = Math.max(10, rect.top - menuHeight - 4);
    }
    if (left < 10) {
        left = 10;
    }

    menu.style.top = top + 'px';
    menu.style.left = left + 'px';
    activeDropdownMenu = menu;
}

window.addEventListener('click', function(e) {
    if (activeDropdownMenu && !activeDropdownMenu.contains(e.target)) {
        activeDropdownMenu.style.display = 'none';
        activeDropdownMenu = null;
    }
});

window.addEventListener('scroll', function() {
    if (activeDropdownMenu) {
        activeDropdownMenu.style.display = 'none';
        activeDropdownMenu = null;
    }
}, true);

// Modal Approve / Posting Pemakaian
function openModalApprove(pakaiId, pakaiNo, totalRupiah) {
    const modal = document.getElementById('modalApprovePemakaian');
    if (!modal) return;

    const form = modal.querySelector('form');
    if (form) {
        form.action = `/keuangan/verifikasi-pemakaian/${pakaiId}/approve`;
    }

    const noEl = document.getElementById('modalApproveNo');
    if (noEl) noEl.textContent = pakaiNo;

    const rupiahEl = document.getElementById('modalApproveRupiah');
    if (rupiahEl) rupiahEl.textContent = 'Rp ' + Number(totalRupiah).toLocaleString('id-ID');

    modal.style.display = 'flex';
}

function closeModalApprove() {
    const modal = document.getElementById('modalApprovePemakaian');
    if (modal) modal.style.display = 'none';
}

// Modal Reject / Kembalikan ke Gudang
function openModalReject(pakaiId, pakaiNo) {
    const modal = document.getElementById('modalRejectPemakaian');
    if (!modal) return;

    const form = modal.querySelector('form');
    if (form) {
        form.action = `/keuangan/verifikasi-pemakaian/${pakaiId}/reject`;
    }

    const noEl = document.getElementById('modalRejectNo');
    if (noEl) noEl.textContent = pakaiNo;

    modal.style.display = 'flex';
}

function closeModalReject() {
    const modal = document.getElementById('modalRejectPemakaian');
    if (modal) modal.style.display = 'none';
}
