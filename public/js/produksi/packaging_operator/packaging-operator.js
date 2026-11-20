/**
 * Modul Produksi: Packaging Mobile (Operator Lapangan) - JavaScript
 * PT Mirasa Food Industry - Enterprise Mobile First
 */

document.addEventListener('DOMContentLoaded', function () {
    initShiftButtons();
    initWipSatuanButtons();
    initSatuanButtons();
    initKaryawanSync();
    initProduksiSync();
    initLakbanUnitSync();
    initLakbanSync();
    initKartonSync();
});

/**
 * Handle Lakban Unit Suffix Update on selection
 */
function initLakbanUnitSync() {
    const lakbanSelect = document.getElementById('lakban_barang_id');
    const lakbanUnitLabel = document.getElementById('lakban_unit_label');

    if (lakbanSelect && lakbanUnitLabel) {
        function updateUnit() {
            const opt = lakbanSelect.options[lakbanSelect.selectedIndex];
            if (opt && opt.value) {
                const satuan = opt.getAttribute('data-satuan') || 'ROLL';
                lakbanUnitLabel.textContent = satuan;
            } else {
                lakbanUnitLabel.textContent = 'Roll / Gram';
            }
        }

        lakbanSelect.addEventListener('change', updateUnit);
        updateUnit();
    }
}

/**
 * Handle Satuan WIP Segmented Buttons (KG / KARTON)
 */
function initWipSatuanButtons() {
    const wipSatuanBtns = document.querySelectorAll('.pkg-wip-satuan-btn');
    const wipSatuanInput = document.getElementById('inputWipSatuan');
    const suffix = document.getElementById('wipSatuanSuffix');

    wipSatuanBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            wipSatuanBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const val = this.getAttribute('data-satuan');
            if (wipSatuanInput) wipSatuanInput.value = val;
            if (suffix) suffix.textContent = val;
        });
    });
}

/**
 * Handle Lakban Dus (Pilihan Satuan: CM/Meter & Roll vs Gram Timbangan)
 */
function initLakbanSync() {
    const hasilKartonIn = document.getElementById('hasil_karton_qty');
    const standarCmIn = document.getElementById('lakban_standar_cm');
    const rusakMIn = document.getElementById('lakban_rusak_m');
    const rollIn = document.getElementById('lakban_roll_qty');

    const atasGrIn = document.getElementById('input_lakban_atas_gr');
    const bawahGrIn = document.getElementById('input_lakban_bawah_gr');
    const rusakGrIn = document.getElementById('input_lakban_rusak_gr');
    const totalGrIn = document.getElementById('input_lakban_total_gr');

    const btnCm = document.getElementById('btn_lakban_mode_cm');
    const btnGram = document.getElementById('btn_lakban_mode_gram');

    if (btnCm) {
        btnCm.addEventListener('click', function () {
            setLakbanSatuanMode('CM');
        });
    }
    if (btnGram) {
        btnGram.addEventListener('click', function () {
            setLakbanSatuanMode('GRAM');
        });
    }

    if (hasilKartonIn) {
        hasilKartonIn.addEventListener('input', function () {
            const currentMode = document.getElementById('lakban_satuan_mode')?.value || 'CM';
            if (currentMode === 'CM') {
                recalculateLakbanFromKarton();
            }
        });
    }
    if (standarCmIn) {
        standarCmIn.addEventListener('input', recalculateLakbanFromKarton);
    }
    if (rusakMIn) {
        rusakMIn.addEventListener('input', recalculateLakbanFromKarton);
    }
    if (rollIn) {
        rollIn.addEventListener('input', onManualRollInput);
    }

    if (atasGrIn) {
        atasGrIn.addEventListener('input', recalculateLakbanFromGram);
    }
    if (bawahGrIn) {
        bawahGrIn.addEventListener('input', recalculateLakbanFromGram);
    }
    if (rusakGrIn) {
        rusakGrIn.addEventListener('input', recalculateLakbanFromGram);
    }
    if (totalGrIn) {
        totalGrIn.addEventListener('input', onManualGramInput);
    }

    const initialMode = document.getElementById('lakban_satuan_mode')?.value || 'CM';
    setLakbanSatuanMode(initialMode);
}

