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

    if (isDraft) {
        btnAddRow.style.display = 'inline-flex';
        btnSaveDraft.style.display = 'inline-flex';
        btnApprove.style.display = 'inline-flex';
        btnReject.style.display = 'inline-flex';
        thAction.style.display = 'table-cell';
    } else {
        btnAddRow.style.display = 'none';
        btnSaveDraft.style.display = 'none';
        btnApprove.style.display = 'none';
        btnReject.style.display = 'none';
        thAction.style.display = 'none';
    }

    // Render Rows Tabel
    const tbody = document.getElementById('qiTableBody');
    tbody.innerHTML = '';
    qiRowCounter = 0;

    if (isDraft && data.initialItems) {
        data.initialItems.forEach(item => {
            renderQiDraftRow(item);
        });
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
    applyQiGradeFilter(tr);
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

    // Opsi Batch disaring menurut Grade
    let batchOptions = '';
    let filteredBatches = tr._allBatches;
    if (curGrade === 'A' || curGrade === 'B') {
        const gradeBatches = tr._allBatches.filter(b => (b.grade_cd || 'A').toUpperCase() === curGrade);
        if (gradeBatches.length > 0) filteredBatches = gradeBatches;
    }

    if (filteredBatches.length === 0) {
        if (curBatchNo) {
            batchOptions = `<option value="${curBatchNo}" selected>${curBatchNo} • [Grade ${curGrade}]</option>`;
        } else {
            batchOptions = `<option value="">-- Pilih Barang Dulu --</option>`;
        }
    } else {
        filteredBatches.forEach((b, idx) => {
            const isSel = (b.batch_no === curBatchNo) ? 'selected' : '';
            const isTop = (idx === 0);
            const prefix = isTop ? '⭐ [FIFO #1] ' : '';
            const gCd = (b.grade_cd || 'A').toUpperCase();
            const sisaFisik = parseFloat(b.sisa_qty || 0);
            const sisaBebas = (b.sisa_bebas !== undefined) ? parseFloat(b.sisa_bebas) : sisaFisik;
            const draftQty = parseFloat(b.draft_qty || 0);
            const draftDocs = b.draft_doc_nos || '';
            const isFullyBooked = (b.is_fully_booked || sisaBebas <= 0.0001) && draftQty > 0;
            const isCommitted = draftQty > 0;

            let tag = '';
            if (isFullyBooked) {
                tag = ' ⛔ [HABIS DIBOOKING DRAFT]';
            } else if (isCommitted) {
                tag = ` ⚠️ [DRAFT ALLOCATED: ${draftQty.toLocaleString('id-ID')}]`;
            }

            batchOptions += `<option value="${b.batch_no}" data-harga="${b.harga_satuan || 0}" data-sisa="${sisaFisik}" data-sisa-bebas="${sisaBebas}" data-draft-qty="${draftQty}" data-draft-docs="${draftDocs}" data-grade="${gCd}" ${isSel}>
                ${prefix}${b.batch_no} • [Grade ${gCd}] (Sisa Bebas: ${sisaBebas.toLocaleString('id-ID')} / Fisik: ${sisaFisik.toLocaleString('id-ID')})${tag}
            </option>`;
        });
    }

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
                ${batchOptions}
            </select>
            <div class="qi-batch-info" style="font-size: 0.725rem; color: #059669; margin-top: 2px;"></div>
        </td>
        <td>
            <input type="number" step="0.0001" min="0.0001" class="qi-input qi-item-qty" value="${curQty}" oninput="recalculateQiRow(this)" style="font-weight: 800; text-align: right; color: #0284c7;">
        </td>
        <td style="text-align: center; font-weight: 700; color: #475569;" class="qi-item-satuan">
            ${curSatuan}
        </td>
        <td>
            <input type="number" step="1" min="0" class="qi-input qi-item-harga" value="${curHarga}" oninput="recalculateQiRow(this)" style="text-align: right; font-family: monospace;">
        </td>
        <td style="text-align: right; font-weight: 800; color: #0f172a; font-family: monospace;" class="qi-item-subtotal">
            Rp ${Math.round(subtotal).toLocaleString('id-ID')}
        </td>
        <td style="text-align: center;">
            <button type="button" class="qi-row-del-btn" onclick="removeQiRow(this)" title="Hapus Baris">&times;</button>
        </td>
    `;

    tbody.appendChild(tr);

    // Initial batch info preview
    const bSel = tr.querySelector('.qi-item-batch');
    if (bSel && bSel.selectedOptions[0]) {
        const sisa = bSel.selectedOptions[0].getAttribute('data-sisa');
        if (sisa) {
            tr.querySelector('.qi-batch-info').innerHTML = `<span style="color: #059669; font-weight: 600;">Sisa gudang: ${parseFloat(sisa).toLocaleString('id-ID')} ${curSatuan}</span>`;
        }
    }
}

function renderQiPostedRow(item) {
    const tbody = document.getElementById('qiTableBody');
    const tr = document.createElement('tr');
    tr.className = 'qi-table-row';

    const subtotal = (parseFloat(item.qty_keluar) || 0) * (parseFloat(item.harga_satuan) || 0);

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
        <td style="text-align: right; font-weight: 800; color: #0f172a; font-family: monospace;">
            ${parseFloat(item.qty_keluar).toLocaleString('id-ID')}
        </td>
        <td style="text-align: center; font-weight: 600; color: #64748b;">
            ${item.satuan_nm}
        </td>
        <td style="text-align: right; font-family: monospace;">
            Rp ${parseFloat(item.harga_satuan).toLocaleString('id-ID')}
        </td>
        <td style="text-align: right; font-weight: 800; color: #059669; font-family: monospace;">
            Rp ${subtotal.toLocaleString('id-ID')}
        </td>
        <td style="display: none;"></td>
    `;

    tbody.appendChild(tr);
}

