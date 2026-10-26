/**
 * JAVASCRIPT - LOGIKA FORM EDIT & VERIFIKASI PEMAKAIAN BAHAN
 * ERP PT Mirasa Food Industry
 */

let rowIndex = 0;
let allowFifoOverride = false;
let pendingActionType = 'save_draft';

function getSelectedGudangId() {
    const el = document.getElementById('gudang_id');
    return el ? el.value : '';
}

function updateSidebarInfo() {
    const gdgSelect = document.getElementById('gudang_id');
    const sideGdg = document.getElementById('sideGudangName');
    if (sideGdg && gdgSelect) {
        if (gdgSelect.tagName === 'SELECT') {
            const opt = gdgSelect.options[gdgSelect.selectedIndex];
            sideGdg.innerText = (opt && opt.value) ? opt.text : '- Belum Dipilih -';
        } else {
            sideGdg.innerText = gdgSelect.value ? gdgSelect.value : '- Belum Dipilih -';
        }
    }

    const tujuanInput = document.getElementById('tujuan_pemakaian');
    const sideTujuan = document.getElementById('sideTujuanName');
    if (sideTujuan && tujuanInput) {
        sideTujuan.innerText = tujuanInput.value ? tujuanInput.value : '- Belum Diisi -';
    }
}

function onGudangChanged() {
    updateSidebarInfo();
    // Re-fetch batches for all rows using the newly selected warehouse
    document.querySelectorAll('#itemsBody tr').forEach(row => {
        const barangSelect = row.querySelector('.barang-select');
        if (barangSelect && barangSelect.value) {
            let gradeFilter = '';
            const activeGradeBtn = row.querySelector('.grade-pill-group .btn-chip.active');
            if (activeGradeBtn) {
                if (activeGradeBtn.classList.contains('btn-chip-grade-a')) gradeFilter = 'A';
                else if (activeGradeBtn.classList.contains('btn-chip-grade-b')) gradeFilter = 'B';
            }
            fetchBatchesForRow(row, barangSelect.value, gradeFilter);
        }
    });
}

function renderBarangOptions(selectedBarangId = '', categoryFilter = '') {
    const list = window.appConfig?.barangList || [];
    const filtered = categoryFilter 
        ? list.filter(b => b.kategori_kelompok === categoryFilter)
        : list;

    let html = '<option value="">-- Pilih Bahan Produksi --</option>';

    if (categoryFilter) {
        filtered.forEach(b => {
            const jenis = b.jenis_barang ? b.jenis_barang.jenis_barang_cd : '';
            const harga = b.harga_beli_standar || 0;
            const isSelected = (parseInt(b.barang_id) === parseInt(selectedBarangId)) ? 'selected' : '';
            html += `<option value="${b.barang_id}" data-harga="${harga}" data-satuan="${b.satuan_dasar?.satuan_nm || ''}" data-kategori="${b.kategori_kelompok}" ${isSelected}>[${b.barang_cd}] ${b.barang_nm} (${jenis})</option>`;
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
                    const isSelected = (parseInt(b.barang_id) === parseInt(selectedBarangId)) ? 'selected' : '';
                    html += `<option value="${b.barang_id}" data-harga="${harga}" data-satuan="${b.satuan_dasar?.satuan_nm || ''}" data-kategori="${b.kategori_kelompok}" ${isSelected}>[${b.barang_cd}] ${b.barang_nm} (${jenis})</option>`;
                });
                html += `</optgroup>`;
            }
        }
    }

    return html;
}

function setRowCategory(btn, cat) {
    const group = btn.closest('.cat-pill-group');
    if (group) {
        group.querySelectorAll('.btn-chip').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
    }

    const row = btn.closest('tr');
    const barangSelect = row.querySelector('.barang-select');
    const currentVal = barangSelect.value;
    barangSelect.innerHTML = renderBarangOptions(currentVal, cat);
    if (currentVal && !barangSelect.value) {
        onBarangSelect(barangSelect);
    }
}

function updateRowNumbers() {
    document.querySelectorAll('#itemsBody tr').forEach((row, idx) => {
        const numEl = row.querySelector('.row-num');
        if (numEl) numEl.innerText = idx + 1;
    });
}

function attachExcelKeyboardEvents(rowElement) {
    const inputs = rowElement.querySelectorAll('input, select');
    inputs.forEach(input => {
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const allRows = Array.from(document.querySelectorAll('#itemsBody tr'));
                const currentRowIdx = allRows.indexOf(rowElement);

                if (input.classList.contains('qty-input') || input.classList.contains('harga-input')) {
                    if (currentRowIdx === allRows.length - 1) {
                        addRow();
                        const newRows = document.querySelectorAll('#itemsBody tr');
                        const lastRow = newRows[newRows.length - 1];
                        lastRow.querySelector('.barang-select')?.focus();
                    } else {
                        const nextRow = allRows[currentRowIdx + 1];
                        const targetClass = input.classList.contains('qty-input') ? '.qty-input' : '.harga-input';
                        const target = nextRow.querySelector(targetClass);
                        if (target) target.focus();
                    }
                }
            }
        });
    });
}

function setRowGradeFilter(btn, grade) {
    const group = btn.closest('.grade-pill-group');
    if (group) {
        group.querySelectorAll('.btn-chip').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
    }

    const row = btn.closest('tr');
    const barangSelect = row.querySelector('.barang-select');

    if ((!row._allBatches || row._allBatches.length === 0) && barangSelect && barangSelect.value) {
        fetchBatchesForRow(row, barangSelect.value, grade);
        return;
    }

    renderBatchOptionsInRow(row, grade);
}

function renderBatchOptionsInRow(row, gradeFilter = '') {
    const batchSelect = row.querySelector('.batch-select');
    const batchInfo = row.querySelector('.batch-info');
    if (!batchSelect) return;

    const batches = row._allBatches || [];

    if (batches.length === 0) {
        batchSelect.innerHTML = '<option value="">(Stok Fisik Kosong / Habis di Gudang ini)</option>';
        if (batchInfo) {
            batchInfo.innerHTML = '<span style="color: #dc2626; font-weight: 700; background: #fee2e2; padding: 0.2rem 0.5rem; border-radius: 4px;">⚠️ Stok fisik bahan ini habis di gudang terpilih.</span>';
        }
        row.querySelector('.harga-input').value = 0;
        row.querySelector('.stok-id-input').value = '';
        calcRow(batchSelect);
        return;
    }

    let filtered = gradeFilter 
        ? batches.filter(b => (b.grade_cd || 'A').toUpperCase() === gradeFilter.toUpperCase())
        : batches;

    if (filtered.length === 0) {
        batchSelect.innerHTML = `<option value="">(Tidak ada stok Grade ${gradeFilter} di gudang)</option>`;
        if (batchInfo) {
            batchInfo.innerHTML = `<span style="color: #b45309; font-weight: 700; background: #fef3c7; padding: 0.2rem 0.5rem; border-radius: 4px;">Stok Grade ${gradeFilter} tidak tersedia di gudang terpilih.</span>`;
        }
        row.querySelector('.grade-input').value = gradeFilter;
        row.querySelector('.stok-id-input').value = '';
        calcRow(batchSelect);
        return;
    }

    let html = '';
    filtered.forEach((b, idx) => {
        const isTop = (idx === 0);
        const prefix = isTop ? '⭐ [FIFO Tertua] ' : '';
        const gCd = (b.grade_cd || 'A').toUpperCase();
        const gradeBadge = (gCd === 'A') ? '🟢 Grade A' : ((gCd === 'B') ? '🟡 Grade B' : `Grade ${gCd}`);
        const expInfo = (b.expired_tgl && b.expired_tgl !== '-') ? ` | Exp: ${b.expired_tgl}` : '';
        const tglTerima = (b.tgl_terima && b.tgl_terima !== '-') ? ` | Masuk: ${b.tgl_terima}` : '';
        html += `<option value="${b.batch_no}" data-stok-id="${b.stok_id || ''}" data-grade="${gCd}" data-sisa="${b.sisa_qty}" data-harga="${b.harga_satuan || 0}" data-masuk="${b.tgl_terima}" data-exp="${b.expired_tgl || '-'}" ${isTop ? 'selected' : ''}>
            ${prefix}${b.batch_no} • [${gradeBadge}] (Sisa: ${parseFloat(b.sisa_qty).toLocaleString('id-ID')})${tglTerima}${expInfo}
        </option>`;
    });
    batchSelect.innerHTML = html;
    onBatchSelect(batchSelect);
}

