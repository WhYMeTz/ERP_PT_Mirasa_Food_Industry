/**
 * JAVASCRIPT LOGIC - MODUL VERIFIKASI PEMAKAIAN BAHAN & POSTING JURNAL HPP
 * ERP PT Mirasa Food Industry
 */

let activeDropdownMenu = null;
let currentQiData = null;
let qiRowCounter = 0;

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

window.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeQuickInspectModal();
        closeModalApprove();
        closeModalReject();
    }
});

// ═══════════════════════════════════════════════════════════════════════════
// MODAL APPROVE / POSTING PEMAKAIAN BIASA
// ═══════════════════════════════════════════════════════════════════════════
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

// ═══════════════════════════════════════════════════════════════════════════
// MODAL REJECT / TOLAK DOKUMEN KE GUDANG
// ═══════════════════════════════════════════════════════════════════════════
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

// ═══════════════════════════════════════════════════════════════════════════
// QUICK INSPECT & EDIT MODAL (POPUP VERIFIKASI & DOUBLE-CLICK TABLE ROW)
// ═══════════════════════════════════════════════════════════════════════════

function openQuickInspectModal(pakaiId) {
    const modal = document.getElementById('modalQuickInspect');
    if (!modal) return;

    // Reset & Tampilkan modal dengan loading
    modal.style.display = 'flex';
    document.getElementById('qiLoadingSpinner').style.display = 'flex';
    document.getElementById('qiModalBody').style.display = 'none';
    hideQiAlert();

    const url = `/keuangan/verifikasi-pemakaian/${pakaiId}`;
    fetch(url, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => {
        if (!res.ok) throw new Error('Gagal memuat data dokumen pemakaian.');
        return res.json();
    })
    .then(data => {
        currentQiData = data;
        populateQuickInspectModal(data);
    })
    .catch(err => {
        console.error(err);
        alert('Gagal mengambil data verifikasi pemakaian: ' + err.message);
        closeQuickInspectModal();
    });
}

function closeQuickInspectModal() {
    const modal = document.getElementById('modalQuickInspect');
    if (modal) modal.style.display = 'none';
    currentQiData = null;
}

function populateQuickInspectModal(data) {
    const pakai = data.pakai;
    const isDraft = (pakai.status_st === 'DRAFT');

    // Header info
    document.getElementById('qiHeaderPakaiNo').textContent = pakai.pakai_no || '-';
    document.getElementById('qiHeaderCreatedBy').textContent = pakai.created_by || '-';

    const statusBadge = document.getElementById('qiHeaderStatusBadge');
    if (isDraft) {
        statusBadge.className = 'badge-status badge-draft';
        statusBadge.textContent = '⏳ DRAFT';
    } else {
        statusBadge.className = 'badge-status badge-posted';
        statusBadge.textContent = '✓ POSTED';
    }

    // Input form
    const tglInput = document.getElementById('qi_pakai_tgl');
    const gudangSelect = document.getElementById('qi_gudang_id');
    const tujuanInput = document.getElementById('qi_tujuan_pemakaian');
    const catatanInput = document.getElementById('qi_catatan_txt');

    tglInput.value = pakai.pakai_tgl || '';
    tglInput.readOnly = !isDraft;

    // Render gudang options
    let gudangHtml = '';
    const gudangList = data.gudangList || [];
    if (gudangList.length > 0) {
        gudangList.forEach(g => {
            const isSel = (parseInt(g.gudang_id) === parseInt(pakai.gudang_id)) ? 'selected' : '';
            gudangHtml += `<option value="${g.gudang_id}" ${isSel}>${g.gudang_nm}</option>`;
        });
    } else {
        gudangHtml = `<option value="${pakai.gudang_id}" selected>${pakai.gudang_nm}</option>`;
    }
    gudangSelect.innerHTML = gudangHtml;
    gudangSelect.disabled = !isDraft;

    tujuanInput.value = pakai.tujuan_pemakaian || '';
    tujuanInput.readOnly = !isDraft;

    catatanInput.value = pakai.catatan_txt || '';
    catatanInput.readOnly = !isDraft;

    // Tombol aksi footer & full page link
    const btnFullPage = document.getElementById('qiBtnFullPage');
    btnFullPage.href = `/keuangan/verifikasi-pemakaian/${pakai.pakai_id}`;

    const btnAddRow = document.getElementById('qiBtnAddRow');
    const btnSaveDraft = document.getElementById('qiBtnSaveDraft');
    const btnApprove = document.getElementById('qiBtnApprove');
    const btnReject = document.getElementById('qiBtnReject');
    const thAction = document.getElementById('qiThAction');
    const footAction = document.getElementById('qiFootTdAction');

    if (isDraft) {
        btnAddRow.style.display = 'inline-flex';
        btnSaveDraft.style.display = 'inline-flex';
        btnApprove.style.display = 'inline-flex';
        btnReject.style.display = 'inline-flex';
        thAction.style.display = 'table-cell';
        if (footAction) footAction.style.display = 'table-cell';
    } else {
        btnAddRow.style.display = 'none';
        btnSaveDraft.style.display = 'none';
        btnApprove.style.display = 'none';
        btnReject.style.display = 'none';
        thAction.style.display = 'none';
        if (footAction) footAction.style.display = 'none';
    }

    // Render Rows Tabel
    const tbody = document.getElementById('qiTableBody');
    tbody.innerHTML = '';
    qiRowCounter = 0;

    if (isDraft && data.initialItems) {
        data.initialItems.forEach(item => {
            renderQiDraftRow(item);
        });
        syncQiBatchOptionsAcrossRows();
    } else if (!isDraft && data.itemsCheck) {
        data.itemsCheck.forEach(item => {
            renderQiPostedRow(item);
        });
    }

    updateQiTotals();

    // Selesai memuat
    document.getElementById('qiLoadingSpinner').style.display = 'none';
    document.getElementById('qiModalBody').style.display = 'block';
}

function renderQiBarangOptions(selectedBarangId = '', categoryFilter = '') {
    const list = currentQiData?.barangList || [];
    const filtered = categoryFilter 
        ? list.filter(b => b.kategori_kelompok === categoryFilter)
        : list;

    let html = '<option value="">-- Pilih Bahan Produksi --</option>';

    if (categoryFilter) {
        filtered.forEach(b => {
            const jenis = b.jenis_barang ? b.jenis_barang.jenis_barang_cd : '';
            const harga = b.harga_beli_standar || 0;
            const satuan = b.satuan_dasar?.satuan_nm || 'Kg';
            const isSelected = (parseInt(b.barang_id) === parseInt(selectedBarangId)) ? 'selected' : '';
            html += `<option value="${b.barang_id}" data-harga="${harga}" data-satuan="${satuan}" data-kategori="${b.kategori_kelompok}" ${isSelected}>[${b.barang_cd}] ${b.barang_nm} (${jenis})</option>`;
        });
    } else {
        const groups = {
            'BAHAN_BAKU': { label: 'Bahan Baku (Singkong, Ubi, Berko, Opak)', items: [] },
            'BAHAN_PENOLONG': { label: 'Bahan Penolong & Bumbu (Minyak, Bumbu, dll)', items: [] },
            'KEMASAN': { label: 'Kemasan & Packaging (Karton, Plastik, Lakban)', items: [] },
        };

        filtered.forEach(b => {
            const grp = b.kategori_kelompok || 'BAHAN_PENOLONG';
            if (groups[grp]) {
                groups[grp].items.push(b);
            } else {
                groups['BAHAN_PENOLONG'].items.push(b);
            }
        });

        for (const [key, grp] of Object.entries(groups)) {
            if (grp.items.length > 0) {
                html += `<optgroup label="${grp.label}">`;
                grp.items.forEach(b => {
                    const jenis = b.jenis_barang ? b.jenis_barang.jenis_barang_cd : '';
                    const harga = b.harga_beli_standar || 0;
                    const satuan = b.satuan_dasar?.satuan_nm || 'Kg';
                    const isSelected = (parseInt(b.barang_id) === parseInt(selectedBarangId)) ? 'selected' : '';
                    html += `<option value="${b.barang_id}" data-harga="${harga}" data-satuan="${satuan}" data-kategori="${b.kategori_kelompok}" ${isSelected}>[${b.barang_cd}] ${b.barang_nm} (${jenis})</option>`;
                });
                html += `</optgroup>`;
            }
        }
    }

    return html;
}