function addQiRow(defaultCategory = '') {
    renderQiDraftRow(null, defaultCategory);
    updateQiTotals();
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
                applyQiGradeFilter(tr);
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

function applyQiGradeFilter(tr) {
    const gradeInput = tr.querySelector('.qi-item-grade');
    const curGrade = (gradeInput?.value || 'A').toUpperCase();
    const batchSelect = tr.querySelector('.qi-item-batch');
    const batchInfo = tr.querySelector('.qi-batch-info');
    const batches = tr._allBatches || [];

    const filtered = (curGrade === 'A' || curGrade === 'B')
        ? batches.filter(b => (b.grade_cd || 'A').toUpperCase() === curGrade)
        : batches;

    if (filtered.length === 0) {
        const gradeText = (curGrade === 'A' || curGrade === 'B') ? `Grade ${curGrade}` : 'gudang ini';
        batchSelect.innerHTML = `<option value="">(Tidak ada stok ${gradeText})</option>`;
        batchInfo.innerHTML = `<span style="color: #dc2626; font-weight: 600;">⚠️ Stok fisik ${gradeText} kosong di gudang terpilih.</span>`;
        tr.querySelector('.qi-item-harga').value = 0;
        recalculateQiRow(batchSelect);
        return;
    }

    let html = '';
    filtered.forEach((b, idx) => {
        const isTop = (idx === 0);
        const prefix = isTop ? '⭐ [FIFO #1] ' : '';
        const gCd = (b.grade_cd || 'A').toUpperCase();
        const expInfo = (b.expired_tgl && b.expired_tgl !== '-') ? ` | Exp: ${b.expired_tgl}` : '';
        const tglTerima = (b.tgl_terima && b.tgl_terima !== '-') ? ` | Masuk: ${b.tgl_terima}` : '';
        const sisaFisik = parseFloat(b.sisa_qty || 0);
        const sisaBebas = (b.sisa_bebas !== undefined) ? parseFloat(b.sisa_bebas) : sisaFisik;
        const draftQty = parseFloat(b.draft_qty || 0);
        const draftDocs = b.draft_doc_nos || '';
        const isFullyBooked = (b.is_fully_booked || sisaBebas <= 0.0001) && draftQty > 0;
        const isCommitted = draftQty > 0;

        let tag = '';
        if (isFullyBooked) {
            tag = ' ⛔ [HABIS DIBOOKING DRAFT]';
        } else if (isCommitted) {
            tag = ` ⚠️ [DRAFT ALLOCATED: ${draftQty.toLocaleString('id-ID')}]`;
        }

        html += `<option value="${b.batch_no}" data-harga="${b.harga_satuan || 0}" data-sisa="${sisaFisik}" data-sisa-bebas="${sisaBebas}" data-draft-qty="${draftQty}" data-draft-docs="${draftDocs}" data-grade="${gCd}" ${isTop ? 'selected' : ''}>
            ${prefix}${b.batch_no} • [Grade ${gCd}] (Sisa Bebas: ${sisaBebas.toLocaleString('id-ID')} / Fisik: ${sisaFisik.toLocaleString('id-ID')})${tag}${tglTerima}${expInfo}
        </option>`;
    });

    batchSelect.innerHTML = html;
    onQiBatchChange(batchSelect);
}

function onQiBatchChange(batchSelect) {
    const tr = batchSelect.closest('tr');
    const selectedOpt = batchSelect.selectedOptions[0];
    if (!selectedOpt) return;

    const harga = parseFloat(selectedOpt.getAttribute('data-harga') || 0);
    const sisaFisik = parseFloat(selectedOpt.getAttribute('data-sisa') || 0);
    const sisaBebas = parseFloat(selectedOpt.getAttribute('data-sisa-bebas') || sisaFisik);
    const draftQty = parseFloat(selectedOpt.getAttribute('data-draft-qty') || 0);
    const draftDocs = selectedOpt.getAttribute('data-draft-docs') || '';
    const satuan = tr.querySelector('.qi-item-satuan').textContent.trim();

    if (harga > 0) {
        tr.querySelector('.qi-item-harga').value = harga;
    }

    const batchInfo = tr.querySelector('.qi-batch-info');
    if (batchInfo) {
        let draftInfo = '';
        if (draftQty > 0) {
            draftInfo = ` <span style="color: #b45309; font-weight: 700;">⚠️ ${draftQty.toLocaleString('id-ID')} ${satuan} dibooking draft [${draftDocs}]!</span>`;
        }
        batchInfo.innerHTML = `<span style="color: #059669; font-weight: 600;">Sisa Bebas: ${sisaBebas.toLocaleString('id-ID')} ${satuan} (Fisik: ${sisaFisik.toLocaleString('id-ID')})</span>${draftInfo}`;
    }

    recalculateQiRow(batchSelect);
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
    if (selectedOpt && qtyInput) {
        const sisaFisik = parseFloat(selectedOpt.getAttribute('data-sisa') || 0);
        const sisaBebas = parseFloat(selectedOpt.getAttribute('data-sisa-bebas') || sisaFisik);
        const draftQty = parseFloat(selectedOpt.getAttribute('data-draft-qty') || 0);

        if ((sisaBebas > 0 && qty > sisaBebas) || (sisaBebas <= 0.0001 && draftQty > 0 && qty > 0)) {
            qtyInput.style.borderColor = '#dc2626';
            qtyInput.style.backgroundColor = '#fef2f2';
            qtyInput.title = `Kuantitas (${qty}) melebihi sisa bebas (${sisaBebas})!`;
        } else {
            qtyInput.style.borderColor = '#cbd5e1';
            qtyInput.style.backgroundColor = '#ffffff';
            qtyInput.title = '';
        }
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

    const rows = document.querySelectorAll('#qiTableBody tr');
    rows.forEach(tr => {
        const qtyEl = tr.querySelector('.qi-item-qty');
        const hargaEl = tr.querySelector('.qi-item-harga');

        if (qtyEl && hargaEl) {
            const qty = parseFloat(qtyEl.value) || 0;
            const harga = parseFloat(hargaEl.value) || 0;
            grandQty += qty;
            grandHpp += (qty * harga);
        } else {
            // Read-only posted row fallback
            const tdSubtotal = tr.querySelector('.qi-item-subtotal');
            if (tdSubtotal) {
                const subStr = tdSubtotal.textContent.replace(/[^0-9]/g, '');
                grandHpp += parseFloat(subStr) || 0;
            }
        }
    });

    const formattedHpp = 'Rp ' + Math.round(grandHpp).toLocaleString('id-ID');
    document.getElementById('qiTotalQty').textContent = grandQty.toLocaleString('id-ID', { maximumFractionDigits: 4 });
    document.getElementById('qiTotalHpp').textContent = formattedHpp;
    document.getElementById('qiJournalDebet').textContent = formattedHpp;
    document.getElementById('qiJournalKredit').textContent = formattedHpp;
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
