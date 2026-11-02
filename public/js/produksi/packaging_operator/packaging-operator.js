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
 * Handle Timbangan Lakban Dus (Atas, Bawah, Total & Rupiah Live)
 */
function initLakbanSync() {
    const atasInput = document.getElementById('lakban_atas_gr');
    const bawahInput = document.getElementById('lakban_bawah_gr');

    if (atasInput && bawahInput) {
        atasInput.addEventListener('input', recalculateLakbanTotal);
        bawahInput.addEventListener('input', recalculateLakbanTotal);
        recalculateLakbanTotal();
    }
}

function recalculateLakbanTotal() {
    const atas = parseFloat(document.getElementById('lakban_atas_gr')?.value) || 0;
    const bawah = parseFloat(document.getElementById('lakban_bawah_gr')?.value) || 0;
    const total = Math.round((atas + bawah) * 10) / 10;

    const hiddenQty = document.getElementById('lakban_qty');
    if (hiddenQty) hiddenQty.value = total;

    const lblTotal = document.getElementById('lbl_lakban_total');
    if (lblTotal) lblTotal.textContent = total.toLocaleString('id-ID');
}

/**
 * Handle Karton Sirkular (Puteran vs Baru) & Auto FIFO calculation
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
    const customQty = parseFloat(document.getElementById('karton_custom_qty')?.value) || 0;

    const customWrapper = document.getElementById('kartonCustomWrapper');
    const isCustomActive = customWrapper && customWrapper.style.display !== 'none';

    let total = 0;
    if (isCustomActive && customQty > 0) {
        total = customQty;
    } else {
        total = puteran + baru + reject;
    }

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

                if (wipInfoBox && wipInfoText) {
                    wipInfoText.textContent = `Varian: ${barangNama} • Batch: ${batchNo} • Tersedia: ${qtyKg} Kg`;
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
