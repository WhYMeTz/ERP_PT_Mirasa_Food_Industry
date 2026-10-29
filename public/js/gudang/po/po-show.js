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

function openForceCloseModal(payload) {
    const modal = document.getElementById('modalForceClose');
    if (!modal) return;

    if (payload) {
        const form = document.getElementById('formForceClose');
        if (form && payload.url) form.action = payload.url;
        const elPoNo = document.getElementById('mfcPoNo');
        if (elPoNo && payload.po_no) elPoNo.textContent = payload.po_no;
        const elSup = document.getElementById('mfcSupplier');
        if (elSup && payload.supplier_nm) elSup.textContent = payload.supplier_nm;
        const elTerima = document.getElementById('mfcTerimaQty');
        if (elTerima && payload.total_terima !== undefined) elTerima.textContent = Number(payload.total_terima).toLocaleString('id-ID');
        const elSisa = document.getElementById('mfcSisaQty');
        if (elSisa && payload.total_sisa !== undefined) elSisa.textContent = Number(payload.total_sisa).toLocaleString('id-ID');
    }

    const textarea = document.getElementById('closed_reason');
    if (textarea) textarea.value = '';

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}
window.openForceCloseModal = openForceCloseModal;
window.openBatalkanSisaModal = openForceCloseModal;

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

    const formatDateIndo = (dateStr) => {
        if (!dateStr) return '-';
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return dateStr;
        return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
    };

    const elTgl = document.getElementById('haccpSheetTgl');
    if (elTgl) elTgl.textContent = formatDateIndo(po.po_tgl);

    // Supplier Box
    const sup = po.supplier || {};
    const elSupNm = document.getElementById('haccpSheetSupplierNm');
    const elSupAlamat = document.getElementById('haccpSheetSupplierAlamat');
    const elSupUp = document.getElementById('haccpSheetSupplierUp');
    const elSupTelp = document.getElementById('haccpSheetSupplierTelp');

    if (elSupNm) elSupNm.textContent = sup.supplier_nm || '-';
    if (elSupAlamat) elSupAlamat.textContent = sup.alamat || sup.alamat_txt || '-';
    if (elSupUp) elSupUp.textContent = sup.kontak_person || '-';
    if (elSupTelp) elSupTelp.textContent = sup.kontak_no || sup.telepon || sup.telepon_no || '-';

    // Link Download PDF
    const baseUrl = (window.poConfig && window.poConfig.baseUrl) ? window.poConfig.baseUrl : '/gudang/po';
    const btnPdf = document.getElementById('btnHaccpDownloadPdf');
    if (btnPdf) btnPdf.href = `${baseUrl}/${po.po_id}/export-pdf`;

    // Render Tabel Rincian Bahan
    const tbody = document.getElementById('haccpSheetItemsBody');
    if (!tbody) return;
    tbody.innerHTML = '';

    const details = po.details || [];
    let subtotal = 0;

    details.forEach((d, idx) => {
        const brg = d.barang || {};
        const satuan = brg.satuan_dasar ? (brg.satuan_dasar.satuan_cd || brg.satuan_dasar.satuan_nm) : (d.satuan_nm || 'PCS');
        const qtyPesan = parseFloat(d.pesan_qty) || 0;
        const harga = parseFloat(d.harga_nominal) || 0;
        const total = parseFloat(d.subtotal_nominal) || (qtyPesan * harga);
        subtotal += total;

        const tr = document.createElement('tr');
        tr.style.fontSize = '8.5pt';
        tr.innerHTML = `
            <td style="border-left: 1px solid #000000; border-right: 1px solid #000000; border-bottom: 1px solid #e5e7eb; padding: 4px 6px; text-align: center; font-weight: 600;">${idx + 1}</td>
            <td style="border-left: 1px solid #000000; border-right: 1px solid #000000; border-bottom: 1px solid #e5e7eb; padding: 4px 8px;">
                <strong style="text-transform: uppercase;">${brg.barang_nm || '-'}</strong>
                ${brg.barang_cd ? `<span style="font-size: 7.5pt; color: #4b5563;">(${brg.barang_cd})</span>` : ''}
            </td>
            <td style="border-left: 1px solid #000000; border-right: 1px solid #000000; border-bottom: 1px solid #e5e7eb; padding: 4px 6px; text-align: right; font-weight: 700;">
                ${qtyPesan.toLocaleString('id-ID')}
            </td>
            <td style="border-left: 1px solid #000000; border-right: 1px solid #000000; border-bottom: 1px solid #e5e7eb; padding: 4px 6px; text-align: center; text-transform: uppercase;">
                ${satuan}
            </td>
            <td style="border-left: 1px solid #000000; border-right: 1px solid #000000; border-bottom: 1px solid #e5e7eb; padding: 4px 6px; text-align: right;">
                <span style="float: left; font-size: 7.5pt;">Rp</span> ${harga.toLocaleString('id-ID')}
            </td>
            <td style="border-left: 1px solid #000000; border-right: 1px solid #000000; border-bottom: 1px solid #e5e7eb; padding: 4px 8px; text-align: right; font-weight: 700;">
                <span style="float: left; font-size: 7.5pt;">Rp</span> ${total.toLocaleString('id-ID')}
            </td>
        `;
        tbody.appendChild(tr);
    });

    // Baris kosong
    const emptyRows = Math.max(0, 6 - details.length);
    for (let i = 0; i < emptyRows; i++) {
        const trEmpty = document.createElement('tr');
        trEmpty.style.height = '18px';
        trEmpty.innerHTML = `
            <td style="border-left: 1px solid #000000; border-right: 1px solid #000000; padding: 4px 6px; text-align: center; color: #9ca3af; font-size: 7.5pt;">${details.length + i + 1}</td>
            <td style="border-left: 1px solid #000000; border-right: 1px solid #000000; padding: 4px 8px;">&nbsp;</td>
            <td style="border-left: 1px solid #000000; border-right: 1px solid #000000; padding: 4px 6px;">&nbsp;</td>
            <td style="border-left: 1px solid #000000; border-right: 1px solid #000000; padding: 4px 6px;">&nbsp;</td>
            <td style="border-left: 1px solid #000000; border-right: 1px solid #000000; padding: 4px 6px;">&nbsp;</td>
            <td style="border-left: 1px solid #000000; border-right: 1px solid #000000; padding: 4px 8px;">&nbsp;</td>
        `;
        tbody.appendChild(trEmpty);
    }

    // Hitung Totals & PPN
    let ppn = parseFloat(po.ppn_nominal) || 0;
    if (ppn === 0 && (po.is_harga_termasuk_pajak || (po.catatan_txt && po.catatan_txt.toUpperCase().includes('PPN')))) {
        ppn = Math.round(subtotal * 0.11);
    }
    const grandTotal = parseFloat(po.total_tagihan) || (subtotal + ppn);

    // Update Elements
    const elSub = document.getElementById('haccpSheetSubtotal');
    const elPpn = document.getElementById('haccpSheetPpn');
    const elGrand = document.getElementById('haccpSheetGrandTotal');
    if (elSub) elSub.textContent = subtotal.toLocaleString('id-ID');
    if (elPpn) elPpn.textContent = ppn.toLocaleString('id-ID');
    if (elGrand) elGrand.textContent = grandTotal.toLocaleString('id-ID');

    // Note, TOP & Remark
    const elNoteEta = document.getElementById('haccpSheetNoteEta');
    const elCatatan = document.getElementById('haccpSheetCatatan');
    if (elNoteEta) {
        if (po.catatan_txt) {
            elNoteEta.textContent = po.catatan_txt;
        } else if (po.tgl_estimasi_datang) {
            elNoteEta.textContent = `*Mohon dikirim tanggal ${formatDateIndo(po.tgl_estimasi_datang)}`;
        } else {
            elNoteEta.textContent = '-';
        }
    }
    if (elCatatan) elCatatan.textContent = '';

    const elTop = document.getElementById('haccpSheetTop');
    if (elTop) {
        if (po.term_of_payment) {
            elTop.textContent = po.term_of_payment;
        } else if (sup.top_hari && sup.top_hari > 0) {
            elTop.textContent = `Tempo ${sup.top_hari} Hari (Transfer)`;
        } else {
            elTop.textContent = 'Transfer';
        }
    }
    const elRemark = document.getElementById('haccpSheetRemark');
    if (elRemark) elRemark.textContent = po.remark_txt || '-';

    const elApprover = document.getElementById('haccpSheetApprover');
    if (elApprover) elApprover.textContent = po.approved_by || (po.approver ? po.approver.name : 'Habieb Soleh Yoenanto');
}
window.renderPoHaccpSheet = renderPoHaccpSheet;