function setQiRowCategory(btn, cat) {
    const group = btn.closest('.cat-pill-group');
    if (group) {
        group.querySelectorAll('.btn-chip').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
    }

    const tr = btn.closest('tr');
    const select = tr.querySelector('.qi-item-barang');
    const currentVal = select.value;
    select.innerHTML = renderQiBarangOptions(currentVal, cat);

    if (currentVal && !select.value) {
        onQiBarangChange(select);
    }
}

let isSyncingQiRows = false;

// Sinkronisasi ketersediaan batch ke baris-baris lain dalam modal Quick Inspect
function syncQiBatchOptionsAcrossRows(triggerRow = null) {
    if (isSyncingQiRows) return;
    isSyncingQiRows = true;
    try {
        const rows = document.querySelectorAll('#qiTableBody tr.qi-table-row');
        rows.forEach(r => {
            if (r !== triggerRow) {
                const barangId = r.querySelector('.qi-item-barang')?.value;
                const batchSelect = r.querySelector('.qi-item-batch');
                const curBatch = batchSelect?.value;
                const curGrade = (r.querySelector('.qi-item-grade')?.value || 'A').toUpperCase();

                if (barangId && r._allBatches && r._allBatches.length > 0) {
                    renderQiBatchOptionsInRow(r, curGrade, curBatch);
                }
            }
        });
    } finally {
        isSyncingQiRows = false;
    }
}

function setQiRowGrade(btn, grade) {
    const group = btn.closest('.grade-pill-group');
    if (group) {
        group.querySelectorAll('.btn-chip').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
    }

    const tr = btn.closest('tr');
    const gradeInput = tr.querySelector('.qi-item-grade');
    if (gradeInput) {
        gradeInput.value = grade;
    }
    const curBatch = tr.querySelector('.qi-item-batch')?.value;
    renderQiBatchOptionsInRow(tr, grade, curBatch);
}