/**
 * Switch mode satuan input lakban: 'CM' atau 'GRAM'
 */
function setLakbanSatuanMode(mode) {
    const modeInput = document.getElementById('lakban_satuan_mode');
    if (modeInput) modeInput.value = mode;

    const btnCm = document.getElementById('btn_lakban_mode_cm');
    const btnGram = document.getElementById('btn_lakban_mode_gram');
    const containerCm = document.getElementById('container_lakban_cm');
    const containerGram = document.getElementById('container_lakban_gram');

    if (mode === 'GRAM') {
        if (btnGram) btnGram.classList.add('active');
        if (btnCm) btnCm.classList.remove('active');
        if (containerGram) containerGram.style.display = 'block';
        if (containerCm) containerCm.style.display = 'none';

        // Jika input gram masih kosong dan ada nilai sebelumnya, sinkronkan
        const totalGrIn = document.getElementById('input_lakban_total_gr');
        const atasGrIn = document.getElementById('input_lakban_atas_gr');
        const bawahGrIn = document.getElementById('input_lakban_bawah_gr');
        if (totalGrIn && (!totalGrIn.value || parseFloat(totalGrIn.value) === 0)) {
            const hAtas = parseFloat(document.getElementById('lakban_atas_gr')?.value) || 0;
            const hBawah = parseFloat(document.getElementById('lakban_bawah_gr')?.value) || 0;
            if (hAtas > 0 || hBawah > 0) {
                if (atasGrIn) atasGrIn.value = hAtas;
                if (bawahGrIn) bawahGrIn.value = hBawah;
                if (totalGrIn) totalGrIn.value = hAtas + hBawah;
            }
        }
        recalculateLakbanFromGram();
    } else {
        if (btnCm) btnCm.classList.add('active');
        if (btnGram) btnGram.classList.remove('active');
        if (containerCm) containerCm.style.display = 'block';
        if (containerGram) containerGram.style.display = 'none';
        recalculateLakbanFromKarton();
    }
}
window.setLakbanSatuanMode = setLakbanSatuanMode;

/**
 * Kalkulasi otomatis kebutuhan lakban dari jumlah karton (Mode CM)
 */
function recalculateLakbanFromKarton() {
    const currentMode = document.getElementById('lakban_satuan_mode')?.value || 'CM';
    const hasilKarton = parseFloat(document.getElementById('hasil_karton_qty')?.value) || 0;
    const standarCm = parseFloat(document.getElementById('lakban_standar_cm')?.value) || 120;
    const rusakM = parseFloat(document.getElementById('lakban_rusak_m')?.value) || 0;

    // 1. Kebutuhan Normal = hasil karton * standar cm / 100
    const normalMeter = Math.round((hasilKarton * (standarCm / 100)) * 100) / 100;

    // 2. Total Meter = Normal + Rusak
    const totalMeter = Math.round((normalMeter + rusakM) * 100) / 100;

    // 3. Konversi ke Roll (1 Roll = 100 Meter)
    const totalRoll = Math.round((totalMeter / 100) * 100) / 100;

    // Update UI elements
    const lblRefKarton = document.getElementById('lbl_ref_karton_qty');
    if (lblRefKarton) lblRefKarton.textContent = hasilKarton.toLocaleString('id-ID');

    const lblKebutuhanM = document.getElementById('lbl_lakban_kebutuhan_m');
    if (lblKebutuhanM) lblKebutuhanM.textContent = normalMeter.toLocaleString('id-ID');

    const summaryBadge = document.getElementById('lbl_lakban_summary');
    if (summaryBadge && currentMode === 'CM') {
        summaryBadge.textContent = `${totalMeter.toLocaleString('id-ID')} Meter (${totalRoll.toLocaleString('id-ID')} Roll)`;
    }

    const rollInput = document.getElementById('lakban_roll_qty');
    if (rollInput && document.activeElement !== rollInput) {
        rollInput.value = totalRoll > 0 ? totalRoll : '';
    }

    if (currentMode === 'CM') {
        // Sinkronkan ke hidden input untuk backend (Roll & Gramatisasi kompatibilitas)
        const hiddenQty = document.getElementById('lakban_qty');
        if (hiddenQty) hiddenQty.value = totalRoll > 0 ? totalRoll : 0;

        // Estimasi gram (1 roll ~1250g -> ~12.5g/meter)
        const totalGram = Math.round(totalMeter * 12.5 * 10) / 10;
        const atasGram = Math.round((totalGram / 2) * 10) / 10;
        const bawahGram = Math.round((totalGram / 2) * 10) / 10;

        const hiddenAtas = document.getElementById('lakban_atas_gr');
        if (hiddenAtas) hiddenAtas.value = atasGram;

        const hiddenBawah = document.getElementById('lakban_bawah_gr');
        if (hiddenBawah) hiddenBawah.value = bawahGram;
    }
}
window.recalculateLakbanFromKarton = recalculateLakbanFromKarton;

