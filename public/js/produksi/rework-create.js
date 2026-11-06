/**
 * Formulir Sortir Ulang & Rework Produk (KW 2)
 * PT Mirasa Food Industry
 */

document.addEventListener('DOMContentLoaded', function () {
    let availableBatches = [];

    const gudangSelect = document.getElementById('gudangId');
    const batchSelect = document.getElementById('batchSelect');
    const reworkBarangId = document.getElementById('reworkBarangId');
    const reworkBatchNo = document.getElementById('reworkBatchNo');
    const batchSummaryBox = document.getElementById('batchSummaryBox');
    const summaryBarang = document.getElementById('summaryBarang');
    const summaryJenis = document.getElementById('summaryJenis');
    const summarySisaStok = document.getElementById('summarySisaStok');
    const summaryHpp = document.getElementById('summaryHpp');

    const reworkQtyInput = document.getElementById('reworkQty');
    const reworkSatuanLabel = document.getElementById('reworkSatuanLabel');
    const qtyKw2Input = document.getElementById('qtyKw2');
    const qtyLengketInput = document.getElementById('qtyLengket');
    const qtyRemahanInput = document.getElementById('qtyRemahan');

    const pctKw2Display = document.getElementById('pctKw2Display');
    const pctLengketDisplay = document.getElementById('pctLengketDisplay');
    const pctRemahanDisplay = document.getElementById('pctRemahanDisplay');
    const totalOutputFooter = document.getElementById('totalOutputFooter');
    const balanceStatusBar = document.getElementById('balanceStatusBar');
    const balanceStatusText = document.getElementById('balanceStatusText');

    const cardPemulihanKarton = document.getElementById('cardPemulihanKarton');
    const dusDibongkarInput = document.getElementById('dusDibongkar');
    const dusPuteranInput = document.getElementById('dusPuteran');
    const dusRusakInput = document.getElementById('dusRusak');
    const kartonBalanceNotice = document.getElementById('kartonBalanceNotice');

    const formRework = document.getElementById('formRework');
    const btnSubmit = document.getElementById('btnSubmit');

    // Format Rupiah helper
    function formatRupiah(num) {
        return 'Rp ' + Number(num || 0).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    }

    // Load batches via AJAX when warehouse changes
    window.loadBatchesForWarehouse = function () {
        const gudangId = gudangSelect.value;
        if (!gudangId) return;

        batchSelect.innerHTML = '<option value="">Memuat daftar batch tersedia...</option>';
        batchSelect.disabled = true;
        batchSummaryBox.style.display = 'none';
        reworkBarangId.value = '';
        reworkBatchNo.value = '';

        const url = (window.appConfig && window.appConfig.ajaxBatchesUrl)
            ? `${window.appConfig.ajaxBatchesUrl}?gudang_id=${encodeURIComponent(gudangId)}`
            : `/produksi/ajax-batches-rework?gudang_id=${encodeURIComponent(gudangId)}`;

        fetch(url)
            .then(res => res.json())
            .then(res => {
                batchSelect.disabled = false;
                if (!res.success || !res.data || res.data.length === 0) {
                    batchSelect.innerHTML = '<option value="">-- Tidak ada stok batch WIP / FG di gudang ini --</option>';
                    availableBatches = [];
                    return;
                }

                availableBatches = res.data;
                let optionsHtml = '<option value="">-- Pilih Batch Sumber yang Akan Disortir --</option>';

                res.data.forEach((b, idx) => {
                    const labelBadge = b.is_fg ? '[FG]' : '[WIP]';
                    optionsHtml += `<option value="${idx}">
                        ${labelBadge} ${b.barang_nm} (${b.barang_cd}) - Batch: ${b.batch_no} | Sisa: ${Number(b.sisa_qty).toLocaleString('id-ID', { maximumFractionDigits: 2 })} ${b.satuan_nm}
                    </option>`;
                });

                batchSelect.innerHTML = optionsHtml;
            })
            .catch(err => {
                console.error('Gagal memuat batch:', err);
                batchSelect.disabled = false;
                batchSelect.innerHTML = '<option value="">Gagal memuat daftar batch. Silakan coba lagi.</option>';
            });
    };

    // On batch select
    window.onBatchChange = function (sel) {
        const idx = sel.value;
        if (idx === '' || !availableBatches[idx]) {
            batchSummaryBox.style.display = 'none';
            reworkBarangId.value = '';
            reworkBatchNo.value = '';
            cardPemulihanKarton.style.display = 'none';
            return;
        }

        const b = availableBatches[idx];
        reworkBarangId.value = b.barang_id;
        reworkBatchNo.value = b.batch_no;

        summaryBarang.textContent = `${b.barang_nm} (${b.barang_cd})`;
        summaryJenis.textContent = b.is_fg ? 'Finished Goods (FG)' : 'Olahan Curah (WIP)';
        summarySisaStok.textContent = `${Number(b.sisa_qty).toLocaleString('id-ID', { maximumFractionDigits: 2 })} ${b.satuan_nm}`;
        summaryHpp.textContent = formatRupiah(b.harga_satuan);

        batchSummaryBox.style.display = 'block';

        // Set max and placeholder for initial qty
        reworkQtyInput.max = b.sisa_qty;
        reworkSatuanLabel.textContent = b.satuan_nm || 'KG';

        if (!reworkQtyInput.value || parseFloat(reworkQtyInput.value) <= 0) {
            reworkQtyInput.value = b.sisa_qty;
        }

        // Show / hide Kemasan Karton section
        if (b.is_fg) {
            cardPemulihanKarton.style.display = 'block';
            if (dusDibongkarInput && (!dusDibongkarInput.value || parseInt(dusDibongkarInput.value) === 0)) {
                dusDibongkarInput.value = Math.floor(b.sisa_qty);
            }
        } else {
            cardPemulihanKarton.style.display = 'none';
            if (dusDibongkarInput) dusDibongkarInput.value = 0;
            if (dusPuteranInput) dusPuteranInput.value = 0;
            if (dusRusakInput) dusRusakInput.value = 0;
        }

        calculateAllocation();
    };

    // Calculate allocation balance
    window.calculateAllocation = function () {
        const qtyAwal = parseFloat(reworkQtyInput.value) || 0;
        const qtyKw2 = parseFloat(qtyKw2Input.value) || 0;
        const qtyLengket = parseFloat(qtyLengketInput.value) || 0;
        const qtyRemahan = parseFloat(qtyRemahanInput.value) || 0;

        const totalOutput = qtyKw2 + qtyLengket + qtyRemahan;
        const selisih = qtyAwal - totalOutput;

        // Percentages
        const pctKw2 = qtyAwal > 0 ? ((qtyKw2 / qtyAwal) * 100).toFixed(1) : '0.0';
        const pctLengket = qtyAwal > 0 ? ((qtyLengket / qtyAwal) * 100).toFixed(1) : '0.0';
        const pctRemahan = qtyAwal > 0 ? ((qtyRemahan / qtyAwal) * 100).toFixed(1) : '0.0';

        pctKw2Display.textContent = `${pctKw2}%`;
        pctLengketDisplay.textContent = `${pctLengket}%`;
        pctRemahanDisplay.textContent = `${pctRemahan}%`;

        totalOutputFooter.textContent = `${totalOutput.toFixed(2)} KG`;

        // Status Bar Update
        balanceStatusBar.className = 'balance-status-bar';

        if (qtyAwal <= 0) {
            balanceStatusBar.classList.add('invalid');
            balanceStatusText.textContent = 'Silakan masukkan kuantitas awal yang disortir ulang.';
            return;
        }

        if (Math.abs(selisih) < 0.001) {
            balanceStatusBar.classList.add('valid');
            balanceStatusText.textContent = `Alokasi Seimbang 100% (Total hasil pemilahan: ${totalOutput.toFixed(2)} KG sama dengan kuantitas awal disortir).`;
        } else if (selisih > 0) {
            const pctSusut = ((selisih / qtyAwal) * 100).toFixed(1);
            balanceStatusBar.classList.add('diff');
            balanceStatusText.textContent = `Terdapat selisih susut pengerjaan sebesar ${selisih.toFixed(2)} KG (Susut: ${pctSusut}%). Hasil sortir valid untuk dicatat.`;
        } else {
            const over = Math.abs(selisih).toFixed(2);
            balanceStatusBar.classList.add('invalid');
            balanceStatusText.textContent = `Peringatan: Total alokasi hasil sortir (${totalOutput.toFixed(2)} KG) melebihi kuantitas bahan yang disortir sebesar ${over} KG!`;
        }
    };

    // Validate carton balance if visible
    window.checkCartonBalance = function () {
        if (!cardPemulihanKarton || cardPemulihanKarton.style.display === 'none') return true;

        const bongkar = parseInt(dusDibongkarInput.value) || 0;
        const puteran = parseInt(dusPuteranInput.value) || 0;
        const rusak = parseInt(dusRusakInput.value) || 0;
        const totalAlokasi = puteran + rusak;

        if (bongkar > 0 && totalAlokasi > bongkar) {
            kartonBalanceNotice.style.display = 'block';
            kartonBalanceNotice.textContent = `Peringatan: Alokasi dus puteran (${puteran}) + dus rusak (${rusak}) melebihi total dus dibongkar (${bongkar}).`;
            kartonBalanceNotice.style.color = '#dc2626';
            return false;
        } else if (bongkar > 0 && totalAlokasi === bongkar) {
            kartonBalanceNotice.style.display = 'block';
            kartonBalanceNotice.textContent = `Alokasi dus seimbang (${totalAlokasi} dari ${bongkar} pcs).`;
            kartonBalanceNotice.style.color = '#16a34a';
            return true;
        } else {
            kartonBalanceNotice.style.display = 'none';
            return true;
        }
    };

    if (dusDibongkarInput) dusDibongkarInput.addEventListener('input', checkCartonBalance);
    if (dusPuteranInput) dusPuteranInput.addEventListener('input', checkCartonBalance);
    if (dusRusakInput) dusRusakInput.addEventListener('input', checkCartonBalance);

    // Form submit validation
    formRework.addEventListener('submit', function (e) {
        if (!reworkBarangId.value || !reworkBatchNo.value) {
            e.preventDefault();
            alert('Silakan pilih batch sumber yang akan disortir terlebih dahulu.');
            batchSelect.focus();
            return false;
        }

        const qtyAwal = parseFloat(reworkQtyInput.value) || 0;
        const qtyKw2 = parseFloat(qtyKw2Input.value) || 0;
        const qtyLengket = parseFloat(qtyLengketInput.value) || 0;
        const qtyRemahan = parseFloat(qtyRemahanInput.value) || 0;
        const totalOutput = qtyKw2 + qtyLengket + qtyRemahan;

        if (qtyAwal <= 0) {
            e.preventDefault();
            alert('Kuantitas yang disortir ulang harus lebih dari 0.');
            reworkQtyInput.focus();
            return false;
        }

        if (qtyKw2 <= 0) {
            e.preventDefault();
            alert('Kuantitas hasil baik KS CURAH SORTIR (KW 2) wajib diisi lebih dari 0.');
            qtyKw2Input.focus();
            return false;
        }

        if (totalOutput > qtyAwal + 0.001) {
            e.preventDefault();
            alert(`Total hasil pemilahan (${totalOutput.toFixed(2)} KG) tidak boleh melebihi kuantitas bahan yang disortir (${qtyAwal.toFixed(2)} KG).`);
            return false;
        }

        btnSubmit.disabled = true;
        btnSubmit.innerHTML = `
            <svg class="animate-spin" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <circle cx="12" cy="12" r="10" stroke-width="4" style="opacity: 0.25;"></circle>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M4 12a8 8 0 018-8v8H4z" style="opacity: 0.75;"></path>
            </svg>
            <span>Menyimpan Dokumen...</span>
        `;
    });

    // Auto load on initial render if warehouse is selected
    if (gudangSelect && gudangSelect.value) {
        loadBatchesForWarehouse();
    }
});