// Render Opsi Batch ke select row modal Quick Inspect dengan proteksi alokasi antar-baris (cross-row tracking)
function renderQiBatchOptionsInRow(tr, gradeFilter = '', preferredBatchNo = null) {
    const batchSelect = tr.querySelector('.qi-item-batch');
    const batchInfo = tr.querySelector('.qi-batch-info');
    if (!batchSelect) return;

    const batches = tr._allBatches || [];

    if (batches.length === 0) {
        batchSelect.innerHTML = '<option value="">(Stok Fisik Kosong / Habis di Gudang ini)</option>';
        if (batchInfo) {
            batchInfo.innerHTML = '<span style="color: #dc2626; font-weight: 700; background: #fee2e2; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.725rem;">⚠️ Stok fisik bahan ini habis di gudang terpilih.</span>';
        }
        const hargaEl = tr.querySelector('.qi-item-harga');
        if (hargaEl) hargaEl.value = 0;
        recalculateQiRow(batchSelect);
        return;
    }

    let filtered = gradeFilter 
        ? batches.filter(b => (b.grade_cd || 'A').toUpperCase() === gradeFilter.toUpperCase())
        : batches;

    if (filtered.length === 0) {
        filtered = batches;
        const group = tr.querySelector('.grade-pill-group');
        if (group) {
            group.querySelectorAll('.btn-chip').forEach(b => b.classList.remove('active'));
            group.children[0]?.classList.add('active');
        }
    }

    // Hitung kuantitas batch yang sudah dipakai di baris-baris lain dalam modal Quick Inspect (khusus barang yang sama)
    const usedQtyByOtherRows = {};
    const usedDetailByOtherRows = {};
    const rowBarangId = tr.querySelector('.qi-item-barang')?.value || '';
    document.querySelectorAll('#qiTableBody tr.qi-table-row').forEach(r => {
        if (r !== tr) {
            const barVal = r.querySelector('.qi-item-barang')?.value || '';
            const bVal = r.querySelector('.qi-item-batch')?.value;
            const gVal = (r.querySelector('.qi-item-grade')?.value || '').toUpperCase();
            const qVal = parseFloat(r.querySelector('.qi-item-qty')?.value || 0);
            const rNum = r.querySelector('.qi-row-index')?.textContent.trim() || 'Baris';
            if (barVal && rowBarangId && parseInt(barVal) === parseInt(rowBarangId) && bVal && qVal > 0) {
                const key = `${bVal}_${gVal}`;
                usedQtyByOtherRows[key] = (usedQtyByOtherRows[key] || 0) + qVal;
                usedQtyByOtherRows[bVal] = (usedQtyByOtherRows[bVal] || 0) + qVal;

                if (!usedDetailByOtherRows[key]) usedDetailByOtherRows[key] = [];
                usedDetailByOtherRows[key].push(`Baris #${rNum} (${qVal.toLocaleString('id-ID')})`);
            }
        }
    });

    let firstAvailableIdx = -1;
    filtered.forEach((b, idx) => {
        const gCd = (b.grade_cd || 'A').toUpperCase();
        const sisaFisik = parseFloat(b.sisa_qty || 0);
        const sisaBebasServer = (b.sisa_bebas !== undefined) ? parseFloat(b.sisa_bebas) : sisaFisik;
        const draftQty = parseFloat(b.draft_qty || 0);
        const usedByOthers = usedQtyByOtherRows[`${b.batch_no}_${gCd}`] || usedQtyByOtherRows[b.batch_no] || 0;
        const netSisaBebas = Math.max(0, sisaBebasServer - usedByOthers);
        const isUsedUp = (sisaBebasServer > 0.0001 && netSisaBebas <= 0.0001);
        const booked = b.is_fully_booked || (sisaBebasServer <= 0.0001 && draftQty > 0) || isUsedUp;

        if (!booked && netSisaBebas > 0.0001 && firstAvailableIdx === -1) {
            firstAvailableIdx = idx;
        }
    });

    // Tentukan batch mana yang terpilih
    let selectedIdx = -1;
    if (preferredBatchNo) {
        selectedIdx = filtered.findIndex(b => b.batch_no === preferredBatchNo);
        if (selectedIdx !== -1) {
            const b = filtered[selectedIdx];
            const gCd = (b.grade_cd || 'A').toUpperCase();
            const sisaBebasServer = (b.sisa_bebas !== undefined) ? parseFloat(b.sisa_bebas) : parseFloat(b.sisa_qty || 0);
            const usedByOthers = usedQtyByOtherRows[`${b.batch_no}_${gCd}`] || usedQtyByOtherRows[b.batch_no] || 0;
            const netSisaBebas = Math.max(0, sisaBebasServer - usedByOthers);
            if (netSisaBebas <= 0.0001) {
                // Batch pilihan sudah habis dipakai di baris lain -> alihkan otomatis ke batch tertua berikutnya yang masih tersedia
                selectedIdx = firstAvailableIdx;
            }
        }
    }

    if (selectedIdx === -1) {
        selectedIdx = firstAvailableIdx;
    }

    let html = '';
    if (firstAvailableIdx === -1) {
        html += `<option value="" disabled ${selectedIdx === -1 ? 'selected' : ''} style="color: #dc2626; font-weight: 700; background: #fee2e2;">⛔ SEMUA BATCH HABIS DIPAKAI DI BARIS LAIN</option>`;
    }

    filtered.forEach((b, idx) => {
        const isSelected = (idx === selectedIdx);
        const isTop = (idx === firstAvailableIdx);
        const prefix = isTop ? '⭐ [FIFO Tertua] ' : '';
        const gCd = (b.grade_cd || 'A').toUpperCase();
        const expInfo = (b.expired_tgl && b.expired_tgl !== '-') ? ` | Exp: ${b.expired_tgl}` : '';
        const tglTerima = (b.tgl_terima && b.tgl_terima !== '-') ? ` | Masuk: ${b.tgl_terima}` : '';
        
        const sisaFisik = parseFloat(b.sisa_qty || 0);
        const sisaBebasServer = (b.sisa_bebas !== undefined) ? parseFloat(b.sisa_bebas) : sisaFisik;
        const draftQty = parseFloat(b.draft_qty || 0);
        const draftDocs = (Array.isArray(b.draft_doc_nos) ? b.draft_doc_nos.join(', ') : (b.draft_doc_nos || ''));

        const usedByOthers = usedQtyByOtherRows[`${b.batch_no}_${gCd}`] || usedQtyByOtherRows[b.batch_no] || 0;
        const usedDetails = (usedDetailByOtherRows[`${b.batch_no}_${gCd}`] || []).join(', ');
        const netSisaBebas = Math.max(0, sisaBebasServer - usedByOthers);
        const isUsedUpInForm = (sisaBebasServer > 0.0001 && netSisaBebas <= 0.0001);
        const isFullyBooked = b.is_fully_booked || (sisaBebasServer <= 0.0001 && draftQty > 0) || isUsedUpInForm;

        let labelText = '';
        if (isUsedUpInForm) {
            labelText = `⛔ ${b.batch_no} • [Grade ${gCd}] (HABIS - Dipakai di ${usedDetails || 'baris lain'})`;
        } else if (b.is_fully_booked || (sisaBebasServer <= 0.0001 && draftQty > 0)) {
            labelText = `⛔ ${b.batch_no} • [Grade ${gCd}] (HABIS di Draft [${draftDocs}])`;
        } else if (usedByOthers > 0) {
            labelText = `⚠️ ${prefix}${b.batch_no} • [Grade ${gCd}] (Sisa Bebas: ${netSisaBebas.toLocaleString('id-ID')} | ${usedByOthers.toLocaleString('id-ID')} dipakai di ${usedDetails || 'baris lain'})`;
        } else if (draftQty > 0) {
            labelText = `⚠️ ${prefix}${b.batch_no} • [Grade ${gCd}] (Sisa Bebas: ${netSisaBebas.toLocaleString('id-ID')})${expInfo}`;
        } else {
            labelText = `${prefix}${b.batch_no} • [Grade ${gCd}] (Sisa: ${netSisaBebas.toLocaleString('id-ID')})${tglTerima}${expInfo}`;
        }

        const disabledAttr = (isFullyBooked || netSisaBebas <= 0.0001) 
            ? 'disabled style="color: #94a3b8; background: #f1f5f9; text-decoration: line-through; cursor: not-allowed;"' 
            : '';

        html += `<option value="${b.batch_no}" 
            data-stok-id="${b.stok_id || ''}" 
            data-grade="${gCd}" 
            data-sisa="${sisaFisik}" 
            data-sisa-bebas="${netSisaBebas}"
            data-draft-qty="${draftQty}"
            data-draft-docs="${draftDocs}"
            data-is-fully-booked="${(isFullyBooked && netSisaBebas <= 0.0001) ? 1 : 0}"
            data-harga="${b.harga_satuan || 0}" 
            data-masuk="${b.tgl_terima || '-'}" 
            data-exp="${b.expired_tgl || '-'}" 
            ${disabledAttr}
            ${isSelected ? 'selected' : ''}>
            ${labelText}
        </option>`;
    });
    batchSelect.innerHTML = html;

    onQiBatchChange(batchSelect);
}

function selectOldestBatchInQiRow(btn) {
    const tr = btn.closest('tr');
    if (!tr) return;
    const batchSelect = tr.querySelector('.qi-item-batch');
    if (!batchSelect) return;

    for (let i = 0; i < batchSelect.options.length; i++) {
        const opt = batchSelect.options[i];
        if (opt.value && !opt.disabled) {
            batchSelect.selectedIndex = i;
            onQiBatchChange(batchSelect);
            break;
        }
    }
}
window.selectOldestBatchInQiRow = selectOldestBatchInQiRow;