/**
 * Handle jika user mengedit total roll secara manual di lapangan (Mode CM)
 */
function onManualRollInput() {
    const currentMode = document.getElementById('lakban_satuan_mode')?.value || 'CM';
    const rollVal = parseFloat(document.getElementById('lakban_roll_qty')?.value) || 0;
    const meterVal = Math.round(rollVal * 100 * 100) / 100;

    const summaryBadge = document.getElementById('lbl_lakban_summary');
    if (summaryBadge && currentMode === 'CM') {
        summaryBadge.textContent = `${meterVal.toLocaleString('id-ID')} Meter (${rollVal.toLocaleString('id-ID')} Roll)`;
    }

    if (currentMode === 'CM') {
        const hiddenQty = document.getElementById('lakban_qty');
        if (hiddenQty) hiddenQty.value = rollVal;

        const totalGram = Math.round(meterVal * 12.5 * 10) / 10;
        const atasGram = Math.round((totalGram / 2) * 10) / 10;
        const bawahGram = Math.round((totalGram / 2) * 10) / 10;

        const hiddenAtas = document.getElementById('lakban_atas_gr');
        if (hiddenAtas) hiddenAtas.value = atasGram;

        const hiddenBawah = document.getElementById('lakban_bawah_gr');
        if (hiddenBawah) hiddenBawah.value = bawahGram;
    }
}
window.onManualRollInput = onManualRollInput;

/**
 * Kalkulasi pemakaian lakban dari timbangan gram (Mode GRAM)
 */
function recalculateLakbanFromGram() {
    const currentMode = document.getElementById('lakban_satuan_mode')?.value || 'CM';
    const atasGr = parseFloat(document.getElementById('input_lakban_atas_gr')?.value) || 0;
    const bawahGr = parseFloat(document.getElementById('input_lakban_bawah_gr')?.value) || 0;
    const rusakGr = parseFloat(document.getElementById('input_lakban_rusak_gr')?.value) || 0;

    const totalGr = Math.round((atasGr + bawahGr + rusakGr) * 100) / 100;

    const totalGrIn = document.getElementById('input_lakban_total_gr');
    if (totalGrIn && document.activeElement !== totalGrIn) {
        totalGrIn.value = totalGr > 0 ? totalGr : '';
    }

    // Estimasi konversi ke roll (1 roll 100m ~ 1250g)
    const estRoll = Math.round((totalGr / 1250) * 100) / 100;

    const summaryBadge = document.getElementById('lbl_lakban_summary');
    if (summaryBadge && currentMode === 'GRAM') {
        summaryBadge.textContent = `${totalGr.toLocaleString('id-ID')} Gram (~${estRoll.toLocaleString('id-ID')} Roll)`;
    }

    if (currentMode === 'GRAM') {
        const hiddenQty = document.getElementById('lakban_qty');
        if (hiddenQty) hiddenQty.value = totalGr > 0 ? totalGr : 0;

        const hiddenAtas = document.getElementById('lakban_atas_gr');
        if (hiddenAtas) hiddenAtas.value = atasGr;

        const hiddenBawah = document.getElementById('lakban_bawah_gr');
        if (hiddenBawah) hiddenBawah.value = bawahGr;
    }
}
window.recalculateLakbanFromGram = recalculateLakbanFromGram;

