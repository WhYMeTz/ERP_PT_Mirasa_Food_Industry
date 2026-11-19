/**
 * Transfer Antar-Gudang Index JS
 * ERP PT Mirasa Food Industry
 */

let activeDropdownMenu = null;

function toggleSmartActionDropdown(button, event, menuId) {
    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }

    const menu = document.getElementById(menuId);
    if (!menu) return;

    // Tutup menu lain yang sedang terbuka
    if (activeDropdownMenu && activeDropdownMenu !== menu) {
        activeDropdownMenu.style.display = 'none';
    }

    const isVisible = menu.style.display === 'block';

    if (isVisible) {
        menu.style.display = 'none';
        activeDropdownMenu = null;
    } else {
        const rect = button.getBoundingClientRect();
        menu.style.position = 'fixed';
        menu.style.top = (rect.bottom + 4) + 'px';
        menu.style.left = (rect.right - 170) + 'px';
        menu.style.display = 'block';
        activeDropdownMenu = menu;
    }
}

// Tutup dropdown saat klik di luar
document.addEventListener('click', function(e) {
    if (activeDropdownMenu && !activeDropdownMenu.contains(e.target)) {
        activeDropdownMenu.style.display = 'none';
        activeDropdownMenu = null;
    }
});

// Tutup dropdown saat scroll tabel
window.addEventListener('scroll', function() {
    if (activeDropdownMenu) {
        activeDropdownMenu.style.display = 'none';
        activeDropdownMenu = null;
    }
}, true);

// Modal Void Confirmation
function openVoidTransferModal(transferId, transferNo) {
    const modal = document.getElementById('modalVoidTransfer');
    const form = document.getElementById('formVoidTransfer');
    const labelNo = document.getElementById('voidTransferNoDisplay');

    if (!modal || !form) return;

    form.action = `/gudang/transfer/${transferId}/void`;
    if (labelNo) labelNo.textContent = transferNo;

    const inputAlasan = document.getElementById('voidAlasanInput');
    if (inputAlasan) inputAlasan.value = '';

    modal.style.display = 'flex';
}

function closeVoidTransferModal() {
    const modal = document.getElementById('modalVoidTransfer');
    if (modal) modal.style.display = 'none';
}