function addRow(initialCategory = '') {
    const tbody = document.getElementById('itemsBody');
    const tr = document.createElement('tr');
    tr.id = `row-${rowIndex}`;

    const barangOptions = renderBarangOptions('', initialCategory);

    tr.innerHTML = `
        <td style="text-align: center; vertical-align: middle; padding: 0.35rem 0.25rem; background: #f8fafc;">
            <div class="row-num" style="font-weight: 700; color: #475569; font-size: 0.8rem;">
                ${tbody.children.length + 1}
            </div>
            <div class="audit-badge-cell audit-badge-ok" title="Audit: Menunggu input">✓</div>
        </td>
        <td style="padding: 0.45rem 0.55rem;">
            <div class="cat-pill-group" style="display: flex; gap: 0.35rem; margin-bottom: 0.4rem; align-items: center; flex-wrap: wrap;">
                <button type="button" class="btn-chip ${initialCategory === '' ? 'active' : ''}" onclick="setRowCategory(this, '')">Semua</button>
                <button type="button" class="btn-chip ${initialCategory === 'BAHAN_BAKU' ? 'active' : ''}" onclick="setRowCategory(this, 'BAHAN_BAKU')">Bahan Baku</button>
                <button type="button" class="btn-chip ${initialCategory === 'BAHAN_PENOLONG' ? 'active' : ''}" onclick="setRowCategory(this, 'BAHAN_PENOLONG')">Bumbu / Penolong</button>
                <button type="button" class="btn-chip ${initialCategory === 'KEMASAN' ? 'active' : ''}" onclick="setRowCategory(this, 'KEMASAN')">Kemasan</button>
            </div>
            <select name="items[${rowIndex}][barang_id]" class="form-control barang-select" style="font-size: 0.85rem; font-weight: 600; color: #0f172a; height: 38px;" required onchange="onBarangSelect(this)">
                ${barangOptions}
            </select>
        </td>
        <td style="padding: 0.45rem 0.55rem;">
            <div class="grade-pill-group" style="display: flex; gap: 0.35rem; margin-bottom: 0.4rem; align-items: center; flex-wrap: nowrap;">
                <button type="button" class="btn-chip active" onclick="setRowGradeFilter(this, '')">Semua</button>
                <button type="button" class="btn-chip btn-chip-grade-a" onclick="setRowGradeFilter(this, 'A')">🟢 Grade A</button>
                <button type="button" class="btn-chip btn-chip-grade-b" onclick="setRowGradeFilter(this, 'B')">🟡 Grade B</button>
            </div>
            <input type="hidden" name="items[${rowIndex}][grade_cd]" class="grade-input" value="A">
            <input type="hidden" name="items[${rowIndex}][stok_id]" class="stok-id-input" value="">
            <select name="items[${rowIndex}][batch_no]" class="form-control batch-select" style="font-size: 0.85rem; height: 38px; font-family: monospace;" required onchange="onBatchSelect(this)">
                <option value="">-- Pilih Barang Dulu --</option>
            </select>
            <div class="batch-info" style="font-size: 0.75rem; margin-top: 3px;"></div>
        </td>
        <td style="padding: 0.45rem 0.55rem;">
            <input type="number" step="0.0001" min="0.0001" name="items[${rowIndex}][qty_keluar]" class="form-control qty-input" placeholder="0" style="font-weight: 800; text-align: right; font-size: 1.15rem; height: 38px; color: #0284c7;" required oninput="calcRow(this)">
        </td>
        <td style="text-align: center; padding: 0.45rem 0.55rem;">
            <span class="row-satuan" style="font-weight: 800; color: #475569; font-size: 0.85rem;">-</span>
        </td>
        <td style="padding: 0.45rem 0.55rem;">
            <input type="number" step="0.01" min="0" name="items[${rowIndex}][harga_satuan]" class="form-control harga-input" placeholder="0" style="text-align: right; font-size: 0.85rem; height: 38px; font-family: monospace;" oninput="calcRow(this)">
        </td>
        <td style="padding: 0.45rem 0.55rem; text-align: right; font-weight: 800; color: #0f172a; font-size: 0.95rem; font-family: monospace;" class="subtotal-cell">
            Rp 0
        </td>
        <td style="padding: 0.45rem 0.55rem; text-align: center;">
            <button type="button" class="btn btn-secondary btn-sm" onclick="removeRow(this)" style="color: #ef4444; border: 1.5px solid #fecaca; background: #fef2f2; width: 34px; height: 34px; border-radius: 6px; font-size: 1.1rem; font-weight: 700; cursor: pointer;" title="Hapus Baris">&times;</button>
        </td>
    `;

    tbody.appendChild(tr);
    attachExcelKeyboardEvents(tr);
    rowIndex++;
    updateRowNumbers();
    calculateGrandTotal();
    updateAuditSummaryCounts();
}

function removeRow(btn) {
    const tbody = document.getElementById('itemsBody');
    if (tbody.children.length > 1) {
        btn.closest('tr').remove();
        updateRowNumbers();
        calculateGrandTotal();
    } else {
        alert('Minimal harus ada 1 item barang yang dikeluarkan.');
    }
}

function resetItemsTable() {
    const tbody = document.getElementById('itemsBody');
    tbody.innerHTML = '';
    rowIndex = 0;
    addRow();
    calculateGrandTotal();
    const feedback = document.getElementById('resepFeedback');
    if (feedback) feedback.style.display = 'none';
}