/**
 * Handle jika user mengedit total timbangan gram secara langsung di lapangan (Mode GRAM)
 */
function onManualGramInput() {
    const currentMode = document.getElementById('lakban_satuan_mode')?.value || 'CM';
    const totalGr = parseFloat(document.getElementById('input_lakban_total_gr')?.value) || 0;
    const rusakGr = parseFloat(document.getElementById('input_lakban_rusak_gr')?.value) || 0;

    const netGr = Math.max(0, totalGr - rusakGr);
    const halfGr = Math.round((netGr / 2) * 10) / 10;

    const atasGrIn = document.getElementById('input_lakban_atas_gr');
    const bawahGrIn = document.getElementById('input_lakban_bawah_gr');
    if (atasGrIn && document.activeElement !== atasGrIn) {
        atasGrIn.value = halfGr > 0 ? halfGr : '';
    }
    if (bawahGrIn && document.activeElement !== bawahGrIn) {
        bawahGrIn.value = halfGr > 0 ? halfGr : '';
    }

    const estRoll = Math.round((totalGr / 1250) * 100) / 100;
    const summaryBadge = document.getElementById('lbl_lakban_summary');
    if (summaryBadge && currentMode === 'GRAM') {
        summaryBadge.textContent = `${totalGr.toLocaleString('id-ID')} Gram (~${estRoll.toLocaleString('id-ID')} Roll)`;
    }

    if (currentMode === 'GRAM') {
        const hiddenQty = document.getElementById('lakban_qty');
        if (hiddenQty) hiddenQty.value = totalGr;

        const hiddenAtas = document.getElementById('lakban_atas_gr');
        if (hiddenAtas) hiddenAtas.value = halfGr;

        const hiddenBawah = document.getElementById('lakban_bawah_gr');
        if (hiddenBawah) hiddenBawah.value = halfGr;
    }
}
window.onManualGramInput = onManualGramInput;

/**
 * Handle Karton Sirkular (Puteran vs Baru) & Auto FIFO calculation (Poin 42)
 */
function initKartonSync() {
    const puteranInput = document.getElementById('karton_puteran_qty');
    const baruInput = document.getElementById('karton_baru_qty');
    const rejectInput = document.getElementById('karton_reject_qty');

    if (puteranInput) puteranInput.addEventListener('input', updateKartonTotal);
    if (baruInput) baruInput.addEventListener('input', updateKartonTotal);
    if (rejectInput) rejectInput.addEventListener('input', updateKartonTotal);
    updateKartonTotal();
}

function updateKartonTotal() {
    const puteran = parseFloat(document.getElementById('karton_puteran_qty')?.value) || 0;
    const baru = parseFloat(document.getElementById('karton_baru_qty')?.value) || 0;
    const reject = parseFloat(document.getElementById('karton_reject_qty')?.value) || 0;

    const total = puteran + baru + reject;

    const totalInput = document.getElementById('karton_qty');
    if (totalInput) totalInput.value = total;

    const totalBadge = document.getElementById('lbl_karton_total_dipakai');
    if (totalBadge) totalBadge.textContent = total.toLocaleString('id-ID');
}

/**
 * ⚡ Smart Auto-Allocate: Pakai stok Puteran dulu (FIFO), sisanya ambil Karton Baru
 */