function autoSplitQiBatchForRow(btn) {
    const tr = btn.closest('tr');
    if (!tr) return;
    const barangSelect = tr.querySelector('.qi-item-barang');
    const batchSelect = tr.querySelector('.qi-item-batch');
    const qtyInput = tr.querySelector('.qi-item-qty');
    const gradeInput = tr.querySelector('.qi-item-grade');
    if (!barangSelect || !batchSelect || !qtyInput) return;

    const barangId = barangSelect.value;
    const totalRequested = parseFloat(qtyInput.value || 0);
    const selectedOpt = batchSelect.selectedOptions[0];
    if (!selectedOpt) return;

    const sisaFisik = parseFloat(selectedOpt.getAttribute('data-sisa') || 0);
    const sisaBebas = parseFloat(selectedOpt.getAttribute('data-sisa-bebas') !== null ? selectedOpt.getAttribute('data-sisa-bebas') : sisaFisik);
    if (totalRequested <= sisaBebas) return;

    const takeCurrent = Math.min(totalRequested, sisaBebas);
    qtyInput.value = takeCurrent;
    recalculateQiRow(qtyInput);

    let remainingQty = totalRequested - takeCurrent;
    const currentGrade = (gradeInput?.value || selectedOpt.getAttribute('data-grade') || 'A').toUpperCase();

    const allBatches = tr._allBatches || [];
    let candidateBatches = allBatches.filter(b => (b.grade_cd || 'A').toUpperCase() === currentGrade);
    if (candidateBatches.length === 0) candidateBatches = allBatches;

    for (const b of candidateBatches) {
        if (remainingQty <= 0.0001) break;
        if (b.batch_no === selectedOpt.value) continue;

        let usedOther = 0;
        document.querySelectorAll('#qiTableBody tr.qi-table-row').forEach(r => {
            const bId = r.querySelector('.qi-item-barang')?.value;
            const bNo = r.querySelector('.qi-item-batch')?.value;
            const q = parseFloat(r.querySelector('.qi-item-qty')?.value || 0);
            if (bId === barangId && bNo === b.batch_no) {
                usedOther += q;
            }
        });

        const bSisaFisik = parseFloat(b.sisa_qty || 0);
        const bSisaBebas = (b.sisa_bebas !== undefined) ? parseFloat(b.sisa_bebas) : bSisaFisik;
        const netAvailable = Math.max(0, bSisaBebas - usedOther);

        if (netAvailable > 0.0001) {
            const takeFromThis = Math.min(remainingQty, netAvailable);
            const itemObj = (currentQiData?.barangList || []).find(it => parseInt(it.barang_id) === parseInt(barangId));
            const newItem = {
                barang_id: barangId,
                batch_no: b.batch_no,
                grade_cd: b.grade_cd || currentGrade,
                qty_keluar: takeFromThis,
                harga_satuan: b.harga_satuan || parseFloat(tr.querySelector('.qi-item-harga')?.value || 0),
                satuan_nm: tr.querySelector('.qi-item-satuan')?.textContent.trim() || '',
                all_batches: allBatches
            };
            renderQiDraftRow(newItem, itemObj?.kategori_kelompok || '');
            remainingQty -= takeFromThis;
        }
    }

    if (remainingQty > 0.0001) {
        showQiAlert(`Sebagian kuantitas (${remainingQty.toLocaleString('id-ID')}) tidak dapat dialokasikan karena stok batch gudang sudah habis.`, 'warning');
    }

    reindexQiRows();
    updateQiTotals();
    syncQiBatchOptionsAcrossRows();
}
window.autoSplitQiBatchForRow = autoSplitQiBatchForRow;

function renderNormalQiBatchInfo(tr) {
    const batchSelect = tr.querySelector('.qi-item-batch');
    const info = tr.querySelector('.qi-batch-info');
    if (!batchSelect || !info) return;

    const selectedOpt = batchSelect.selectedOptions[0];
    if (!selectedOpt || !selectedOpt.value) {
        info.innerHTML = '';
        return;
    }

    const sisa = parseFloat(selectedOpt.getAttribute('data-sisa') || 0);
    const sisaBebas = parseFloat(selectedOpt.getAttribute('data-sisa-bebas') !== null ? selectedOpt.getAttribute('data-sisa-bebas') : sisa);
    const draftQty = parseFloat(selectedOpt.getAttribute('data-draft-qty') || 0);
    const draftDocs = selectedOpt.getAttribute('data-draft-docs') || '';
    const tglMasuk = selectedOpt.getAttribute('data-masuk') || '-';
    const grade = (tr.querySelector('.qi-item-grade')?.value || selectedOpt.getAttribute('data-grade') || 'A').toUpperCase();

    const gradeBadge = (grade === 'A')
        ? `<span style="background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; font-weight: 700; padding: 1px 5px; border-radius: 4px; font-size: 0.7rem;">🟢 Grade A</span>`
        : ((grade === 'B')
            ? `<span style="background: #fffbeb; color: #b45309; border: 1px solid #fde68a; font-weight: 700; padding: 1px 5px; border-radius: 4px; font-size: 0.7rem;">🟡 Grade B</span>`
            : `<span style="background: #f1f5f9; color: #334155; font-weight: 600; padding: 1px 5px; border-radius: 4px; font-size: 0.7rem;">Grade ${grade}</span>`);

    let draftNotice = '';
    if (draftQty > 0) {
        draftNotice = `
            <div style="background: #fffbeb; border: 1px solid #f59e0b; border-radius: 4px; padding: 0.2rem 0.4rem; margin-top: 0.2rem; font-size: 0.7rem; color: #92400e;">
                ⚠️ <strong>${draftQty.toLocaleString('id-ID')} unit</strong> di DRAFT [${draftDocs}]. Sisa bebas: <strong style="color:#065f46;">${sisaBebas.toLocaleString('id-ID')}</strong>.
            </div>
        `;
    }

    // Hitung kuantitas batch yang sudah dipakai di baris-baris lain dalam form ini
    const usedQtyByOtherRows = {};
    const rowBarangId = tr.querySelector('.qi-item-barang')?.value || '';
    document.querySelectorAll('#qiTableBody tr.qi-table-row').forEach(r => {
        if (r !== tr) {
            const barVal = r.querySelector('.qi-item-barang')?.value || '';
            const bVal = r.querySelector('.qi-item-batch')?.value?.trim();
            const gVal = (r.querySelector('.qi-item-grade')?.value || '').toUpperCase();
            const qVal = parseFloat(r.querySelector('.qi-item-qty')?.value || 0);
            if (barVal && rowBarangId && parseInt(barVal) === parseInt(rowBarangId) && bVal && qVal > 0) {
                const key = bVal + '_' + gVal;
                usedQtyByOtherRows[key] = (usedQtyByOtherRows[key] || 0) + qVal;
                usedQtyByOtherRows[bVal] = (usedQtyByOtherRows[bVal] || 0) + qVal;
            }
        }
    });

    // Temukan opsi batch terlama yang BENAR-BENAR MASIH MEMILIKI SISA BEBAS (> 0)
    let oldestOpt = null;
    for (let i = 0; i < batchSelect.options.length; i++) {
        const opt = batchSelect.options[i];
        if (!opt.value) continue;

        const optGrade = (opt.getAttribute('data-grade') || 'A').toUpperCase();
        if (grade && optGrade !== grade) continue;

        const optSisaBebas = parseFloat(opt.getAttribute('data-sisa-bebas') !== null ? opt.getAttribute('data-sisa-bebas') : (opt.getAttribute('data-sisa') || 0));
        const optFullyBooked = (opt.getAttribute('data-is-fully-booked') === '1') || opt.disabled;
        const usedOther = usedQtyByOtherRows[opt.value + '_' + optGrade] || usedQtyByOtherRows[opt.value] || 0;
        const netAvailable = optSisaBebas - usedOther;

        if (!optFullyBooked && netAvailable > 0.0001) {
            oldestOpt = opt;
            break;
        }
    }

    const isSameBatch = selectedOpt && oldestOpt && (selectedOpt.value === oldestOpt.value);
    const oldestSisaBebas = oldestOpt ? parseFloat(oldestOpt.getAttribute('data-sisa-bebas') !== null ? oldestOpt.getAttribute('data-sisa-bebas') : (oldestOpt.getAttribute('data-sisa') || 0)) : 0;
    const oldestBatchNo = oldestOpt ? oldestOpt.value : '-';
    const oldestMasuk = oldestOpt ? (oldestOpt.getAttribute('data-masuk') || '-') : '-';

    const isSkippingOldest = oldestOpt && selectedOpt && (selectedOpt.index > oldestOpt.index);

    if (isSameBatch || !isSkippingOldest || !oldestOpt) {
        batchSelect.style.borderColor = '#cbd5e1';
        batchSelect.style.backgroundColor = '#ffffff';
        info.innerHTML = `
            <div style="display: flex; align-items: center; gap: 0.35rem; margin-top: 0.25rem;">
                ${gradeBadge}
                <span style="color: #065f46; font-weight: 700; background: #ecfdf5; padding: 0.15rem 0.5rem; border-radius: 4px; border: 1px solid #a7f3d0; font-size: 0.725rem;">
                    ⭐ Urutan FIFO Tepat (Masuk: ${tglMasuk})
                </span>
            </div>
            ${draftNotice}
        `;
    } else if (isSkippingOldest && oldestSisaBebas > 0) {
        batchSelect.style.borderColor = '#f59e0b';
        batchSelect.style.backgroundColor = '#fffdf5';
        info.innerHTML = `
            <div style="background: #fffbeb; border: 1.5px solid #f59e0b; border-radius: 6px; padding: 0.35rem 0.55rem; margin-top: 0.25rem; color: #92400e;">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.35rem; flex-wrap: wrap;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 0.25rem; margin-bottom: 0.1rem;">
                            ${gradeBadge}
                            <strong style="color: #b45309; font-size: 0.75rem;">
                                ⚠️ Ada Batch Lebih Lama: [${oldestBatchNo}]
                            </strong>
                        </div>
                        <div style="font-size: 0.7rem; color: #78350f;">
                            Masuk: <strong>${oldestMasuk}</strong> • Sisa Bebas: <strong>${oldestSisaBebas.toLocaleString('id-ID')} unit</strong>
                        </div>
                    </div>
                    <button type="button" onclick="selectOldestBatchInQiRow(this)" style="background: #059669; color: #ffffff; font-weight: 700; font-size: 0.725rem; padding: 3px 8px; border: none; border-radius: 4px; cursor: pointer; white-space: nowrap; display: inline-flex; align-items: center; gap: 0.2rem; box-shadow: 0 1px 2px rgba(5,150,105,0.25);" title="Otomatis pilih batch tertua yang tersedia">
                        <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>👉 Pakai Terlama Ini</span>
                    </button>
                </div>
            </div>
            ${draftNotice}
        `;
    } else {
        batchSelect.style.borderColor = '#cbd5e1';
        batchSelect.style.backgroundColor = '#ffffff';
        info.innerHTML = `
            <div style="display: flex; align-items: center; gap: 0.35rem; margin-top: 0.25rem;">
                ${gradeBadge}
                <span style="color: #0369a1; font-weight: 600; background: #f0f9ff; padding: 0.15rem 0.5rem; border-radius: 4px; border: 1px solid #bae6fd; font-size: 0.725rem;">
                    Pilihan Manual • Masuk: ${tglMasuk}
                </span>
            </div>
            ${draftNotice}
        `;
    }
}

