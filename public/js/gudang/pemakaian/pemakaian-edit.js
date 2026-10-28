/**
 * public/js/gudang/pemakaian/pemakaian-edit.js
 * Logika Frontend Edit Pemakaian Bahan Baku & Penolong (BPPB) - ERP PT Mirasa
 */

(function () {
    'use strict';

    const BARANG_LIST = window.appConfig?.barangList || [];
    const EXISTING_DETAILS = window.appConfig?.existingDetails || [];
    let rowIndex = 0;
    let allowFifoOverride = false;

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
        document.querySelectorAll('#itemsBody tr').forEach(row => {
            const barangSelect = row.querySelector('.barang-select');
            if (barangSelect && barangSelect.value) {
                const group = row.querySelector('.grade-pill-group');
                if (group) {
                    group.querySelectorAll('.btn-chip').forEach(b => b.classList.remove('active'));
                    group.children[0]?.classList.add('active');
                }
                fetchBatchesForRow(row, barangSelect.value, '');
            }
        });
    }

    function renderBarangOptions(selectedBarangId = '', categoryFilter = '') {
        const filtered = categoryFilter 
            ? BARANG_LIST.filter(b => b.kategori_kelompok === categoryFilter)
            : BARANG_LIST;

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

    function addRow(initialCategory = '') {
        const tbody = document.getElementById('itemsBody');
        const tr = document.createElement('tr');
        tr.id = `row-${rowIndex}`;

        const barangOptions = renderBarangOptions('', initialCategory);

        tr.innerHTML = `
            <td class="row-num" style="font-weight: 800; text-align: center; color: #475569; background: #f8fafc; font-size: 0.95rem;">
                ${tbody.children.length + 1}
            </td>
            <td style="padding: 0.65rem 0.65rem;">
                <div class="cat-pill-group" style="display: flex; gap: 0.35rem; margin-bottom: 0.45rem; align-items: center; flex-wrap: wrap;">
                    <button type="button" class="btn-chip ${initialCategory === '' ? 'active' : ''}" onclick="window.pemakaianApp.setRowCategory(this, '')">Semua</button>
                    <button type="button" class="btn-chip ${initialCategory === 'BAHAN_BAKU' ? 'active' : ''}" onclick="window.pemakaianApp.setRowCategory(this, 'BAHAN_BAKU')">Bahan Baku</button>
                    <button type="button" class="btn-chip ${initialCategory === 'BAHAN_PENOLONG' ? 'active' : ''}" onclick="window.pemakaianApp.setRowCategory(this, 'BAHAN_PENOLONG')">Bumbu / Penolong</button>
                    <button type="button" class="btn-chip ${initialCategory === 'KEMASAN' ? 'active' : ''}" onclick="window.pemakaianApp.setRowCategory(this, 'KEMASAN')">Kemasan</button>
                </div>
                <select name="items[${rowIndex}][barang_id]" class="form-control barang-select" required onchange="window.pemakaianApp.onBarangSelect(this)">
                    ${barangOptions}
                </select>
            </td>
            <td style="padding: 0.65rem 0.65rem;">
                <div class="grade-pill-group" style="display: flex; gap: 0.35rem; margin-bottom: 0.45rem; align-items: center; flex-wrap: nowrap;">
                    <button type="button" class="btn-chip active" onclick="window.pemakaianApp.setRowGradeFilter(this, '')">Semua</button>
                    <button type="button" class="btn-chip btn-chip-grade-a" onclick="window.pemakaianApp.setRowGradeFilter(this, 'A')">🟢 Grade A</button>
                    <button type="button" class="btn-chip btn-chip-grade-b" onclick="window.pemakaianApp.setRowGradeFilter(this, 'B')">🟡 Grade B</button>
                </div>
                <input type="hidden" name="items[${rowIndex}][grade_cd]" class="grade-input" value="A">
                <input type="hidden" name="items[${rowIndex}][stok_id]" class="stok-id-input" value="">
                <select name="items[${rowIndex}][batch_no]" class="form-control batch-select" required onchange="window.pemakaianApp.onBatchSelect(this)">
                    <option value="">-- Pilih Barang Dulu --</option>
                </select>
                <div class="batch-info" style="font-size: 0.775rem; color: #059669; font-weight: 600; margin-top: 0.35rem;"></div>
            </td>
            <td style="padding: 0.65rem 0.65rem;">
                <input type="number" step="0.0001" min="0.0001" name="items[${rowIndex}][qty_keluar]" class="form-control qty-input" placeholder="0" required oninput="window.pemakaianApp.calcRow(this)">
            </td>
            <td style="text-align: center; padding: 0.65rem 0.65rem;">
                <span class="row-satuan" style="font-weight: 800; color: #334155; font-size: 0.95rem;">-</span>
                <input type="hidden" name="items[${rowIndex}][harga_satuan]" class="harga-input" value="0">
            </td>
            <td style="padding: 0.65rem 0.65rem; text-align: center;">
                <button type="button" onclick="window.pemakaianApp.removeRow(this)" style="width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.25rem; font-weight: 700; color: #dc2626; background: #fef2f2; border: 1.5px solid #fecaca; border-radius: 6px; cursor: pointer; transition: all 0.15s ease;" title="Hapus Baris">&times;</button>
            </td>
        `;

        tbody.appendChild(tr);
        attachExcelKeyboardEvents(tr);
        rowIndex++;
        updateRowNumbers();
        calculateGrandTotal();
        return tr;
    }

    function addExistingRow(detail) {
        const tr = addRow();
        const barangSelect = tr.querySelector('.barang-select');
        const qtyInput = tr.querySelector('.qty-input');
        const hargaInput = tr.querySelector('.harga-input');
        const gradeInput = tr.querySelector('.grade-input');
        const stokIdInput = tr.querySelector('.stok-id-input');

        barangSelect.value = detail.barang_id;
        qtyInput.value = parseFloat(detail.qty_keluar);
        hargaInput.value = detail.harga_satuan || 0;
        gradeInput.value = detail.grade_cd || 'A';
        stokIdInput.value = detail.stok_id || '';

        const opt = barangSelect.options[barangSelect.selectedIndex];
        if (opt) {
            tr.querySelector('.row-satuan').innerText = opt.getAttribute('data-satuan') || '-';
        }

        const gudangId = getSelectedGudangId();
        const batchSelect = tr.querySelector('.batch-select');

        const baseUrl = window.appConfig?.batchesUrl || '/gudang/pemakaian/batches';
        const excludePakaiId = window.appConfig?.pakaiId || 0;
        const url = `${baseUrl}?gudang_id=${encodeURIComponent(gudangId)}&barang_id=${encodeURIComponent(detail.barang_id)}&exclude_pakai_id=${encodeURIComponent(excludePakaiId)}`;

        fetch(url)
            .then(res => {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .then(res => {
                if (res.status === 'success') {
                    tr._allBatches = res.batches || [];
                    let html = '';
                    let matched = false;

                    if (res.batches && res.batches.length > 0) {
                        res.batches.forEach((b, idx) => {
                            const isSelected = (b.batch_no === detail.batch_no && (b.grade_cd || 'A') === (detail.grade_cd || 'A'));
                            if (isSelected) matched = true;
                            const prefix = (idx === 0) ? '⭐ [FIFO Tertua] ' : '';
                            const gCd = (b.grade_cd || 'A').toUpperCase();
                            const gradeBadge = (gCd === 'A') ? '🟢 Grade A' : ((gCd === 'B') ? '🟡 Grade B' : `Grade ${gCd}`);
                            
                            const sisaFisik = parseFloat(b.sisa_qty || 0);
                            const sisaBebas = (b.sisa_bebas !== undefined) ? parseFloat(b.sisa_bebas) : sisaFisik;
                            const draftQty = parseFloat(b.draft_qty || 0);
                            const draftDocs = (b.draft_doc_nos || []).join(', ');
                            const isFullyBooked = b.is_fully_booked || (sisaBebas <= 0.0001 && draftQty > 0);

                            let labelText = '';
                            if (draftQty > 0) {
                                if (isFullyBooked) {
                                    labelText = `⛔ [HABIS DIBOOKING DRAFT] ${prefix}${b.batch_no} • [${gradeBadge}] (Sisa Bebas: 0 | Fisik: ${sisaFisik.toLocaleString('id-ID')} | ⚠️ ${draftQty.toLocaleString('id-ID')} di Draft ${draftDocs})`;
                                } else {
                                    labelText = `⚠️ [DRAFT ALLOCATED] ${prefix}${b.batch_no} • [${gradeBadge}] Sisa Bebas: ${sisaBebas.toLocaleString('id-ID')} (Fisik: ${sisaFisik.toLocaleString('id-ID')} | ⚠️ ${draftQty.toLocaleString('id-ID')} di Draft ${draftDocs})`;
                                }
                            } else {
                                labelText = `${prefix}${b.batch_no} • [${gradeBadge}] (Sisa: ${sisaFisik.toLocaleString('id-ID')})`;
                            }

                            html += `<option value="${b.batch_no}" 
                                data-stok-id="${b.stok_id || ''}" 
                                data-grade="${gCd}" 
                                data-sisa="${sisaFisik}" 
                                data-sisa-bebas="${sisaBebas}"
                                data-draft-qty="${draftQty}"
                                data-draft-docs="${draftDocs}"
                                data-is-fully-booked="${isFullyBooked ? 1 : 0}"
                                data-harga="${b.harga_satuan || 0}" 
                                data-masuk="${b.tgl_terima || '-'}" 
                                data-exp="${b.expired_tgl || '-'}" 
                                ${isSelected ? 'selected' : ''}>
                                ${labelText}
                            </option>`;
                        });
                    }

                    if (!matched && detail.batch_no) {
                        html = `<option value="${detail.batch_no}" data-stok-id="${detail.stok_id || ''}" data-grade="${detail.grade_cd || 'A'}" data-sisa="0" data-sisa-bebas="0" data-draft-qty="0" data-harga="${detail.harga_satuan || 0}" selected>
                            ${detail.batch_no} • [Grade ${detail.grade_cd || 'A'}] (Batch Eksisting Dokumen)
                        </option>` + html;
                    }

                    batchSelect.innerHTML = html;
                    renderNormalBatchInfo(tr);
                    calculateGrandTotal();
                }
            })
            .catch(err => {
                console.error(err);
                batchSelect.innerHTML = `<option value="${detail.batch_no}" selected>${detail.batch_no}</option>`;
            });
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

    function addRowFromAllocation(item) {
        const tbody = document.getElementById('itemsBody');
        const tr = document.createElement('tr');
        tr.id = `row-${rowIndex}`;

        const itemObj = BARANG_LIST.find(b => parseInt(b.barang_id) === parseInt(item.barang_id));
        const itemCat = itemObj?.kategori_kelompok || '';
        const barangOptions = renderBarangOptions(item.barang_id, itemCat);

        tr._allBatches = item.all_batches || [];

        const curGrade = (item.grade_cd || 'A').toUpperCase();

        let batchOptions = '';
        if (!item.all_batches || item.all_batches.length === 0) {
            batchOptions = '<option value="">(Stok Fisik Habis di Gudang ini)</option>';
        } else {
            let batchesToShow = item.all_batches;
            if (curGrade === 'A' || curGrade === 'B') {
                const gradeFiltered = batchesToShow.filter(b => (b.grade_cd || 'A').toUpperCase() === curGrade);
                if (gradeFiltered.length > 0) batchesToShow = gradeFiltered;
            }

            batchesToShow.forEach(b => {
                const isSelected = (b.batch_no === item.batch_no && (b.grade_cd || 'A') === curGrade) ? 'selected' : '';
                const prefix = b.is_fifo_top ? '⭐ [FIFO Tertua] ' : '';
                const gCd = (b.grade_cd || 'A').toUpperCase();
                const gradeBadge = (gCd === 'A') ? '🟢 Grade A' : ((gCd === 'B') ? '🟡 Grade B' : `Grade ${gCd}`);
                const expInfo = b.expired_tgl ? ` | Exp: ${b.expired_tgl}` : '';
                const tglTerima = b.tgl_terima ? ` | Masuk: ${b.tgl_terima}` : '';

                const sisaFisik = parseFloat(b.sisa_qty || 0);
                const sisaBebas = (b.sisa_bebas !== undefined) ? parseFloat(b.sisa_bebas) : sisaFisik;
                const draftQty = parseFloat(b.draft_qty || 0);
                const draftDocs = Array.isArray(b.draft_doc_nos) ? b.draft_doc_nos.join(', ') : (b.draft_doc_nos || '');
                const isFullyBooked = b.is_fully_booked || (sisaBebas <= 0.0001 && draftQty > 0);

                let labelText = '';
                if (draftQty > 0) {
                    if (isFullyBooked) {
                        labelText = `⛔ [HABIS DIBOOKING DRAFT] ${prefix}${b.batch_no} • [${gradeBadge}] (Sisa Bebas: 0 | Fisik: ${sisaFisik.toLocaleString('id-ID')} | ⚠️ ${draftQty.toLocaleString('id-ID')} di Draft [${draftDocs}])`;
                    } else {
                        labelText = `⚠️ [DRAFT ALLOCATED] ${prefix}${b.batch_no} • [${gradeBadge}] Sisa Bebas: ${sisaBebas.toLocaleString('id-ID')} (Fisik: ${sisaFisik.toLocaleString('id-ID')} | ⚠️ ${draftQty.toLocaleString('id-ID')} di Draft [${draftDocs}])`;
                    }
                } else {
                    labelText = `${prefix}${b.batch_no} • [${gradeBadge}] (Sisa: ${sisaFisik.toLocaleString('id-ID')})${tglTerima}${expInfo}`;
                }

                batchOptions += `<option value="${b.batch_no}" 
                    data-stok-id="${b.stok_id || ''}" 
                    data-grade="${gCd}" 
                    data-sisa="${sisaFisik}" 
                    data-sisa-bebas="${sisaBebas}"
                    data-draft-qty="${draftQty}"
                    data-draft-docs="${draftDocs}"
                    data-is-fully-booked="${isFullyBooked ? 1 : 0}"
                    data-harga="${b.harga_satuan || 0}" 
                    data-masuk="${b.tgl_terima || '-'}" 
                    data-exp="${b.expired_tgl || '-'}" 
                    ${isSelected}>
                    ${labelText}
                </option>`;
            });
        }

        let badgeHtml = '';
        const gradeBadgeItem = (curGrade === 'A')
            ? `<span style="background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; font-weight: 700; padding: 1px 6px; border-radius: 4px; font-size: 0.72rem;">🟢 Grade A (Super)</span>`
            : ((curGrade === 'B')
                ? `<span style="background: #fffbeb; color: #b45309; border: 1px solid #fde68a; font-weight: 700; padding: 1px 6px; border-radius: 4px; font-size: 0.72rem;">🟡 Grade B (Standar)</span>`
                : `<span style="background: #f1f5f9; color: #334155; font-weight: 600; padding: 1px 6px; border-radius: 4px; font-size: 0.72rem;">Grade ${curGrade}</span>`);

        if (item.is_allocated) {
            const badgeBg = item.is_split ? '#fef3c7' : '#ecfdf5';
            const badgeColor = item.is_split ? '#92400e' : '#065f46';
            const badgeBorder = item.is_split ? '#fde68a' : '#a7f3d0';
            badgeHtml = `<div style="display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap;">
                ${gradeBadgeItem}
                <span style="color: ${badgeColor}; font-weight: 700; background: ${badgeBg}; padding: 0.15rem 0.5rem; border-radius: 4px; border: 1px solid ${badgeBorder}; display: inline-flex; align-items: center; gap: 0.25rem;">
                    ${item.catatan_fifo} • Sisa: ${item.sisa_batch.toLocaleString('id-ID')} unit
                </span>
            </div>`;
        } else {
            badgeHtml = `<div style="display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap;">
                ${gradeBadgeItem}
                <span style="color: #dc2626; font-weight: 700; background: #fee2e2; padding: 0.15rem 0.5rem; border-radius: 4px; border: 1px solid #fecaca; display: inline-block;">
                    ${item.catatan_fifo}
                </span>
            </div>`;
        }

        tr.innerHTML = `
            <td class="row-num" style="font-weight: 800; text-align: center; color: #475569; background: #f8fafc; font-size: 0.95rem;">
                ${tbody.children.length + 1}
            </td>
            <td style="padding: 0.65rem 0.65rem;">
                <div class="cat-pill-group" style="display: flex; gap: 0.35rem; margin-bottom: 0.45rem; align-items: center; flex-wrap: wrap;">
                    <button type="button" class="btn-chip ${itemCat === '' ? 'active' : ''}" onclick="window.pemakaianApp.setRowCategory(this, '')">Semua</button>
                    <button type="button" class="btn-chip ${itemCat === 'BAHAN_BAKU' ? 'active' : ''}" onclick="window.pemakaianApp.setRowCategory(this, 'BAHAN_BAKU')">Bahan Baku</button>
                    <button type="button" class="btn-chip ${itemCat === 'BAHAN_PENOLONG' ? 'active' : ''}" onclick="window.pemakaianApp.setRowCategory(this, 'BAHAN_PENOLONG')">Bumbu / Penolong</button>
                    <button type="button" class="btn-chip ${itemCat === 'KEMASAN' ? 'active' : ''}" onclick="window.pemakaianApp.setRowCategory(this, 'KEMASAN')">Kemasan</button>
                </div>
                <select name="items[${rowIndex}][barang_id]" class="form-control barang-select" required onchange="window.pemakaianApp.onBarangSelect(this)">
                    ${barangOptions}
                </select>
            </td>
            <td style="padding: 0.65rem 0.65rem;">
                <div class="grade-pill-group" style="display: flex; gap: 0.35rem; margin-bottom: 0.45rem; align-items: center; flex-wrap: nowrap;">
                    <button type="button" class="btn-chip ${curGrade !== 'A' && curGrade !== 'B' ? 'active' : ''}" onclick="window.pemakaianApp.setRowGradeFilter(this, '')">Semua</button>
                    <button type="button" class="btn-chip btn-chip-grade-a ${curGrade === 'A' ? 'active' : ''}" onclick="window.pemakaianApp.setRowGradeFilter(this, 'A')">🟢 Grade A</button>
                    <button type="button" class="btn-chip btn-chip-grade-b ${curGrade === 'B' ? 'active' : ''}" onclick="window.pemakaianApp.setRowGradeFilter(this, 'B')">🟡 Grade B</button>
                </div>
                <input type="hidden" name="items[${rowIndex}][grade_cd]" class="grade-input" value="${item.grade_cd || 'A'}">
                <input type="hidden" name="items[${rowIndex}][stok_id]" class="stok-id-input" value="${item.stok_id || ''}">
                <select name="items[${rowIndex}][batch_no]" class="form-control batch-select" required onchange="window.pemakaianApp.onBatchSelect(this)">
                    ${batchOptions}
                </select>
                <div class="batch-info" style="font-size: 0.775rem; color: #059669; font-weight: 600; margin-top: 0.35rem;">
                    ${badgeHtml}
                </div>
            </td>
            <td style="padding: 0.65rem 0.65rem;">
                <input type="number" step="0.0001" min="0.0001" name="items[${rowIndex}][qty_keluar]" class="form-control qty-input" value="${item.qty_keluar}" placeholder="0" required oninput="window.pemakaianApp.calcRow(this)">
            </td>
            <td style="text-align: center; padding: 0.65rem 0.65rem;">
                <span class="row-satuan" style="font-weight: 800; color: #334155; font-size: 0.95rem;">${item.satuan_nm}</span>
                <input type="hidden" name="items[${rowIndex}][harga_satuan]" class="harga-input" value="${item.harga_satuan || 0}">
            </td>
            <td style="padding: 0.65rem 0.65rem; text-align: center;">
                <button type="button" onclick="window.pemakaianApp.removeRow(this)" style="width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.25rem; font-weight: 700; color: #dc2626; background: #fef2f2; border: 1.5px solid #fecaca; border-radius: 6px; cursor: pointer; transition: all 0.15s ease;" title="Hapus Baris">&times;</button>
            </td>
        `;

        tbody.appendChild(tr);
        attachExcelKeyboardEvents(tr);
        rowIndex++;
        updateRowNumbers();

        if (item.batch_no) {
            calcRow(tr.querySelector('.qty-input'));
        }
    }

    function setBomGradeFilter(btn, grade) {
        const group = btn.closest('.grade-pill-group') || document.getElementById('bomGradePillGroup');
        if (group) {
            group.querySelectorAll('.btn-chip').forEach(b => b.classList.remove('active'));
        }
        btn.classList.add('active');
        const input = document.getElementById('bom_grade_filter');
        if (input) input.value = grade;
    }

    function tarikBahanResepFifo() {
        const gudangId = getSelectedGudangId();
        if (!gudangId) {
            alert('Harap pilih Lokasi Gudang Asal Barang terlebih dahulu!');
            document.getElementById('gudang_id')?.focus();
            return;
        }

        const bomSelect = document.getElementById('bom_select') || document.getElementById('bom_id');
        const bomId = bomSelect ? bomSelect.value : '';
        if (!bomId) {
            alert('Harap pilih Formula Resep Produk (BOM) terlebih dahulu!');
            bomSelect?.focus();
            return;
        }

        const targetQty = parseFloat(document.getElementById('target_produksi_qty')?.value) || 0;
        if (targetQty <= 0) {
            alert('Target Rencana Produksi harus lebih dari 0 karton!');
            document.getElementById('target_produksi_qty')?.focus();
            return;
        }

        const gradeFilter = document.getElementById('bom_grade_filter')?.value || '';
        const gradeLabelText = (gradeFilter === 'A') 
            ? ' (Khusus Grade A Saja)' 
            : ((gradeFilter === 'B') ? ' (Khusus Grade B Saja)' : '');

        const btn = document.getElementById('btnTarikResep');
        const btnText = document.getElementById('btnTarikText');
        const feedback = document.getElementById('resepFeedback');

        if (btn) btn.disabled = true;
        if (btnText) btnText.innerHTML = `Menghitung Batch FIFO${gradeLabelText}...`;
        if (feedback) feedback.style.display = 'none';

        const baseUrl = window.appConfig?.alokasiResepUrl || '/gudang/pemakaian/alokasi-resep';
        const url = `${baseUrl}?gudang_id=${encodeURIComponent(gudangId)}&bom_id=${encodeURIComponent(bomId)}&target_qty=${encodeURIComponent(targetQty)}&grade_filter=${encodeURIComponent(gradeFilter)}`;

        fetch(url)
            .then(res => {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .then(res => {
                if (btn) btn.disabled = false;
                if (btnText) btnText.innerHTML = 'Muat Ulang Resep';

                if (res.status === 'success') {
                    const data = res.data;
                    const items = data.items || [];

                    if (items.length === 0) {
                        alert('Resep ini belum memiliki rincian bahan baku terdaftar.');
                        return;
                    }

                    const tbody = document.getElementById('itemsBody');
                    tbody.innerHTML = '';
                    rowIndex = 0;

                    items.forEach(item => {
                        addRowFromAllocation(item);
                    });

                    calculateGrandTotal();

                    if (feedback) {
                        feedback.style.display = 'block';
                        feedback.innerHTML = `
                            <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 6px; padding: 0.65rem 1rem; color: #065f46; font-size: 0.85rem; display: flex; align-items: center; gap: 0.5rem;">
                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span><strong>✓ Resep Berhasil Dimuat:</strong> Alokasi batch FIFO telah diperbarui untuk ${items.length} baris bahan.</span>
                            </div>
                        `;
                    }

                    document.getElementById('itemsTable')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                } else {
                    alert(res.message || 'Gagal mengalkulasi resep BOM.');
                }
            })
            .catch(err => {
                console.error(err);
                if (btn) btn.disabled = false;
                if (btnText) btnText.innerHTML = 'Muat Ulang Resep';
                alert('Terjadi kesalahan koneksi saat menghitung alokasi resep.');
            });
    }

    function onBarangSelect(select) {
        const row = select.closest('tr');
        const barangId = select.value;
        const opt = select.selectedOptions[0];

        if (!barangId) {
            row.querySelector('.row-satuan').innerText = '-';
            row.querySelector('.harga-input').value = 0;
            row.querySelector('.batch-select').innerHTML = '<option value="">-- Pilih Barang Dulu --</option>';
            row.querySelector('.batch-info').innerHTML = '';
            calcRow(select);
            return;
        }

        row.querySelector('.row-satuan').innerText = opt.getAttribute('data-satuan') || '-';

        const group = row.querySelector('.grade-pill-group');
        if (group) {
            group.querySelectorAll('.btn-chip').forEach(b => b.classList.remove('active'));
            group.children[0]?.classList.add('active');
        }

        fetchBatchesForRow(row, barangId, '');
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
            filtered = batches;
            const group = row.querySelector('.grade-pill-group');
            if (group) {
                group.querySelectorAll('.btn-chip').forEach(b => b.classList.remove('active'));
                group.children[0]?.classList.add('active');
            }
        }

        let html = '';
        filtered.forEach((b, idx) => {
            const isTop = (idx === 0);
            const prefix = isTop ? '⭐ [FIFO Tertua] ' : '';
            const gCd = (b.grade_cd || 'A').toUpperCase();
            const gradeBadge = (gCd === 'A') ? '🟢 Grade A' : ((gCd === 'B') ? '🟡 Grade B' : `Grade ${gCd}`);
            const expInfo = b.expired_tgl ? ` | Exp: ${b.expired_tgl}` : '';
            const tglTerima = b.tgl_terima ? ` | Masuk: ${b.tgl_terima}` : '';
            
            const sisaFisik = parseFloat(b.sisa_qty || 0);
            const sisaBebas = (b.sisa_bebas !== undefined) ? parseFloat(b.sisa_bebas) : sisaFisik;
            const draftQty = parseFloat(b.draft_qty || 0);
            const draftDocs = (b.draft_doc_nos || []).join(', ');
            const isFullyBooked = b.is_fully_booked || (sisaBebas <= 0.0001 && draftQty > 0);

            let labelText = '';
            if (draftQty > 0) {
                if (isFullyBooked) {
                    labelText = `⛔ [HABIS DIBOOKING DRAFT] ${prefix}${b.batch_no} • [${gradeBadge}] (Sisa Bebas: 0 | Fisik: ${sisaFisik.toLocaleString('id-ID')} | ⚠️ ${draftQty.toLocaleString('id-ID')} di Draft ${draftDocs})`;
                } else {
                    labelText = `⚠️ [DRAFT ALLOCATED] ${prefix}${b.batch_no} • [${gradeBadge}] Sisa Bebas: ${sisaBebas.toLocaleString('id-ID')} (Fisik: ${sisaFisik.toLocaleString('id-ID')} | ⚠️ ${draftQty.toLocaleString('id-ID')} di Draft ${draftDocs})`;
                }
            } else {
                labelText = `${prefix}${b.batch_no} • [${gradeBadge}] (Sisa: ${sisaFisik.toLocaleString('id-ID')})${tglTerima}${expInfo}`;
            }

            html += `<option value="${b.batch_no}" 
                data-stok-id="${b.stok_id || ''}" 
                data-grade="${gCd}" 
                data-sisa="${sisaFisik}" 
                data-sisa-bebas="${sisaBebas}"
                data-draft-qty="${draftQty}"
                data-draft-docs="${draftDocs}"
                data-is-fully-booked="${isFullyBooked ? 1 : 0}"
                data-harga="${b.harga_satuan || 0}" 
                data-masuk="${b.tgl_terima || '-'}" 
                data-exp="${b.expired_tgl || '-'}" 
                ${isTop ? 'selected' : ''}>
                ${labelText}
            </option>`;
        });
        batchSelect.innerHTML = html;

        onBatchSelect(batchSelect);
    }

    function fetchBatchesForRow(row, barangId, forcedGrade = null) {
        const gudangId = getSelectedGudangId();
        const batchSelect = row.querySelector('.batch-select');
        const batchInfo = row.querySelector('.batch-info');

        if (!gudangId) {
            batchSelect.innerHTML = '<option value="">-- Pilih Gudang di Atas Terlebih Dahulu --</option>';
            if (batchInfo) {
                batchInfo.innerHTML = '<span style="color: #dc2626; font-weight: 600;">⚠️ Harap pilih Gudang Asal Barang di bagian atas terlebih dahulu.</span>';
            }
            return;
        }

        batchSelect.innerHTML = '<option value="">Memuat data batch FIFO...</option>';
        if (batchInfo) {
            batchInfo.innerHTML = '<span style="color: #64748b;">Memeriksa stok fisik & urutan batch FIFO di gudang...</span>';
        }

        const baseUrl = window.appConfig?.batchesUrl || '/gudang/pemakaian/batches';
        const excludePakaiId = window.appConfig?.pakaiId || 0;
        const url = `${baseUrl}?gudang_id=${encodeURIComponent(gudangId)}&barang_id=${encodeURIComponent(barangId)}&exclude_pakai_id=${encodeURIComponent(excludePakaiId)}`;

        fetch(url)
            .then(res => {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            })
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
                    if (batchInfo) {
                        batchInfo.innerHTML = `<span style="color: #dc2626;">${res.message || 'Gagal memuat batch.'}</span>`;
                    }
                }
            })
            .catch(err => {
                console.error(err);
                batchSelect.innerHTML = '<option value="">Gagal memuat batch</option>';
                if (batchInfo) {
                    batchInfo.innerHTML = '<span style="color: #dc2626;">Koneksi gagal saat mengambil batch stok.</span>';
                }
            });
    }

    function renderNormalBatchInfo(row) {
        const batchSelect = row.querySelector('.batch-select');
        const batchInfo = row.querySelector('.batch-info');
        if (!batchSelect || !batchInfo) return;

        const opt = batchSelect.selectedOptions[0];
        if (!opt || !opt.value) {
            batchInfo.innerHTML = '';
            return;
        }

        const sisa = parseFloat(opt.getAttribute('data-sisa') || 0);
        const sisaBebas = parseFloat(opt.getAttribute('data-sisa-bebas') || sisa);
        const draftQty = parseFloat(opt.getAttribute('data-draft-qty') || 0);
        const draftDocs = opt.getAttribute('data-draft-docs') || '';
        const tglMasuk = opt.getAttribute('data-masuk') || '-';
        const grade = (row.querySelector('.grade-input')?.value || opt.getAttribute('data-grade') || 'A').toUpperCase();

        const gradeBadge = (grade === 'A')
            ? `<span style="background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; font-weight: 700; padding: 1px 6px; border-radius: 4px; font-size: 0.725rem;">🟢 Grade A</span>`
            : ((grade === 'B')
                ? `<span style="background: #fffbeb; color: #b45309; border: 1px solid #fde68a; font-weight: 700; padding: 1px 6px; border-radius: 4px; font-size: 0.725rem;">🟡 Grade B</span>`
                : `<span style="background: #f1f5f9; color: #334155; font-weight: 600; padding: 1px 6px; border-radius: 4px; font-size: 0.725rem;">Grade ${grade}</span>`);

        const isFirst = (batchSelect.selectedIndex === 0);
        const oldestOpt = batchSelect.options[0];
        const oldestSisa = oldestOpt ? parseFloat(oldestOpt.getAttribute('data-sisa') || 0) : 0;
        const isSame = oldestOpt && (opt.value === oldestOpt.value);

        let oldestAlreadyUsed = false;
        if (oldestOpt) {
            document.querySelectorAll('#itemsBody tr').forEach(r => {
                if (r !== row && r.querySelector('.batch-select')?.value === oldestOpt.value) {
                    oldestAlreadyUsed = true;
                }
            });
        }

        let draftWarningNotice = '';
        if (draftQty > 0) {
            draftWarningNotice = `
                <div style="background: #fffbeb; border: 1.5px solid #f59e0b; border-radius: 6px; padding: 0.35rem 0.6rem; margin-top: 0.3rem; color: #92400e;">
                    <div style="display: flex; align-items: center; gap: 0.35rem; font-weight: 700; font-size: 0.74rem;">
                        <span>⚠️ Terikat Dokumen DRAFT: [${draftDocs}]</span>
                    </div>
                    <div style="font-size: 0.72rem; color: #78350f; margin-top: 2px;">
                        Kuantitas dibooking draft lain: <strong>${draftQty.toLocaleString('id-ID')} unit</strong>. Sisa bebas unallocated: <strong style="color: #065f46;">${sisaBebas.toLocaleString('id-ID')} unit</strong> (dari fisik ${sisa.toLocaleString('id-ID')} unit).
                    </div>
                </div>
            `;
        }

        if (isFirst || isSame || oldestAlreadyUsed) {
            batchSelect.style.borderColor = '#cbd5e1';
            batchSelect.style.backgroundColor = '#ffffff';
            batchInfo.innerHTML = `
                <div style="display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap;">
                    ${gradeBadge}
                    <span style="color: #065f46; font-weight: 600; background: #ecfdf5; padding: 0.15rem 0.5rem; border-radius: 4px; border: 1px solid #a7f3d0; font-size: 0.75rem;">
                        ⭐ FIFO Terverifikasi • Masuk: ${tglMasuk} • Sisa Bebas: ${sisaBebas.toLocaleString('id-ID')} unit (Fisik: ${sisa.toLocaleString('id-ID')})
                    </span>
                </div>
                ${draftWarningNotice}
            `;
        } else if (oldestOpt && oldestSisa > 0) {
            batchSelect.style.borderColor = '#f59e0b';
            batchSelect.style.backgroundColor = '#fffdf5';
            batchInfo.innerHTML = `
                <div style="background: #fffbeb; border: 1.5px solid #f59e0b; border-radius: 6px; padding: 0.45rem 0.65rem; margin-top: 0.25rem; color: #92400e;">
                    <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 0.5rem; flex-wrap: wrap;">
                        <div>
                            <div style="display: flex; align-items: center; gap: 0.35rem; margin-bottom: 0.2rem;">
                                ${gradeBadge}
                                <strong style="color: #b45309; font-size: 0.775rem;">⚠️ Melompati Batch Tertua!</strong>
                            </div>
                            <div style="font-size: 0.735rem; color: #78350f; line-height: 1.35;">
                                Batch <strong>${opt.value}</strong> lebih baru. Masih ada Batch Terlama <strong>[${oldestOpt.value}]</strong> (Sisa: <strong>${oldestSisa.toLocaleString('id-ID')} unit</strong>) yang harus dihabiskan dahulu!
                            </div>
                        </div>
                    </div>
                </div>
                ${draftWarningNotice}
            `;
        } else {
            batchSelect.style.borderColor = '#cbd5e1';
            batchSelect.style.backgroundColor = '#ffffff';
            batchInfo.innerHTML = `
                <div style="display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap;">
                    ${gradeBadge}
                    <span style="color: #0369a1; font-weight: 600; background: #f0f9ff; padding: 0.15rem 0.5rem; border-radius: 4px; border: 1px solid #bae6fd; font-size: 0.75rem;">
                        Pilihan Manual • Masuk ${tglMasuk} • Sisa Bebas: ${sisaBebas.toLocaleString('id-ID')} unit (Fisik: ${sisa.toLocaleString('id-ID')})
                    </span>
                </div>
                ${draftWarningNotice}
            `;
        }
    }

    function onBatchSelect(select) {
        const row = select.closest('tr');
        const opt = select.selectedOptions[0];

        if (!opt || !opt.value) {
            row.querySelector('.harga-input').value = 0;
            row.querySelector('.stok-id-input').value = '';
            renderNormalBatchInfo(row);
            calcRow(select);
            return;
        }

        const harga = parseFloat(opt.getAttribute('data-harga') || 0);
        const stokId = opt.getAttribute('data-stok-id') || '';
        const grade = (opt.getAttribute('data-grade') || 'A').toUpperCase();

        row.querySelector('.harga-input').value = harga;
        row.querySelector('.stok-id-input').value = stokId;
        row.querySelector('.grade-input').value = grade;

        renderNormalBatchInfo(row);
        calcRow(select);
    }

    function calcRow(el) {
        const row = el.closest('tr');
        const qty = parseFloat(row.querySelector('.qty-input')?.value || 0);
        const batchSelect = row.querySelector('.batch-select');
        const batchInfo = row.querySelector('.batch-info');
        const selectedBatchOpt = batchSelect?.selectedOptions[0];
        const sisaFisik = selectedBatchOpt ? parseFloat(selectedBatchOpt.getAttribute('data-sisa') || 0) : 0;
        const sisaBebas = selectedBatchOpt ? parseFloat(selectedBatchOpt.getAttribute('data-sisa-bebas') || sisaFisik) : 0;
        const draftQty = selectedBatchOpt ? parseFloat(selectedBatchOpt.getAttribute('data-draft-qty') || 0) : 0;
        const draftDocs = selectedBatchOpt ? selectedBatchOpt.getAttribute('data-draft-docs') || '' : '';
        const qtyInput = row.querySelector('.qty-input');
        const grade = (row.querySelector('.grade-input')?.value || selectedBatchOpt?.getAttribute('data-grade') || 'A').toUpperCase();

        const gradeBadge = (grade === 'A')
            ? `<span style="background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; font-weight: 700; padding: 1px 6px; border-radius: 4px; font-size: 0.725rem;">🟢 Grade A</span>`
            : ((grade === 'B')
                ? `<span style="background: #fffbeb; color: #b45309; border: 1px solid #fde68a; font-weight: 700; padding: 1px 6px; border-radius: 4px; font-size: 0.725rem;">🟡 Grade B</span>`
                : `<span style="background: #f1f5f9; color: #334155; font-weight: 600; padding: 1px 6px; border-radius: 4px; font-size: 0.725rem;">Grade ${grade}</span>`);

        if (selectedBatchOpt && selectedBatchOpt.value && qty > sisaBebas) {
            qtyInput.style.borderColor = '#dc2626';
            qtyInput.style.backgroundColor = '#fef2f2';

            const isDraftConflict = (qty <= sisaFisik && draftQty > 0);
            const titleMsg = isDraftConflict
                ? `⚠️ Melebihi Sisa Bebas! Terikat di DRAFT Lain`
                : (sisaBebas <= 0.0001 && draftQty > 0 ? `⛔ Batch Habis Ter-booking di DRAFT Lain!` : `⚠️ Kuantitas Melebihi Sisa Fisik Batch!`);

            const descMsg = isDraftConflict
                ? `Batch <strong>${selectedBatchOpt.value}</strong> memiliki fisik <strong>${sisaFisik.toLocaleString('id-ID')} unit</strong>, namun <strong>${draftQty.toLocaleString('id-ID')} unit</strong> telah dibooking di draft <strong>[${draftDocs}]</strong>. Sisa bebas hanya <strong>${sisaBebas.toLocaleString('id-ID')} unit</strong>. Sisa <strong>${(qty - sisaBebas).toLocaleString('id-ID')} unit</strong> harus diambil dari batch lain!`
                : (sisaBebas <= 0.0001 && draftQty > 0
                    ? `Batch <strong>${selectedBatchOpt.value}</strong> memiliki fisik <strong>${sisaFisik.toLocaleString('id-ID')} unit</strong>, tapi seluruhnya telah di-booking di dokumen DRAFT <strong>[${draftDocs}]</strong> yang belum di-acc/potong stok. Sisa bebas: <strong>0 unit</strong>.`
                    : `Batch <strong>${selectedBatchOpt.value}</strong> hanya bersisa <strong>${sisaFisik.toLocaleString('id-ID')} unit</strong>. Sisa <strong>${(qty - sisaFisik).toLocaleString('id-ID')} unit</strong> harus diambil dari batch lain!`);

            if (batchInfo) {
                batchInfo.innerHTML = `
                    <div style="background: #fef2f2; border: 1.5px solid #f87171; border-radius: 6px; padding: 0.5rem 0.75rem; margin-top: 0.35rem; color: #991b1b;">
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; flex-wrap: wrap;">
                            <div>
                                <div style="display: flex; align-items: center; gap: 0.35rem; margin-bottom: 0.2rem;">
                                    ${gradeBadge}
                                    <strong style="color: #dc2626; font-size: 0.775rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                                        ${titleMsg}
                                    </strong>
                                </div>
                                <div style="font-size: 0.735rem; color: #7f1d1d; line-height: 1.35;">
                                    ${descMsg}
                                </div>
                            </div>
                            <div style="display: flex; gap: 0.35rem; align-items: center; flex-wrap: wrap;">
                                <button type="button" onclick="window.pemakaianApp.autoSplitBatchForRow(this.closest('tr'), 'SAME_GRADE')" style="background: ${grade === 'A' ? '#059669' : '#d97706'}; color: #ffffff; font-weight: 700; font-size: 0.725rem; padding: 5px 10px; border: none; border-radius: 4px; cursor: pointer; white-space: nowrap; display: inline-flex; align-items: center; gap: 0.35rem; box-shadow: 0 1px 3px rgba(0,0,0,0.15);" title="Pecah dan ambil lanjutan HANYA dari sesama Grade ${grade}">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    <span>⚡ Pecah (Grade ${grade} Saja)</span>
                                </button>
                                <button type="button" onclick="window.pemakaianApp.autoSplitBatchForRow(this.closest('tr'), 'CAMPUR')" style="background: #dc2626; color: #ffffff; font-weight: 700; font-size: 0.725rem; padding: 5px 10px; border: none; border-radius: 4px; cursor: pointer; white-space: nowrap; display: inline-flex; align-items: center; gap: 0.35rem; box-shadow: 0 1px 3px rgba(220,38,38,0.25);" title="Pecah dan ambil lanjutan dari batch tertua apa saja (Campur / Bebas Grade)">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    <span>🔄 Pecah Campur (Semua Grade)</span>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            }
        } else if (selectedBatchOpt && selectedBatchOpt.value) {
            qtyInput.style.borderColor = '#cbd5e1';
            qtyInput.style.backgroundColor = '#ffffff';
            renderNormalBatchInfo(row);
        }

        calculateGrandTotal();
    }

    function calculateGrandTotal() {
        let totalQty = 0;
        let totalGradeA = 0;
        let totalGradeB = 0;
        let count = 0;

        document.querySelectorAll('#itemsBody tr').forEach(row => {
            const qty = parseFloat(row.querySelector('.qty-input')?.value || 0);
            const grade = (row.querySelector('.grade-input')?.value || 'A').toUpperCase();

            if (qty > 0) {
                totalQty += qty;
                count++;
                if (grade === 'A') totalGradeA += qty;
                else if (grade === 'B') totalGradeB += qty;
            }
        });

        const totalQtyEl = document.getElementById('sideTotalQty');
        const totalQtyLargeEl = document.getElementById('sideTotalQtyLarge');
        const botBarTotalQtyEl = document.getElementById('botBarTotalQty');
        const gradeAEl = document.getElementById('sideGradeAQty');
        const gradeBEl = document.getElementById('sideGradeBQty');
        const totalItemsEl = document.getElementById('sideTotalItems');

        if (totalQtyEl) totalQtyEl.innerText = totalQty.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        if (totalQtyLargeEl) totalQtyLargeEl.innerText = totalQty.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        if (botBarTotalQtyEl) botBarTotalQtyEl.innerText = totalQty.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' Kg';
        if (gradeAEl) gradeAEl.innerText = totalGradeA.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' Kg';
        if (gradeBEl) gradeBEl.innerText = totalGradeB.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' Kg';
        if (totalItemsEl) totalItemsEl.innerText = count;
    }

    function autoSplitBatchForRow(row, splitMode = 'SAME_GRADE') {
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

        const sisaCurrentFisik = parseFloat(selectedOpt.getAttribute('data-sisa') || 0);
        const sisaCurrentBebas = parseFloat(selectedOpt.getAttribute('data-sisa-bebas') || sisaCurrentFisik);
        if (totalRequested <= sisaCurrentBebas) return;

        let allBatches = row._allBatches || [];
        if (allBatches.length === 0 && batchSelect.options.length > 0) {
            allBatches = Array.from(batchSelect.options).filter(opt => opt.value).map(opt => ({
                batch_no: opt.value,
                stok_id: opt.getAttribute('data-stok-id') || '',
                grade_cd: opt.getAttribute('data-grade') || 'A',
                sisa_qty: parseFloat(opt.getAttribute('data-sisa') || 0),
                sisa_bebas: parseFloat(opt.getAttribute('data-sisa-bebas') || opt.getAttribute('data-sisa') || 0),
                draft_qty: parseFloat(opt.getAttribute('data-draft-qty') || 0),
                draft_doc_nos: (opt.getAttribute('data-draft-docs') || '').split(',').map(s => s.trim()).filter(Boolean),
                harga_satuan: parseFloat(opt.getAttribute('data-harga') || 0),
                tgl_terima: opt.getAttribute('data-masuk') || '',
                expired_tgl: opt.getAttribute('data-exp') || ''
            }));
            row._allBatches = allBatches;
        }

        const currentGrade = (gradeInput?.value || selectedOpt.getAttribute('data-grade') || 'A').toUpperCase();

        const takeCurrent = Math.min(totalRequested, sisaCurrentBebas);
        qtyInput.value = takeCurrent;
        calcRow(qtyInput);

        let remainingQty = totalRequested - takeCurrent;

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

        let candidateBatches = [];
        if (splitMode === 'SAME_GRADE') {
            candidateBatches = allBatches.filter(b => (b.grade_cd || 'A').toUpperCase() === currentGrade);
        } else {
            candidateBatches = allBatches;
        }

        let addedRows = 0;
        for (const b of candidateBatches) {
            if (remainingQty <= 0) break;
            const bGrade = (b.grade_cd || 'A').toUpperCase();
            const key = `${b.batch_no}_${bGrade}`;
            const alreadyUsed = usedBatchMap[key] || 0;
            const sisaBebasB = (b.sisa_bebas !== undefined) ? parseFloat(b.sisa_bebas) : parseFloat(b.sisa_qty);
            const availableInBatch = Math.max(0, sisaBebasB - alreadyUsed);

            if (availableInBatch > 0) {
                const takeFromThis = Math.min(remainingQty, availableInBatch);
                remainingQty -= takeFromThis;
                usedBatchMap[key] = alreadyUsed + takeFromThis;

                const itemObj = BARANG_LIST.find(bl => parseInt(bl.barang_id) === parseInt(barangId));
                addRowFromAllocation({
                    barang_id: barangId,
                    batch_no: b.batch_no,
                    grade_cd: bGrade,
                    stok_id: b.stok_id || '',
                    qty_keluar: takeFromThis,
                    harga_satuan: b.harga_satuan || 0,
                    satuan_nm: row.querySelector('.row-satuan')?.textContent || itemObj?.satuan_dasar?.satuan_nm || 'Kg',
                    is_allocated: true,
                    is_split: true,
                    catatan_fifo: splitMode === 'SAME_GRADE' ? `⚡ Pecahan Khusus Grade ${bGrade}` : `🔄 Pecahan Campur (FIFO)`,
                    sisa_batch: sisaBebasB,
                    all_batches: allBatches
                });
                addedRows++;
            }
        }

        if (splitMode === 'SAME_GRADE' && remainingQty > 0) {
            const otherBatches = allBatches.filter(b => (b.grade_cd || 'A').toUpperCase() !== currentGrade);
            let otherAvailable = 0;
            otherBatches.forEach(b => {
                const bGrade = (b.grade_cd || 'A').toUpperCase();
                const key = `${b.batch_no}_${bGrade}`;
                const alreadyUsed = usedBatchMap[key] || 0;
                const sisaBebasOther = (b.sisa_bebas !== undefined) ? parseFloat(b.sisa_bebas) : parseFloat(b.sisa_qty);
                otherAvailable += Math.max(0, sisaBebasOther - alreadyUsed);
            });

            if (otherAvailable > 0) {
                const agree = confirm(`Perhatian: Sisa bebas Grade ${currentGrade} telah habis terpakai!\nMasih kurang ${remainingQty.toLocaleString('id-ID')} unit yang belum terpenuhi.\n\nDi gudang ini masih tersedia saldo bebas Grade lain sebanyak ${otherAvailable.toLocaleString('id-ID')} unit.\n\nApakah Anda ingin melanjutkan alokasi sisa kekurangan tersebut dari Grade lain (Campur)?`);
                if (agree) {
                    for (const b of otherBatches) {
                        if (remainingQty <= 0) break;
                        const bGrade = (b.grade_cd || 'A').toUpperCase();
                        const key = `${b.batch_no}_${bGrade}`;
                        const alreadyUsed = usedBatchMap[key] || 0;
                        const sisaBebasOther = (b.sisa_bebas !== undefined) ? parseFloat(b.sisa_bebas) : parseFloat(b.sisa_qty);
                        const availableInBatch = Math.max(0, sisaBebasOther - alreadyUsed);

                        if (availableInBatch > 0) {
                            const takeFromThis = Math.min(remainingQty, availableInBatch);
                            remainingQty -= takeFromThis;
                            usedBatchMap[key] = alreadyUsed + takeFromThis;

                            const itemObj = BARANG_LIST.find(bl => parseInt(bl.barang_id) === parseInt(barangId));
                            addRowFromAllocation({
                                barang_id: barangId,
                                batch_no: b.batch_no,
                                grade_cd: bGrade,
                                stok_id: b.stok_id || '',
                                qty_keluar: takeFromThis,
                                harga_satuan: b.harga_satuan || 0,
                                satuan_nm: row.querySelector('.row-satuan')?.textContent || itemObj?.satuan_dasar?.satuan_nm || 'Kg',
                                is_allocated: true,
                                is_split: true,
                                catatan_fifo: `🔄 Pecahan Campur (Grade ${bGrade})`,
                                sisa_batch: sisaBebasOther,
                                all_batches: allBatches
                            });
                            addedRows++;
                        }
                    }
                }
            }
        }

        calculateGrandTotal();

        if (remainingQty > 0) {
            alert(`Perhatian: Berhasil memecah ke ${addedRows} baris batch lanjutan.\nNamun sisa bebas barang ini di seluruh batch gudang sudah habis!\nMasih kurang ${remainingQty.toLocaleString('id-ID')} unit yang tidak dapat dialokasikan.`);
        }
    }

    // Pengecekan Stock Limit & Komitmen Draft (Mencegah submit jika kuantitas melebihi sisa bebas / bentrok draft)
    function checkStockLimitViolations() {
        const violations = [];
        const batchUsage = {};

        // Pass 1: Kumpulkan akumulasi kuantitas per batch
        document.querySelectorAll('#itemsBody tr').forEach(row => {
            const barangSelect = row.querySelector('.barang-select');
            const batchSelect = row.querySelector('.batch-select');
            const qtyInput = row.querySelector('.qty-input');

            if (!barangSelect || !barangSelect.value || !batchSelect || !batchSelect.value) return;

            const qty = parseFloat(qtyInput?.value || 0);
            if (qty <= 0) return;

            const selectedOpt = batchSelect.selectedOptions[0];
            if (!selectedOpt) return;

            const sisaFisik = parseFloat(selectedOpt.getAttribute('data-sisa') || 0);
            const sisaBebasAttr = selectedOpt.getAttribute('data-sisa-bebas');
            const sisaBebas = (sisaBebasAttr !== null && sisaBebasAttr !== '') ? parseFloat(sisaBebasAttr) : sisaFisik;
            const draftQty = parseFloat(selectedOpt.getAttribute('data-draft-qty') || 0);
            const draftDocs = selectedOpt.getAttribute('data-draft-docs') || '';
            const grade = (row.querySelector('.grade-input')?.value || selectedOpt.getAttribute('data-grade') || 'A').toUpperCase();
            const isFullyBooked = selectedOpt.getAttribute('data-is-fully-booked') === '1' || (sisaBebas <= 0.0001 && draftQty > 0);

            const key = `${barangSelect.value}_${selectedOpt.value}_${grade}`;
            if (!batchUsage[key]) {
                batchUsage[key] = {
                    totalQty: 0,
                    sisaBebas: sisaBebas,
                    sisaFisik: sisaFisik,
                    draftQty: draftQty,
                    draftDocs: draftDocs,
                    isFullyBooked: isFullyBooked,
                    batchNo: selectedOpt.value,
                    grade: grade,
                    rows: []
                };
            }
            batchUsage[key].totalQty += qty;
            batchUsage[key].rows.push({ row, qty });
        });

        // Pass 2: Deteksi pelanggaran batas stok fisik maupun alokasi draft lain
        Object.keys(batchUsage).forEach(key => {
            const b = batchUsage[key];
            if (b.totalQty > b.sisaBebas || b.isFullyBooked || (b.sisaBebas <= 0.0001 && b.draftQty > 0)) {
                b.rows.forEach(item => {
                    const row = item.row;
                    const barangSelect = row.querySelector('.barang-select');
                    const barangOpt = barangSelect ? barangSelect.selectedOptions[0] : null;
                    const barangNm = barangOpt ? barangOpt.textContent.trim() : 'Bahan Produksi';
                    const rowNum = row.querySelector('.row-num')?.textContent.trim() || '-';

                    violations.push({
                        row: row,
                        rowNum: rowNum,
                        barangNm: barangNm,
                        batchNo: b.batchNo,
                        grade: b.grade,
                        qtyRequested: item.qty,
                        totalRequested: b.totalQty,
                        sisaBatch: b.sisaBebas,
                        sisaFisik: b.sisaFisik,
                        draftQty: b.draftQty,
                        draftDocs: b.draftDocs,
                        isFullyBooked: b.isFullyBooked,
                        selisih: Math.max(0, b.totalQty - b.sisaBebas)
                    });
                });
            }
        });

        return violations;
    }

    function checkFifoViolations() {
        const violations = [];
        
        const totalUsedPerStokId = {};
        const totalUsedPerKeyGrade = {};
        const totalUsedPerBatchNo = {};
        const rowsData = [];

        document.querySelectorAll('#itemsBody tr').forEach(row => {
            const barangSelect = row.querySelector('.barang-select');
            const batchSelect = row.querySelector('.batch-select');
            const qtyInput = row.querySelector('.qty-input');
            const gradeInput = row.querySelector('.grade-input');
            const stokIdInput = row.querySelector('.stok-id-input');

            if (!barangSelect || !barangSelect.value || !batchSelect || !batchSelect.value) return;

            const qty = parseFloat(qtyInput?.value || 0);
            if (qty <= 0) return;

            const barangId = barangSelect.value;
            const chosenOpt = batchSelect.selectedOptions[0];
            if (!chosenOpt || !chosenOpt.value) return;

            const batchNo = chosenOpt.value;
            const grade = (gradeInput?.value || chosenOpt.getAttribute('data-grade') || 'A').toUpperCase();
            const stokId = stokIdInput?.value || chosenOpt.getAttribute('data-stok-id') || '';

            if (stokId) {
                totalUsedPerStokId[stokId] = (totalUsedPerStokId[stokId] || 0) + qty;
            }

            const keyGrade = `${barangId}_${batchNo}_${grade}`;
            totalUsedPerKeyGrade[keyGrade] = (totalUsedPerKeyGrade[keyGrade] || 0) + qty;

            const keyBatch = `${barangId}_${batchNo}`;
            totalUsedPerBatchNo[keyBatch] = (totalUsedPerBatchNo[keyBatch] || 0) + qty;

            rowsData.push({
                row: row,
                barangId: barangId,
                barangNm: barangSelect.selectedOptions[0]?.textContent.trim() || 'Bahan Produksi',
                batchNo: batchNo,
                stokId: stokId,
                grade: grade,
                qty: qty,
                chosenOpt: chosenOpt,
                batchSelect: batchSelect,
                allBatches: row._allBatches || []
            });
        });

        const flaggedKeys = new Set();

        rowsData.forEach(item => {
            let candidateBatches = [];

            if (item.allBatches && item.allBatches.length > 0) {
                candidateBatches = item.allBatches;
            } else {
                const opts = Array.from(item.batchSelect.options);
                candidateBatches = opts.map(opt => ({
                    stok_id: opt.getAttribute('data-stok-id') || '',
                    batch_no: opt.value,
                    grade_cd: opt.getAttribute('data-grade') || 'A',
                    sisa_qty: parseFloat(opt.getAttribute('data-sisa') || 0),
                    tgl_terima: opt.getAttribute('data-masuk') || '-'
                })).filter(b => b.batch_no);
            }

            if (candidateBatches.length <= 1) return;

            const sameGradeBatches = candidateBatches.filter(b => {
                const bGrade = (b.grade_cd || 'A').toUpperCase();
                return bGrade === item.grade;
            });

            if (sameGradeBatches.length <= 1) return;

            const chosenIdx = sameGradeBatches.findIndex(b => 
                (item.stokId && b.stok_id && String(b.stok_id) === String(item.stokId)) ||
                (b.batch_no === item.batchNo)
            );

            if (chosenIdx > 0) {
                for (let i = 0; i < chosenIdx; i++) {
                    const older = sameGradeBatches[i];
                    if (!older || !older.batch_no) continue;

                    if (older.batch_no === item.batchNo) continue;
                    if (item.stokId && older.stok_id && String(older.stok_id) === String(item.stokId)) continue;

                    const olderGrade = (older.grade_cd || 'A').toUpperCase();
                    const olderKeyGrade = `${item.barangId}_${older.batch_no}_${olderGrade}`;
                    const olderKeyBatch = `${item.barangId}_${older.batch_no}`;

                    let usedInForm = 0;
                    if (older.stok_id && totalUsedPerStokId[older.stok_id] !== undefined) {
                        usedInForm = totalUsedPerStokId[older.stok_id];
                    } else if (totalUsedPerKeyGrade[olderKeyGrade] !== undefined) {
                        usedInForm = totalUsedPerKeyGrade[olderKeyGrade];
                    } else {
                        usedInForm = totalUsedPerBatchNo[olderKeyBatch] || 0;
                    }

                    const stockInWarehouse = parseFloat(older.sisa_qty || 0);
                    const realAvailableOlder = stockInWarehouse - usedInForm;

                    if (realAvailableOlder > 0.01) {
                        const vKey = `${item.barangId}_${item.grade}_${item.batchNo}_vs_${older.batch_no}`;
                        if (!flaggedKeys.has(vKey)) {
                            flaggedKeys.add(vKey);
                            violations.push({
                                barang_nm: item.barangNm,
                                selected_batch: item.batchNo,
                                fifo_batch: older.batch_no,
                                fifo_sisa: realAvailableOlder,
                                fifo_masuk: older.tgl_terima || older.created_at || '-'
                            });
                        }
                        break;
                    }
                }
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
                        ${v.draftQty > 0 ? `<div style="font-size:0.68rem; color:#b45309; font-weight:600; font-family:sans-serif;">⚠️ ${v.draftQty.toLocaleString('id-ID')} di Draft [${v.draftDocs}]</div>` : ''}
                    </td>
                    <td style="padding: 0.55rem 0.65rem; text-align: center;">
                        <div style="display:inline-flex; gap:0.3rem;">
                            <button type="button" onclick="window.pemakaianApp.splitSpecificRowFromModal(${idx}, 'SAME_GRADE')" style="background:${v.grade === 'A' ? '#059669' : '#d97706'}; color:#ffffff; font-weight:700; font-size:0.72rem; padding:4px 8px; border:none; border-radius:4px; cursor:pointer;" title="Pecah khusus Grade ${v.grade}">
                                ⚡ Grade ${v.grade}
                            </button>
                            <button type="button" onclick="window.pemakaianApp.splitSpecificRowFromModal(${idx}, 'CAMPUR')" style="background:#dc2626; color:#ffffff; font-weight:700; font-size:0.72rem; padding:4px 8px; border:none; border-radius:4px; cursor:pointer;" title="Pecah Campur (Semua Grade / FIFO)">
                                🔄 Campur
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });
        tbody.innerHTML = html;

        window._currentStockViolations = violations;

        const modal = document.getElementById('modalWarningStockLimit');
        if (modal) modal.style.display = 'flex';
    }

    function closeWarningStockLimitModal() {
        const modal = document.getElementById('modalWarningStockLimit');
        if (modal) modal.style.display = 'none';
    }

    function splitSpecificRowFromModal(idx, splitMode = 'SAME_GRADE') {
        if (window._currentStockViolations && window._currentStockViolations[idx]) {
            const v = window._currentStockViolations[idx];
            autoSplitBatchForRow(v.row, splitMode);
            const remaining = checkStockLimitViolations();
            if (remaining.length > 0) {
                openWarningStockLimitModal(remaining);
            } else {
                closeWarningStockLimitModal();
            }
        }
    }

    function applyAutoSplitAllViolations(splitMode = 'SAME_GRADE') {
        const violations = checkStockLimitViolations();
        violations.forEach(v => {
            autoSplitBatchForRow(v.row, splitMode);
        });
        closeWarningStockLimitModal();
        calculateGrandTotal();
    }

    function openWarningFifoModal(violations) {
        const listContainer = document.getElementById('fifoViolationList');
        if (!listContainer) return;

        let html = '';
        violations.forEach(v => {
            html += `
                <div style="background: #ffffff; border: 1.5px solid #fde68a; border-radius: 8px; padding: 0.85rem; margin-bottom: 0.65rem;">
                    <div style="font-weight: 700; color: #92400e; font-size: 0.875rem;">${v.barang_nm}</div>
                    <div style="font-size: 0.8rem; color: #475569; margin-top: 0.25rem;">
                        Batch Dipilih: <code style="font-weight: 700; color: #dc2626;">${v.selected_batch}</code> (Bukan batch tertua)
                    </div>
                    <div style="font-size: 0.8rem; color: #059669; font-weight: 600; margin-top: 0.2rem;">
                        Batch FIFO Tertua: <code style="font-weight: 700; color: #047857;">${v.fifo_batch}</code> (Sisa: ${parseFloat(v.fifo_sisa).toLocaleString('id-ID')} unit, Masuk: ${v.fifo_masuk})
                    </div>
                </div>
            `;
        });
        listContainer.innerHTML = html;
        document.getElementById('modalWarningFifo').style.display = 'flex';
    }

    function closeWarningFifoModal() {
        const modal = document.getElementById('modalWarningFifo');
        if (modal) modal.style.display = 'none';
    }

    function proceedOverrideFifo() {
        allowFifoOverride = true;
        closeWarningFifoModal();
        document.getElementById('pemakaianForm').submit();
    }

    function initFloatingWidget() {
        const widget = document.getElementById('liveTotalWidget');
        const wrapper = document.getElementById('liveTotalWidgetWrapper');
        if (!widget || !wrapper) return;

        function checkScroll() {
            const rect = wrapper.getBoundingClientRect();
            if (rect.top < 56) {
                widget.classList.add('is-floating');
            } else {
                widget.classList.remove('is-floating');
            }
        }

        window.addEventListener('scroll', checkScroll, { passive: true });
        checkScroll();
    }

    // Validasi Terpadu Sebelum Submit Form (Mencegah submit jika ada pelanggaran stok, draft collision, atau batch kosong)
    function validateFormBeforeSubmit() {
        const rows = document.querySelectorAll('#itemsBody tr');
        if (rows.length === 0) {
            alert('Minimal harus ada 1 baris bahan yang dikeluarkan.');
            return false;
        }

        let hasEmptyBatch = false;
        let emptyBatchItemName = '';
        let hasZeroQty = false;

        rows.forEach(r => {
            const barangSelect = r.querySelector('.barang-select');
            const batchSelect = r.querySelector('.batch-select');
            const qtyInput = r.querySelector('.qty-input');
            const qty = parseFloat(qtyInput?.value || 0);

            if (barangSelect && barangSelect.value) {
                const barangNm = barangSelect.selectedOptions[0]?.textContent?.trim() || 'Barang';
                if (!batchSelect || !batchSelect.value) {
                    hasEmptyBatch = true;
                    emptyBatchItemName = barangNm;
                }
                if (qty <= 0) {
                    hasZeroQty = true;
                }
            }
        });

        if (hasEmptyBatch) {
            alert(`Perhatian: Bahan "${emptyBatchItemName}" belum memiliki batch yang valid (stok fisik di gudang kosong atau habis terikat draft lain)!\nHarap pilih batch lain yang tersedia atau hapus baris ini terlebih dahulu.`);
            return false;
        }

        if (hasZeroQty) {
            alert('Harap pastikan semua kuantitas pengeluaran bahan yang diisi lebih besar dari 0!');
            return false;
        }

        // 1. Cek Pelanggaran Saldo Stok & Komitmen Draft (Hard Stop!)
        const stockViolations = checkStockLimitViolations();
        if (stockViolations.length > 0) {
            openWarningStockLimitModal(stockViolations);
            return false;
        }

        // 2. Cek Pelanggaran Urutan FIFO
        if (!allowFifoOverride) {
            const violations = checkFifoViolations();
            if (violations.length > 0) {
                openWarningFifoModal(violations);
                return false;
            }
        }

        return true;
    }

    function init() {
        if (EXISTING_DETAILS && EXISTING_DETAILS.length > 0) {
            EXISTING_DETAILS.forEach(it => {
                addExistingRow(it);
            });
        } else {
            addRow();
        }

        updateSidebarInfo();
        initFloatingWidget();

        document.getElementById('tujuan_pemakaian')?.addEventListener('change', updateSidebarInfo);
        document.getElementById('tujuan_pemakaian')?.addEventListener('input', updateSidebarInfo);
        document.getElementById('gudang_id')?.addEventListener('change', onGudangChanged);

        const form = document.getElementById('pemakaianForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                if (!validateFormBeforeSubmit()) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
            });
        }

        // Intersepsi langsung pada tombol Simpan (Save Button Integration)
        const btnSubmit = document.getElementById('btnSubmitPemakaian');
        if (btnSubmit) {
            btnSubmit.addEventListener('click', function(e) {
                if (!form) return;
                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }
                if (!validateFormBeforeSubmit()) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    window.pemakaianApp = {
        setRowCategory,
        setRowGradeFilter,
        addRow,
        removeRow,
        resetItemsTable,
        setBomGradeFilter,
        tarikBahanResepFifo,
        onBarangSelect,
        onBatchSelect,
        calcRow,
        calculateGrandTotal,
        autoSplitBatchForRow,
        splitSpecificRowFromModal,
        applyAutoSplitAllViolations,
        closeWarningStockLimitModal,
        closeWarningFifoModal,
        proceedOverrideFifo,
    };

    window.setRowCategory = setRowCategory;
    window.setRowGradeFilter = setRowGradeFilter;
    window.addRow = addRow;
    window.removeRow = removeRow;
    window.resetItemsTable = resetItemsTable;
    window.setBomGradeFilter = setBomGradeFilter;
    window.tarikBahanResepFifo = tarikBahanResepFifo;
    window.onBarangSelect = onBarangSelect;
    window.onBatchSelect = onBatchSelect;
    window.calcRow = calcRow;
    window.autoSplitBatchForRow = autoSplitBatchForRow;
    window.splitSpecificRowFromModal = splitSpecificRowFromModal;
    window.applyAutoSplitAllViolations = applyAutoSplitAllViolations;
    window.closeWarningStockLimitModal = closeWarningStockLimitModal;
    window.closeWarningFifoModal = closeWarningFifoModal;
    window.proceedOverrideFifo = proceedOverrideFifo;

})();