function autoAllocateKartonFifo() {
    const targetKarton = parseFloat(document.getElementById('hasil_karton_qty')?.value) || 0;
    if (targetKarton <= 0) {
        alert('Silakan isi "Jumlah Hasil Jadi Utama" terlebih dahulu di Seksi 4.');
        document.getElementById('hasil_karton_qty')?.focus();
        return;
    }

    const maxPuteranStok = parseFloat(window.stokKartonPuteranAvailable || 0);

    const alokasiPuteran = Math.min(targetKarton, Math.max(0, maxPuteranStok));
    const alokasiBaru = Math.max(0, targetKarton - alokasiPuteran);

    const puteranInput = document.getElementById('karton_puteran_qty');
    const baruInput = document.getElementById('karton_baru_qty');

    if (puteranInput) puteranInput.value = alokasiPuteran;
    if (baruInput) baruInput.value = alokasiBaru;

    updateKartonTotal();
}

/**
 * Toggle Dus Khusus / Non-IFM Dropdown
 */
function toggleCustomKarton() {
    const wrap = document.getElementById('kartonCustomWrapper');
    const sirkularGrid = document.getElementById('kartonSirkularGrid');
    const btnToggle = document.getElementById('btnToggleKarton');

    if (wrap && sirkularGrid) {
        if (wrap.style.display === 'none') {
            wrap.style.display = 'block';
            sirkularGrid.style.display = 'none';
            if (btnToggle) btnToggle.textContent = '← Kembali ke Karton Sirkular (Puteran & Baru)';
        } else {
            wrap.style.display = 'none';
            sirkularGrid.style.display = 'grid';
            if (btnToggle) btnToggle.textContent = '📦 Gunakan Dus Khusus / Non-IFM';
        }
        updateKartonTotal();
    }
}

/**
 * Handle Shift Segmented Buttons
 */
function initShiftButtons() {
    const shiftBtns = document.querySelectorAll('.pkg-shift-btn');
    const shiftInput = document.getElementById('inputShift');

    shiftBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            shiftBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            if (shiftInput) {
                shiftInput.value = this.getAttribute('data-shift');
            }
        });
    });
}

/**
 * Handle Satuan Hasil Segmented Buttons
 */
function initSatuanButtons() {
    const satuanBtns = document.querySelectorAll('.pkg-satuan-btn');
    const satuanInput = document.getElementById('inputSatuanHasil');

    satuanBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            satuanBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            if (satuanInput) {
                satuanInput.value = this.getAttribute('data-satuan');
            }
        });
    });
}

/**
 * Sync Karyawan Dropdown to Operator Name Field
 */
function initKaryawanSync() {
    const karyawanSelect = document.getElementById('karyawan_id');
    const operatorInput = document.getElementById('operator_nama');

    if (karyawanSelect && operatorInput) {
        karyawanSelect.addEventListener('change', function () {
            const selectedOpt = this.options[this.selectedIndex];
            if (selectedOpt && selectedOpt.value) {
                const nameText = selectedOpt.textContent.split('(')[0].trim();
                operatorInput.value = nameText;
            }
        });
    }
}

/**
 * Sync Produksi WIP Selection & show Berko / Varian info
 */