function addRowWithItemData(item) {
    const tbody = document.getElementById('itemsBody');
    const tr = document.createElement('tr');
    tr.id = `row-${rowIndex}`;

    const list = window.appConfig?.barangList || [];
    const itemObj = list.find(b => parseInt(b.barang_id) === parseInt(item.barang_id));
    const itemCat = itemObj?.kategori_kelompok || '';
    const barangOptions = renderBarangOptions(item.barang_id, itemCat);

    tr._allBatches = item.all_batches || [];
    const curGrade = (item.grade_cd || 'A').toUpperCase();

    // Saring batch sesuai grade item jika ada (misal Grade A hanya tampil Grade A)
    let batchesToShow = item.all_batches || [];
    if (curGrade === 'A' || curGrade === 'B') {
        const gradeFiltered = batchesToShow.filter(b => (b.grade_cd || 'A').toUpperCase() === curGrade);
        if (gradeFiltered.length > 0) {
            batchesToShow = gradeFiltered;
        }
    }

    let batchOptions = '';
    if (batchesToShow.length === 0) {
        batchOptions = `<option value="${item.batch_no}" selected>${item.batch_no} • [Grade ${curGrade}]</option>`;
    } else {
        batchesToShow.forEach((b, idx) => {
            const isSelected = (b.batch_no === item.batch_no && (b.grade_cd || 'A') === curGrade) ? 'selected' : '';
            const prefix = (idx === 0) ? '⭐ [FIFO Tertua] ' : '';
            const gCd = (b.grade_cd || 'A').toUpperCase();
            const gradeBadge = (gCd === 'A') ? '🟢 Grade A' : ((gCd === 'B') ? '🟡 Grade B' : `Grade ${gCd}`);
            const expInfo = (b.expired_tgl && b.expired_tgl !== '-') ? ` | Exp: ${b.expired_tgl}` : '';
            const tglTerima = (b.tgl_terima && b.tgl_terima !== '-') ? ` | Masuk: ${b.tgl_terima}` : '';
            batchOptions += `<option value="${b.batch_no}" data-stok-id="${b.stok_id || ''}" data-grade="${gCd}" data-sisa="${b.sisa_qty}" data-harga="${b.harga_satuan || 0}" data-masuk="${b.tgl_terima}" data-exp="${b.expired_tgl || '-'}" ${isSelected}>
                ${prefix}${b.batch_no} • [${gradeBadge}] (Sisa: ${parseFloat(b.sisa_qty).toLocaleString('id-ID')})${tglTerima}${expInfo}
            </option>`;
        });
    }

    const subtotal = (parseFloat(item.qty_keluar) || 0) * (parseFloat(item.harga_satuan) || 0);

    tr.innerHTML = `
        <td style="text-align: center; vertical-align: middle; padding: 0.35rem 0.25rem; background: #f8fafc;">
            <div class="row-num" style="font-weight: 700; color: #475569; font-size: 0.8rem;">
                ${tbody.children.length + 1}
            </div>
            <div class="audit-badge-cell audit-badge-ok" title="Audit: Data fisik & FIFO aman">✓</div>
        </td>
        <td style="padding: 0.45rem 0.55rem;">
            <div class="cat-pill-group" style="display: flex; gap: 0.35rem; margin-bottom: 0.4rem; align-items: center; flex-wrap: wrap;">
                <button type="button" class="btn-chip ${itemCat === '' ? 'active' : ''}" onclick="setRowCategory(this, '')">Semua</button>
                <button type="button" class="btn-chip ${itemCat === 'BAHAN_BAKU' ? 'active' : ''}" onclick="setRowCategory(this, 'BAHAN_BAKU')">Bahan Baku</button>
                <button type="button" class="btn-chip ${itemCat === 'BAHAN_PENOLONG' ? 'active' : ''}" onclick="setRowCategory(this, 'BAHAN_PENOLONG')">Bumbu / Penolong</button>
                <button type="button" class="btn-chip ${itemCat === 'KEMASAN' ? 'active' : ''}" onclick="setRowCategory(this, 'KEMASAN')">Kemasan</button>
            </div>
            <select name="items[${rowIndex}][barang_id]" class="form-control barang-select" style="font-size: 0.85rem; font-weight: 600; color: #0f172a; height: 38px;" required onchange="onBarangSelect(this)">
                ${barangOptions}
            </select>
        </td>
        <td style="padding: 0.45rem 0.55rem;">
            <div class="grade-pill-group" style="display: flex; gap: 0.35rem; margin-bottom: 0.4rem; align-items: center; flex-wrap: nowrap;">
                <button type="button" class="btn-chip ${curGrade !== 'A' && curGrade !== 'B' ? 'active' : ''}" onclick="setRowGradeFilter(this, '')">Semua</button>
                <button type="button" class="btn-chip btn-chip-grade-a ${curGrade === 'A' ? 'active' : ''}" onclick="setRowGradeFilter(this, 'A')">🟢 Grade A</button>
                <button type="button" class="btn-chip btn-chip-grade-b ${curGrade === 'B' ? 'active' : ''}" onclick="setRowGradeFilter(this, 'B')">🟡 Grade B</button>
            </div>
            <input type="hidden" name="items[${rowIndex}][grade_cd]" class="grade-input" value="${curGrade}">
            <input type="hidden" name="items[${rowIndex}][stok_id]" class="stok-id-input" value="${item.stok_id || ''}">
            <select name="items[${rowIndex}][batch_no]" class="form-control batch-select" style="font-size: 0.85rem; height: 38px; font-family: monospace;" required onchange="onBatchSelect(this)">
                ${batchOptions}
            </select>
            <div class="batch-info" style="font-size: 0.75rem; margin-top: 3px;"></div>
        </td>
        <td style="padding: 0.45rem 0.55rem;">
            <input type="number" step="0.0001" min="0.0001" name="items[${rowIndex}][qty_keluar]" value="${item.qty_keluar}" class="form-control qty-input" placeholder="0" style="font-weight: 800; text-align: right; font-size: 1.15rem; height: 38px; color: #0284c7;" required oninput="calcRow(this)">
        </td>
        <td style="text-align: center; padding: 0.45rem 0.55rem;">
            <span class="row-satuan" style="font-weight: 800; color: #475569; font-size: 0.85rem;">${item.satuan_nm || '-'}</span>
        </td>
        <td style="padding: 0.45rem 0.55rem;">
            <input type="number" step="0.01" min="0" name="items[${rowIndex}][harga_satuan]" value="${item.harga_satuan}" class="form-control harga-input" placeholder="0" style="text-align: right; font-size: 0.85rem; height: 38px; font-family: monospace;" oninput="calcRow(this)">
        </td>
        <td style="padding: 0.45rem 0.55rem; text-align: right; font-weight: 800; color: #0f172a; font-size: 0.95rem; font-family: monospace;" class="subtotal-cell">
            Rp ${subtotal.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 })}
        </td>
        <td style="padding: 0.45rem 0.55rem; text-align: center;">
            <button type="button" class="btn btn-secondary btn-sm" onclick="removeRow(this)" style="color: #ef4444; border: 1.5px solid #fecaca; background: #fef2f2; width: 34px; height: 34px; border-radius: 6px; font-size: 1.1rem; font-weight: 700; cursor: pointer;" title="Hapus Baris">&times;</button>
        </td>
    `;

    tbody.appendChild(tr);
    attachExcelKeyboardEvents(tr);

    // Update batch info display
    const batchSelect = tr.querySelector('.batch-select');
    if (batchSelect && batchSelect.selectedOptions[0]) {
        const sisa = parseFloat(batchSelect.selectedOptions[0].getAttribute('data-sisa') || 0);
        tr.querySelector('.batch-info').innerHTML = `<span style="color: #059669; font-weight: 600;">Sisa gudang: ${sisa.toLocaleString('id-ID')} unit</span>`;
    }

    rowIndex++;
    updateRowNumbers();
    calculateGrandTotal();
    updateAuditSummaryCounts();
}