function renderQiDraftRow(item = null, defaultCategory = '') {
    const tbody = document.getElementById('qiTableBody');
    const rowId = `qiRow_${qiRowCounter++}`;
    const tr = document.createElement('tr');
    tr.id = rowId;
    tr.className = 'qi-table-row';

    const barangList = currentQiData?.barangList || [];
    const curBarangId = item ? item.barang_id : '';
    const itemObj = barangList.find(b => parseInt(b.barang_id) === parseInt(curBarangId));
    const activeCat = item ? (itemObj?.kategori_kelompok || '') : defaultCategory;

    const curGrade = item ? (item.grade_cd || 'A').toUpperCase() : 'A';
    const curBatchNo = item ? item.batch_no : '';
    const curQty = item ? item.qty_keluar : 1;
    const curHarga = item ? item.harga_satuan : (itemObj?.harga_beli_standar || 0);
    const curSatuan = item ? (item.satuan_nm || itemObj?.satuan_dasar?.satuan_nm || 'Kg') : (itemObj?.satuan_dasar?.satuan_nm || 'Kg');

    tr._allBatches = item?.all_batches || [];

    // Opsi Barang dengan filter kategori
    const barangOptions = renderQiBarangOptions(curBarangId, activeCat);
    const subtotal = parseFloat(curQty) * parseFloat(curHarga);

    tr.innerHTML = `
        <td style="text-align: center; color: #64748b; font-weight: 700;" class="qi-row-index">
            ${tbody.children.length + 1}
        </td>
        <td style="min-width: 260px;">
            <div class="cat-pill-group" style="display: flex; gap: 0.25rem; margin-bottom: 0.35rem; align-items: center; flex-wrap: wrap;">
                <button type="button" class="btn-chip ${activeCat === '' ? 'active' : ''}" onclick="setQiRowCategory(this, '')">Semua</button>
                <button type="button" class="btn-chip ${activeCat === 'BAHAN_BAKU' ? 'active' : ''}" onclick="setQiRowCategory(this, 'BAHAN_BAKU')">Bahan Baku</button>
                <button type="button" class="btn-chip ${activeCat === 'BAHAN_PENOLONG' ? 'active' : ''}" onclick="setQiRowCategory(this, 'BAHAN_PENOLONG')">Bumbu / Penolong</button>
                <button type="button" class="btn-chip ${activeCat === 'KEMASAN' ? 'active' : ''}" onclick="setQiRowCategory(this, 'KEMASAN')">Kemasan</button>
            </div>
            <select class="qi-select qi-item-barang" onchange="onQiBarangChange(this)">
                ${barangOptions}
            </select>
        </td>
        <td style="text-align: center; width: 110px;">
            <div class="grade-pill-group" style="display: flex; gap: 0.2rem; align-items: center; justify-content: center; margin-bottom: 0.25rem;">
                <button type="button" class="btn-chip btn-chip-grade-a ${curGrade === 'A' ? 'active' : ''}" onclick="setQiRowGrade(this, 'A')" title="Saring hanya Grade A">🟢 A</button>
                <button type="button" class="btn-chip btn-chip-grade-b ${curGrade === 'B' ? 'active' : ''}" onclick="setQiRowGrade(this, 'B')" title="Saring hanya Grade B">🟡 B</button>
                <button type="button" class="btn-chip ${curGrade !== 'A' && curGrade !== 'B' ? 'active' : ''}" onclick="setQiRowGrade(this, '')" title="Semua Grade">All</button>
            </div>
            <input type="hidden" class="qi-item-grade" value="${curGrade}">
        </td>
        <td style="min-width: 250px;">
            <select class="qi-select qi-item-batch" onchange="onQiBatchChange(this)" style="font-family: monospace;">
                <option value="">Memuat batch...</option>
            </select>
            <div class="qi-batch-info" style="font-size: 0.725rem; margin-top: 2px;"></div>
        </td>
        <td>
            <input type="number" step="0.0001" min="0.0001" class="qi-input qi-item-qty" value="${curQty}" oninput="recalculateQiRow(this)" style="font-weight: 800; text-align: right; color: #0284c7;">
        </td>
        <td style="text-align: center; font-weight: 700; color: #475569;" class="qi-item-satuan">
            ${curSatuan}
        </td>
        <td>
            <input type="number" step="1" min="0" class="qi-input qi-item-harga" value="${curHarga}" oninput="recalculateQiRow(this)" style="text-align: right; font-family: monospace; background-color: #f1f5f9; color: #475569; cursor: not-allowed; border-color: #cbd5e1; font-weight: 700;" readonly tabindex="-1" title="Harga Satuan terkunci otomatis dari saldo penerimaan FIFO batch">
        </td>
        <td style="text-align: right; font-weight: 800; color: #0f172a; font-family: monospace;" class="qi-item-subtotal">
            Rp ${Math.round(subtotal).toLocaleString('id-ID')}
        </td>
        <td style="text-align: center;">
            <button type="button" class="qi-row-del-btn" onclick="removeQiRow(this)" title="Hapus Baris">&times;</button>
        </td>
    `;

    tbody.appendChild(tr);

    if (tr._allBatches && tr._allBatches.length > 0) {
        renderQiBatchOptionsInRow(tr, curGrade, curBatchNo);
    } else if (curBarangId) {
        onQiBarangChange(tr.querySelector('.qi-item-barang'));
    } else {
        tr.querySelector('.qi-item-batch').innerHTML = '<option value="">-- Pilih Barang Dulu --</option>';
    }
}