function initProduksiSync() {
    const outputSelect = document.getElementById('output_id');
    const legacyProdSelect = document.getElementById('produksi_id');
    const hiddenProdId = document.getElementById('hidden_produksi_id');
    const hiddenWipBarangId = document.getElementById('hidden_wip_barang_id');
    const hiddenWipBatchNo = document.getElementById('hidden_wip_batch_no');
    const wipQtyInput = document.getElementById('wip_qty_kg');
    const wipInfoBox = document.getElementById('wipInfoBox');
    const wipInfoText = document.getElementById('wipInfoText');

    if (outputSelect) {
        function syncOutput() {
            const opt = outputSelect.options[outputSelect.selectedIndex];
            if (opt && opt.value) {
                const prodId = opt.getAttribute('data-produksi-id') || '';
                const barangId = opt.getAttribute('data-barang-id') || '';
                const batchNo = opt.getAttribute('data-batch') || '-';
                const barangNama = opt.getAttribute('data-barang-nama') || 'Keripik Curah';
                const qtyKg = opt.getAttribute('data-qty') || '0';

                if (hiddenProdId) hiddenProdId.value = prodId;
                if (hiddenWipBarangId) hiddenWipBarangId.value = barangId;
                if (hiddenWipBatchNo) hiddenWipBatchNo.value = batchNo;

                const isBerbumbu = opt.getAttribute('data-is-berbumbu') === '1';
                updateBumbuVisibility(isBerbumbu);

                if (wipInfoBox && wipInfoText) {
                    const bumbuBadge = isBerbumbu ? ' • <span style="color: #059669; font-weight: 700;">✨ Sudah Berbumbu</span>' : '';
                    wipInfoText.innerHTML = `Varian: <strong>${barangNama}</strong> • Batch: <code>${batchNo}</code> • Tersedia: <strong>${qtyKg} Kg</strong>${bumbuBadge}`;
                    wipInfoBox.style.display = 'block';
                }

                // Jika input masih kosong, beri nilai default stok yang tersedia
                if (wipQtyInput && (!wipQtyInput.value || parseFloat(wipQtyInput.value) === 0)) {
                    wipQtyInput.value = qtyKg;
                }
            } else {
                if (hiddenProdId) hiddenProdId.value = '';
                if (hiddenWipBarangId) hiddenWipBarangId.value = '';
                if (hiddenWipBatchNo) hiddenWipBatchNo.value = '';
                if (wipInfoBox) wipInfoBox.style.display = 'none';
                updateBumbuVisibility(false);
            }
        }

        outputSelect.addEventListener('change', syncOutput);
        syncOutput();
    } else if (legacyProdSelect) {
        legacyProdSelect.addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            if (opt && opt.value) {
                const qtyVal = opt.getAttribute('data-qty') || '0';
                const batchNo = opt.getAttribute('data-batch') || '-';
                const barangNm = opt.getAttribute('data-barang-nm') || 'Keripik Curah';
                const prodNo = opt.getAttribute('data-no') || '';
                const tglStr = opt.getAttribute('data-tgl') || '';

                if (wipInfoBox && wipInfoText) {
                    wipInfoText.innerHTML = `✓ <strong>${prodNo}</strong> (${tglStr}) &bull; <strong>${barangNm}</strong> &bull; Batch: <code>${batchNo}</code> &bull; Tersedia: <strong>${parseFloat(qtyVal).toLocaleString('id-ID')} Kg</strong>`;
                    wipInfoBox.style.display = 'block';
                }
                // Jika input masih kosong atau 100 default, beri nilai stok curah yang dipilih
                if (wipQtyInput && (!wipQtyInput.value || parseFloat(wipQtyInput.value) === 0 || parseFloat(wipQtyInput.value) === 100)) {
                    wipQtyInput.value = qtyVal;
                }
            } else {
                if (wipInfoBox) wipInfoBox.style.display = 'none';
            }
        });
    }
}

/**
 * Multi-Item Kemasan Tambahan (Dinamis Add/Remove Row)
 */
let kemasanTambahanIndex = 0;