function onBarangSelect(selectEl) {
    const row = selectEl.closest('tr');
    const selectedOpt = selectEl.selectedOptions[0];
    const barangId = selectEl.value;
    const satuan = selectedOpt ? selectedOpt.getAttribute('data-satuan') : '';
    const defaultHarga = selectedOpt ? parseFloat(selectedOpt.getAttribute('data-harga') || 0) : 0;

    const satuanEl = row.querySelector('.row-satuan');
    if (satuanEl) satuanEl.textContent = satuan || '-';

    const hargaInput = row.querySelector('.harga-input');
    if (defaultHarga > 0) {
        hargaInput.value = defaultHarga;
    }

    if (barangId) {
        // Reset grade pill group to 'Semua' so all batches of this item load by default
        const group = row.querySelector('.grade-pill-group');
        if (group) {
            group.querySelectorAll('.btn-chip').forEach(b => b.classList.remove('active'));
            group.children[0]?.classList.add('active');
        }
        fetchBatchesForRow(row, barangId, '');
    } else {
        const batchSelect = row.querySelector('.batch-select');
        batchSelect.innerHTML = '<option value="">-- Pilih Barang Dulu --</option>';
        if (row.querySelector('.batch-info')) row.querySelector('.batch-info').textContent = '';
        row._allBatches = [];
        if (satuanEl) satuanEl.textContent = '-';
    }

    calcRow(selectEl);
}

function fetchBatchesForRow(row, barangId, forcedGrade = null) {
    const gudangId = getSelectedGudangId();
    const batchSelect = row.querySelector('.batch-select');
    const batchInfo = row.querySelector('.batch-info');

    if (!gudangId) {
        batchSelect.innerHTML = '<option value="">-- Pilih Gudang di Atas Dulu --</option>';
        if (batchInfo) batchInfo.innerHTML = '<span style="color: #dc2626; font-weight: 600;">⚠️ Gudang belum dipilih di bagian atas!</span>';
        return;
    }

    batchSelect.innerHTML = '<option value="">Memuat data batch FIFO...</option>';
    if (batchInfo) batchInfo.innerHTML = '<span style="color: #64748b;">Memeriksa stok fisik gudang &amp; urutan batch...</span>';

    const baseUrl = window.appConfig?.urlGetBatches || '/gudang/pemakaian/batches';
    const url = `${baseUrl}?gudang_id=${encodeURIComponent(gudangId)}&barang_id=${encodeURIComponent(barangId)}`;

    fetch(url)
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                row._allBatches = res.batches || [];

                let gradeFilter = '';
                if (forcedGrade !== null) {
                    gradeFilter = forcedGrade;
                } else {
                    const activeGradeBtn = row.querySelector('.grade-pill-group .btn-chip.active');
                    if (activeGradeBtn) {
                        if (activeGradeBtn.classList.contains('btn-chip-grade-a')) gradeFilter = 'A';
                        else if (activeGradeBtn.classList.contains('btn-chip-grade-b')) gradeFilter = 'B';
                    }
                }

                renderBatchOptionsInRow(row, gradeFilter);
            } else {
                batchSelect.innerHTML = '<option value="">Gagal memuat batch</option>';
                if (batchInfo) batchInfo.innerHTML = `<span style="color: #dc2626;">${res.message || 'Gagal memuat batch'}</span>`;
            }
        })
        .catch(err => {
            console.error(err);
            batchSelect.innerHTML = '<option value="">Gagal memuat batch</option>';
            if (batchInfo) batchInfo.innerHTML = '<span style="color: #dc2626;">Koneksi gagal saat mengambil batch.</span>';
        });
}


function renderNormalBatchInfo(row) {
    const batchSelect = row.querySelector('.batch-select');
    const info = row.querySelector('.batch-info');
    if (!batchSelect || !info) return;

    const selectedOpt = batchSelect.selectedOptions[0];
    if (!selectedOpt || !selectedOpt.value) {
        info.innerHTML = '';
        return;
    }

    const sisa = parseFloat(selectedOpt.getAttribute('data-sisa') || 0);
    const grade = (row.querySelector('.grade-input')?.value || selectedOpt.getAttribute('data-grade') || 'A').toUpperCase();
    const isTopFifo = (batchSelect.selectedIndex === 0);

    let gradeBadge = (grade === 'A')
        ? `<span style="background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; font-weight: 700; padding: 1px 5px; border-radius: 3px; font-size: 0.7rem;">🟢 Grade A</span>`
        : ((grade === 'B')
            ? `<span style="background: #fffbeb; color: #b45309; border: 1px solid #fde68a; font-weight: 700; padding: 1px 5px; border-radius: 3px; font-size: 0.7rem;">🟡 Grade B</span>`
            : `Grade ${grade}`);

    if (isTopFifo) {
        info.innerHTML = `
            <div style="display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap;">
                ${gradeBadge}
                <span style="color: #059669; font-weight: 700; background: #ecfdf5; padding: 0.15rem 0.5rem; border-radius: 4px; border: 1px solid #a7f3d0;">
                    ⭐ Batch Tertua (Sisa: ${sisa.toLocaleString('id-ID')} unit)
                </span>
            </div>
        `;
    } else {
        info.innerHTML = `
            <div style="display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap;">
                ${gradeBadge}
                <span style="color: #b45309; font-weight: 600; background: #fffbeb; padding: 0.15rem 0.5rem; border-radius: 4px; border: 1px solid #fde68a;">
                    ⚠️ Batch Tersedia (Sisa: ${sisa.toLocaleString('id-ID')} unit)
                </span>
            </div>
        `;
    }
}

function onBatchSelect(selectEl) {
    const row = selectEl.closest('tr');
    const selectedOpt = selectEl.selectedOptions[0];
    if (!selectedOpt || !selectedOpt.value) return;

    const harga = parseFloat(selectedOpt.getAttribute('data-harga') || 0);
    const grade = (selectedOpt.getAttribute('data-grade') || 'A').toUpperCase();
    const stokId = selectedOpt.getAttribute('data-stok-id') || '';

    row.querySelector('.grade-input').value = grade;
    row.querySelector('.stok-id-input').value = stokId;

    const gradeGroup = row.querySelector('.grade-pill-group');
    if (gradeGroup) {
        gradeGroup.querySelectorAll('.btn-chip').forEach(b => b.classList.remove('active'));
        if (grade === 'A') gradeGroup.querySelector('.btn-chip-grade-a')?.classList.add('active');
        else if (grade === 'B') gradeGroup.querySelector('.btn-chip-grade-b')?.classList.add('active');
        else gradeGroup.children[0]?.classList.add('active');
    }

    const hargaInput = row.querySelector('.harga-input');
    if (harga > 0 && (!hargaInput.value || parseFloat(hargaInput.value) === 0)) {
        hargaInput.value = harga;
    }

    renderNormalBatchInfo(row);

    calcRow(selectEl);
}