function renderQiPostedRow(item) {
    const tbody = document.getElementById('qiTableBody');
    const tr = document.createElement('tr');
    tr.className = 'qi-table-row';

    const qty = parseFloat(item.qty_keluar) || 0;
    const harga = parseFloat(item.harga_satuan) || 0;
    const subtotal = (parseFloat(item.total_harga) || (qty * harga)) || 0;

    tr.innerHTML = `
        <td style="text-align: center; color: #64748b; font-weight: 700;">
            ${tbody.children.length + 1}
        </td>
        <td>
            <div style="font-weight: 700; color: #0f172a;">${item.barang_nm}</div>
            <div style="font-size: 0.725rem; color: #64748b; font-family: monospace;">${item.barang_cd}</div>
        </td>
        <td style="text-align: center;">
            <span class="badge" style="background: #e2e8f0; color: #334155; font-weight: 700;">
                Grade ${item.grade_cd || 'A'}
            </span>
        </td>
        <td>
            <span style="font-family: monospace; font-weight: 700; color: #0f172a;">${item.batch_no}</span>
            <div style="font-size: 0.725rem; color: #059669;">✓ Stok resmi dipotong</div>
        </td>
        <td style="text-align: right; font-weight: 800; color: #0284c7; font-family: monospace;" class="qi-item-qty-val" data-qty="${qty}">
            ${qty.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 4 })}
        </td>
        <td style="text-align: center; font-weight: 600; color: #64748b;" class="qi-item-satuan">
            ${item.satuan_nm || 'Kg'}
        </td>
        <td style="text-align: right; font-family: monospace;">
            Rp ${Math.round(harga).toLocaleString('id-ID')}
        </td>
        <td style="text-align: right; font-weight: 800; color: #059669; font-family: monospace;" class="qi-item-subtotal" data-subtotal="${subtotal}">
            Rp ${Math.round(subtotal).toLocaleString('id-ID')}
        </td>
        <td style="display: none;"></td>
    `;

    tbody.appendChild(tr);
}

function addQiRow(defaultCategory = '') {
    renderQiDraftRow(null, defaultCategory);
    updateQiTotals();
    syncQiBatchOptionsAcrossRows();
}

function removeQiRow(btn) {
    const tbody = document.getElementById('qiTableBody');
    if (tbody.children.length <= 1) {
        showQiAlert('Minimal harus ada 1 item bahan baku dalam pengeluaran ini.', 'warning');
        return;
    }
    btn.closest('tr').remove();
    reindexQiRows();
    updateQiTotals();
    syncQiBatchOptionsAcrossRows();
}

function reindexQiRows() {
    const rows = document.querySelectorAll('#qiTableBody tr');
    rows.forEach((tr, idx) => {
        const idxEl = tr.querySelector('.qi-row-index');
        if (idxEl) idxEl.textContent = idx + 1;
    });
}

function onQiBarangChange(selectEl) {
    const tr = selectEl.closest('tr');
    const barangId = selectEl.value;
    const selectedOpt = selectEl.selectedOptions[0];
    const gudangId = document.getElementById('qi_gudang_id').value;

    const satuan = selectedOpt ? selectedOpt.getAttribute('data-satuan') : 'Kg';
    const defHarga = selectedOpt ? parseFloat(selectedOpt.getAttribute('data-harga') || 0) : 0;

    tr.querySelector('.qi-item-satuan').textContent = satuan;
    if (defHarga > 0) {
        tr.querySelector('.qi-item-harga').value = defHarga;
    }

    if (!barangId) {
        tr.querySelector('.qi-item-batch').innerHTML = '<option value="">-- Pilih Barang Dulu --</option>';
        tr.querySelector('.qi-batch-info').textContent = '';
        tr._allBatches = [];
        recalculateQiRow(selectEl);
        if (!isSyncingQiRows) syncQiBatchOptionsAcrossRows(tr);
        return;
    }

    const batchSelect = tr.querySelector('.qi-item-batch');
    const batchInfo = tr.querySelector('.qi-batch-info');
    batchSelect.innerHTML = '<option value="">Memuat batch FIFO...</option>';
    batchInfo.textContent = 'Memeriksa ketersediaan stok fisik...';

    const excludePakaiId = currentQiData?.pakai?.pakai_id || 0;
    const url = `/gudang/pemakaian/batches?gudang_id=${encodeURIComponent(gudangId)}&barang_id=${encodeURIComponent(barangId)}&exclude_pakai_id=${encodeURIComponent(excludePakaiId)}`;
    fetch(url)
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                tr._allBatches = res.batches || [];
                const curGrade = (tr.querySelector('.qi-item-grade')?.value || 'A').toUpperCase();
                renderQiBatchOptionsInRow(tr, curGrade, '');
                if (!isSyncingQiRows) {
                    syncQiBatchOptionsAcrossRows(tr);
                }
            } else {
                batchSelect.innerHTML = '<option value="">(Gagal memuat batch)</option>';
                batchInfo.textContent = '';
            }
        })
        .catch(err => {
            console.error(err);
            batchSelect.innerHTML = '<option value="">(Error memuat batch)</option>';
            batchInfo.textContent = '';
        });
}

