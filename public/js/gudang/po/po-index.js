/**
 * =========================================================================
 * ERP PT MIRASA - PURCHASE ORDER INDEX JAVASCRIPT
 * =========================================================================
 */

// Toggle expandable item row tanpa pindah halaman
function togglePoRow(rowId, btn) {
    const row = document.getElementById(rowId);
    if (!row) return;

    const isHidden = row.style.display === 'none' || row.style.display === '';
    row.style.display = isHidden ? 'table-row' : 'none';

    const icon = btn.querySelector('.chevron-icon');
    if (icon) {
        icon.style.transform = isHidden ? 'rotate(90deg)' : 'rotate(0deg)';
    }
}

// Buka Modal Catat Terima Cepat di halaman Indeks
function openQuickReceiveIndexModal(po) {
    const elTitle = document.getElementById('modalPoTitle');
    const elSub = document.getElementById('modalPoSubtitle');
    const elPoId = document.getElementById('quick_po_id');
    const elSupId = document.getElementById('quick_supplier_id');
    const elGdgId = document.getElementById('quick_gudang_id');
    const elLink = document.getElementById('linkFullForm');

    if (elTitle) elTitle.textContent = 'Catat Penerimaan: ' + po.po_no;
    if (elSub) elSub.textContent = (po.supplier_nm || '-') + ' \u2022 ' + (po.gudang_nm || '-');
    if (elPoId) elPoId.value = po.po_id;
    if (elSupId) elSupId.value = po.supplier_id;
    if (elGdgId) elGdgId.value = po.gudang_id;
    if (elLink && window.quickTerimaCreateRoute) {
        elLink.href = window.quickTerimaCreateRoute + "?po_id=" + po.po_id;
    }

    const tbody = document.getElementById('quickReceiveItemsBody');
    if (!tbody) return;
    tbody.innerHTML = '';

    let rowCount = 0;
    po.items.forEach((item) => {
        if (item.sisa_qty > 0) {
            const tr = document.createElement('tr');
            tr.style.borderTop = '1px solid #f1f5f9';
            tr.innerHTML = `
                <td style="padding: 0.6rem 0.75rem;">
                    <input type="hidden" name="items[${rowCount}][podtl_id]" value="${item.podtl_id}">
                    <input type="hidden" name="items[${rowCount}][barang_id]" value="${item.barang_id}">
                    <input type="hidden" name="items[${rowCount}][harga_nominal]" value="${item.harga_nominal}">
                    <strong style="color: #0f172a; display: block; font-size: 0.85rem;">${item.barang_nm}</strong>
                    <span style="font-size: 0.75rem; color: #64748b;">Satuan: ${item.satuan_nm}</span>
                </td>
                <td style="padding: 0.6rem 0.75rem; text-align: right; font-weight: 600; color: #b45309;">
                    ${Number(item.sisa_qty).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 })}
                </td>
                <td style="padding: 0.6rem 0.75rem;">
                    <input type="text" 
                           name="items[${rowCount}][batch_no]" 
                           value="${item.batch_prefix || 'BRG-'}" 
                           placeholder="${item.batch_prefix || 'BRG-'}... (isi no batch supplier)" 
                           class="form-control quick-index-batch" 
                           style="font-family: monospace; font-weight: 700; color: #0284c7; width: 100%; font-size: 0.825rem; padding: 0.35rem 0.5rem;" 
                           required>
                </td>
                <td style="padding: 0.6rem 0.75rem; text-align: right;">
                    <input type="number" 
                           step="0.0001" 
                           min="0" 
                           max="${item.sisa_qty}" 
                           name="items[${rowCount}][terima_qty]" 
                           value="${item.sisa_qty}" 
                           data-sisa="${item.sisa_qty}"
                           class="form-control quick-index-input" 
                           style="text-align: right; font-weight: 700; width: 100%; display: inline-block; padding: 0.35rem 0.5rem; font-size: 0.85rem;" 
                           required>
                </td>
            `;
            tbody.appendChild(tr);
            rowCount++;
        }
    });

    if (rowCount === 0) {
        tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding:1.5rem; color:#94a3b8;">Seluruh item pesanan PO ini sudah diterima lengkap.</td></tr>';
    }

    const modal = document.getElementById('modalQuickReceiveIndex');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeQuickReceiveIndexModal() {
    const modal = document.getElementById('modalQuickReceiveIndex');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

function fillAllQuickSisa() {
    document.querySelectorAll('.quick-index-input').forEach(input => {
        input.value = input.getAttribute('data-sisa') || 0;
    });
}

function clearAllQuickInputs() {
    document.querySelectorAll('.quick-index-input').forEach(input => {
        input.value = 0;
    });
}

let activeDropdownMenu = null;
let activeTriggerButton = null;

/**
 * Toggle dropdown Aksi per baris PO dengan Smart Viewport Positioning (Bebas Scroll Table)
 */
function toggleSmartActionDropdown(button, event, menuId) {
    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }

    const targetMenu = document.getElementById(menuId);
    if (!targetMenu) return;

    // Jika dropdown yang sama sedang terbuka, tutup
    if (activeDropdownMenu === targetMenu && targetMenu.style.display === 'block') {
        closeAllActionDropdowns();
        return;
    }

    // Tutup dropdown lain yang sedang terbuka
    closeAllActionDropdowns();

    // Tampilkan menu dan hitung posisinya secara pintar (Fixed Viewport)
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

    // Cek ruang vertikal: jika ruang bawah tidak cukup, letakkan di atas tombol (Dropup)
    const spaceBelow = viewportHeight - rect.bottom;
    const spaceAbove = rect.top;

    if (spaceBelow < menuHeight && spaceAbove > spaceBelow) {
        // Dropup (di atas tombol)
        menu.style.top = `${rect.top - menuHeight - 4}px`;
    } else {
        // Dropdown (di bawah tombol)
        menu.style.top = `${rect.bottom + 4}px`;
    }

    // Cek ruang horizontal: posisikan rata kanan tombol
    let leftPos = rect.right - menuWidth;
    if (leftPos < 8) {
        leftPos = 8;
    }
    if (leftPos + menuWidth > viewportWidth - 8) {
        leftPos = viewportWidth - menuWidth - 8;
    }

    menu.style.left = `${leftPos}px`;
}

function closeAllActionDropdowns() {
    document.querySelectorAll('.action-dropdown-menu, .po-action-menu-dropdown').forEach(menu => {
        menu.style.display = 'none';
    });
    if (activeTriggerButton) {
        activeTriggerButton.classList.remove('active');
        activeTriggerButton = null;
    }
    activeDropdownMenu = null;
}

// Fallback alias
function togglePoIndexDropdown(event, dropdownId) {
    const btn = event ? event.currentTarget : null;
    toggleSmartActionDropdown(btn, event, dropdownId);
}


document.addEventListener('DOMContentLoaded', function () {
    // Escape key listener
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeQuickReceiveIndexModal();
            closeAllActionDropdowns();
        }
    });

    // Validasi Kelengkapan Batch Fisik Supplier di Quick Receive Modal
    const quickIndexForm = document.getElementById('formQuickReceiveIndex');
    if (quickIndexForm) {
        quickIndexForm.addEventListener('submit', function (e) {
            const rows = document.querySelectorAll('#quickReceiveItemsBody tr');
            let errorFound = false;

            rows.forEach((row, idx) => {
                if (errorFound) return;
                const qtyInput = row.querySelector('.quick-index-input');
                const batchInput = row.querySelector('.quick-index-batch');
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
            if (e.target && e.target.classList.contains('quick-index-batch')) {
                const val = e.target.value.trim();
                if (val && !val.endsWith('-')) {
                    e.target.style.border = '';
                    e.target.style.backgroundColor = '';
                }
            }
        });
    }
});

// Tutup dropdown saat scroll window/tabel atau resize
window.addEventListener('scroll', function () {
    closeAllActionDropdowns();
}, true);

window.addEventListener('resize', function () {
    closeAllActionDropdowns();
});

// Tutup dropdown saat klik di luar
document.addEventListener('click', function (e) {
    if (!e.target.closest('.btn-action-trigger') && 
        !e.target.closest('.po-dropdown-trigger') && 
        !e.target.closest('.action-dropdown-menu') && 
        !e.target.closest('.po-action-menu-dropdown')) {
        closeAllActionDropdowns();
    }
});