function updateRowAuditStatus(row) {
    if (!row) return 'ok';
    const barangSelect = row.querySelector('.barang-select');
    const batchSelect = row.querySelector('.batch-select');
    const qtyInput = row.querySelector('.qty-input');
    const badge = row.querySelector('.audit-badge-cell');

    row.classList.remove('row-audit-ok', 'row-audit-warning', 'row-audit-danger');
    if (badge) {
        badge.classList.remove('audit-badge-ok', 'audit-badge-warn', 'audit-badge-danger');
    }

    if (!barangSelect || !barangSelect.value) {
        if (badge) {
            badge.className = 'audit-badge-cell';
            badge.textContent = '-';
            badge.title = 'Bahan belum dipilih';
        }
        return 'none';
    }

    const qty = parseFloat(qtyInput ? qtyInput.value : 0) || 0;
    const selectedOpt = batchSelect ? batchSelect.selectedOptions[0] : null;
    const sisa = selectedOpt ? parseFloat(selectedOpt.getAttribute('data-sisa') || 0) : 0;

    // 1. Cek anomali bahaya: Qty melebihi sisa fisik atau Qty <= 0
    if (qty <= 0 || (sisa > 0 && qty > sisa) || (selectedOpt && !selectedOpt.value)) {
        row.classList.add('row-audit-danger');
        if (badge) {
            badge.className = 'audit-badge-cell audit-badge-danger';
            badge.innerHTML = '!';
            badge.title = (qty <= 0) 
                ? 'Anomali: Kuantitas harus lebih dari 0' 
                : `Anomali Stok: Kuantitas (${qty.toLocaleString('id-ID')}) melebihi sisa fisik batch (${sisa.toLocaleString('id-ID')})`;
        }
        return 'danger';
    }

    // 2. Cek peringatan FIFO: melompati batch tertua
    if (batchSelect && batchSelect.selectedIndex > 0 && batchSelect.options.length > 1) {
        const oldestOpt = batchSelect.options[0];
        const oldestSisa = parseFloat(oldestOpt ? oldestOpt.getAttribute('data-sisa') || 0 : 0);
        if (oldestSisa > 0) {
            row.classList.add('row-audit-warning');
            if (badge) {
                badge.className = 'audit-badge-cell audit-badge-warn';
                badge.innerHTML = '⚠';
                badge.title = `Peringatan FIFO: Melompati batch tertua [${oldestOpt.value}] yang masih bersisa ${oldestSisa.toLocaleString('id-ID')} unit`;
            }
            return 'warning';
        }
    }

    // 3. Status aman & lolos audit
    row.classList.add('row-audit-ok');
    if (badge) {
        badge.className = 'audit-badge-cell audit-badge-ok';
        badge.innerHTML = '✓';
        badge.title = 'Audit Lolos: Stok batch fisik cukup & sesuai aturan FIFO';
    }
    return 'ok';
}

function updateAuditSummaryCounts() {
    let countOk = 0;
    let countWarn = 0;
    let countDanger = 0;

    document.querySelectorAll('#itemsBody tr').forEach(row => {
        const st = updateRowAuditStatus(row);
        if (st === 'danger') countDanger++;
        else if (st === 'warning') countWarn++;
        else if (st === 'ok') countOk++;
    });

    const elOk = document.getElementById('auditCountOk');
    if (elOk) elOk.innerText = countOk;

    const elWarn = document.getElementById('auditCountWarn');
    if (elWarn) elWarn.innerText = countWarn;

    const elDanger = document.getElementById('auditCountDanger');
    if (elDanger) elDanger.innerText = countDanger;
}

function calcRow(el) {
    const row = el.closest('tr');
    const qtyInput = row.querySelector('.qty-input');
    const hargaInput = row.querySelector('.harga-input');
    const subtotalCell = row.querySelector('.subtotal-cell');
    const batchSelect = row.querySelector('.batch-select');
    const batchInfo = row.querySelector('.batch-info');

    const qty = parseFloat(qtyInput.value || 0);
    const harga = parseFloat(hargaInput.value || 0);
    const subtotal = qty * harga;

    subtotalCell.innerText = 'Rp ' + Math.round(subtotal).toLocaleString('id-ID');

    if (batchSelect && batchSelect.selectedOptions[0]) {
        const selectedOpt = batchSelect.selectedOptions[0];
        const sisa = parseFloat(selectedOpt.getAttribute('data-sisa') || 0);
        const grade = (row.querySelector('.grade-input')?.value || selectedOpt.getAttribute('data-grade') || 'A').toUpperCase();
        const gradeBadge = (grade === 'A')
            ? `<span style="background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; font-weight: 700; padding: 1px 5px; border-radius: 3px; font-size: 0.7rem;">🟢 Grade A</span>`
            : ((grade === 'B')
                ? `<span style="background: #fffbeb; color: #b45309; border: 1px solid #fde68a; font-weight: 700; padding: 1px 5px; border-radius: 3px; font-size: 0.7rem;">🟡 Grade B</span>`
                : `Grade ${grade}`);

        if (sisa > 0 && qty > sisa) {
            qtyInput.style.borderColor = '#dc2626';
            qtyInput.style.background = '#fef2f2';
            qtyInput.title = `Kuantitas melebihi sisa fisik batch (${sisa.toLocaleString('id-ID')})`;
            if (batchInfo) {
                batchInfo.innerHTML = `
                    <div style="background: #fef2f2; border: 1.5px solid #f87171; border-radius: 6px; padding: 0.45rem 0.65rem; margin-top: 0.35rem; color: #991b1b;">
                        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 0.5rem; flex-wrap: wrap;">
                            <div>
                                <div style="display: flex; align-items: center; gap: 0.35rem; margin-bottom: 0.2rem;">
                                    ${gradeBadge}
                                    <strong style="color: #dc2626; font-size: 0.775rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                                        ⚠️ Kuantitas Melebihi Sisa Fisik Batch!
                                    </strong>
                                </div>
                                <div style="font-size: 0.735rem; color: #7f1d1d; line-height: 1.35;">
                                    Batch <strong>${selectedOpt.value}</strong> hanya bersisa <strong>${sisa.toLocaleString('id-ID')} unit</strong>. Sisa <strong>${(qty - sisa).toLocaleString('id-ID')} unit</strong> harus diambil dari batch lain!
                                </div>
                            </div>
                            <button type="button" onclick="autoSplitBatchForRow(this.closest('tr'))" style="background: #dc2626; color: #ffffff; font-weight: 700; font-size: 0.725rem; padding: 4px 10px; border: none; border-radius: 4px; cursor: pointer; white-space: nowrap; display: inline-flex; align-items: center; gap: 0.35rem; box-shadow: 0 1px 3px rgba(220,38,38,0.3);" title="Ambil sisa batch ini & otomatis tambah baris untuk sisa kuantitas">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                <span>⚡ Pecah ke Batch Berikutnya</span>
                            </button>
                        </div>
                    </div>
                `;
            }
        } else {
            qtyInput.style.borderColor = '#cbd5e1';
            qtyInput.style.background = '#ffffff';
            qtyInput.title = '';
            renderNormalBatchInfo(row); // Otomatis kembalikan status normal & hilangkan kartu merah
        }
    }

    calculateGrandTotal();
    updateAuditSummaryCounts();
}

