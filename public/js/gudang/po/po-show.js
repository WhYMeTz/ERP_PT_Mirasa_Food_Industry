/**
 * =========================================================================
 * ERP PT MIRASA - PURCHASE ORDER SHOW JAVASCRIPT
 * =========================================================================
 */

function openQuickReceiveModal() {
    const modal = document.getElementById('modalQuickReceive');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeQuickReceiveModal() {
    const modal = document.getElementById('modalQuickReceive');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

function fillAllSisa() {
    document.querySelectorAll('.quick-terima-input').forEach(input => {
        input.value = input.dataset.sisa || 0;
    });
}

function clearAllInputs() {
    document.querySelectorAll('.quick-terima-input').forEach(input => {
        input.value = 0;
    });
}

function openForceCloseModal() {
    const modal = document.getElementById('modalForceClose');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeForceCloseModal() {
    const modal = document.getElementById('modalForceClose');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

/**
 * Toggle dropdown Menu Aksi Dokumen PO dengan Smart Positioning
 */
function toggleShowActionMenu(event) {
    if (event) event.stopPropagation();
    const btn = event.currentTarget;
    const dropdown = document.getElementById('showActionMenuDropdown');
    if (!dropdown) return;

    const isOpen = dropdown.style.display === 'block';
    if (isOpen) {
        dropdown.style.display = 'none';
        return;
    }

    dropdown.style.display = 'block';
    dropdown.style.visibility = 'hidden';
    dropdown.style.position = 'fixed';
    dropdown.style.zIndex = '999999';

    const rect = btn.getBoundingClientRect();
    const dropdownHeight = dropdown.offsetHeight || 260;
    const dropdownWidth = dropdown.offsetWidth || 250;
    const spaceBelow = window.innerHeight - rect.bottom;

    if (spaceBelow < dropdownHeight && rect.top > dropdownHeight) {
        dropdown.style.top = (rect.top - dropdownHeight - 4) + 'px';
    } else {
        dropdown.style.top = (rect.bottom + 4) + 'px';
    }

    let leftPos = rect.right - dropdownWidth;
    if (leftPos < 10) leftPos = 10;
    dropdown.style.left = leftPos + 'px';

    dropdown.style.visibility = 'visible';
}

document.addEventListener('DOMContentLoaded', function () {
    // Escape key closes modals
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeQuickReceiveModal();
            closeForceCloseModal();
        }
    });

    // Validasi Kelengkapan Batch Fisik Supplier di Quick Receive Modal Show
    const quickShowForm = document.getElementById('formQuickReceiveShow');
    if (quickShowForm) {
        quickShowForm.addEventListener('submit', function (e) {
            const rows = document.querySelectorAll('#modalQuickReceive tbody tr');
            let errorFound = false;

            rows.forEach((row, idx) => {
                if (errorFound) return;
                const qtyInput = row.querySelector('.quick-terima-input');
                const batchInput = row.querySelector('.quick-show-batch');
                const qty = parseFloat(qtyInput ? qtyInput.value : 0) || 0;

                if (qty > 0 && batchInput) {
                    const batchVal = batchInput.value.trim();
                    if (!batchVal || batchVal.endsWith('-')) {
                        e.preventDefault();
                        errorFound = true;
                        batchInput.style.border = '2px solid #ef4444';
                        batchInput.style.backgroundColor = '#fef2f2';
                        batchInput.focus();
                        const len = batchInput.value.length;
                        batchInput.setSelectionRange(len, len);

                        alert(`⚠️ Nomor Batch Fisik Supplier pada baris ke-${idx + 1} belum diisi lengkap!\n\nSilakan ketikkan kode lot / faktur yang tertera pada surat jalan atau kemasan supplier di belakang tanda strip.`);
                    } else {
                        batchInput.style.border = '';
                        batchInput.style.backgroundColor = '';
                    }
                }
            });

            if (errorFound) {
                return false;
            }
        });

        document.addEventListener('input', function (e) {
            if (e.target && e.target.classList.contains('quick-show-batch')) {
                const val = e.target.value.trim();
                if (val && !val.endsWith('-')) {
                    e.target.style.border = '';
                    e.target.style.backgroundColor = '';
                }
            }
        });
    }
});

// Tutup dropdown saat scroll window atau resize
window.addEventListener('scroll', function () {
    const dropdown = document.getElementById('showActionMenuDropdown');
    if (dropdown) dropdown.style.display = 'none';
}, true);

window.addEventListener('resize', function () {
    const dropdown = document.getElementById('showActionMenuDropdown');
    if (dropdown) dropdown.style.display = 'none';
});

// Tutup dropdown saat klik di luar
document.addEventListener('click', function (e) {
    if (!e.target.closest('.show-dropdown-trigger') && !e.target.closest('.show-action-menu-dropdown')) {
        const dropdown = document.getElementById('showActionMenuDropdown');
        if (dropdown) dropdown.style.display = 'none';
    }
});
