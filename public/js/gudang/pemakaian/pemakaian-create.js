/**
 * public/js/gudang/pemakaian/pemakaian-create.js
 * Logika Frontend Pemakaian Bahan Baku & Penolong (BPPB) - ERP PT Mirasa
 */

(function () {
    'use strict';

    const BARANG_LIST = window.appConfig?.barangList || [];
    let rowIndex = 0;
    let allowFifoOverride = false;

    // Helper: Mendapatkan gudang yang dipilih di toolbar atas
    function getSelectedGudangId() {
        const el = document.getElementById('gudang_id');
        return el ? el.value : '';
    }

    // Helper: Update badge sidebar / ringkasan dokumen
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

    // Event Handler saat gudang asal diubah
    function onGudangChanged() {
        updateSidebarInfo();
        document.querySelectorAll('#itemsBody tr').forEach(row => {
            const barangSelect = row.querySelector('.barang-select');
            if (barangSelect && barangSelect.value) {
                // Reset tombol filter grade ke "Semua"
                const group = row.querySelector('.grade-pill-group');
                if (group) {
                    group.querySelectorAll('.btn-chip').forEach(b => b.classList.remove('active'));
                    group.children[0]?.classList.add('active');
                }
                fetchBatchesForRow(row, barangSelect.value, '');
            }
        });
    }

    // Render options dropdown barang
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

    // Filter pill kategori bahan (Bahan Baku / Penolong / Kemasan)
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

    // Nomor urut tabel
    function updateRowNumbers() {
        document.querySelectorAll('#itemsBody tr').forEach((row, idx) => {
            const numEl = row.querySelector('.row-num');
            if (numEl) numEl.innerText = idx + 1;
        });
    }

    // Navigasi keyboard ala Excel
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

    // Filter pill grade per baris (Semua / Grade A / Grade B)
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

    // Tambah baris baru
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
                <div class="cat-pill-group" style="display: flex; gap: 0.35rem; margin-bottom: 0.45rem; align-items: center; flex-wrap: nowrap;">
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
        triggerAutoSave();
    }

    // Hapus baris
    function removeRow(btn) {
        const tbody = document.getElementById('itemsBody');
        if (tbody.children.length > 1) {
            btn.closest('tr').remove();
            updateRowNumbers();
            calculateGrandTotal();
            triggerAutoSave();
        } else {
            alert('Minimal harus ada 1 item barang yang dikeluarkan.');
        }
    }

    // Reset tabel & bersihkan draft tersimpan
    function resetItemsTable() {
        if (!confirm('Apakah Anda yakin ingin mengosongkan semua baris dan mereset draft input ini?')) {
            return;
        }
        clearFormDraft();
        const tbody = document.getElementById('itemsBody');
        tbody.innerHTML = '';
        rowIndex = 0;
        addRow();
        calculateGrandTotal();
        const feedback = document.getElementById('resepFeedback');
        if (feedback) feedback.style.display = 'none';
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // FITUR AUTO-SAVE & PULIHKAN DRAFT BROWSER (ANTI-HILANG SAAT REFRESH / F5)
    // ═══════════════════════════════════════════════════════════════════════════
    const DRAFT_STORAGE_KEY = 'erp_mirasa_pemakaian_create_draft';
    let autoSaveTimer = null;

    function triggerAutoSave() {
        if (autoSaveTimer) clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(() => {
            saveFormDraft();
        }, 350);
    }

    function saveFormDraft() {
        if (isSubmitting) return; // Jangan simpan draft jika form sedang atau telah disubmit
        try {
            const rows = [];
            document.querySelectorAll('#itemsBody tr').forEach(tr => {
                const barangSelect = tr.querySelector('.barang-select');
                const barangId = barangSelect?.value || '';
                const activeCatBtn = tr.querySelector('.cat-pill-group .btn-chip.active');
                let cat = '';
                if (activeCatBtn) {
                    if (activeCatBtn.textContent.includes('Bahan Baku')) cat = 'BAHAN_BAKU';
                    else if (activeCatBtn.textContent.includes('Bumbu')) cat = 'BAHAN_PENOLONG';
                    else if (activeCatBtn.textContent.includes('Kemasan')) cat = 'KEMASAN';
                }
                const gradeCd = tr.querySelector('.grade-input')?.value || 'A';
                const batchSelect = tr.querySelector('.batch-select');
                const batchNo = batchSelect?.value || '';
                const stokId = tr.querySelector('.stok-id-input')?.value || '';
                const qty = tr.querySelector('.qty-input')?.value || '';
                const harga = tr.querySelector('.harga-input')?.value || 0;
                const satuan = tr.querySelector('.row-satuan')?.textContent.trim() || '-';
                const allBatches = tr._allBatches || [];

                if (barangId || qty || batchNo) {
                    rows.push({
                        barang_id: barangId,
                        kategori: cat,
                        grade_cd: gradeCd,
                        batch_no: batchNo,
                        stok_id: stokId,
                        qty_keluar: qty,
                        harga_satuan: harga,
                        satuan_nm: satuan,
                        all_batches: allBatches
                    });
                }
            });

            const gdgVal = document.getElementById('gudang_id')?.value || '';
            const tglVal = document.getElementById('pakai_tgl')?.value || '';
            const tujuanVal = document.getElementById('tujuan_pemakaian')?.value || '';
            const catVal = document.getElementById('catatan_txt')?.value || '';
            const bomVal = (document.getElementById('bom_select') || document.getElementById('bom_id'))?.value || '';
            const targetVal = document.getElementById('target_produksi_qty')?.value || '100';
            const filterVal = document.getElementById('bom_grade_filter')?.value || '';

            const data = {
                gudang_id: gdgVal,
                pakai_tgl: tglVal,
                tujuan_pemakaian: tujuanVal,
                catatan_txt: catVal,
                bom_id: bomVal,
                target_produksi_qty: targetVal,
                bom_grade_filter: filterVal,
                items: rows,
                saved_at: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' })
            };

            // Simpan hanya jika pengguna sudah mengisi data
            if (rows.length > 0 || catVal || (tujuanVal && tujuanVal !== 'PRODUKSI BERKO')) {
                localStorage.setItem(DRAFT_STORAGE_KEY, JSON.stringify(data));
                updateDraftNoticeIndicator(true, false, data.saved_at);
            }
        } catch (e) {
            console.warn('Gagal menyimpan draft otomatis ke localStorage:', e);
        }
    }

    function clearFormDraft() {
        try {
            localStorage.removeItem(DRAFT_STORAGE_KEY);
            updateDraftNoticeIndicator(false);
        } catch (e) {
            console.warn('Gagal membersihkan draft localStorage:', e);
        }
    }

    function clearAndResetDraft() {
        if (confirm('Apakah Anda ingin membersihkan draft tersimpan dan memulai form kosong baru?')) {
            clearFormDraft();
            location.reload();
        }
    }

    function updateDraftNoticeIndicator(visible, isRestored = false, timeStr = '') {
        const badge = document.getElementById('draftAutoSaveBadge');
        const text = document.getElementById('draftAutoSaveText');
        if (!badge || !text) return;

        if (visible) {
            badge.style.display = 'flex';
            if (isRestored) {
                text.innerHTML = `<strong>✓ Data input sebelumnya berhasil dipulihkan otomatis dari browser.</strong> Layar aman di-refresh kembali kapan saja.`;
                badge.style.background = '#ecfdf5';
                badge.style.borderColor = '#a7f3d0';
                badge.style.color = '#065f46';
            } else {
                const atText = timeStr ? ` (pukul ${timeStr})` : '';
                text.innerHTML = `<strong>💾 Draft tersimpan otomatis di browser${atText}.</strong> Jika layar ter-refresh / tidak sengaja tertutup, data tidak akan hilang.`;
                badge.style.background = '#f0f9ff';
                badge.style.borderColor = '#bae6fd';
                badge.style.color = '#0369a1';
            }
        } else {
            badge.style.display = 'none';
        }
    }

    function restoreFormDraft() {
        try {
            const raw = localStorage.getItem(DRAFT_STORAGE_KEY);
            if (!raw) return false;
            const data = JSON.parse(raw);
            if (!data) return false;

            const items = data.items || [];
            if (items.length === 0 && !data.catatan_txt && !data.tujuan_pemakaian) {
                return false;
            }

            // Restore field header
            if (data.gudang_id) {
                const gdg = document.getElementById('gudang_id');
                if (gdg) gdg.value = data.gudang_id;
            }
            if (data.pakai_tgl) {
                const tgl = document.getElementById('pakai_tgl');
                if (tgl) tgl.value = data.pakai_tgl;
            }
            if (data.tujuan_pemakaian) {
                const tujuan = document.getElementById('tujuan_pemakaian');
                if (tujuan) tujuan.value = data.tujuan_pemakaian;
            }
            if (data.catatan_txt) {
                const cat = document.getElementById('catatan_txt');
                if (cat) cat.value = data.catatan_txt;
            }
            if (data.bom_id) {
                const bom = document.getElementById('bom_select') || document.getElementById('bom_id');
                if (bom) bom.value = data.bom_id;
            }
            if (data.target_produksi_qty) {
                const tgt = document.getElementById('target_produksi_qty');
                if (tgt) tgt.value = data.target_produksi_qty;
            }
            if (data.bom_grade_filter !== undefined) {
                const filterInput = document.getElementById('bom_grade_filter');
                if (filterInput) filterInput.value = data.bom_grade_filter;
                const btnAll = document.getElementById('btnBomGradeAll');
                const btnA = document.getElementById('btnBomGradeA');
                const btnB = document.getElementById('btnBomGradeB');
                [btnAll, btnA, btnB].forEach(b => b?.classList.remove('active'));
                if (data.bom_grade_filter === 'A' && btnA) btnA.classList.add('active');
                else if (data.bom_grade_filter === 'B' && btnB) btnB.classList.add('active');
                else if (btnAll) btnAll.classList.add('active');
            }

            // Restore rincian baris barang
            const tbody = document.getElementById('itemsBody');
            tbody.innerHTML = '';
            rowIndex = 0;

            if (items.length > 0) {
                items.forEach(it => {
                    addRowWithRestoredData(it);
                });
            } else {
                addRow();
            }

            calculateGrandTotal();
            updateSidebarInfo();
            updateDraftNoticeIndicator(true, true);
            return true;
        } catch (e) {
            console.error('Error saat memulihkan draft form pemakaian:', e);
            return false;
        }
    }

    // Tambah baris dari data yang dipulihkan dari localStorage
    function addRowWithRestoredData(item) {
        const tbody = document.getElementById('itemsBody');
        const tr = document.createElement('tr');
        tr.id = `row-${rowIndex}`;

        const itemObj = BARANG_LIST.find(b => parseInt(b.barang_id) === parseInt(item.barang_id));
        const itemCat = item.kategori || itemObj?.kategori_kelompok || '';
        const barangOptions = renderBarangOptions(item.barang_id, itemCat);

        tr._allBatches = item.all_batches || [];
        const curGrade = (item.grade_cd || 'A').toUpperCase();

        let batchesToShow = tr._allBatches;
        if (curGrade === 'A' || curGrade === 'B') {
            const gradeFiltered = batchesToShow.filter(b => (b.grade_cd || 'A').toUpperCase() === curGrade);
            if (gradeFiltered.length > 0) batchesToShow = gradeFiltered;
        }

        let batchOptions = '';
        if (batchesToShow.length === 0) {
            if (item.batch_no) {
                batchOptions = `<option value="${item.batch_no}" selected>${item.batch_no} • [Grade ${curGrade}]</option>`;
            } else {
                batchOptions = '<option value="">-- Pilih Barang Dulu --</option>';
            }
        } else {
            let firstAvailInBomRow = false;
            batchesToShow.forEach(b => {
                const sisaFisik = parseFloat(b.sisa_qty || 0);
                const sisaBebas = (b.sisa_bebas !== undefined) ? parseFloat(b.sisa_bebas) : sisaFisik;
                const draftQty = parseFloat(b.draft_qty || 0);
                const draftDocs = Array.isArray(b.draft_doc_nos) ? b.draft_doc_nos.join(', ') : (b.draft_doc_nos || '');
                const isFullyBooked = b.is_fully_booked || (sisaBebas <= 0.0001 && draftQty > 0);

                let isFifoTop = false;
                if (!isFullyBooked && sisaBebas > 0.0001 && !firstAvailInBomRow) {
                    isFifoTop = true;
                    firstAvailInBomRow = true;
                }

                const isSelected = (b.batch_no === item.batch_no && (b.grade_cd || 'A') === curGrade) ? 'selected' : '';
                const prefix = isFifoTop ? '⭐ [FIFO Tertua] ' : '';
                const gCd = (b.grade_cd || 'A').toUpperCase();
                const gradeBadge = (gCd === 'A') ? '🟢 Grade A' : ((gCd === 'B') ? '🟡 Grade B' : `Grade ${gCd}`);
                const expInfo = b.expired_tgl ? ` | Exp: ${b.expired_tgl}` : '';
                const tglTerima = b.tgl_terima ? ` | Masuk: ${b.tgl_terima}` : '';

                let labelText = '';
                if (draftQty > 0) {
                    if (isFullyBooked) {
                        labelText = `⛔ [HABIS DIBOOKING DRAFT] ${b.batch_no} • [${gradeBadge}] (Sisa Bebas: 0 | Fisik: ${sisaFisik.toLocaleString('id-ID')} | ⚠️ ${draftQty.toLocaleString('id-ID')} di Draft [${draftDocs}])`;
                    } else {
                        labelText = `⚠️ [DRAFT ALLOCATED] ${prefix}${b.batch_no} • [${gradeBadge}] Sisa Bebas: ${sisaBebas.toLocaleString('id-ID')} (Fisik: ${sisaFisik.toLocaleString('id-ID')} | ⚠️ ${draftQty.toLocaleString('id-ID')} di Draft [${draftDocs}])`;
                    }
                } else {
                    labelText = `${prefix}${b.batch_no} • [${gradeBadge}] (Sisa: ${sisaFisik.toLocaleString('id-ID')})${tglTerima}${expInfo}`;
                }

                const disabledAttr = (isFullyBooked && sisaBebas <= 0.0001) ? 'disabled style="color: #94a3b8; background: #f8fafc;"' : '';

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
                    ${disabledAttr}
                    ${isSelected}>
                    ${labelText}
                </option>`;
            });
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
                <input type="hidden" name="items[${rowIndex}][grade_cd]" class="grade-input" value="${curGrade}">
                <input type="hidden" name="items[${rowIndex}][stok_id]" class="stok-id-input" value="${item.stok_id || ''}">
                <select name="items[${rowIndex}][batch_no]" class="form-control batch-select" required onchange="window.pemakaianApp.onBatchSelect(this)">
                    ${batchOptions}
                </select>
                <div class="batch-info" style="font-size: 0.775rem; color: #059669; font-weight: 600; margin-top: 0.35rem;"></div>
            </td>
            <td style="padding: 0.65rem 0.65rem;">
                <input type="number" step="0.0001" min="0.0001" name="items[${rowIndex}][qty_keluar]" class="form-control qty-input" value="${item.qty_keluar || ''}" placeholder="0" required oninput="window.pemakaianApp.calcRow(this)">
            </td>
            <td style="text-align: center; padding: 0.65rem 0.65rem;">
                <span class="row-satuan" style="font-weight: 800; color: #334155; font-size: 0.95rem;">${item.satuan_nm || itemObj?.satuan_dasar?.satuan_nm || '-'}</span>
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
        } else if (item.barang_id && (!tr._allBatches || tr._allBatches.length === 0)) {
            fetchBatchesForRow(tr, item.barang_id, curGrade);
        }
    }

    // Tambah baris dari hasil alokasi resep (BOM)
    function addRowFromAllocation(item) {
        const tbody = document.getElementById('itemsBody');
        const tr = document.createElement('tr');
        tr.id = `row-${rowIndex}`;

        const itemObj = BARANG_LIST.find(b => parseInt(b.barang_id) === parseInt(item.barang_id));
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
            batchOptions = '<option value="">(Stok Fisik Habis di Gudang ini)</option>';
        } else {
            let firstAvailInBomRow = false;
            batchesToShow.forEach(b => {
                const sisaFisik = parseFloat(b.sisa_qty || 0);
                const sisaBebas = (b.sisa_bebas !== undefined) ? parseFloat(b.sisa_bebas) : sisaFisik;
                const draftQty = parseFloat(b.draft_qty || 0);
                const draftDocs = Array.isArray(b.draft_doc_nos) ? b.draft_doc_nos.join(', ') : (b.draft_doc_nos || '');
                const isFullyBooked = b.is_fully_booked || (sisaBebas <= 0.0001 && draftQty > 0);

                let isFifoTop = false;
                if (!isFullyBooked && sisaBebas > 0.0001 && !firstAvailInBomRow) {
                    isFifoTop = true;
                    firstAvailInBomRow = true;
                }

                const isSelected = (b.batch_no === item.batch_no && (b.grade_cd || 'A') === curGrade) ? 'selected' : '';
                const prefix = isFifoTop ? '⭐ [FIFO Tertua] ' : '';
                const gCd = (b.grade_cd || 'A').toUpperCase();
                const gradeBadge = (gCd === 'A') ? '🟢 Grade A' : ((gCd === 'B') ? '🟡 Grade B' : `Grade ${gCd}`);
                const expInfo = b.expired_tgl ? ` | Exp: ${b.expired_tgl}` : '';
                const tglTerima = b.tgl_terima ? ` | Masuk: ${b.tgl_terima}` : '';

                let labelText = '';
                if (draftQty > 0) {
                    if (isFullyBooked) {
                        labelText = `⛔ [HABIS DIBOOKING DRAFT] ${b.batch_no} • [${gradeBadge}] (Sisa Bebas: 0 | Fisik: ${sisaFisik.toLocaleString('id-ID')} | ⚠️ ${draftQty.toLocaleString('id-ID')} di Draft [${draftDocs}])`;
                    } else {
                        labelText = `⚠️ [DRAFT ALLOCATED] ${prefix}${b.batch_no} • [${gradeBadge}] Sisa Bebas: ${sisaBebas.toLocaleString('id-ID')} (Fisik: ${sisaFisik.toLocaleString('id-ID')} | ⚠️ ${draftQty.toLocaleString('id-ID')} di Draft [${draftDocs}])`;
                    }
                } else {
                    labelText = `${prefix}${b.batch_no} • [${gradeBadge}] (Sisa: ${sisaFisik.toLocaleString('id-ID')})${tglTerima}${expInfo}`;
                }

                const disabledAttr = (isFullyBooked && sisaBebas <= 0.0001) ? 'disabled style="color: #94a3b8; background: #f8fafc;"' : '';

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
                    ${disabledAttr}
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

    // Filter pill grade BOM (Resep)
    function setBomGradeFilter(btn, grade) {
        const group = btn.closest('.grade-pill-group') || document.getElementById('bomGradePillGroup');
        if (group) {
            group.querySelectorAll('.btn-chip').forEach(b => b.classList.remove('active'));
        }
        btn.classList.add('active');
        const input = document.getElementById('bom_grade_filter');
        if (input) input.value = grade;
    }

    // Tarik alokasi bahan resep FIFO (BOM)
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
                if (btnText) btnText.innerHTML = 'Muat Batch FIFO Tertua';

                if (res.status === 'success') {
                    const data = res.data;
                    const items = data.items || [];

                    if (items.length === 0) {
                        alert('Resep ini belum memiliki rincian bahan baku terdaftar.');
                        return;
                    }

                    const tujuanInput = document.getElementById('tujuan_pemakaian');
                    if (tujuanInput && !tujuanInput.value) {
                        tujuanInput.value = 'PRODUKSI BERKO';
                        updateSidebarInfo();
                    }

                    const tbody = document.getElementById('itemsBody');
                    tbody.innerHTML = '';
                    rowIndex = 0;

                    items.forEach(item => {
                        addRowFromAllocation(item);
                    });

                    calculateGrandTotal();
                    triggerAutoSave();

                    let gradeBadgeHeader = '';
                    if (data.grade_filter === 'A') {
                        gradeBadgeHeader = `<span style="background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; padding: 2px 7px; border-radius: 4px; font-weight: 700; font-size: 0.75rem; margin-left: 0.4rem;">🟢 Khusus Grade A</span>`;
                    } else if (data.grade_filter === 'B') {
                        gradeBadgeHeader = `<span style="background: #fffbeb; color: #b45309; border: 1px solid #fde68a; padding: 2px 7px; border-radius: 4px; font-weight: 700; font-size: 0.75rem; margin-left: 0.4rem;">🟡 Khusus Grade B</span>`;
                    } else {
                        gradeBadgeHeader = `<span style="background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; padding: 2px 7px; border-radius: 4px; font-weight: 700; font-size: 0.75rem; margin-left: 0.4rem;">🔄 Campur / FIFO Bebas</span>`;
                    }

                    if (feedback) {
                        feedback.style.display = 'block';
                        if (data.is_lengkap) {
                            feedback.innerHTML = `
                                <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 6px; padding: 0.75rem 1rem; color: #065f46; display: flex; align-items: flex-start; gap: 0.65rem;">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="flex-shrink: 0; color: #059669; margin-top: 2px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <div>
                                        <strong style="display: inline-flex; align-items: center; font-size: 0.875rem;">
                                            Alokasi Batch Tertua (FIFO) Berhasil Dimuat: ${gradeBadgeHeader}
                                        </strong>
                                        <span style="font-size: 0.8rem; line-height: 1.4; display: block; margin-top: 0.2rem;">
                                            Total <strong>${items.length} baris bahan</strong> telah dialokasikan otomatis untuk target <strong>${targetQty} ${data.satuan_target}</strong>.
                                            Operator gudang dapat memeriksa dan menyesuaikan angka timbangan aktual jika terdapat deviasi sebelum klik simpan.
                                        </span>
                                    </div>
                                </div>
                            `;
                        } else {
                            const peringatanItems = (data.peringatan || []).map(p => `<li>${p}</li>`).join('');
                            let switchCampurBtnHtml = '';
                            if (data.can_switch_to_campur && data.defisit_info) {
                                switchCampurBtnHtml = `
                                    <div style="margin-top: 0.75rem; padding-top: 0.65rem; border-top: 1px dashed #fde68a; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem;">
                                        <span style="font-size: 0.8rem; font-weight: 600; color: #92400e;">
                                            💡 Tersedia saldo fisik ${data.defisit_info.other_grade_label} sebanyak ${parseFloat(data.defisit_info.other_stock).toLocaleString('id-ID')} ${data.defisit_info.satuan_nm}!
                                        </span>
                                        <button type="button" onclick="window.pemakaianApp.switchBomGradeToCampurAndReload()" class="btn btn-warning btn-sm" style="background: #d97706; border-color: #d97706; color: #ffffff; font-weight: 700; font-size: 0.775rem; border-radius: 6px; padding: 0.35rem 0.75rem; display: inline-flex; align-items: center; gap: 0.35rem; box-shadow: 0 1px 2px rgba(217,119,6,0.25);">
                                            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                            <span>Beralih ke Campur (Penuhi dari Grade Lain)</span>
                                        </button>
                                    </div>
                                `;
                            }

                            feedback.innerHTML = `
                                <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 6px; padding: 0.75rem 1rem; color: #92400e;">
                                    <div style="display: flex; align-items: flex-start; gap: 0.65rem; margin-bottom: 0.35rem;">
                                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="flex-shrink: 0; color: #d97706; margin-top: 2px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        <div>
                                            <strong style="display: inline-flex; align-items: center; font-size: 0.875rem;">
                                                Perhatian: Ada Bahan Fisik yang Kurang di Gudang Ini ${gradeBadgeHeader}
                                            </strong>
                                            <span style="font-size: 0.8rem; display: block; margin-top: 0.2rem;">
                                                Sistem berhasil memuat batch yang tersedia, namun beberapa item tidak mencukupi untuk memenuhi seluruh kebutuhan resep (${targetQty} ${data.satuan_target}):
                                            </span>
                                        </div>
                                    </div>
                                    <ul style="margin: 0.35rem 0 0 1.75rem; font-size: 0.8rem; padding: 0; line-height: 1.5;">
                                        ${peringatanItems}
                                    </ul>
                                    ${switchCampurBtnHtml}
                                </div>
                            `;

                            if (data.can_switch_to_campur && data.defisit_info) {
                                openWarningGradeShortageModal(data);
                            }
                        }
                    }

                    document.getElementById('itemsTable')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                } else {
                    alert('Gagal: ' + res.message);
                }
            })
            .catch(err => {
                console.error(err);
                if (btn) btn.disabled = false;
                if (btnText) btnText.innerHTML = 'Muat Batch FIFO Tertua';
                alert('Terjadi kesalahan koneksi saat menghitung alokasi resep.');
            });
    }

    // Handler saat barang dipilih di dropdown tabel
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
            // Reset tombol filter grade ke "Semua" agar memuat seluruh batch secara default
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

    // Render options batch ke select row
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

        let firstAvailableIdx = -1;
        filtered.forEach((b, idx) => {
            const sisaFisik = parseFloat(b.sisa_qty || 0);
            const sisaBebas = (b.sisa_bebas !== undefined) ? parseFloat(b.sisa_bebas) : sisaFisik;
            const draftQty = parseFloat(b.draft_qty || 0);
            const isFullyBooked = b.is_fully_booked || (sisaBebas <= 0.0001 && draftQty > 0);
            if (!isFullyBooked && sisaBebas > 0.0001 && firstAvailableIdx === -1) {
                firstAvailableIdx = idx;
            }
        });

        // Jika semua habis, fallback ke 0
        if (firstAvailableIdx === -1) firstAvailableIdx = 0;

        let html = '';
        filtered.forEach((b, idx) => {
            const isTop = (idx === firstAvailableIdx);
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
                    labelText = `⛔ [HABIS DIBOOKING DRAFT] ${b.batch_no} • [${gradeBadge}] (Sisa Bebas: 0 | Fisik: ${sisaFisik.toLocaleString('id-ID')} | ⚠️ ${draftQty.toLocaleString('id-ID')} di Draft ${draftDocs})`;
                } else {
                    labelText = `⚠️ [DRAFT ALLOCATED] ${prefix}${b.batch_no} • [${gradeBadge}] Sisa Bebas: ${sisaBebas.toLocaleString('id-ID')} (Fisik: ${sisaFisik.toLocaleString('id-ID')} | ⚠️ ${draftQty.toLocaleString('id-ID')} di Draft ${draftDocs})`;
                }
            } else {
                labelText = `${prefix}${b.batch_no} • [${gradeBadge}] (Sisa: ${sisaFisik.toLocaleString('id-ID')})${tglTerima}${expInfo}`;
            }

            const disabledAttr = (isFullyBooked && sisaBebas <= 0.0001) ? 'disabled style="color: #94a3b8; background: #f8fafc;"' : '';

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
                ${disabledAttr}
                ${isTop ? 'selected' : ''}>
                ${labelText}
            </option>`;
        });
        batchSelect.innerHTML = html;

        onBatchSelect(batchSelect);
    }

    // Ambil data batch stok fisik dari backend AJAX
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
        const url = `${baseUrl}?gudang_id=${encodeURIComponent(gudangId)}&barang_id=${encodeURIComponent(barangId)}`;

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

    // Tampilkan rincian status batch normal / badge peringatan
    function renderNormalBatchInfo(row) {
        const batchSelect = row.querySelector('.batch-select');
        const batchInfo = row.querySelector('.batch-info');
        if (!batchSelect || !batchInfo) return;

        const selectedOpt = batchSelect.selectedOptions[0];
        if (!selectedOpt || !selectedOpt.value) {
            batchInfo.innerHTML = '';
            return;
        }

        const sisa = parseFloat(selectedOpt.getAttribute('data-sisa') || 0);
        const sisaBebas = parseFloat(selectedOpt.getAttribute('data-sisa-bebas') !== null ? selectedOpt.getAttribute('data-sisa-bebas') : sisa);
        const draftQty = parseFloat(selectedOpt.getAttribute('data-draft-qty') || 0);
        const draftDocs = selectedOpt.getAttribute('data-draft-docs') || '';
        const tglMasuk = selectedOpt.getAttribute('data-masuk') || '-';
        const grade = (row.querySelector('.grade-input')?.value || selectedOpt.getAttribute('data-grade') || 'A').toUpperCase();

        const gradeBadge = (grade === 'A')
            ? `<span style="background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; font-weight: 700; padding: 1px 6px; border-radius: 4px; font-size: 0.725rem;">🟢 Grade A (Super)</span>`
            : ((grade === 'B')
                ? `<span style="background: #fffbeb; color: #b45309; border: 1px solid #fde68a; font-weight: 700; padding: 1px 6px; border-radius: 4px; font-size: 0.725rem;">🟡 Grade B (Standar)</span>`
                : `<span style="background: #f1f5f9; color: #334155; font-weight: 600; padding: 1px 6px; border-radius: 4px; font-size: 0.725rem;">Grade ${grade}</span>`);

        // Hitung kuantitas batch yang sudah dipakai di baris-baris lain dalam form ini
        const usedQtyByOtherRows = {};
        document.querySelectorAll('#itemsBody tr').forEach(r => {
            if (r !== row) {
                const bVal = r.querySelector('.batch-select')?.value;
                const gVal = (r.querySelector('.grade-input')?.value || '').toUpperCase();
                const qVal = parseFloat(r.querySelector('.qty-input')?.value || 0);
                if (bVal && qVal > 0) {
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
            const isFullyBooked = (opt.getAttribute('data-is-fully-booked') === '1') || opt.disabled;
            const usedOther = usedQtyByOtherRows[opt.value + '_' + optGrade] || usedQtyByOtherRows[opt.value] || 0;
            const netAvailable = optSisaBebas - usedOther;

            if (!isFullyBooked && netAvailable > 0.0001) {
                oldestOpt = opt;
                break;
            }
        }

        const isSameBatch = selectedOpt && oldestOpt && (selectedOpt.value === oldestOpt.value);
        const oldestSisaBebas = oldestOpt ? parseFloat(oldestOpt.getAttribute('data-sisa-bebas') !== null ? oldestOpt.getAttribute('data-sisa-bebas') : (oldestOpt.getAttribute('data-sisa') || 0)) : 0;
        const oldestBatchNo = oldestOpt ? oldestOpt.value : '-';
        const oldestMasuk = oldestOpt ? (oldestOpt.getAttribute('data-masuk') || '-') : '-';

        // Melompati batch tertua HANYA jika ada batch terlama dengan sisa bebas > 0 yang posisinya mendahului batch terpilih
        const isSkippingOldest = oldestOpt && selectedOpt && (selectedOpt.index > oldestOpt.index);

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

        if (isSameBatch || !isSkippingOldest || !oldestOpt) {
            batchSelect.style.borderColor = '#cbd5e1';
            batchSelect.style.backgroundColor = '#ffffff';
            batchInfo.innerHTML = `
                <div style="display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap;">
                    ${gradeBadge}
                    <span style="color: #065f46; font-weight: 600; background: #ecfdf5; padding: 0.15rem 0.5rem; border-radius: 4px; border: 1px solid #a7f3d0; font-size: 0.75rem;">
                        ⭐ FIFO Terverifikasi: Masuk ${tglMasuk} • Sisa Bebas: ${sisaBebas.toLocaleString('id-ID')} unit (Fisik: ${sisa.toLocaleString('id-ID')})
                    </span>
                </div>
                ${draftWarningNotice}
            `;
        } else if (isSkippingOldest && oldestSisaBebas > 0) {
            batchSelect.style.borderColor = '#f59e0b';
            batchSelect.style.backgroundColor = '#fffdf5';
            batchInfo.innerHTML = `
                <div style="background: #fffbeb; border: 1.5px solid #f59e0b; border-radius: 6px; padding: 0.45rem 0.65rem; margin-top: 0.25rem; color: #92400e;">
                    <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 0.5rem; flex-wrap: wrap;">
                        <div>
                            <div style="display: flex; align-items: center; gap: 0.35rem; margin-bottom: 0.2rem;">
                                ${gradeBadge}
                                <strong style="color: #b45309; font-size: 0.775rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                                    ⚠️ Melompati Batch Tertua!
                                </strong>
                            </div>
                            <div style="font-size: 0.735rem; color: #78350f; line-height: 1.35;">
                                Batch <strong>${selectedOpt.value}</strong> lebih baru. Masih ada Batch Terlama <strong>[${oldestBatchNo}]</strong> (Sisa Bebas: <strong>${oldestSisaBebas.toLocaleString('id-ID')} unit</strong> • Masuk: <strong>${oldestMasuk}</strong>) yang harus dihabiskan dahulu!
                            </div>
                        </div>
                        <button type="button" onclick="window.pemakaianApp.selectOldestBatchInRow(this)" style="background: #059669; color: #ffffff; font-weight: 700; font-size: 0.725rem; padding: 3px 8px; border: none; border-radius: 4px; cursor: pointer; white-space: nowrap; display: inline-flex; align-items: center; gap: 0.25rem; box-shadow: 0 1px 2px rgba(5,150,105,0.25);" title="Otomatis pilih batch tertua yang tersedia">
                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span>Pakai Terlama (${oldestBatchNo})</span>
                        </button>
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

    // Handler saat batch dipilih
    function onBatchSelect(selectEl) {
        const row = selectEl.closest('tr');
        const selectedOpt = selectEl.selectedOptions[0];
        if (!selectedOpt || !selectedOpt.value) return;

        const harga = parseFloat(selectedOpt.getAttribute('data-harga') || 0);
        const grade = (selectedOpt.getAttribute('data-grade') || 'A').toUpperCase();
        const stokId = selectedOpt.getAttribute('data-stok-id') || '';

        const gradeInput = row.querySelector('.grade-input');
        if (gradeInput) gradeInput.value = grade;
        const stokIdInput = row.querySelector('.stok-id-input');
        if (stokIdInput) stokIdInput.value = stokId;

        renderNormalBatchInfo(row);

        const hargaInput = row.querySelector('.harga-input');
        if (harga > 0) {
            hargaInput.value = harga;
        }

        calcRow(selectEl);
    }

    // Tombol jalan pintas memilih batch tertua
    function selectOldestBatchInRow(btn) {
        const row = btn.closest('tr');
        const batchSelect = row.querySelector('.batch-select');
        if (!batchSelect) return;

        // Cari opsi pertama yang memiliki sisa bebas > 0 dan tidak disabled
        for (let i = 0; i < batchSelect.options.length; i++) {
            const opt = batchSelect.options[i];
            const sisaBebas = parseFloat(opt.getAttribute('data-sisa-bebas') !== null ? opt.getAttribute('data-sisa-bebas') : (opt.getAttribute('data-sisa') || 0));
            const isFullyBooked = (opt.getAttribute('data-is-fully-booked') === '1') || opt.disabled;
            if (opt.value && !isFullyBooked && sisaBebas > 0.0001) {
                batchSelect.selectedIndex = i;
                onBatchSelect(batchSelect);
                return;
            }
        }

        if (batchSelect.options.length > 0) {
            batchSelect.selectedIndex = 0;
            onBatchSelect(batchSelect);
        }
    }

    // Kalkulasi kuantitas baris & validasi komitmen draft / limit stok
    function calcRow(el) {
        const row = el.closest('tr');
        const qtyInput = row.querySelector('.qty-input');
        const hargaInput = row.querySelector('.harga-input');
        const batchSelect = row.querySelector('.batch-select');
        const batchInfo = row.querySelector('.batch-info');
        const selectedBatchOpt = batchSelect ? batchSelect.selectedOptions[0] : null;

        const qty = parseFloat(qtyInput?.value) || 0;
        const sisaFisik = selectedBatchOpt ? parseFloat(selectedBatchOpt.getAttribute('data-sisa') || 0) : Infinity;
        const sisaBebas = selectedBatchOpt ? parseFloat(selectedBatchOpt.getAttribute('data-sisa-bebas') || sisaFisik) : Infinity;
        const draftQty = selectedBatchOpt ? parseFloat(selectedBatchOpt.getAttribute('data-draft-qty') || 0) : 0;
        const draftDocs = selectedBatchOpt ? selectedBatchOpt.getAttribute('data-draft-docs') || '' : '';
        const grade = (selectedBatchOpt?.getAttribute('data-grade') || row.querySelector('.grade-input')?.value || 'A').toUpperCase();

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
        } else if (selectedBatchOpt && selectedBatchOpt.value) {
            qtyInput.style.borderColor = '#cbd5e1';
            qtyInput.style.backgroundColor = '#ffffff';
            renderNormalBatchInfo(row);
        }

        calculateGrandTotal();
        triggerAutoSave();
    }

    // Kalkulasi total kuantitas seluruh baris
    function calculateGrandTotal() {
        let totalQty = 0;
        let totalNilai = 0;
        let activeItems = 0;
        let totalGradeAQty = 0;
        let totalGradeBQty = 0;

        document.querySelectorAll('#itemsBody tr').forEach(row => {
            const barangSelect = row.querySelector('.barang-select');
            if (barangSelect && barangSelect.value) {
                activeItems++;
            }
            const qty = parseFloat(row.querySelector('.qty-input')?.value || 0);
            const harga = parseFloat(row.querySelector('.harga-input')?.value || 0);
            const grade = (row.querySelector('.grade-input')?.value || '').toUpperCase();

            totalQty += qty;
            totalNilai += (qty * harga);

            if (grade === 'A') {
                totalGradeAQty += qty;
            } else if (grade === 'B') {
                totalGradeBQty += qty;
            }
        });

        const grandTotalQty = document.getElementById('grandTotalQty');
        const grandTotalNilai = document.getElementById('grandTotalNilai');
        if (grandTotalQty) grandTotalQty.textContent = totalQty.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        if (grandTotalNilai) grandTotalNilai.textContent = 'Rp ' + totalNilai.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        const footGradeA = document.getElementById('footGradeAQty');
        const footGradeB = document.getElementById('footGradeBQty');
        if (footGradeA) footGradeA.textContent = totalGradeAQty.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        if (footGradeB) footGradeB.textContent = totalGradeBQty.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        const sideGrandTotal = document.getElementById('sideGrandTotal');
        const sideTotalQty = document.getElementById('sideTotalQty');
        const sideTotalQtyLarge = document.getElementById('sideTotalQtyLarge');
        const botBarTotalQty = document.getElementById('botBarTotalQty');
        const sideTotalItems = document.getElementById('sideTotalItems');
        const sideGradeA = document.getElementById('sideGradeAQty');
        const sideGradeB = document.getElementById('sideGradeBQty');
        if (sideGrandTotal) sideGrandTotal.textContent = 'Rp ' + totalNilai.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        if (sideTotalQty) sideTotalQty.textContent = totalQty.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        if (sideTotalQtyLarge) sideTotalQtyLarge.textContent = totalQty.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        if (botBarTotalQty) botBarTotalQty.textContent = totalQty.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' Kg';
        if (sideTotalItems) sideTotalItems.textContent = activeItems;
        if (sideGradeA) sideGradeA.textContent = totalGradeAQty.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' Kg';
        if (sideGradeB) sideGradeB.textContent = totalGradeBQty.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' Kg';
    }

    // Auto-split batch saat kuantitas melebihi sisa fisik (mode: 'SAME_GRADE' atau 'CAMPUR')
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

        // Tentukan kandidat batch berdasarkan splitMode
        let candidateBatches = [];
        if (splitMode === 'SAME_GRADE') {
            candidateBatches = allBatches.filter(b => (b.grade_cd || 'A').toUpperCase() === currentGrade);
        } else {
            // Mode 'CAMPUR' / FIFO bebas: seluruh batch
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

        // Jika memilih SAME_GRADE tetapi kuantitas masih belum cukup dan di grade lain masih ada stok fisik
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

    // Modal Stock Limit & Draft Collision
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

            const isFullyBooked = v.isFullyBooked || (v.sisaBatch <= 0.0001 && v.draftQty > 0);
            const isDraftConflict = (v.draftQty > 0);

            let sisaBebasHtml = '';
            if (isFullyBooked) {
                sisaBebasHtml = `<span style="color:#dc2626; font-weight:800;">0 (Habis)</span>
                    <div style="font-size:0.68rem; color:#b45309; font-weight:600; font-family:sans-serif; margin-top:2px;">⚠️ ${v.draftQty.toLocaleString('id-ID')} di Draft [${v.draftDocs}]</div>`;
            } else if (isDraftConflict) {
                sisaBebasHtml = `<span style="color:#059669; font-weight:700;">${v.sisaBatch.toLocaleString('id-ID')}</span>
                    <div style="font-size:0.68rem; color:#b45309; font-weight:600; font-family:sans-serif; margin-top:2px;">⚠️ ${v.draftQty.toLocaleString('id-ID')} di Draft [${v.draftDocs}] (Fisik: ${v.sisaFisik.toLocaleString('id-ID')})</div>`;
            } else {
                sisaBebasHtml = `<span style="color:#059669; font-weight:700;">${v.sisaBatch.toLocaleString('id-ID')}</span>`;
            }

            const totalInfo = (v.totalRequested && v.totalRequested !== v.qtyRequested)
                ? `<div style="font-size:0.68rem; color:#64748b; margin-top:2px;">Total akumulasi: ${v.totalRequested.toLocaleString('id-ID')}</div>`
                : '';

            html += `
                <tr style="border-bottom: 1px solid #e2e8f0; ${idx % 2 === 1 ? 'background:#fafafa;' : ''}">
                    <td style="padding: 0.55rem 0.65rem;">
                        <div style="font-weight: 600; color: #0f172a;">${v.barangNm}</div>
                        <div style="font-size: 0.72rem; color: #64748b; margin-top: 2px;">Baris #${v.rowNum} &bull; ${gradeBadge}</div>
                    </td>
                    <td style="padding: 0.55rem 0.65rem;">
                        <strong style="color: #b45309; font-family: monospace;">${v.batchNo}</strong>
                        ${isFullyBooked ? `<div style="font-size:0.68rem; color:#dc2626; font-weight:700; margin-top:1px;">⛔ Habis Ter-booking</div>` : ''}
                    </td>
                    <td style="padding: 0.55rem 0.65rem; text-align: right; font-weight: 700; color: #dc2626; font-family: monospace;">
                        ${v.qtyRequested.toLocaleString('id-ID')}
                        ${totalInfo}
                    </td>
                    <td style="padding: 0.55rem 0.65rem; text-align: right; font-family: monospace;">
                        ${sisaBebasHtml}
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

    // Pengecekan Pelanggaran FIFO
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
                    sisa_bebas: parseFloat(opt.getAttribute('data-sisa-bebas') !== null ? opt.getAttribute('data-sisa-bebas') : (opt.getAttribute('data-sisa') || 0)),
                    is_fully_booked: (opt.getAttribute('data-is-fully-booked') === '1') || opt.disabled,
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

                    const stockInWarehouse = parseFloat((older.sisa_bebas !== undefined ? older.sisa_bebas : older.sisa_qty) || 0);
                    const isFullyBooked = older.is_fully_booked || (stockInWarehouse <= 0.0001);
                    if (isFullyBooked) continue; // Abaikan batch lama yang sudah 0 / 100% dibooking dokumen draft lain

                    const realAvailableOlder = stockInWarehouse - usedInForm;

                    if (realAvailableOlder > 0.01) {
                        const vKey = `${item.barangId}_${item.grade}_${item.batchNo}_vs_${older.batch_no}`;
                        if (!flaggedKeys.has(vKey)) {
                            flaggedKeys.add(vKey);
                            violations.push({
                                barangNm: item.barangNm,
                                grade: item.grade,
                                chosenBatch: item.batchNo,
                                chosenMasuk: item.chosenOpt.getAttribute('data-masuk') || '-',
                                oldestBatch: older.batch_no,
                                oldestMasuk: older.tgl_terima || older.created_at || '-',
                                oldestSisa: realAvailableOlder.toLocaleString('id-ID')
                            });
                        }
                        break;
                    }
                }
            }
        });

        return violations;
    }

    // Modal Peringatan FIFO
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
        if (modal) modal.style.display = 'flex';
    }

    function closeWarningFifoModal() {
        const modal = document.getElementById('modalWarningFifo');
        if (modal) modal.style.display = 'none';
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
        if (isSubmitting) return;

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

        // Jika memerlukan konfirmasi periode bulan sebelumnya, serahkan ke validasi form submit
        if (!previousMonthConfirmed) {
            const form = document.getElementById('pemakaianForm');
            if (form) {
                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                } else {
                    form.submit();
                }
            }
            return;
        }

        setSubmittingState(true, 'Menyimpan BPPB...');
        clearFormDraft();
        document.getElementById('pemakaianForm').submit();
    }

    // Modal Grade Shortage (Defisit Grade)
    function openWarningGradeShortageModal(data) {
        const content = document.getElementById('gradeShortageModalContent');
        if (!content) return;

        const info = data.defisit_info;
        if (info) {
            content.innerHTML = `
                <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 0.85rem 1rem; margin-bottom: 0.85rem;">
                    <div style="display: flex; gap: 0.5rem; align-items: flex-start;">
                        <span style="font-size: 1.25rem; line-height: 1;">⚠️</span>
                        <div>
                            <strong style="color: #92400e; font-size: 0.875rem; display: block; margin-bottom: 0.25rem;">
                                Defisit Bahan: ${info.barang_nm} (${info.barang_cd})
                            </strong>
                            <span style="font-size: 0.825rem; color: #78350f; line-height: 1.45; display: block;">
                                Anda memilih alokasi <strong>Khusus Grade ${info.chosen_grade} Saja</strong>, namun saldo fisik Grade ${info.chosen_grade} di gudang ini telah habis sebelum seluruh target produksi tercapai.
                            </span>
                        </div>
                    </div>
                </div>

                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 0.85rem 1rem;">
                    <div style="display: flex; gap: 0.5rem; align-items: flex-start;">
                        <span style="font-size: 1.25rem; line-height: 1;">💡</span>
                        <div>
                            <strong style="color: #166534; font-size: 0.875rem; display: block; margin-bottom: 0.25rem;">
                                Stok Fisik di Grade Lain Tersedia:
                            </strong>
                            <span style="font-size: 0.825rem; color: #14532d; line-height: 1.45; display: block;">
                                Di gudang ini masih tersedia saldo fisik <strong>${info.other_grade_label}</strong> sebanyak <strong>${parseFloat(info.other_stock).toLocaleString('id-ID')} ${info.satuan_nm}</strong>.
                            </span>
                        </div>
                    </div>
                </div>
            `;
        }

        const modal = document.getElementById('modalWarningGradeShortage');
        if (modal) modal.style.display = 'flex';
    }

    function closeWarningGradeShortageModal() {
        const modal = document.getElementById('modalWarningGradeShortage');
        if (modal) modal.style.display = 'none';
    }

    function switchBomGradeToCampurAndReload() {
        const btnAll = document.getElementById('btnBomGradeAll');
        if (btnAll) {
            setBomGradeFilter(btnAll, '');
        }
        closeWarningGradeShortageModal();
        tarikBahanResepFifo();
    }

    // Floating Widget Scroll Handler
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

        // 3. Proteksi Periode Bulan Sebelumnya & Override Nomor Urut Manual
        if (!previousMonthConfirmed) {
            const pakaiTglInput = document.getElementById('pakai_tgl');
            const pakaiNoInput = document.getElementById('pakai_no');
            const pakaiTglVal = pakaiTglInput ? pakaiTglInput.value : '';
            const pakaiNoVal = pakaiNoInput ? pakaiNoInput.value.trim() : '';
            const currentServerYM = window.currentServerYearMonth || (new Date().toISOString().slice(0, 7));

            const docYM = pakaiTglVal ? pakaiTglVal.slice(0, 7) : '';
            const isPreviousMonth = Boolean(docYM && docYM < currentServerYM);

            const currentParts = parsePakaiNoParts(pakaiNoVal);
            const suggestedParts = parsePakaiNoParts(lastSystemSuggestedPakaiNo);
            const isManualSequence = Boolean(currentParts && suggestedParts && (currentParts.seq !== suggestedParts.seq));

            if (isPreviousMonth || isManualSequence) {
                let title = '';
                let subtitle = '';
                let alerts = [];

                if (isPreviousMonth && isManualSequence) {
                    title = 'Peringatan: Periode Sebelumnya & Nomor Urut Manual';
                    subtitle = 'Dua penyesuaian khusus terdeteksi pada dokumen pengeluaran ini';
                } else if (isPreviousMonth) {
                    title = 'Konfirmasi Penambahan Dokumen Periode Sebelumnya';
                    subtitle = 'Dokumen pengeluaran ditujukan untuk periode bulan yang telah lewat';
                } else {
                    title = 'Peringatan: Nomor Urut BPPB Telah Diubah Manual';
                    subtitle = 'Nomor urut berbeda dari rekomendasi otomatis sistem';
                }

                if (isPreviousMonth) {
                    alerts.push(`
                        <div style="margin-bottom: 0.45rem;">
                            <strong>1. Periode Bulan Sebelumnya:</strong> Dokumen pengeluaran ini tercatat pada tanggal <strong>${formatTanggalIndo(pakaiTglVal)}</strong>, sedangkan periode operasional aktif saat ini adalah <strong>${formatTanggalIndo(new Date().toISOString().slice(0, 10))}</strong>. Menginput transaksi pengeluaran ke bulan sebelumnya dapat memengaruhi saldo akhir inventori dan laporan closing pembukuan.
                        </div>
                    `);
                }

                if (isManualSequence) {
                    alerts.push(`
                        <div style="margin-bottom: 0.45rem;">
                            <strong>${isPreviousMonth ? '2. ' : ''}Nomor Urut Manual:</strong> Anda memasukkan nomor urut <strong>${currentParts.seq}</strong> (dokumen: <code>${pakaiNoVal}</code>), sedangkan nomor urut otomatis yang disarankan sistem untuk periode ini adalah <strong>${suggestedParts.seq}</strong> (dokumen: <code>${lastSystemSuggestedPakaiNo}</code>). Perubahan manual berisiko menyebabkan nomor loncat atau potensi duplikasi arsip di masa mendatang.
                        </div>
                    `);
                }

                openPreviousMonthModal({
                    pakaiNo: pakaiNoVal,
                    pakaiTgl: pakaiTglVal,
                    title: title,
                    subtitle: subtitle,
                    contentHtml: alerts.join(''),
                    question: 'Apakah Anda yakin data ini sudah benar dan tetap ingin menyimpan dokumen BPPB ini?'
                });

                return false;
            }
        }

        return true;
    }

    /**
     * =========================================================================
     * PROTEKSI PERIODE BULAN SEBELUMNYA & LIVE SYNC NOMOR BPPB DENGAN BULAN
     * =========================================================================
     */
    function formatTanggalIndo(dateStr) {
        if (!dateStr) return '-';
        const months = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        const parts = dateStr.split('-');
        if (parts.length === 3) {
            const year = parts[0];
            const monthIdx = parseInt(parts[1], 10) - 1;
            const day = parseInt(parts[2], 10);
            return `${day} ${months[monthIdx] || ''} ${year}`;
        }
        return dateStr;
    }

    function openPreviousMonthModal(options) {
        const modal = document.getElementById('modalConfirmPreviousMonth');
        if (!modal) return;
        const elPakaiNo = document.getElementById('mcpmPakaiNo');
        const elPakaiTgl = document.getElementById('mcpmPakaiTgl');
        const elTitle = document.getElementById('mcpmTitle');
        const elSubtitle = document.getElementById('mcpmSubtitle');
        const elContent = document.getElementById('mcpmAlertContent');
        const elQuestion = document.getElementById('mcpmQuestion');

        if (elPakaiNo) elPakaiNo.textContent = options.pakaiNo || '-';
        if (elPakaiTgl) elPakaiTgl.textContent = formatTanggalIndo(options.pakaiTgl);
        if (elTitle) elTitle.textContent = options.title || 'Peringatan Dokumen Pengeluaran (BPPB)';
        if (elSubtitle) elSubtitle.textContent = options.subtitle || 'Perhatian khusus penomoran dokumen & periode pembukuan pemakaian';
        if (elContent) elContent.innerHTML = options.contentHtml || '';
        if (elQuestion) elQuestion.textContent = options.question || 'Apakah Anda yakin ingin melanjutkan penyimpanan?';

        modal.style.display = 'flex';
    }

    function closePreviousMonthModal() {
        const modal = document.getElementById('modalConfirmPreviousMonth');
        if (modal) modal.style.display = 'none';
    }

    function parsePakaiNoParts(val) {
        if (!val) return null;
        const match = val.trim().match(/^(\d+)\/BPPB\/MFI\/(\d{1,2})\/(\d{2,4})$/i);
        if (match) {
            const rawYear = match[3];
            const year2 = rawYear.length === 4 ? rawYear.slice(-2) : rawYear.padStart(2, '0');
            return {
                seq: match[1],
                month: match[2].padStart(2, '0'),
                year: year2,
            };
        }
        return null;
    }

    let lastSystemSuggestedPakaiNo = '';
    let previousMonthConfirmed = false;

    function initCreatePakaiDateNumberSync() {
        const pakaiTglInput = document.getElementById('pakai_tgl');
        const pakaiNoInput = document.getElementById('pakai_no');
        const pakaiNoHelp = document.getElementById('pakai_no_help');

        if (!pakaiTglInput || !pakaiNoInput) return;

        lastSystemSuggestedPakaiNo = pakaiNoInput.value.trim();
        let initialParts = parsePakaiNoParts(pakaiNoInput.value);
        let activeMonthYear = initialParts 
            ? `${initialParts.year}-${initialParts.month}` 
            : (pakaiTglInput.value ? pakaiTglInput.value.slice(2, 4) + '-' + pakaiTglInput.value.slice(5, 7) : '');

        function fetchAndApplyPakaiCode(targetDateStr, reasonText) {
            pakaiNoInput.style.opacity = '0.5';
            pakaiNoInput.style.pointerEvents = 'none';
            if (pakaiNoHelp) {
                pakaiNoHelp.style.display = 'block';
                pakaiNoHelp.innerHTML = `<span style="color:#0284c7;">⚡ Memeriksa urutan nomor BPPB untuk periode ${formatTanggalIndo(targetDateStr)}...</span>`;
            }

            fetch(`/ajax/generate-code?type=bppb&date=${encodeURIComponent(targetDateStr)}`)
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success' && data.code) {
                        pakaiNoInput.value = data.code;
                        lastSystemSuggestedPakaiNo = data.code;
                        const newParts = parsePakaiNoParts(data.code);
                        if (newParts) {
                            activeMonthYear = `${newParts.year}-${newParts.month}`;
                        }

                        // Visual highlight lembut
                        pakaiNoInput.style.background = '#f0fdf4';
                        setTimeout(() => {
                            pakaiNoInput.style.background = '#ffffff';
                        }, 1200);

                        if (pakaiNoHelp) {
                            pakaiNoHelp.style.display = 'block';
                            pakaiNoHelp.innerHTML = `<span style="color:#15803d; font-weight:600;">✓ Nomor BPPB disesuaikan otomatis (${reasonText}): <strong>${data.code}</strong></span>`;
                        }
                    }
                })
                .catch(err => {
                    console.error('Gagal generate nomor BPPB:', err);
                    if (pakaiNoHelp) {
                        pakaiNoHelp.style.display = 'block';
                        pakaiNoHelp.innerHTML = `<span style="color:#dc2626;">Gagal menyinkronkan nomor urut otomatis. Anda dapat mengetiknya secara manual.</span>`;
                    }
                })
                .finally(() => {
                    pakaiNoInput.style.opacity = '1';
                    pakaiNoInput.style.pointerEvents = 'auto';
                });
        }

        // 1. KETIKA TANGGAL PEMAKAIAN DIUBAH PENGGUNA (LANGSUNG LIVE)
        ['change', 'input'].forEach(evtName => {
            pakaiTglInput.addEventListener(evtName, function () {
                const newDate = this.value;
                if (!newDate) return;

                const newYM = newDate.slice(2, 4) + '-' + newDate.slice(5, 7);
                if (newYM !== activeMonthYear) {
                    fetchAndApplyPakaiCode(newDate, `urutan bulan ${formatTanggalIndo(newDate)}`);
                }
            });
        });

        // 2. KETIKA BULAN/TAHUN PADA INPUT NOMOR BPPB DIUBAH LANGSUNG
        let pakaiNoDebounceTimer = null;

        function handlePakaiNoInputSync() {
            const parts = parsePakaiNoParts(pakaiNoInput.value);
            if (!parts) return;

            const mNum = parseInt(parts.month, 10);
            if (isNaN(mNum) || mNum < 1 || mNum > 12) return;

            const typedYM = `${parts.year}-${parts.month}`;
            if (typedYM !== activeMonthYear) {
                const targetDate = `20${parts.year}-${parts.month}-01`;
                
                if (pakaiTglInput.value) {
                    const currentDay = pakaiTglInput.value.slice(8, 10);
                    const safeDate = `20${parts.year}-${parts.month}-${currentDay}`;
                    const testD = new Date(safeDate);
                    pakaiTglInput.value = (!isNaN(testD.getTime())) ? safeDate : targetDate;
                } else {
                    pakaiTglInput.value = targetDate;
                }

                fetchAndApplyPakaiCode(targetDate, `bulan ${parts.month} tahun 20${parts.year}`);
            }
        }

        pakaiNoInput.addEventListener('input', function () {
            clearTimeout(pakaiNoDebounceTimer);
            pakaiNoDebounceTimer = setTimeout(handlePakaiNoInputSync, 300);
        });

        ['change', 'blur'].forEach(evtName => {
            pakaiNoInput.addEventListener(evtName, function () {
                clearTimeout(pakaiNoDebounceTimer);
                handlePakaiNoInputSync();
            });
        });
    }

    /**
     * =========================================================================
     * ANTI DOUBLE SUBMISSION & SCREEN LOCK LOADING STATE
     * =========================================================================
     */
    let isSubmitting = false;
    let submitSafetyTimeout = null;

    function setSubmittingState(isSubmittingNow, customText = 'Menyimpan BPPB...') {
        isSubmitting = isSubmittingNow;

        const btnSubmit = document.getElementById('btnSubmitPemakaian');
        const btnConfirmPrev = document.getElementById('btnConfirmSubmitPreviousMonth');
        const allSubmitButtons = [btnSubmit, btnConfirmPrev].filter(Boolean);

        allSubmitButtons.forEach(btn => {
            btn.disabled = isSubmittingNow;
            if (isSubmittingNow) {
                if (!btn.dataset.originalHtml) {
                    btn.dataset.originalHtml = btn.innerHTML;
                }
                btn.classList.add('btn-is-submitting');
                btn.innerHTML = `
                    <svg class="submit-spinner-icon" width="18" height="18" fill="none" viewBox="0 0 24 24">
                        <circle style="opacity: 0.25;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path style="opacity: 0.75;" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>${customText}</span>
                `;
            } else {
                if (btn.dataset.originalHtml) {
                    btn.innerHTML = btn.dataset.originalHtml;
                }
                btn.classList.remove('btn-is-submitting');
            }
        });

        // Overlay pelindung seluruh layar agar tidak bisa diklik dobel
        let overlay = document.getElementById('screenSubmissionLockOverlay');
        if (isSubmittingNow) {
            if (!overlay) {
                overlay = document.createElement('div');
                overlay.id = 'screenSubmissionLockOverlay';
                overlay.style.cssText = 'position: fixed; inset: 0; background: rgba(15, 23, 42, 0.45); z-index: 100000; cursor: wait; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(2px);';
                overlay.innerHTML = `
                    <div style="background: #ffffff; padding: 1.35rem 2rem; border-radius: 12px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); display: flex; align-items: center; gap: 1rem; border: 1.5px solid #059669; animation: slideDownFloatingWidget 0.2s ease-out;">
                        <div style="width: 32px; height: 32px; border: 3px solid #e2e8f0; border-top-color: #059669; border-radius: 50%; animation: mirasaSpin 0.8s linear infinite; flex-shrink: 0;"></div>
                        <div>
                            <strong style="color: #0f172a; font-size: 0.95rem; display: block;">Sedang Menyimpan Data BPPB...</strong>
                            <span style="color: #64748b; font-size: 0.8rem;">Mohon tunggu, sistem sedang memproses dokumen &amp; alokasi stok. Jangan menutup jendela.</span>
                        </div>
                    </div>
                `;
                document.body.appendChild(overlay);
            } else {
                overlay.style.display = 'flex';
            }

            // Safety timeout 20 detik jika terjadi kendala jaringan agar UI tidak terkunci permanen
            clearTimeout(submitSafetyTimeout);
            submitSafetyTimeout = setTimeout(() => {
                if (isSubmitting) {
                    setSubmittingState(false);
                    console.warn('Submission state reset due to timeout fallback.');
                }
            }, 20000);
        } else {
            clearTimeout(submitSafetyTimeout);
            if (overlay) {
                overlay.style.display = 'none';
            }
        }
    }

    // Inisialisasi awal saat dokumen siap (dengan pemulihan draft otomatis jika ada)
    function init() {
        const restored = restoreFormDraft();
        if (!restored) {
            addRow();
        }
        updateSidebarInfo();
        initFloatingWidget();
        initCreatePakaiDateNumberSync();

        const btnConfirmPrev = document.getElementById('btnConfirmSubmitPreviousMonth');
        if (btnConfirmPrev) {
            btnConfirmPrev.addEventListener('click', function () {
                if (isSubmitting) return;
                previousMonthConfirmed = true;
                closePreviousMonthModal();
                clearFormDraft();
                setSubmittingState(true, 'Menyimpan BPPB...');
                const form = document.getElementById('pemakaianForm');
                if (form) {
                    form.submit();
                }
            });
        }

        document.getElementById('tujuan_pemakaian')?.addEventListener('change', () => {
            updateSidebarInfo();
            triggerAutoSave();
        });
        document.getElementById('tujuan_pemakaian')?.addEventListener('input', () => {
            updateSidebarInfo();
            triggerAutoSave();
        });
        document.getElementById('gudang_id')?.addEventListener('change', (e) => {
            onGudangChanged(e);
            triggerAutoSave();
        });

        // Auto-save debounced listener pada perubahan form
        document.getElementById('pemakaianForm')?.addEventListener('input', () => {
            triggerAutoSave();
        });
        document.getElementById('pemakaianForm')?.addEventListener('change', () => {
            triggerAutoSave();
        });

        // Simpan langsung saat window sebelum unload (refresh / F5 / pindah tab)
        window.addEventListener('beforeunload', () => {
            if (!isSubmitting) {
                saveFormDraft();
            }
        });

        // Tangani bfcache (Back-Forward Cache): Jika pengguna menekan tombol "Back / Kembali" di browser
        window.addEventListener('pageshow', (event) => {
            setSubmittingState(false);
            previousMonthConfirmed = false;
        });

        const form = document.getElementById('pemakaianForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                if (isSubmitting) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }

                if (!validateFormBeforeSubmit()) {
                    e.preventDefault();
                    e.stopPropagation();
                    setSubmittingState(false);
                    return false;
                }

                setSubmittingState(true, 'Menyimpan BPPB...');
                // Bersihkan draft tersimpan saat berhasil submit
                clearFormDraft();
            });
        }

        // Intersepsi langsung pada tombol Simpan (Save Button Integration)
        const btnSubmit = document.getElementById('btnSubmitPemakaian');
        if (btnSubmit) {
            btnSubmit.addEventListener('click', function(e) {
                if (isSubmitting) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
                if (!form) return;
                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }
                if (!validateFormBeforeSubmit()) {
                    e.preventDefault();
                    e.stopPropagation();
                    setSubmittingState(false);
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

    // Ekspos fungsi ke global namespace agar dapat dipanggil dari inline HTML onclick
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
        selectOldestBatchInRow,
        calcRow,
        calculateGrandTotal,
        autoSplitBatchForRow,
        splitSpecificRowFromModal,
        applyAutoSplitAllViolations,
        closeWarningStockLimitModal,
        closeWarningFifoModal,
        applyFixAllToOldestBatch,
        proceedOverrideFifoSubmit,
        closeWarningGradeShortageModal,
        switchBomGradeToCampurAndReload,
        clearAndResetDraft,
        saveFormDraft,
        restoreFormDraft,
        clearFormDraft,
        closePreviousMonthModal,
    };

    // Alias global untuk fungsi-fungsi lama agar kompatibel
    window.setRowCategory = setRowCategory;
    window.setRowGradeFilter = setRowGradeFilter;
    window.addRow = addRow;
    window.removeRow = removeRow;
    window.resetItemsTable = resetItemsTable;
    window.setBomGradeFilter = setBomGradeFilter;
    window.tarikBahanResepFifo = tarikBahanResepFifo;
    window.onBarangSelect = onBarangSelect;
    window.onBatchSelect = onBatchSelect;
    window.selectOldestBatchInRow = selectOldestBatchInRow;
    window.calcRow = calcRow;
    window.autoSplitBatchForRow = autoSplitBatchForRow;
    window.splitSpecificRowFromModal = splitSpecificRowFromModal;
    window.applyAutoSplitAllViolations = applyAutoSplitAllViolations;
    window.closeWarningStockLimitModal = closeWarningStockLimitModal;
    window.closeWarningFifoModal = closeWarningFifoModal;
    window.applyFixAllToOldestBatch = applyFixAllToOldestBatch;
    window.proceedOverrideFifoSubmit = proceedOverrideFifoSubmit;
    window.closeWarningGradeShortageModal = closeWarningGradeShortageModal;
    window.switchBomGradeToCampurAndReload = switchBomGradeToCampurAndReload;
    window.clearAndResetDraft = clearAndResetDraft;
    window.closePreviousMonthModal = closePreviousMonthModal;

})();