function calculateGrandTotal() {
    let grandTotal = 0;
    let totalQty = 0;
    let gradeAQty = 0;
    let gradeBQty = 0;
    let validItemCount = 0;

    document.querySelectorAll('#itemsBody tr').forEach(row => {
        const barangSelect = row.querySelector('.barang-select');
        const qtyInput = row.querySelector('.qty-input');
        const hargaInput = row.querySelector('.harga-input');
        const gradeInput = row.querySelector('.grade-input');

        const qty = parseFloat(qtyInput ? qtyInput.value : 0) || 0;
        const harga = parseFloat(hargaInput ? hargaInput.value : 0) || 0;
        const grade = (gradeInput ? gradeInput.value : 'A').toUpperCase();

        if (barangSelect && barangSelect.value && qty > 0) {
            validItemCount++;
            grandTotal += (qty * harga);
            totalQty += qty;

            if (grade === 'A') gradeAQty += qty;
            else if (grade === 'B') gradeBQty += qty;
        }
    });

    // Update foot table
    const grandTotalNilai = document.getElementById('grandTotalNilai');
    if (grandTotalNilai) grandTotalNilai.innerText = 'Rp ' + Math.round(grandTotal).toLocaleString('id-ID');

    const grandTotalQty = document.getElementById('grandTotalQty');
    if (grandTotalQty) grandTotalQty.innerText = totalQty.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    const footGradeA = document.getElementById('footGradeAQty');
    if (footGradeA) footGradeA.innerText = gradeAQty.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    const footGradeB = document.getElementById('footGradeBQty');
    if (footGradeB) footGradeB.innerText = gradeBQty.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    // Update sticky sidebar & header widget
    const sideGrandTotal = document.getElementById('sideGrandTotal');
    if (sideGrandTotal) sideGrandTotal.innerText = 'Rp ' + Math.round(grandTotal).toLocaleString('id-ID');

    const sideTotalQty = document.getElementById('sideTotalQty');
    if (sideTotalQty) sideTotalQty.innerText = totalQty.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    const sideTotalQtyLarge = document.getElementById('sideTotalQtyLarge');
    if (sideTotalQtyLarge) sideTotalQtyLarge.innerText = totalQty.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    const sideGradeAQty = document.getElementById('sideGradeAQty');
    if (sideGradeAQty) sideGradeAQty.innerText = gradeAQty.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' Kg';

    const sideGradeBQty = document.getElementById('sideGradeBQty');
    if (sideGradeBQty) sideGradeBQty.innerText = gradeBQty.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' Kg';

    const sideTotalItems = document.getElementById('sideTotalItems');
    if (sideTotalItems) sideTotalItems.innerText = validItemCount;

    // Update bottom bar live values
    const botBarTotalQty = document.getElementById('botBarTotalQty');
    if (botBarTotalQty) botBarTotalQty.innerText = totalQty.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' Kg';

    const botBarGrandTotal = document.getElementById('botBarGrandTotal');
    if (botBarGrandTotal) botBarGrandTotal.innerText = 'Rp ' + Math.round(grandTotal).toLocaleString('id-ID');

    // Update sticky sidebar / bottom bar simulasi jurnal umum otomatis
    const sideJournalDebet = document.getElementById('sideJournalDebet');
    if (sideJournalDebet) sideJournalDebet.innerText = 'Rp ' + Math.round(grandTotal).toLocaleString('id-ID');

    const sideJournalKredit = document.getElementById('sideJournalKredit');
    if (sideJournalKredit) sideJournalKredit.innerText = 'Rp ' + Math.round(grandTotal).toLocaleString('id-ID');
}

function autoSplitBatchForRow(row) {
    if (!row) return;
    const barangSelect = row.querySelector('.barang-select');
    const batchSelect = row.querySelector('.batch-select');
    const qtyInput = row.querySelector('.qty-input');
    const gradeInput = row.querySelector('.grade-input');

    if (!barangSelect || !barangSelect.value || !batchSelect || !batchSelect.value) return;

    const barangId = barangSelect.value;
    const totalRequested = parseFloat(qtyInput?.value || 0);
    if (totalRequested <= 0) return;

    const selectedOpt = batchSelect.selectedOptions[0];
    if (!selectedOpt) return;

    const sisaCurrent = parseFloat(selectedOpt.getAttribute('data-sisa') || 0);
    if (totalRequested <= sisaCurrent) {
        return;
    }

    let allBatches = row._allBatches || [];
    if (allBatches.length === 0 && batchSelect.options.length > 0) {
        allBatches = Array.from(batchSelect.options).filter(opt => opt.value).map(opt => ({
            batch_no: opt.value,
            stok_id: opt.getAttribute('data-stok-id') || '',
            grade_cd: opt.getAttribute('data-grade') || 'A',
            sisa_qty: parseFloat(opt.getAttribute('data-sisa') || 0),
            harga_satuan: parseFloat(opt.getAttribute('data-harga') || 0),
            tgl_terima: opt.getAttribute('data-masuk') || '',
            expired_tgl: opt.getAttribute('data-exp') || ''
        }));
        row._allBatches = allBatches;
    }

    const currentGrade = (gradeInput?.value || selectedOpt.getAttribute('data-grade') || 'A').toUpperCase();

    // 1. Baris saat ini mengambil kuantitas maksimal yang masih ada pada batch tersebut
    const takeCurrent = Math.min(totalRequested, sisaCurrent);
    qtyInput.value = takeCurrent;
    calcRow(qtyInput);

    let remainingQty = totalRequested - takeCurrent;

    // 2. Kumpulkan kuantitas batch yang telah digunakan pada baris-baris lain
    const usedBatchMap = {};
    document.querySelectorAll('#itemsBody tr').forEach(r => {
        const bId = r.querySelector('.barang-select')?.value;
        const bNo = r.querySelector('.batch-select')?.value;
        const gCd = (r.querySelector('.grade-input')?.value || 'A').toUpperCase();
        const qVal = parseFloat(r.querySelector('.qty-input')?.value || 0);
        if (bId === barangId && bNo) {
            const key = `${bNo}_${gCd}`;
            usedBatchMap[key] = (usedBatchMap[key] || 0) + qVal;
        }
    });

    // 3. Cari batch berikutnya yang masih memiliki saldo fisik
    let candidateBatches = allBatches.filter(b => (b.grade_cd || 'A').toUpperCase() === currentGrade);
    if (candidateBatches.length === 0) {
        candidateBatches = allBatches;
    }

    let addedRows = 0;
    for (const b of candidateBatches) {
        if (remainingQty <= 0) break;
        const bGrade = (b.grade_cd || 'A').toUpperCase();
        const key = `${b.batch_no}_${bGrade}`;
        const alreadyUsed = usedBatchMap[key] || 0;
        const availableInBatch = Math.max(0, parseFloat(b.sisa_qty) - alreadyUsed);

        if (availableInBatch > 0) {
            const takeFromThis = Math.min(remainingQty, availableInBatch);
            remainingQty -= takeFromThis;
            usedBatchMap[key] = alreadyUsed + takeFromThis;

            addRowWithItemData({
                barang_id: barangId,
                batch_no: b.batch_no,
                grade_cd: bGrade,
                stok_id: b.stok_id || '',
                qty_keluar: takeFromThis,
                harga_satuan: b.harga_satuan || 0,
                satuan_nm: row.querySelector('.row-satuan')?.textContent || 'Kg',
                all_batches: allBatches
            });
            addedRows++;
        }
    }

    calculateGrandTotal();

    if (remainingQty > 0) {
        alert(`Perhatian: Berhasil memecah ke ${addedRows} baris batch lanjutan.\nNamun stok fisik barang ini di seluruh batch gudang sudah habis total!\nMasih kurang ${remainingQty.toLocaleString('id-ID')} unit yang tidak dapat dialokasikan.`);
    }
}

