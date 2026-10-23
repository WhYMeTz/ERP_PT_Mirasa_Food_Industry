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

// =========================================================================
// FITUR MODAL POPUP DOKUMEN FORM HACCP RESMI (SURAT PERMINTAAN BARANG)
// =========================================================================
let cachedPoHaccpData = {};

function openPoHaccpModal(poId) {
    const modal = document.getElementById('modalHaccpPo');
    const spinner = document.getElementById('haccpLoadingSpinner');
    const sheet = document.getElementById('haccpPaperSheet');

    if (!modal) {
        console.error('Modal #modalHaccpPo tidak ditemukan di DOM!');
        return;
    }
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    if (spinner) spinner.style.display = 'block';
    if (sheet) sheet.style.display = 'none';

    if (cachedPoHaccpData[poId]) {
        renderPoHaccpSheet(cachedPoHaccpData[poId]);
        return;
    }

    const baseUrl = (window.poConfig && window.poConfig.baseUrl) ? window.poConfig.baseUrl : '/gudang/po';

    fetch(`${baseUrl}/${poId}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(async res => {
        const data = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(data.message || `HTTP ${res.status}`);
        return data;
    })
    .then(result => {
        if (result.status === 'success' && result.data) {
            cachedPoHaccpData[poId] = result.data;
            renderPoHaccpSheet(result.data);
        } else {
            alert(result.message || 'Gagal memuat formulir dokumen HACCP.');
            closePoHaccpModal();
        }
    })
    .catch(err => {
        console.error('Error memuat dokumen HACCP:', err);
        alert('Gagal memuat dokumen HACCP: ' + err.message);
        closePoHaccpModal();
    });
}
window.openPoHaccpModal = openPoHaccpModal;

function closePoHaccpModal() {
    const modal = document.getElementById('modalHaccpPo');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}
window.closePoHaccpModal = closePoHaccpModal;

function printHaccpDocument() {
    window.print();
}
window.printHaccpDocument = printHaccpDocument;

function renderPoHaccpSheet(po) {
    const spinner = document.getElementById('haccpLoadingSpinner');
    const sheet = document.getElementById('haccpPaperSheet');

    if (spinner) spinner.style.display = 'none';
    if (sheet) sheet.style.display = 'block';

    const elModalNo = document.getElementById('haccpModalPoNo');
    const elSheetNo = document.getElementById('haccpSheetPoNo');
    if (elModalNo) elModalNo.textContent = po.po_no || '-';
    if (elSheetNo) elSheetNo.textContent = po.po_no || '-';

    const sup = po.supplier || {};
    const elSupNm = document.getElementById('haccpSheetSupplierNm');
    const elSupAlamat = document.getElementById('haccpSheetSupplierAlamat');
    if (elSupNm) elSupNm.textContent = sup.supplier_nm || '-';
    if (elSupAlamat) {
        elSupAlamat.textContent = [
            sup.alamat || '',
            sup.telepon ? `Telp: ${sup.telepon}` : ''
        ].filter(Boolean).join(' • ') || '-';
    }

    const gdg = po.gudang || {};
    const elGdgCity = document.getElementById('haccpSheetGudangCity');
    const elGdgAlamat = document.getElementById('haccpSheetGudangAlamat');
    if (elGdgCity) elGdgCity.textContent = gdg.gudang_nm ? `${gdg.gudang_nm} - Magelang` : 'Magelang';
    if (elGdgAlamat) elGdgAlamat.textContent = gdg.alamat || 'Jl. Munggur No. 2 Ambartawang, Kec. Mungkid, Kab. Magelang, Jawa Tengah 56512';

    const formatDateIndo = (dateStr) => {
        if (!dateStr) return new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return dateStr;
        return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
    };
    const elTgl = document.getElementById('haccpSheetTgl');
    if (elTgl) elTgl.textContent = formatDateIndo(po.po_tgl);

    const baseUrl = (window.poConfig && window.poConfig.baseUrl) ? window.poConfig.baseUrl : '/gudang/po';
    const btnPdf = document.getElementById('btnHaccpDownloadPdf');
    if (btnPdf) btnPdf.href = `${baseUrl}/${po.po_id}/export-pdf`;

    const tbody = document.getElementById('haccpSheetItemsBody');
    if (!tbody) return;
    tbody.innerHTML = '';

    const details = po.details || [];
    details.forEach((d, idx) => {
        const brg = d.barang || {};
        const satuan = brg.satuan_dasar ? (brg.satuan_dasar.satuan_cd || brg.satuan_dasar.satuan_nm) : 'KG';
        const qtyPesan = parseFloat(d.pesan_qty) || 0;

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td style="border: 1px solid #000000; padding: 8px 6px; text-align: center; font-weight: 700; font-size: 10pt;">${idx + 1}.</td>
            <td style="border: 1px solid #000000; padding: 8px 12px; font-size: 10pt;">
                <strong style="color: #000000;">${brg.barang_nm || '-'}</strong>
                ${brg.barang_cd ? `<div style="font-size: 8pt; color: #475569; font-family: monospace;">Kode: ${brg.barang_cd}</div>` : ''}
            </td>
            <td style="border: 1px solid #000000; padding: 8px 10px; text-align: center; font-weight: 800; font-size: 10pt;">
                ${qtyPesan.toLocaleString('id-ID')} ${satuan}
            </td>
            <td style="border: 1px solid #000000; padding: 8px 10px; font-size: 9.5pt;">
                ${d.catatan_txt || '-'}
            </td>
        `;
        tbody.appendChild(tr);
    });

    const emptyRows = Math.max(1, 3 - details.length);
    for (let i = 0; i < emptyRows; i++) {
        const trEmpty = document.createElement('tr');
        trEmpty.innerHTML = `
            <td style="border: 1px solid #000000; padding: 14px; text-align: center; color: transparent;">&nbsp;</td>
            <td style="border: 1px solid #000000; padding: 14px;">&nbsp;</td>
            <td style="border: 1px solid #000000; padding: 14px;">&nbsp;</td>
            <td style="border: 1px solid #000000; padding: 14px;">&nbsp;</td>
        `;
        tbody.appendChild(trEmpty);
    }
}
window.renderPoHaccpSheet = renderPoHaccpSheet;

