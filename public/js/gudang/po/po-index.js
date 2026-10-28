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


// =========================================================================
// FITUR MODAL POPUP DETAIL DOKUMEN PURCHASE ORDER (QUICK VIEW)
// =========================================================================
function openPoDetailModal(poId) {
    const modal = document.getElementById('modalDetailPo');
    const spinner = document.getElementById('poDetailLoadingSpinner');
    const content = document.getElementById('poDetailContentBody');

    if (!modal) {
        console.error('Modal #modalDetailPo tidak ditemukan di DOM!');
        return;
    }
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    if (spinner) spinner.style.display = 'block';
    if (content) content.style.display = 'none';

    const baseUrl = (window.poConfig && window.poConfig.baseUrl) ? window.poConfig.baseUrl : '/gudang/po';

    fetch(`${baseUrl}/${poId}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(async response => {
        const res = await response.json().catch(() => ({}));
        if (!response.ok) {
            throw new Error(res.message || (`HTTP ${response.status} ${response.statusText}`));
        }
        return res;
    })
    .then(result => {
        if (result.status === 'success' && result.data) {
            renderPoDetail(result.data);
        } else {
            alert(result.message || 'Gagal memuat rincian Purchase Order.');
            closePoDetailModal();
        }
    })
    .catch(err => {
        console.error('Error memuat detail PO:', err);
        alert('Terjadi kesalahan saat memuat dokumen PO: ' + err.message);
        closePoDetailModal();
    });
}
window.openPoDetailModal = openPoDetailModal;

function closePoDetailModal() {
    const modal = document.getElementById('modalDetailPo');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}
window.closePoDetailModal = closePoDetailModal;

function renderPoDetail(po) {
    const spinner = document.getElementById('poDetailLoadingSpinner');
    const content = document.getElementById('poDetailContentBody');

    if (spinner) spinner.style.display = 'none';
    if (content) content.style.display = 'block';

    // Set Header
    document.getElementById('poDetailNo').textContent = po.po_no || '-';

    // Status Badge
    const badge = document.getElementById('poDetailStatusBadge');
    let badgeText = po.status_cd || '-';
    let badgeBg = '#f1f5f9';
    let badgeClr = '#475569';

    if (po.status_cd === 'COMPLETED') {
        badgeText = 'Selesai (100%)';
        badgeBg = '#dcfce7';
        badgeClr = '#15803d';
    } else if (po.status_cd === 'PARTIAL') {
        badgeText = `Sebagian Diterima (${po.persentase_terima || 0}%)`;
        badgeBg = '#e0f2fe';
        badgeClr = '#0369a1';
    } else if (po.status_cd === 'APPROVED') {
        badgeText = 'Disetujui (Approved)';
        badgeBg = '#e0f2fe';
        badgeClr = '#0284c7';
    } else if (po.status_cd === 'CLOSED') {
        badgeText = 'Ditutup (Closed)';
        badgeBg = '#f1f5f9';
        badgeClr = '#475569';
    } else if (po.status_cd === 'DRAFT') {
        badgeText = 'Draft';
        badgeBg = '#f1f5f9';
        badgeClr = '#475569';
    } else if (po.status_cd === 'CANCELLED') {
        badgeText = 'Dibatalkan';
        badgeBg = '#fee2e2';
        badgeClr = '#dc2626';
    }
    badge.textContent = badgeText;
    badge.style.background = badgeBg;
    badge.style.color = badgeClr;

    // Parameter Dokumen
    const formatDate = (dateStr) => {
        if (!dateStr) return '-';
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return dateStr;
        return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
    };

    document.getElementById('poDetailTgl').textContent = formatDate(po.po_tgl);
    document.getElementById('poDetailEta').textContent = formatDate(po.tgl_estimasi_datang);
    document.getElementById('poDetailCreatedBy').textContent = po.created_by || 'SYSTEM';

    // Supplier
    const sup = po.supplier || {};
    document.getElementById('poDetailSupplierNm').textContent = sup.supplier_nm || '-';
    document.getElementById('poDetailSupplierCd').textContent = sup.supplier_cd ? `Kode: ${sup.supplier_cd}` : '';
    document.getElementById('poDetailSupplierAlamat').textContent = [sup.telepon ? `📞 ${sup.telepon}` : '', sup.alamat || ''].filter(Boolean).join(' • ') || '-';

    // Gudang
    const gdg = po.gudang || {};
    document.getElementById('poDetailGudangNm').textContent = gdg.gudang_nm || '-';
    document.getElementById('poDetailGudangCd').textContent = gdg.gudang_cd ? `Kode: ${gdg.gudang_cd}` : '';
    document.getElementById('poDetailGudangAlamat').textContent = gdg.alamat || '-';

    // Progress
    const details = po.details || [];
    let totPesan = 0;
    let totTerima = 0;
    let totSisa = 0;

    details.forEach(d => {
        totPesan += parseFloat(d.pesan_qty) || 0;
        totTerima += parseFloat(d.terima_qty) || 0;
        totSisa += parseFloat(d.sisa_qty) || 0;
    });

    const pct = totPesan > 0 ? Math.min(100, Math.round((totTerima / totPesan) * 100)) : 0;
    document.getElementById('poDetailPctText').textContent = `${pct}% Terpenuhi`;
    document.getElementById('poDetailProgressBar').style.width = `${pct}%`;
    document.getElementById('poDetailQtyPesan').textContent = totPesan.toLocaleString('id-ID');
    document.getElementById('poDetailQtyTerima').textContent = totTerima.toLocaleString('id-ID');
    document.getElementById('poDetailQtySisa').textContent = totSisa.toLocaleString('id-ID');

    // Rincian Item Barang
    const tbody = document.getElementById('poDetailItemsTbody');
    tbody.innerHTML = '';

    details.forEach((d, idx) => {
        const brg = d.barang || {};
        const satuan = brg.satuan_dasar ? (brg.satuan_dasar.satuan_nm || brg.satuan_dasar.satuan_cd) : '-';
        const pQty = parseFloat(d.pesan_qty) || 0;
        const tQty = parseFloat(d.terima_qty) || 0;
        const sQty = parseFloat(d.sisa_qty) || 0;
        const harga = parseFloat(d.harga_nominal) || 0;
        const subtotal = (parseFloat(d.subtotal_tagihan) || (pQty * harga)) || 0;

        const tr = document.createElement('tr');
        tr.style.borderBottom = '1px solid #f1f5f9';
        tr.innerHTML = `
            <td style="padding: 0.55rem 0.75rem; text-align: center; color: #64748b;">${idx + 1}</td>
            <td style="padding: 0.55rem 0.75rem;">
                <strong style="color: #0f172a; display: block;">${brg.barang_nm || '-'}</strong>
                <span style="font-family: monospace; font-size: 0.75rem; color: #64748b;">${brg.barang_cd || ''}</span>
            </td>
            <td style="padding: 0.55rem 0.75rem; text-align: center; color: #475569;">${satuan}</td>
            <td style="padding: 0.55rem 0.75rem; text-align: right; font-weight: 600; color: #0f172a;">${pQty.toLocaleString('id-ID')}</td>
            <td style="padding: 0.55rem 0.75rem; text-align: right; font-weight: 600; color: #16a34a;">${tQty.toLocaleString('id-ID')}</td>
            <td style="padding: 0.55rem 0.75rem; text-align: right; font-weight: 600; color: ${sQty > 0 ? '#ea580c' : '#64748b'};">${sQty.toLocaleString('id-ID')}</td>
            <td style="padding: 0.55rem 0.75rem; text-align: right; color: #475569;">Rp ${harga.toLocaleString('id-ID')}</td>
            <td style="padding: 0.55rem 0.75rem; text-align: right; font-weight: 700; color: #0f172a;">Rp ${subtotal.toLocaleString('id-ID')}</td>
        `;
        tbody.appendChild(tr);
    });

    if (details.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 1.5rem; color: #94a3b8;">Tidak ada rincian bahan dalam PO ini.</td></tr>';
    }

    // Rekap Finansial
    const formatRp = (num) => 'Rp ' + (parseFloat(num) || 0).toLocaleString('id-ID');
    document.getElementById('poDetailSubtotalBruto').textContent = formatRp(po.subtotal_bruto);
    document.getElementById('poDetailDiskon').textContent = formatRp(parseFloat(po.diskon_total || 0) + parseFloat(po.potongan_nominal || 0));
    document.getElementById('poDetailDpp').textContent = formatRp(po.dpp_nominal);
    const ppnLabel = formatRp(po.ppn_nominal) + (po.is_harga_termasuk_pajak ? ' (Termasuk)' : '');
    document.getElementById('poDetailPpn').textContent = ppnLabel;
    document.getElementById('poDetailTotalTagihan').textContent = formatRp(po.total_tagihan || po.total_nominal);

    // Catatan
    document.getElementById('poDetailCatatan').textContent = po.catatan_txt || 'Tidak ada catatan tambahan untuk dokumen PO ini.';

    // Riwayat Penerimaan GRN
    const penerimaan = po.penerimaan || [];
    const grnList = document.getElementById('poDetailGrnList');
    grnList.innerHTML = '';

    if (penerimaan.length > 0) {
        penerimaan.forEach(grn => {
            const card = document.createElement('div');
            card.style.cssText = 'background: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.65rem 0.85rem; display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem;';
            card.innerHTML = `
                <div>
                    <strong style="color: #0284c7; font-family: monospace;">${grn.terima_no || '-'}</strong>
                    <span style="color: #64748b; margin-left: 0.5rem;">• Tgl: ${formatDate(grn.terima_tgl)}</span>
                    <div style="color: #475569; font-size: 0.75rem; margin-top: 0.15rem;">
                        SJ: <strong>${grn.surat_jalan_no || '-'}</strong> ${grn.nomor_polisi ? `• Truk: ${grn.nomor_polisi}` : ''}
                    </div>
                </div>
                <div>
                    <span class="badge ${grn.status_cd === 'CONFIRMED' ? 'badge-success' : 'badge-info'}" style="font-size: 0.725rem;">
                        ${grn.status_cd || 'DITERIMA'}
                    </span>
                </div>
            `;
            grnList.appendChild(card);
        });
    } else {
        grnList.innerHTML = '<div style="background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 6px; padding: 0.75rem; text-align: center; color: #94a3b8; font-size: 0.8rem;">Belum ada riwayat penerimaan barang fisik (GRN) untuk Purchase Order ini.</div>';
    }

    // Link Tombol Footer
    const baseUrl = (window.poConfig && window.poConfig.baseUrl) ? window.poConfig.baseUrl : '/gudang/po';
    window.currentActivePoId = po.po_id;
    const btnFull = document.getElementById('btnPoDetailFullView');
    const btnPdf = document.getElementById('btnPoDetailPdf');
    if (btnFull) btnFull.href = `${baseUrl}/${po.po_id}`;
    if (btnPdf) btnPdf.href = `${baseUrl}/${po.po_id}/export-pdf`;
}

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

    // Jika data sudah pernah di-fetch, render langsung seketika
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

    // Gudang & Kota
    const gdg = po.gudang || {};
    const elGdgCity = document.getElementById('haccpSheetGudangCity');
    const elGdgAlamat = document.getElementById('haccpSheetGudangAlamat');
    if (elGdgCity) elGdgCity.textContent = gdg.gudang_nm ? `${gdg.gudang_nm} - Magelang` : 'Magelang';
    if (elGdgAlamat) elGdgAlamat.textContent = gdg.alamat || 'Jl. Munggur No. 2 Ambartawang, Kec. Mungkid, Kab. Magelang, Jawa Tengah 56512';

    // Tanggal Tanda Tangan
    const formatDateIndo = (dateStr) => {
        if (!dateStr) return new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return dateStr;
        return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
    };
    const elTgl = document.getElementById('haccpSheetTgl');
    if (elTgl) elTgl.textContent = formatDateIndo(po.po_tgl);

    // Link Download PDF
    const baseUrl = (window.poConfig && window.poConfig.baseUrl) ? window.poConfig.baseUrl : '/gudang/po';
    const btnPdf = document.getElementById('btnHaccpDownloadPdf');
    if (btnPdf) btnPdf.href = `${baseUrl}/${po.po_id}/export-pdf`;

    // Render Tabel Rincian Bahan
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

    // Tambahkan 2-3 baris kosong seperti format formulir asli
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

document.addEventListener('DOMContentLoaded', function () {
    // Escape key listener
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeQuickReceiveIndexModal();
            closePoDetailModal();
            closePoHaccpModal();
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

