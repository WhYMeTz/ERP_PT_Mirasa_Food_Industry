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
    initLakbanSync();
});

/**
 * Handle Lakban Unit Suffix Update on selection
 */
function initLakbanSync() {
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
    const total = atas + bawah;

    const hiddenQty = document.getElementById('lakban_qty');
    if (hiddenQty) hiddenQty.value = total;

    const lblTotal = document.getElementById('lbl_lakban_total');
    if (lblTotal) lblTotal.textContent = total.toLocaleString('id-ID');

    // Tarif acuan lakban timbangan (Rp 35.10 / gram)
    const tarifPerGram = 35.10;
    const rupiah = Math.round(total * tarifPerGram);
    const lblRp = document.getElementById('lbl_lakban_rp');
    if (lblRp) lblRp.textContent = 'Rp ' + rupiah.toLocaleString('id-ID');
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