/**
 * =========================================================================
 * MODAL PENGIRIMAN DOKUMEN PO KE SUPPLIER VIA EMAIL
 * =========================================================================
 */
function openKirimSupplierModal(poData) {
    const modal = document.getElementById('modalKirimSupplier');
    const form = document.getElementById('formKirimSupplier');
    const elPoNo = document.getElementById('modalSendPoNo');
    const elSupplierNm = document.getElementById('modalSendSupplierNm');
    const elEmail = document.getElementById('modalSendSupplierEmail');
    const elCc = document.getElementById('modalSendCcEmail');
    const elNotes = document.getElementById('modalSendCustomNotes');
    const errBox = document.getElementById('sendSupplierAlertError');
    const btn = document.getElementById('btnSubmitSendSupplier');

    if (!modal || !form) return;

    if (errBox) {
        errBox.style.display = 'none';
        errBox.textContent = '';
    }

    if (btn) {
        btn.disabled = false;
        btn.innerHTML = `
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
            <span>Kirim Email Sekarang</span>
        `;
    }

    const base = window.poConfig?.baseUrl || '/gudang/po';
    form.action = base + '/' + poData.po_id + '/send-supplier';

    if (elPoNo) elPoNo.textContent = poData.po_no || '-';
    if (elSupplierNm) elSupplierNm.textContent = poData.supplier_nm || '-';
    if (elEmail) elEmail.value = poData.supplier_email || '';
    if (elCc) elCc.value = poData.default_cc || '';
    if (elNotes) elNotes.value = poData.default_notes || '';

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeKirimSupplierModal() {
    const modal = document.getElementById('modalKirimSupplier');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

function submitKirimSupplier(event) {
    const btn = document.getElementById('btnSubmitSendSupplier');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = `
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="animation: spin 1s linear infinite;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            <span>Sedang Memproses &amp; Mengirim...</span>
        `;
    }
}