function addKemasanTambahanRow(selectedId = '', defaultQty = '') {
    const container = document.getElementById('kemasanTambahanList');
    if (!container) return;

    const optionsData = window.kemasanLainOptions || [];
    let optionsHtml = '<option value="">-- Pilih Kemasan Tambahan --</option>';

    optionsData.forEach(item => {
        const isSelected = selectedId && parseInt(selectedId) === parseInt(item.barang_id) ? 'selected' : '';
        const satuan = item.satuan_nm ? ` (${item.satuan_nm})` : '';
        optionsHtml += `<option value="${item.barang_id}" data-satuan="${item.satuan_nm || 'PCS'}" ${isSelected}>${item.barang_nm}${satuan}</option>`;
    });

    const rowId = `kemasan_row_${kemasanTambahanIndex}`;
    const rowDiv = document.createElement('div');
    rowDiv.className = 'pkg-dynamic-row';
    rowDiv.id = rowId;

    rowDiv.innerHTML = `
        <div class="pkg-dynamic-row-select">
            <select name="kemasan_tambahan[${kemasanTambahanIndex}][barang_id]" class="pkg-select" style="height: 42px; font-size: 0.825rem;" onchange="onKemasanTambahanSelect(this, '${rowId}')">
                ${optionsHtml}
            </select>
        </div>
        <div class="pkg-dynamic-row-qty">
            <div class="pkg-input-suffix-wrapper" style="height: 42px;">
                <input type="number" step="any" min="0" name="kemasan_tambahan[${kemasanTambahanIndex}][qty]" class="pkg-input" placeholder="Qty" value="${defaultQty}" style="height: 42px; font-size: 0.85rem; padding-right: 38px;">
                <span class="pkg-input-suffix-muted unit-suffix" style="font-size: 0.7rem; right: 6px;">Pcs</span>
            </div>
        </div>
        <button type="button" class="pkg-dynamic-row-remove" onclick="removeKemasanTambahanRow('${rowId}')" title="Hapus Baris">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
        </button>
    `;

    container.appendChild(rowDiv);
    kemasanTambahanIndex++;
}

function removeKemasanTambahanRow(rowId) {
    const row = document.getElementById(rowId);
    if (row) {
        row.remove();
    }
}

function onKemasanTambahanSelect(selectElem, rowId) {
    const row = document.getElementById(rowId);
    if (!row) return;

    const suffixSpan = row.querySelector('.unit-suffix');
    const selectedOpt = selectElem.options[selectElem.selectedIndex];
    if (suffixSpan && selectedOpt) {
        const satuan = selectedOpt.getAttribute('data-satuan') || 'Pcs';
        suffixSpan.textContent = satuan;
    }
}

/**
 * Smart Bumbu Visibility Sync (Poin 43)
 * Mencegah duplikasi biaya bumbu jika curah WIP sudah dibumbui di Dapur Goreng.
 */
let isBumbuOverridden = false;

function toggleManualBumbuOverride() {
    isBumbuOverridden = !isBumbuOverridden;
    const container = document.getElementById('containerBumbuInputs');
    const btn = document.getElementById('btnOverrideBumbu');
    if (container) {
        if (isBumbuOverridden) {
            container.style.display = 'block';
            if (btn) btn.textContent = '✖ Batalkan input bumbu manual (kembali ke otomatis)';
        } else {
            container.style.display = 'none';
            if (btn) btn.textContent = '➕ Perlu tambah bumbu manual di meja kemas? Klik di sini';
            resetBumbuInputs();
        }
    }
}

function resetBumbuInputs() {
    const bumbuSelect = document.getElementById('bumbu_barang_id');
    const bumbuQtyIn = document.getElementById('bumbu_qty_kg');
    if (bumbuSelect) bumbuSelect.value = '';
    if (bumbuQtyIn) bumbuQtyIn.value = '0';
}

function updateBumbuVisibility(isBerbumbu) {
    const alertBox = document.getElementById('alertBumbuDapur');
    const badgeStatus = document.getElementById('badgeBumbuStatus');
    const container = document.getElementById('containerBumbuInputs');
    const btn = document.getElementById('btnOverrideBumbu');

    if (isBerbumbu && !isBumbuOverridden) {
        if (alertBox) alertBox.style.display = 'block';
        if (badgeStatus) badgeStatus.style.display = 'inline-block';
        if (container) container.style.display = 'none';
        resetBumbuInputs();
    } else if (!isBerbumbu) {
        isBumbuOverridden = false;
        if (alertBox) alertBox.style.display = 'none';
        if (badgeStatus) badgeStatus.style.display = 'none';
        if (container) container.style.display = 'block';
        if (btn) btn.textContent = '➕ Perlu tambah bumbu manual di meja kemas? Klik di sini';
    }
}