function onQiBatchChange(batchSelect) {
    const tr = batchSelect.closest('tr');
    let selectedOpt = batchSelect.selectedOptions[0];
    if (!selectedOpt || !selectedOpt.value) return;

    // Cegah pemilihan opsi disabled jika diklik paksa atau via panah keyboard
    if (selectedOpt.disabled) {
        let foundAvailable = false;
        for (let i = 0; i < batchSelect.options.length; i++) {
            if (!batchSelect.options[i].disabled && batchSelect.options[i].value) {
                batchSelect.selectedIndex = i;
                selectedOpt = batchSelect.options[i];
                foundAvailable = true;
                break;
            }
        }
        if (!foundAvailable) {
            renderNormalQiBatchInfo(tr);
            recalculateQiRow(batchSelect);
            return;
        }
    }

    const harga = parseFloat(selectedOpt.getAttribute('data-harga') || 0);
    const grade = (selectedOpt.getAttribute('data-grade') || 'A').toUpperCase();

    const gradeInput = tr.querySelector('.qi-item-grade');
    if (gradeInput) gradeInput.value = grade;

    const gradeGroup = tr.querySelector('.grade-pill-group');
    if (gradeGroup) {
        gradeGroup.querySelectorAll('.btn-chip').forEach(b => b.classList.remove('active'));
        if (grade === 'A') gradeGroup.querySelector('.btn-chip-grade-a')?.classList.add('active');
        else if (grade === 'B') gradeGroup.querySelector('.btn-chip-grade-b')?.classList.add('active');
        else gradeGroup.children[0]?.classList.add('active');
    }

    const hargaInput = tr.querySelector('.qi-item-harga');
    if (hargaInput) {
        hargaInput.value = harga;
    }

    renderNormalQiBatchInfo(tr);
    recalculateQiRow(batchSelect);

    if (!isSyncingQiRows) {
        syncQiBatchOptionsAcrossRows(tr);
    }
}

function recalculateQiRow(inputEl) {
    const tr = inputEl.closest('tr');
    const qtyInput = tr.querySelector('.qi-item-qty');
    const qty = parseFloat(qtyInput ? qtyInput.value : 0) || 0;
    const harga = parseFloat(tr.querySelector('.qi-item-harga').value) || 0;
    const subtotal = qty * harga;

    tr.querySelector('.qi-item-subtotal').textContent = 'Rp ' + Math.round(subtotal).toLocaleString('id-ID');

    const batchSelect = tr.querySelector('.qi-item-batch');
    const selectedOpt = batchSelect ? batchSelect.selectedOptions[0] : null;
    const batchInfo = tr.querySelector('.qi-batch-info');

    if (selectedOpt && selectedOpt.value && qtyInput) {
        const sisaFisik = parseFloat(selectedOpt.getAttribute('data-sisa') || 0);
        const sisaBebas = parseFloat(selectedOpt.getAttribute('data-sisa-bebas') !== null ? selectedOpt.getAttribute('data-sisa-bebas') : sisaFisik);
        const draftQty = parseFloat(selectedOpt.getAttribute('data-draft-qty') || 0);

        if ((sisaBebas > 0 && qty > sisaBebas) || (sisaBebas <= 0.0001 && draftQty > 0 && qty > 0)) {
            qtyInput.style.borderColor = '#dc2626';
            qtyInput.style.backgroundColor = '#fef2f2';
            qtyInput.title = `Kuantitas (${qty}) melebihi sisa bebas (${sisaBebas})!`;

            if (batchInfo) {
                batchInfo.innerHTML = `
                    <div style="background: #fef2f2; border: 1.5px solid #f87171; border-radius: 6px; padding: 0.35rem 0.55rem; margin-top: 0.25rem; color: #991b1b;">
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.35rem; flex-wrap: wrap;">
                            <div>
                                <div style="display: flex; align-items: center; gap: 0.25rem; margin-bottom: 0.1rem;">
                                    <strong style="color: #b91c1c; font-size: 0.75rem;">
                                        ⚠️ Kuantitas Melebihi Sisa Bebas Batch!
                                    </strong>
                                </div>
                                <div style="font-size: 0.7rem; color: #7f1d1d;">
                                    Sisa bebas: <strong>${sisaBebas.toLocaleString('id-ID')} unit</strong> (Minta: <strong>${qty.toLocaleString('id-ID')}</strong>)
                                </div>
                            </div>
                            <button type="button" onclick="autoSplitQiBatchForRow(this)" style="background: #ea580c; color: #ffffff; font-weight: 700; font-size: 0.725rem; padding: 3px 8px; border: none; border-radius: 4px; cursor: pointer; white-space: nowrap; display: inline-flex; align-items: center; gap: 0.2rem; box-shadow: 0 1px 2px rgba(234,88,12,0.25);" title="Pecah otomatis sisa kuantitas ke batch berikutnya">
                                <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                <span>⚡ Pecah ke Batch Berikutnya</span>
                            </button>
                        </div>
                    </div>
                `;
            }
        } else {
            qtyInput.style.borderColor = '#cbd5e1';
            qtyInput.style.backgroundColor = '#ffffff';
            qtyInput.title = '';
            renderNormalQiBatchInfo(tr);
        }
    }

    if (!isSyncingQiRows) {
        syncQiBatchOptionsAcrossRows(tr);
    }

    updateQiTotals();
}

function onQiGudangChange() {
    // Reload batches for all rows based on new warehouse
    const rows = document.querySelectorAll('#qiTableBody tr');
    rows.forEach(tr => {
        const barangSelect = tr.querySelector('.qi-item-barang');
        if (barangSelect && barangSelect.value) {
            onQiBarangChange(barangSelect);
        }
    });
}

function updateQiTotals() {
    let grandQty = 0;
    let grandHpp = 0;
    let mainSatuan = 'Kg';

    const rows = document.querySelectorAll('#qiTableBody tr');
    rows.forEach(tr => {
        const qtyEl = tr.querySelector('.qi-item-qty');
        const hargaEl = tr.querySelector('.qi-item-harga');
        const satEl = tr.querySelector('.qi-item-satuan');
        if (satEl && satEl.textContent.trim()) {
            mainSatuan = satEl.textContent.trim();
        }

        if (qtyEl && hargaEl) {
            const qty = parseFloat(qtyEl.value) || 0;
            const harga = parseFloat(hargaEl.value) || 0;
            grandQty += qty;
            grandHpp += (qty * harga);
        } else {
            // Read-only posted row fallback
            const qtyValEl = tr.querySelector('.qi-item-qty-val');
            if (qtyValEl) {
                const q = parseFloat(qtyValEl.getAttribute('data-qty') || 0);
                grandQty += q;
            }
            const tdSubtotal = tr.querySelector('.qi-item-subtotal');
            if (tdSubtotal) {
                const subAttr = tdSubtotal.getAttribute('data-subtotal');
                if (subAttr !== null) {
                    grandHpp += parseFloat(subAttr) || 0;
                } else {
                    const subStr = tdSubtotal.textContent.replace(/[^0-9]/g, '');
                    grandHpp += parseFloat(subStr) || 0;
                }
            }
        }
    });

    const formattedHpp = 'Rp ' + Math.round(grandHpp).toLocaleString('id-ID');
    const formattedQty = grandQty.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 4 });

    const elTotalQty = document.getElementById('qiTotalQty');
    if (elTotalQty) elTotalQty.textContent = formattedQty + ' ' + mainSatuan;

    const elTotalHpp = document.getElementById('qiTotalHpp');
    if (elTotalHpp) elTotalHpp.textContent = formattedHpp;

    const elJournalDebet = document.getElementById('qiJournalDebet');
    if (elJournalDebet) elJournalDebet.textContent = formattedHpp;

    const elJournalKredit = document.getElementById('qiJournalKredit');
    if (elJournalKredit) elJournalKredit.textContent = formattedHpp;

    const elFootQty = document.getElementById('qiFootTotalQty');
    if (elFootQty) elFootQty.textContent = formattedQty;

    const elFootSatuan = document.getElementById('qiFootSatuan');
    if (elFootSatuan) elFootSatuan.textContent = mainSatuan;

    const elFootHpp = document.getElementById('qiFootTotalHpp');
    if (elFootHpp) elFootHpp.textContent = formattedHpp;
}