function checkStockLimitViolations() {
    const violations = [];
    document.querySelectorAll('#itemsBody tr').forEach(row => {
        const barangSelect = row.querySelector('.barang-select');
        const batchSelect = row.querySelector('.batch-select');
        const qtyInput = row.querySelector('.qty-input');

        if (!barangSelect || !barangSelect.value || !batchSelect || !batchSelect.value) return;

        const qty = parseFloat(qtyInput?.value || 0);
        if (qty <= 0) return;

        const selectedOpt = batchSelect.selectedOptions[0];
        if (!selectedOpt) return;

        const sisa = parseFloat(selectedOpt.getAttribute('data-sisa') || 0);
        if (qty > sisa) {
            const barangOpt = barangSelect.selectedOptions[0];
            const barangNm = barangOpt ? barangOpt.textContent.trim() : 'Bahan Produksi';
            const rowNum = row.querySelector('.row-num')?.textContent.trim() || '-';
            violations.push({
                row: row,
                rowNum: rowNum,
                barangNm: barangNm,
                batchNo: selectedOpt.value,
                grade: selectedOpt.getAttribute('data-grade') || 'A',
                qtyRequested: qty,
                sisaBatch: sisa,
                selisih: qty - sisa
            });
        }
    });
    return violations;
}

function openWarningStockLimitModal(violations) {
    const tbody = document.getElementById('stockLimitWarningListBody');
    if (!tbody) return;

    let html = '';
    violations.forEach((v, idx) => {
        const gradeBadge = (v.grade === 'A')
            ? `<span style="background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; padding:1px 5px; border-radius:3px; font-weight:700; font-size:0.7rem;">🟢 Grade A</span>`
            : ((v.grade === 'B')
                ? `<span style="background:#fffbeb; color:#b45309; border:1px solid #fde68a; padding:1px 5px; border-radius:3px; font-weight:700; font-size:0.7rem;">🟡 Grade B</span>`
                : `Grade ${v.grade}`);

        html += `
            <tr style="border-bottom: 1px solid #e2e8f0; ${idx % 2 === 1 ? 'background:#fafafa;' : ''}">
                <td style="padding: 0.55rem 0.65rem;">
                    <div style="font-weight: 600; color: #0f172a;">${v.barangNm}</div>
                    <div style="font-size: 0.72rem; color: #64748b; margin-top: 2px;">Baris #${v.rowNum} &bull; ${gradeBadge}</div>
                </td>
                <td style="padding: 0.55rem 0.65rem;">
                    <strong style="color: #b45309; font-family: monospace;">${v.batchNo}</strong>
                </td>
                <td style="padding: 0.55rem 0.65rem; text-align: right; font-weight: 700; color: #dc2626; font-family: monospace;">
                    ${v.qtyRequested.toLocaleString('id-ID')}
                </td>
                <td style="padding: 0.55rem 0.65rem; text-align: right; font-weight: 700; color: #059669; font-family: monospace;">
                    ${v.sisaBatch.toLocaleString('id-ID')}
                </td>
                <td style="padding: 0.55rem 0.65rem; text-align: center;">
                    <button type="button" onclick="splitSpecificRowFromModal(${idx})" style="background:#dc2626; color:#ffffff; font-weight:700; font-size:0.725rem; padding:4px 8px; border:none; border-radius:4px; cursor:pointer; display:inline-flex; align-items:center; gap:0.25rem;" title="Pecah baris ini">
                        <span>⚡ Pecah Baris</span>
                    </button>
                </td>
            </tr>
        `;
    });
    tbody.innerHTML = html;

    window._currentStockViolations = violations;

    const modal = document.getElementById('modalWarningStockLimit');
    if (modal) {
        modal.style.display = 'flex';
    }
}

function closeWarningStockLimitModal() {
    const modal = document.getElementById('modalWarningStockLimit');
    if (modal) {
        modal.style.display = 'none';
    }
}

function splitSpecificRowFromModal(idx) {
    if (window._currentStockViolations && window._currentStockViolations[idx]) {
        const v = window._currentStockViolations[idx];
        autoSplitBatchForRow(v.row);
        const remaining = checkStockLimitViolations();
        if (remaining.length > 0) {
            openWarningStockLimitModal(remaining);
        } else {
            closeWarningStockLimitModal();
        }
    }
}

function applyAutoSplitAllViolations() {
    const violations = checkStockLimitViolations();
    violations.forEach(v => {
        autoSplitBatchForRow(v.row);
    });
    closeWarningStockLimitModal();
    calculateGrandTotal();
}

function checkFifoViolations() {
    const violations = [];
    document.querySelectorAll('#itemsBody tr').forEach(row => {
        const barangSelect = row.querySelector('.barang-select');
        const batchSelect = row.querySelector('.batch-select');
        const qtyInput = row.querySelector('.qty-input');

        if (!barangSelect || !barangSelect.value || !batchSelect || !batchSelect.value) return;

        const qty = parseFloat(qtyInput?.value || 0);
        if (qty <= 0) return;

        if (batchSelect.selectedIndex > 0 && batchSelect.options.length > 1) {
            const chosenOpt = batchSelect.selectedOptions[0];
            const oldestOpt = batchSelect.options[0];
            const oldestSisa = parseFloat(oldestOpt?.getAttribute('data-sisa') || 0);

            if (oldestSisa > 0) {
                const barangOpt = barangSelect.selectedOptions[0];
                const barangNm = barangOpt ? barangOpt.textContent.trim() : 'Bahan Produksi';
                const grade = chosenOpt.getAttribute('data-grade') || 'A';

                violations.push({
                    barangNm: barangNm,
                    grade: grade,
                    chosenBatch: chosenOpt.value,
                    chosenMasuk: chosenOpt.getAttribute('data-masuk') || '-',
                    oldestBatch: oldestOpt.value,
                    oldestMasuk: oldestOpt.getAttribute('data-masuk') || '-',
                    oldestSisa: oldestSisa.toLocaleString('id-ID')
                });
            }
        }
    });
    return violations;
}

