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
// FITUR MODAL POPUP DETAIL DOKUMEN PURCHASE ORDER (macOS QUICK VIEW)
// =========================================================================
function togglePoDetailMaximize() {
    const dialog = document.querySelector('#modalDetailPo .mac-po-dialog');
    if (dialog) {
        dialog.classList.toggle('is-maximized');
    }
}
window.togglePoDetailMaximize = togglePoDetailMaximize;

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
        const dialog = modal.querySelector('.mac-po-dialog');
        if (dialog) dialog.classList.remove('is-maximized');
    }
}
window.closePoDetailModal = closePoDetailModal;

function renderPoDetail(po) {
    const spinner = document.getElementById('poDetailLoadingSpinner');
    const content = document.getElementById('poDetailContentBody');

    if (spinner) spinner.style.display = 'none';
    if (content) content.style.display = 'block';

    const formatDate = (dateStr) => {
        if (!dateStr) return '-';
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return dateStr;
        return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
    };

    const formatRp = (num) => 'Rp ' + (parseFloat(num) || 0).toLocaleString('id-ID');

    // Set Header Nomor PO
    document.getElementById('poDetailNo').textContent = po.po_no || '-';

    // Status Badge
    const badge = document.getElementById('poDetailStatusBadge');
    let badgeText = po.status_cd || '-';
    let badgeBg = '#f1f5f9';
    let badgeClr = '#475569';

    if (po.status_cd === 'PENDING_APPROVAL') {
        badgeText = 'Menunggu Approval';
        badgeBg = '#fef3c7';
        badgeClr = '#b45309';
    } else if (po.status_cd === 'REJECTED') {
        badgeText = 'Ditolak Atasan';
        badgeBg = '#fee2e2';
        badgeClr = '#b91c1c';
    } else if (po.status_cd === 'COMPLETED') {
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

    // Banner Penolakan / Permintaan Revisi Atasan
    const rejectBanner = document.getElementById('poDetailRejectBanner');
    if (rejectBanner) {
        if (po.rejected_by || po.reject_reason) {
            rejectBanner.style.display = 'block';
            const rDate = document.getElementById('poDetailRejectDate');
            const rAuthor = document.getElementById('poDetailRejectAuthor');
            const rReason = document.getElementById('poDetailRejectReason');
            if (rDate) rDate.textContent = po.rejected_at ? formatDate(po.rejected_at) : '';
            if (rAuthor) rAuthor.textContent = po.rejected_by || 'Direksi / Reviewer';
            if (rReason) rReason.textContent = po.reject_reason || 'Memerlukan peninjauan ulang.';
        } else {
            rejectBanner.style.display = 'none';
        }
    }

    // Parameter Dokumen & Box Metadata
    document.getElementById('poDetailTgl').textContent = formatDate(po.po_tgl);
    document.getElementById('poDetailEta').textContent = formatDate(po.tgl_estimasi_datang);
    document.getElementById('poDetailCreatedBy').textContent = po.created_by || 'SYSTEM';
    const topText = po.term_of_payment || 'Standar Supplier';
    const elTopHeader = document.getElementById('poDetailTopHeader');
    if (elTopHeader) elTopHeader.textContent = topText;

    // Supplier
    const sup = po.supplier || {};
    document.getElementById('poDetailSupplierNm').textContent = sup.supplier_nm || '-';
    document.getElementById('poDetailSupplierCd').textContent = sup.supplier_cd ? `Kode: ${sup.supplier_cd}` : '';
    const supContact = [sup.telepon ? `📞 ${sup.telepon}` : (sup.kontak_no ? `📞 ${sup.kontak_no}` : ''), sup.alamat_txt || sup.alamat || ''].filter(Boolean).join(' • ');
    document.getElementById('poDetailSupplierAlamat').textContent = supContact || '-';

    // Gudang
    const gdg = po.gudang || {};
    document.getElementById('poDetailGudangNm').textContent = gdg.gudang_nm || '-';
    document.getElementById('poDetailGudangCd').textContent = gdg.gudang_cd ? `Kode: ${gdg.gudang_cd}` : '';
    document.getElementById('poDetailGudangAlamat').textContent = gdg.alamat_txt || gdg.alamat || '-';

    // Approval
    const approverNm = po.approver ? po.approver.name : (po.approved_by || 'Menunggu Persetujuan');
    const elApprover = document.getElementById('poDetailApproverNm');
    if (elApprover) elApprover.textContent = approverNm;
    const elApprovedAt = document.getElementById('poDetailApprovedAt');
    if (elApprovedAt) elApprovedAt.textContent = po.approved_at ? formatDate(po.approved_at) : (po.status_cd === 'REJECTED' ? 'Ditolak' : 'Belum');
    const elTaxSchemeHeader = document.getElementById('poDetailTaxSchemeHeader');
    if (elTaxSchemeHeader) {
        elTaxSchemeHeader.textContent = po.is_harga_termasuk_pajak ? 'Include PPN (Harga Termasuk)' : 'Exclude PPN (Plus Pajak)';
    }

    // Rincian Item Komoditas & Skema Pajak / Diskon
    const details = po.details || [];
    let totPesan = 0;
    let totTerima = 0;
    let totSisa = 0;
    const allTaxes = new Set();
    let maxTaxRate = 0;

    const tbody = document.getElementById('poDetailItemsTbody');
    tbody.innerHTML = '';

    details.forEach((d, idx) => {
        const brg = d.barang || {};
        const satuan = brg.satuan_dasar ? (brg.satuan_dasar.satuan_nm || brg.satuan_dasar.satuan_cd) : '-';
        const pQty = parseFloat(d.pesan_qty) || 0;
        const tQty = parseFloat(d.terima_qty) || 0;
        const sQty = (typeof d.sisa_qty !== 'undefined' && d.sisa_qty !== null) ? parseFloat(d.sisa_qty) : Math.max(0, pQty - tQty);
        
        totPesan += pQty;
        totTerima += tQty;
        totSisa += sQty;

        const hargaBruto = parseFloat(d.harga_nominal) || 0;
        const diskonPersen = parseFloat(d.diskon_persen) || 0;
        const diskonNominal = parseFloat(d.diskon_nominal) || 0;
        const potonganNominal = parseFloat(d.potongan_nominal) || 0;
        const hargaNetto = (parseFloat(d.harga_netto) || (hargaBruto - diskonNominal)) || 0;
        const subtotalFinal = (parseFloat(d.subtotal_tagihan) || parseFloat(d.subtotal_nominal) || (pQty * hargaNetto)) || 0;

        // Diskon & Potongan Label (Ringkas & Rapi)
        let diskonPotonganHtml = '';
        if (diskonPersen > 0 || diskonNominal > 0 || potonganNominal > 0) {
            const parts = [];
            if (diskonPersen > 0) {
                parts.push(`<span class="mac-badge-pill" style="background:#fef2f2; color:#dc2626; border-color:#fecaca; font-weight:800; font-size:0.675rem;">-${diskonPersen}%</span>`);
            }
            if (diskonNominal > 0) {
                parts.push(`<div style="color: #dc2626; font-size: 0.675rem; white-space: nowrap;">Disc: Rp ${diskonNominal.toLocaleString('id-ID')}</div>`);
            }
            if (potonganNominal > 0) {
                parts.push(`<div style="color: #b91c1c; font-size: 0.675rem; font-weight:700; white-space: nowrap;">Pot: -Rp ${potonganNominal.toLocaleString('id-ID')}</div>`);
            }
            diskonPotonganHtml = parts.join('');
        } else {
            diskonPotonganHtml = '<span style="color:#94a3b8; font-size:0.75rem;">-</span>';
        }

        // Pajak / PPN Item: Hanya Tampilkan KODE & Persentase Ringkas
        const pjk = d.pajak || {};
        const taxRate = parseFloat(d.ppn_persen) || parseFloat(pjk.tarif_persen) || 0;
        if (taxRate > maxTaxRate) maxTaxRate = taxRate;
        
        let taxCode = pjk.pajak_cd || d.ppn_tipe || (taxRate > 0 ? `PPN ${taxRate}%` : 'NON');
        // Singkatkan jika berupa kode komoditas panjang
        if (taxCode === 'PPH-22-KOMODITAS') taxCode = 'PPh 22';
        else if (taxCode.length > 12) taxCode = taxCode.substring(0, 12);
        allTaxes.add(taxCode);

        const isInclude = (typeof d.is_harga_termasuk_pajak !== 'undefined' && d.is_harga_termasuk_pajak !== null) 
            ? Boolean(d.is_harga_termasuk_pajak) 
            : Boolean(po.is_harga_termasuk_pajak);
        const taxNominal = parseFloat(d.ppn_nominal) || 0;

        let taxCellHtml = '';
        if (taxRate > 0 || taxNominal > 0) {
            taxCellHtml = `
                <span class="mac-badge-pill" style="background: #eff6ff; color: #0284c7; border-color: #bfdbfe; font-weight: 800; font-family: monospace; font-size: 0.675rem;">
                    ${taxCode}
                </span>
                <div style="font-size: 0.675rem; color: ${isInclude ? '#1e40af' : '#15803d'}; font-weight: 600; margin-top: 1px; white-space: nowrap;">
                    ${taxRate}% (${isInclude ? 'Inc' : 'Exc'})
                </div>
                ${taxNominal > 0 ? `<div style="font-size: 0.675rem; color: #64748b; font-family: monospace; white-space: nowrap;">Rp ${taxNominal.toLocaleString('id-ID')}</div>` : ''}
            `;
        } else {
            taxCellHtml = '<span class="mac-badge-pill" style="background: #f1f5f9; color: #64748b; border-color: #e2e8f0; font-size: 0.675rem;">Bebas (0%)</span>';
        }

        const sisaCellHtml = (po.closed_at && sQty > 0)
            ? `<span style="color: #dc2626; font-weight: 700;">${sQty.toLocaleString('id-ID')}</span><span class="badge" style="background:#fee2e2; color:#b91c1c; font-size:0.65rem; display:block; margin-top:2px;">Batal Sisa</span>`
            : `<span style="color: ${sQty > 0 ? '#ea580c' : '#64748b'}; font-weight: 600;">${sQty.toLocaleString('id-ID')}</span>`;

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td style="text-align: center; color: #64748b; font-weight: 600;">${idx + 1}</td>
            <td>
                <strong style="color: #0f172a; display: block; font-size: 0.8rem; line-height: 1.2;">${brg.barang_nm || '-'}</strong>
                <span style="font-family: monospace; font-size: 0.7rem; color: #64748b;">${brg.barang_cd || ''}</span>
            </td>
            <td style="text-align: center; color: #475569; font-weight: 600;">${satuan}</td>
            <td style="text-align: right; font-weight: 700; color: #0f172a; white-space: nowrap;">${pQty.toLocaleString('id-ID')}</td>
            <td style="text-align: right; font-size: 0.75rem; white-space: nowrap;">
                <span style="color: #16a34a; font-weight: 700;">${tQty.toLocaleString('id-ID')}</span> <span style="color:#cbd5e1;">/</span> ${sisaCellHtml}
            </td>
            <td style="text-align: right; color: #475569; white-space: nowrap;">Rp ${hargaBruto.toLocaleString('id-ID')}</td>
            <td style="text-align: right;">${diskonPotonganHtml}</td>
            <td style="text-align: right; font-weight: 600; color: #0f172a; white-space: nowrap;">Rp ${hargaNetto.toLocaleString('id-ID')}</td>
            <td style="text-align: right;">${taxCellHtml}</td>
            <td style="text-align: right; font-weight: 800; color: #0f172a; font-family: monospace; font-size: 0.825rem; white-space: nowrap;">Rp ${subtotalFinal.toLocaleString('id-ID')}</td>
        `;
        tbody.appendChild(tr);
    });

    if (details.length === 0) {
        tbody.innerHTML = '<tr><td colspan="10" style="text-align: center; padding: 2rem; color: #94a3b8;">Tidak ada rincian bahan dalam PO ini.</td></tr>';
    }

    // Progress Realisasi
    const pct = totPesan > 0 ? Math.min(100, Math.round((totTerima / totPesan) * 100)) : 0;
    document.getElementById('poDetailPctText').textContent = `${pct}% Terpenuhi`;
    document.getElementById('poDetailProgressBar').style.width = `${pct}%`;
    document.getElementById('poDetailQtyPesan').textContent = totPesan.toLocaleString('id-ID');
    document.getElementById('poDetailQtyTerima').textContent = totTerima.toLocaleString('id-ID');
    document.getElementById('poDetailQtySisa').textContent = totSisa.toLocaleString('id-ID');

    // Badges & Counters
    const badgeTable = document.getElementById('poDetailTableBadgeCount');
    if (badgeTable) badgeTable.textContent = `${details.length} Item Terdaftar`;
    const countInfo = document.getElementById('poDetailItemCountInfo');
    if (countInfo) countInfo.textContent = `Menampilkan ${details.length} komoditas pesanan`;

    // Rekap Finansial & Kalkulasi Pajak
    document.getElementById('poDetailSubtotalBruto').textContent = formatRp(po.subtotal_bruto);
    
    const diskonTotal = parseFloat(po.diskon_total) || 0;
    const potonganTotal = parseFloat(po.potongan_nominal) || 0;
    document.getElementById('poDetailDiskon').textContent = diskonTotal > 0 ? `- ${formatRp(diskonTotal)}` : 'Rp 0';
    document.getElementById('poDetailPotongan').textContent = potonganTotal > 0 ? `- ${formatRp(potonganTotal)}` : 'Rp 0';
    
    const elDiskonNote = document.getElementById('poDetailDiskonPercentNote');
    if (elDiskonNote) {
        if (parseFloat(po.subtotal_bruto) > 0 && diskonTotal > 0) {
            const avgPct = Math.round((diskonTotal / parseFloat(po.subtotal_bruto)) * 100);
            elDiskonNote.textContent = `(Akumulasi diskon item ~${avgPct}%)`;
        } else {
            elDiskonNote.textContent = '';
        }
    }

    document.getElementById('poDetailDpp').textContent = formatRp(po.dpp_nominal);

    // Pajak Details
    const taxNominal = parseFloat(po.ppn_nominal) || 0;
    document.getElementById('poDetailPpn').textContent = formatRp(taxNominal);
    
    const taxTreatmentText = po.is_harga_termasuk_pajak 
        ? 'Harga Termasuk Pajak (Inclusive)' 
        : 'Harga Belum Termasuk Pajak (Exclusive / Plus Pajak)';
    const taxNameCombined = allTaxes.size > 0 ? Array.from(allTaxes).join(', ') : 'PPN Standar';
    
    const elTaxTreatment = document.getElementById('poDetailTaxTreatment');
    if (elTaxTreatment) elTaxTreatment.textContent = taxTreatmentText;
    const elTaxName = document.getElementById('poDetailTaxName');
    if (elTaxName) elTaxName.textContent = taxNameCombined;
    const elTaxRate = document.getElementById('poDetailTaxRate');
    if (elTaxRate) elTaxRate.textContent = maxTaxRate > 0 ? `${maxTaxRate}%` : (taxNominal > 0 ? 'Dikenakan Pajak' : '0% (Non-PPN)');
    const elTaxSubNote = document.getElementById('poDetailTaxSubNote');
    if (elTaxSubNote) elTaxSubNote.textContent = `${taxNameCombined} • ${taxTreatmentText}`;

    const elTopVal = document.getElementById('poDetailTopVal');
    if (elTopVal) elTopVal.textContent = topText;

    document.getElementById('poDetailTotalTagihan').textContent = formatRp(po.total_tagihan || po.total_nominal);

    // Catatan
    const notesArr = [];
    if (po.catatan_txt) notesArr.push(`Catatan Purchasing: ${po.catatan_txt}`);
    if (po.remark_txt) notesArr.push(`Keterangan Tambahan: ${po.remark_txt}`);
    document.getElementById('poDetailCatatan').textContent = notesArr.length > 0 ? notesArr.join('\n') : 'Tidak ada catatan tambahan untuk dokumen PO ini.';

    // Riwayat Penerimaan GRN
    const penerimaan = po.penerimaan || [];
    const grnList = document.getElementById('poDetailGrnList');
    grnList.innerHTML = '';

    if (penerimaan.length > 0) {
        penerimaan.forEach(grn => {
            const card = document.createElement('div');
            card.style.cssText = 'background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.65rem 0.85rem; display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem;';
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
        grnList.innerHTML = '<div style="background: #ffffff; border: 1px dashed #cbd5e1; border-radius: 8px; padding: 0.85rem; text-align: center; color: #94a3b8; font-size: 0.8rem;">Belum ada riwayat penerimaan barang fisik (GRN) untuk Purchase Order ini.</div>';
    }

    // Link Tombol Footer & Header
    const baseUrl = (window.poConfig && window.poConfig.baseUrl) ? window.poConfig.baseUrl : '/gudang/po';
    window.currentActivePoId = po.po_id;
    const btnFull = document.getElementById('btnPoDetailFullView');
    const btnFullHeader = document.getElementById('btnPoDetailFullHeader');
    const btnPdf = document.getElementById('btnPoDetailPdf');
    if (btnFull) btnFull.href = `${baseUrl}/${po.po_id}`;
    if (btnFullHeader) btnFullHeader.href = `${baseUrl}/${po.po_id}`;
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

/**
 * Handler Modal Batalkan Sisa Pesanan & Selesaikan PO
 */
function openBatalkanSisaModal(poData) {
    const modal = document.getElementById('modalForceClose');
    if (!modal) return;

    const form = document.getElementById('formForceClose');
    const elPoNo = document.getElementById('mfcPoNo');
    const elSupplier = document.getElementById('mfcSupplier');
    const elTerima = document.getElementById('mfcTerimaQty');
    const elSisa = document.getElementById('mfcSisaQty');
    const textarea = document.getElementById('closed_reason');

    if (textarea) textarea.value = '';

    if (poData) {
        const base = window.poConfig?.baseUrl || '/gudang/po';
        if (form) form.action = poData.url || (base + '/' + poData.po_id + '/force-close');
        if (elPoNo) elPoNo.textContent = poData.po_no || '-';
        if (elSupplier) elSupplier.textContent = poData.supplier_nm || '-';
        if (elTerima) elTerima.textContent = Number(poData.total_terima || 0).toLocaleString('id-ID');
        if (elSisa) elSisa.textContent = Number(poData.total_sisa || 0).toLocaleString('id-ID');
    }

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}
window.openBatalkanSisaModal = openBatalkanSisaModal;
window.openForceCloseModal = openBatalkanSisaModal;

function closeForceCloseModal() {
    const modal = document.getElementById('modalForceClose');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}
window.closeForceCloseModal = closeForceCloseModal;