function submitQiForm(actionType) {
    if (!currentQiData) return;

    hideQiAlert();

    const pakaiId = currentQiData.pakai.pakai_id;
    const pakaiTgl = document.getElementById('qi_pakai_tgl').value;
    const gudangId = document.getElementById('qi_gudang_id').value;
    const tujuan = document.getElementById('qi_tujuan_pemakaian').value.trim();
    const catatan = document.getElementById('qi_catatan_txt').value.trim();

    if (!pakaiTgl) {
        showQiAlert('Tanggal pengeluaran wajib diisi.', 'danger');
        return;
    }
    if (!gudangId) {
        showQiAlert('Gudang asal wajib dipilih.', 'danger');
        return;
    }
    if (!tujuan) {
        showQiAlert('Tujuan pemakaian / SPK wajib diisi.', 'danger');
        return;
    }

    const rows = document.querySelectorAll('#qiTableBody tr');
    if (rows.length === 0) {
        showQiAlert('Minimal harus ada 1 item bahan.', 'danger');
        return;
    }

    const items = [];
    let hasError = false;

    rows.forEach((tr, idx) => {
        const barangId = tr.querySelector('.qi-item-barang')?.value;
        const gradeCd = tr.querySelector('.qi-item-grade')?.value || 'A';
        const batchNo = tr.querySelector('.qi-item-batch')?.value;
        const qty = parseFloat(tr.querySelector('.qi-item-qty')?.value) || 0;
        const harga = parseFloat(tr.querySelector('.qi-item-harga')?.value) || 0;

        if (!barangId) {
            showQiAlert(`Baris #${idx + 1}: Bahan belum dipilih.`, 'danger');
            hasError = true;
            return;
        }
        if (!batchNo) {
            showQiAlert(`Baris #${idx + 1}: Batch belum dipilih atau stok kosong.`, 'danger');
            hasError = true;
            return;
        }
        if (qty <= 0) {
            showQiAlert(`Baris #${idx + 1}: Kuantitas keluar harus lebih dari 0.`, 'danger');
            hasError = true;
            return;
        }

        const batchSelect = tr.querySelector('.qi-item-batch');
        const selOpt = batchSelect ? batchSelect.selectedOptions[0] : null;
        if (selOpt) {
            const sisaFisik = parseFloat(selOpt.getAttribute('data-sisa') || 0);
            const sisaBebas = parseFloat(selOpt.getAttribute('data-sisa-bebas') || sisaFisik);
            const draftQty = parseFloat(selOpt.getAttribute('data-draft-qty') || 0);
            const draftDocs = selOpt.getAttribute('data-draft-docs') || '';

            if (qty > sisaBebas) {
                if (draftQty > 0) {
                    showQiAlert(`Baris #${idx + 1}: Kuantitas (${qty.toLocaleString('id-ID')}) melebihi sisa bebas (${sisaBebas.toLocaleString('id-ID')}) karena ${draftQty.toLocaleString('id-ID')} unit sedang dibooking di DRAFT [${draftDocs}]!`, 'danger');
                } else {
                    showQiAlert(`Baris #${idx + 1}: Kuantitas (${qty.toLocaleString('id-ID')}) melebihi sisa batch (${sisaBebas.toLocaleString('id-ID')})!`, 'danger');
                }
                hasError = true;
                return;
            }
        }

        items.push({
            barang_id: barangId,
            grade_cd: gradeCd,
            batch_no: batchNo,
            qty_keluar: qty,
            harga_satuan: harga,
            keterangan_txt: ''
        });
    });

    if (hasError) return;

    const btnDraft = document.getElementById('qiBtnSaveDraft');
    const btnApprove = document.getElementById('qiBtnApprove');
    btnDraft.disabled = true;
    btnApprove.disabled = true;

    const originalApproveText = btnApprove.innerHTML;
    const originalDraftText = btnDraft.innerHTML;

    if (actionType === 'save_and_approve') {
        btnApprove.innerHTML = '⏳ Menyetujui &amp; Menerbitkan Jurnal...';
    } else {
        btnDraft.innerHTML = '💾 Menyimpan Perubahan...';
    }

    const payload = {
        pakai_tgl: pakaiTgl,
        gudang_id: gudangId,
        tujuan_pemakaian: tujuan,
        catatan_txt: catatan,
        action_type: actionType,
        items: items
    };

    const csrfToken = window.appConfig?.csrfToken || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    fetch(`/keuangan/verifikasi-pemakaian/${pakaiId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(payload)
    })
    .then(async res => {
        const data = await res.json();
        if (!res.ok) {
            throw new Error(data.message || 'Gagal menyimpan perubahan.');
        }
        return data;
    })
    .then(res => {
        if (actionType === 'save_and_approve') {
            showQiAlert('✓ Dokumen BERHASIL DISETUJUI &amp; DIPOSTING! Jurnal Umum telah diterbitkan.', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1200);
        } else {
            showQiAlert('✓ Perubahan rincian pemakaian bahan berhasil disimpan sebagai DRAFT.', 'success');
            // Update table cell rupiah di index
            const cellTotal = document.getElementById(`cellTotalRupiah_${pakaiId}`);
            if (cellTotal) {
                const totalNominal = items.reduce((sum, it) => sum + (it.qty_keluar * it.harga_satuan), 0);
                cellTotal.textContent = 'Rp ' + Math.round(totalNominal).toLocaleString('id-ID');
            }
            btnDraft.disabled = false;
            btnApprove.disabled = false;
            btnDraft.innerHTML = originalDraftText;
            btnApprove.innerHTML = originalApproveText;
            setTimeout(() => {
                closeQuickInspectModal();
            }, 1000);
        }
    })
    .catch(err => {
        console.error(err);
        showQiAlert('❌ ' + err.message, 'danger');
        btnDraft.disabled = false;
        btnApprove.disabled = false;
        btnDraft.innerHTML = originalDraftText;
        btnApprove.innerHTML = originalApproveText;
    });
}

function triggerQiReject() {
    if (!currentQiData) return;
    const pakai = currentQiData.pakai;
    closeQuickInspectModal();
    openModalReject(pakai.pakai_id, pakai.pakai_no);
}

function showQiAlert(msg, type = 'info') {
    const alertEl = document.getElementById('qiModalAlert');
    if (!alertEl) return;

    alertEl.style.display = 'block';
    if (type === 'danger') {
        alertEl.style.background = '#fef2f2';
        alertEl.style.border = '1.5px solid #fecaca';
        alertEl.style.color = '#dc2626';
    } else if (type === 'success') {
        alertEl.style.background = '#ecfdf5';
        alertEl.style.border = '1.5px solid #a7f3d0';
        alertEl.style.color = '#065f46';
    } else {
        alertEl.style.background = '#fffbeb';
        alertEl.style.border = '1.5px solid #fde68a';
        alertEl.style.color = '#92400e';
    }
    alertEl.innerHTML = msg;
}

function hideQiAlert() {
    const alertEl = document.getElementById('qiModalAlert');
    if (alertEl) alertEl.style.display = 'none';
}