function openWarningFifoModal(violations) {
    const tbody = document.getElementById('fifoWarningListBody');
    if (!tbody) return;

    let html = '';
    violations.forEach((v, idx) => {
        const gradeBadge = (v.grade === 'A')
            ? `<span style="background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; padding:1px 5px; border-radius:3px; font-weight:700; font-size:0.7rem;">🟢 Grade A</span>`
            : ((v.grade === 'B')
                ? `<span style="background:#fffbeb; color:#b45309; border:1px solid #fde68a; padding:1px 5px; border-radius:3px; font-weight:700; font-size:0.7rem;">🟡 Grade B</span>`
                : `Grade ${v.grade}`);

        html += `
            <tr style="border-bottom: 1px solid #e2e8f0; ${idx % 2 === 1 ? 'background:#fafafa;' : ''}">
                <td style="padding: 0.55rem 0.65rem;">
                    <div style="font-weight: 600; color: #0f172a;">${v.barangNm}</div>
                    <div style="margin-top: 2px;">${gradeBadge}</div>
                </td>
                <td style="padding: 0.55rem 0.65rem;">
                    <strong style="color: #b45309; font-family: monospace;">${v.chosenBatch}</strong>
                    <div style="font-size: 0.72rem; color: #64748b;">Masuk: ${v.chosenMasuk}</div>
                </td>
                <td style="padding: 0.55rem 0.65rem; background: #f0fdf4;">
                    <strong style="color: #065f46; font-family: monospace;">⭐ ${v.oldestBatch}</strong>
                    <div style="font-size: 0.72rem; color: #047857;">Sisa: ${v.oldestSisa} unit • Masuk: ${v.oldestMasuk}</div>
                </td>
            </tr>
        `;
    });
    tbody.innerHTML = html;

    const modal = document.getElementById('modalWarningFifo');
    if (modal) {
        modal.style.display = 'flex';
    }
}

function closeWarningFifoModal() {
    const modal = document.getElementById('modalWarningFifo');
    if (modal) {
        modal.style.display = 'none';
    }
}

function applyFixAllToOldestBatch() {
    document.querySelectorAll('#itemsBody tr').forEach(row => {
        const batchSelect = row.querySelector('.batch-select');
        if (batchSelect && batchSelect.options.length > 0 && batchSelect.selectedIndex > 0) {
            batchSelect.selectedIndex = 0;
            onBatchSelect(batchSelect);
        }
    });

    closeWarningFifoModal();
    calculateGrandTotal();
}

function proceedOverrideFifoSubmit() {
    const reasonInput = document.getElementById('override_reason_input');
    const reason = reasonInput ? reasonInput.value.trim() : '';

    if (!reason) {
        alert('Harap tuliskan alasan singkat mengapa melompati batch terlama (misal: batch lama rusak / di-karantina).');
        reasonInput?.focus();
        return;
    }

    const catatanInput = document.getElementById('catatan_txt');
    if (catatanInput) {
        const prev = catatanInput.value.trim();
        catatanInput.value = prev ? `${prev} [OVERRIDE FIFO: ${reason}]` : `[OVERRIDE FIFO: ${reason}]`;
    }

    allowFifoOverride = true;
    closeWarningFifoModal();
    document.getElementById('action_type').value = pendingActionType;
    document.getElementById('verifikasiEditForm').submit();
}

function openModalConfirmApproveStation() {
    const modal = document.getElementById('modalConfirmApproveStation');
    if (!modal) return;

    let totalQty = 0;
    let grandTotal = 0;
    document.querySelectorAll('#itemsBody tr').forEach(row => {
        const q = parseFloat(row.querySelector('.qty-input')?.value || 0);
        const h = parseFloat(row.querySelector('.harga-input')?.value || 0);
        totalQty += q;
        grandTotal += (q * h);
    });

    const elQty = document.getElementById('modalAuditTotalQty');
    if (elQty) elQty.innerText = totalQty.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' Kg';

    const elTotal = document.getElementById('modalAuditGrandTotal');
    if (elTotal) elTotal.innerText = 'Rp ' + Math.round(grandTotal).toLocaleString('id-ID');

    const elDebet = document.getElementById('modalJournalDebet');
    if (elDebet) elDebet.innerText = 'Rp ' + Math.round(grandTotal).toLocaleString('id-ID');

    const elKredit = document.getElementById('modalJournalKredit');
    if (elKredit) elKredit.innerText = 'Rp ' + Math.round(grandTotal).toLocaleString('id-ID');

    modal.style.display = 'flex';
}

function closeModalConfirmApproveStation() {
    const modal = document.getElementById('modalConfirmApproveStation');
    if (modal) modal.style.display = 'none';
}

function executeApproveStationForm() {
    closeModalConfirmApproveStation();
    const actionInput = document.getElementById('action_type');
    if (actionInput) actionInput.value = 'save_and_approve';
    document.getElementById('verifikasiEditForm').submit();
}

function submitVerifikasiForm(actionType) {
    pendingActionType = actionType;
    const actionInput = document.getElementById('action_type');
    if (actionInput) actionInput.value = actionType;

    const form = document.getElementById('verifikasiEditForm');
    if (!form.reportValidity()) {
        return;
    }

    // 1. Cek kuantitas melebihi sisa fisik batch (STOCK LIMIT) - BLOKIR KERAS!
    const stockViolations = checkStockLimitViolations();
    if (stockViolations.length > 0) {
        openWarningStockLimitModal(stockViolations);
        return;
    }

    // 2. Cek urutan FIFO
    if (!allowFifoOverride) {
        const violations = checkFifoViolations();
        if (violations.length > 0) {
            openWarningFifoModal(violations);
            return;
        }
    }

    // 3. Jika Save & Approve: Tampilkan Modal Konfirmasi Smart Audit (Tanpa alert/confirm browser bawaan)
    if (actionType === 'save_and_approve') {
        openModalConfirmApproveStation();
        return;
    }

    form.submit();
}

// Inisialisasi awal saat halaman selesai dimuat
document.addEventListener('DOMContentLoaded', () => {
    const initialItems = window.appConfig?.initialItems || [];

    if (initialItems.length > 0) {
        initialItems.forEach(item => {
            addRowWithItemData(item);
        });
    } else {
        addRow();
    }

    updateSidebarInfo();
    calculateGrandTotal();
    updateAuditSummaryCounts();

    document.getElementById('tujuan_pemakaian')?.addEventListener('change', updateSidebarInfo);
    document.getElementById('tujuan_pemakaian')?.addEventListener('input', updateSidebarInfo);
    document.getElementById('gudang_id')?.addEventListener('change', () => {
        updateSidebarInfo();
        onGudangChanged();
    });
});
