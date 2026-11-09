/**
 * public/js/produksi/hpp/hpp-create.js
 * Logika Interaktif Formulir Kalkulasi Lembar HPP Produksi (Tahap 3 - Finance)
 * PT Mirasa Food Industry
 */

let currentProduksiData = null;
let bppbOriginalItems = [];
let bppbCurrentItems = [];
let isBppbUnlocked = false;

const setTxt = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
const setVal = (id, val) => { const el = document.getElementById(id); if (el) el.value = val; };

document.addEventListener('DOMContentLoaded', function () {
    const selectedId = window.appConfig?.selectedProduksiId;
    if (selectedId) {
        const sel = document.getElementById('produksi_id');
        if (sel) {
            sel.value = selectedId;
            loadProduksiData(selectedId);
        }
    } else {
        const sel = document.getElementById('produksi_id');
        if (sel && sel.value) {
            loadProduksiData(sel.value);
        } else {
            calcLaborFromOrg();
            calcAllHpp();
            fetchLiveJisdor(false);
        }
    }
});

/**
 * Event Listener saat dropdown dokumen produksi berubah
 */
function onProduksiSelectChange(produksiId) {
    if (!produksiId) {
        resetProduksiView();
        return;
    }
    loadProduksiData(produksiId);
}

/**
 * Reset tampilan jika pilihan dokumen dikosongkan
 */
function resetProduksiView() {
    currentProduksiData = null;

    // Tampilkan placeholder dan sembunyikan preview
    const placeholder = document.getElementById('spkPlaceholderNotice');
    if (placeholder) placeholder.style.display = 'flex';

    const preview = document.getElementById('previewFisikSection');
    if (preview) preview.style.display = 'none';

    // Reset data text
    setTxt('pvProduksiNo', '-');
    setTxt('pvTgl', '-');
    setTxt('pvShiftLini', '-');
    setTxt('pvBppb', '-');
    setTxt('pvSingkongQty', '0 kg');
    setTxt('pvWipQty', '0 kg');
    setTxt('pvRendemen', '0,00 %');

    // Reset Dokumen BPPB
    setTxt('lblBppbNo', '-');
    setTxt('lblBppbTgl', '-');
    setTxt('lblBppbGudang', '-');
    setTxt('lblBppbTotal', 'Rp 0');

    bppbOriginalItems = [];
    bppbCurrentItems = [];
    isBppbUnlocked = false;
    updateKoreksiButtonState();

    setTxt('dispSumBahanUtama', 'Rp 0');
    setTxt('dispSumKemasan', 'Rp 0');
    setTxt('dispSumTotalBahan', 'Rp 0');
    const stEl = document.getElementById('dispSumBppbStatus');
    if (stEl) {
        stEl.className = 'bppb-breakdown-status';
        stEl.textContent = '✓ Sesuai BPPB Gudang';
    }

    const tbodyPemakaian = document.getElementById('pemakaianTableBody');
    if (tbodyPemakaian) {
        tbodyPemakaian.innerHTML = `
            <tr>
                <td colspan="8" class="table-empty-row">
                    <div class="empty-info-msg">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Pilih dokumen produksi fisik pada Card 1 untuk menarik rincian barang dari dokumen pengeluaran gudang (BPPB).</span>
                    </div>
                </td>
            </tr>
        `;
    }
    const tfootPemakaian = document.getElementById('pemakaianTableFoot');
    if (tfootPemakaian) tfootPemakaian.style.display = 'none';

    // Reset Input Biaya Bahan (Hidden)
    setVal('singkong_nilai', 0);
    setVal('minyak_nilai', 0);
    setVal('bumbu_nilai', 0);
    setVal('karton_baru_nilai', 0);
    setVal('karton_bekas_nilai', 0);
    setVal('plastik_hd_nilai', 0);
    setVal('lakban_besar_nilai', 0);
    setVal('lakban_kecil_nilai', 0);
    setVal('tali_rafia_nilai', 0);

    // Reset Gas & Tenaga Kerja
    document.getElementById('cng_mmbtu').value = 0;
    document.getElementById('cng_nilai').value = 0;
    document.getElementById('tk_jumlah_org').value = 0;
    document.getElementById('tk_total_nilai').value = 0;
    const dispCng = document.getElementById('disp_cng_nilai');
    if (dispCng) dispCng.textContent = '0';
    const dispTk = document.getElementById('disp_tk_total_nilai');
    if (dispTk) dispTk.textContent = '0';

    // Reset Tabel Alokasi
    const tbodyAlloc = document.getElementById('allocationTableBody');
    if (tbodyAlloc) {
        tbodyAlloc.innerHTML = `
            <tr>
                <td colspan="7" class="table-empty-row">
                    <div class="empty-info-msg">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Pilih dokumen produksi fisik pada Card 1 untuk memuat rincian alokasi per varian produk WIP.</span>
                    </div>
                </td>
            </tr>
        `;
    }

    calcAllHpp();
}

/**
 * Tampilkan status loading informatif saat penarikan data produksi
 */
function showLoadingState(spkText = '') {
    const sel = document.getElementById('produksi_id');
    if (sel) sel.disabled = true;

    const overlay = document.getElementById('hppLoadingOverlay');
    if (overlay) overlay.style.display = 'flex';

    const notice = document.getElementById('spkLoadingNotice');
    if (notice) notice.style.display = 'flex';

    const placeholder = document.getElementById('spkPlaceholderNotice');
    if (placeholder) placeholder.style.display = 'none';

    const preview = document.getElementById('previewFisikSection');
    if (preview) preview.style.display = 'none';

    if (spkText) {
        const titleEl = document.getElementById('overlayLoadingTitle');
        if (titleEl) titleEl.textContent = `Memuat Data: ${spkText}`;
        const spkTitle = document.getElementById('spkLoadingTitle');
        if (spkTitle) spkTitle.textContent = `Menarik Data: ${spkText}...`;
    }
}

/**
 * Sembunyikan status loading
 */
function hideLoadingState() {
    const sel = document.getElementById('produksi_id');
    if (sel) sel.disabled = false;

    const overlay = document.getElementById('hppLoadingOverlay');
    if (overlay) overlay.style.display = 'none';

    const notice = document.getElementById('spkLoadingNotice');
    if (notice) notice.style.display = 'none';
}

/**
 * Panggil AJAX untuk memuat data produksi fisik, barang terpakai BPPB, dan output WIP
 */
function loadProduksiData(produksiId) {
    const sel = document.getElementById('produksi_id');
    const selectedOpt = sel ? sel.options[sel.selectedIndex] : null;
    const spkNo = selectedOpt?.getAttribute('data-no') || selectedOpt?.textContent?.trim() || '';

    showLoadingState(spkNo);

    const badgeStatus = document.getElementById('pvStatusHppBadge');
    if (badgeStatus) {
        badgeStatus.className = 'badge-status-calc';
        badgeStatus.textContent = 'Memuat data...';
    }

    const url = `${window.appConfig.produksiDataUrl}/${produksiId}`;

    fetch(url, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(async res => {
        const json = await res.json().catch(() => null);
        if (!res.ok) {
            const msg = json?.message || `HTTP ${res.status}`;
            throw new Error(msg);
        }
        return json;
    })
    .then(res => {
        if (res.status !== 'success' || !res.data) {
            throw new Error(res.message || 'Format respon tidak valid');
        }

        currentProduksiData = res.data;
        renderPreviewFisik(currentProduksiData);
        renderPemakaianBahan(currentProduksiData.pemakaian_items, currentProduksiData.pemakaian_bppb);
        populateCostingForm(currentProduksiData);
        renderAllocationTable(currentProduksiData.outputs, currentProduksiData.hpp_per_kg);
        calcAllHpp();
        fetchLiveJisdor(false);
        syncLaborFromKaryawan(false);
    })
    .catch(err => {
        console.error('Gagal memuat data produksi:', err);
        if (badgeStatus) {
            badgeStatus.className = 'badge-status-calc';
            badgeStatus.textContent = 'Gagal Memuat';
        }
        alert('Gagal memuat rincian produksi: ' + err.message);
        const placeholder = document.getElementById('spkPlaceholderNotice');
        if (placeholder) placeholder.style.display = 'flex';
    })
    .finally(() => {
        hideLoadingState();
    });
}

/**
 * Helper terjemahan nama hari ke Bahasa Indonesia
 */
function getIndonesianDayName(dayStr) {
    if (!dayStr) return '';
    const map = {
        'sunday': 'Minggu',
        'monday': 'Senin',
        'tuesday': 'Selasa',
        'wednesday': 'Rabu',
        'thursday': 'Kamis',
        'friday': 'Jumat',
        'saturday': 'Sabtu',
        'minggu': 'Minggu',
        'senin': 'Senin',
        'selasa': 'Selasa',
        'rabu': 'Rabu',
        'kamis': 'Kamis',
        'jumat': 'Jumat',
        'sabtu': 'Sabtu'
    };
    return map[dayStr.trim().toLowerCase()] || dayStr;
}

/**
 * Render ringkasan fisik SPK terpilih ke dalam info card Card 1 (Format Standar Parameter ERP Mirasa)
 */
function renderPreviewFisik(data) {
    const placeholder = document.getElementById('spkPlaceholderNotice');
    if (placeholder) placeholder.style.display = 'none';

    const preview = document.getElementById('previewFisikSection');
    if (preview) preview.style.display = 'block';

    const setTxt = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
    setTxt('pvProduksiNo', data.produksi_no || '-');

    const hariIndo = getIndonesianDayName(data.hari_nm);
    const tglFmt = data.produksi_tgl_fmt ? `${hariIndo}, ${data.produksi_tgl_fmt}` : '-';
    setTxt('pvTgl', tglFmt);

    setTxt('pvShiftLini', `Shift ${data.shift_cd || 'A'} — ${data.lini_produksi || 'PRODUKSI MANUAL'}`);
    setTxt('pvBppb', data.pakai_no || '-');
    setTxt('pvSingkongQty', `${formatNum(data.singkong_qty)} kg`);
    setTxt('pvWipQty', `${formatNum(data.total_wip_qty)} kg`);
    setTxt('pvRendemen', `${formatNum(data.rendemen_persen)} %`);
}

/**
 * Render tabel rincian bahan yang digunakan dari dokumen pengeluaran gudang (BPPB)
 */
function renderPemakaianBahan(items, bppbHdr) {
    // Info Header BPPB
    if (bppbHdr) {
        setTxt('lblBppbNo', bppbHdr.pakai_no || '-');
        setTxt('lblBppbTgl', bppbHdr.pakai_tgl || '-');
        setTxt('lblBppbGudang', bppbHdr.gudang_nm || '-');
        setTxt('lblBppbTotal', `Rp ${formatRp(bppbHdr.total_nilai || 0)}`);
    } else {
        setTxt('lblBppbNo', '-');
        setTxt('lblBppbTgl', '-');
        setTxt('lblBppbGudang', '-');
        setTxt('lblBppbTotal', 'Rp 0');
    }

    bppbOriginalItems = JSON.parse(JSON.stringify(items || []));
    bppbCurrentItems = JSON.parse(JSON.stringify(items || []));
    isBppbUnlocked = false;

    renderPemakaianTableOnly();
    syncBppbToHiddenInputsAndSummary();
    updateKoreksiButtonState();
}

/**
 * Toggle kunci/buka mode koreksi nilai BPPB
 */
function toggleKoreksiBppb() {
    if (!bppbCurrentItems || bppbCurrentItems.length === 0) return;
    isBppbUnlocked = !isBppbUnlocked;
    updateKoreksiButtonState();
    renderPemakaianTableOnly();
}

/**
 * Update tampilan tombol Koreksi dan tombol Reset
 */
function updateKoreksiButtonState() {
    const btn = document.getElementById('btnToggleKoreksiBppb');
    const txt = document.getElementById('txtBtnKoreksiBppb');
    const svg = document.getElementById('svgLockBppb');
    const btnReset = document.getElementById('btnResetBppb');

    if (!btn || !txt) return;

    let isModified = false;
    bppbCurrentItems.forEach((it, idx) => {
        if (bppbOriginalItems[idx] && Math.abs((it.subtotal_nilai || 0) - (bppbOriginalItems[idx].subtotal_nilai || 0)) > 1) {
            isModified = true;
        }
    });

    if (btnReset) {
        btnReset.style.display = (isBppbUnlocked || isModified) ? 'inline-flex' : 'none';
    }

    if (isBppbUnlocked) {
        btn.classList.add('active');
        txt.textContent = 'Kunci';
        if (svg) {
            svg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>';
        }
    } else {
        btn.classList.remove('active');
        txt.textContent = 'Koreksi';
        if (svg) {
            svg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>';
        }
    }
}

/**
 * Kembalikan seluruh baris ke nilai asli BPPB Gudang
 */
function resetBppbToOriginal() {
    if (!bppbOriginalItems || bppbOriginalItems.length === 0) return;
    bppbCurrentItems = JSON.parse(JSON.stringify(bppbOriginalItems));
    syncBppbToHiddenInputsAndSummary();
    renderPemakaianTableOnly();
    updateKoreksiButtonState();
    calcAllHpp();
}

/**
 * Event listener saat subtotal nilai suatu baris BPPB diubah
 */
function onBppbSubtotalChange(idx, val) {
    if (!bppbCurrentItems[idx]) return;
    const rawNum = parseFloat(val) || 0;
    bppbCurrentItems[idx].subtotal_nilai = rawNum;
    bppbCurrentItems[idx].is_modified = true;

    const qty = parseFloat(bppbCurrentItems[idx].qty_keluar) || 0;
    if (qty > 0) {
        bppbCurrentItems[idx].harga_satuan = Math.round((rawNum / qty) * 100) / 100;
        const cellHarga = document.getElementById(`bppb_cell_harga_${idx}`);
        if (cellHarga) cellHarga.textContent = `Rp ${formatRp(bppbCurrentItems[idx].harga_satuan)}`;
    }

    syncBppbToHiddenInputsAndSummary();
    updateKoreksiButtonState();
    calcAllHpp();
}

/**
 * Render ulang isi tbody tabel pemakaian BPPB
 */
function renderPemakaianTableOnly() {
    const tbody = document.getElementById('pemakaianTableBody');
    const tfoot = document.getElementById('pemakaianTableFoot');
    if (!tbody) return;
    tbody.innerHTML = '';

    if (!bppbCurrentItems || bppbCurrentItems.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="table-empty-row">
                    <div class="empty-info-msg">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>Tidak ditemukan data rincian pemakaian bahan gudang (BPPB) yang terhubung dengan SPK ini.</span>
                    </div>
                </td>
            </tr>
        `;
        if (tfoot) tfoot.style.display = 'none';
        return;
    }

    let subtotalBahan = 0;
    bppbCurrentItems.forEach((item, idx) => {
        const itemVal = parseFloat(item.subtotal_nilai || 0);
        subtotalBahan += itemVal;

        const origVal = bppbOriginalItems[idx]?.subtotal_nilai || 0;
        const isModified = Math.abs(itemVal - origVal) > 1;

        let colSubtotalHtml = '';
        if (isBppbUnlocked) {
            colSubtotalHtml = `
                <div class="bppb-edit-wrap">
                    <input type="number" step="any" min="0" class="bppb-input-edit"
                           id="input_bppb_subtotal_${idx}"
                           value="${itemVal}"
                           oninput="onBppbSubtotalChange(${idx}, this.value)">
                </div>
            `;
        } else {
            colSubtotalHtml = `
                <div style="font-weight: 700; color: #0f172a; text-align: right;">Rp ${formatRp(itemVal)}</div>
                ${isModified ? `<div style="font-size: 0.675rem; color: #d97706; font-weight: 600; text-align: right;">(BPPB: Rp ${formatRp(origVal)})</div>` : ''}
            `;
        }

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${idx + 1}</td>
            <td>
                <strong>${item.barang_nm}</strong>
                <div style="font-size: 0.725rem; color: #64748b; font-family: monospace;">${item.barang_cd}</div>
            </td>
            <td>
                <span class="badge-jenis-barang">${item.kategori_bahan || '-'}</span>
            </td>
            <td>
                <span style="font-family: monospace; font-size: 0.775rem;">${item.batch_no || '-'}</span>
            </td>
            <td style="text-align: right; font-weight: 700;">${formatNum(item.qty_keluar)}</td>
            <td style="text-align: center;"><span style="font-size: 0.75rem; color: #475569;">${item.satuan_cd}</span></td>
            <td style="text-align: right;" id="bppb_cell_harga_${idx}">Rp ${formatRp(item.harga_satuan)}</td>
            <td style="text-align: right;">${colSubtotalHtml}</td>
        `;
        tbody.appendChild(tr);
    });

    if (tfoot) {
        tfoot.style.display = '';
        const footVal = document.getElementById('lblSubtotalPemakaian');
        if (footVal) footVal.textContent = `Rp ${formatRp(subtotalBahan)}`;
    }
}

/**
 * Sinkronkan nilai item BPPB ke hidden inputs dan kartu rekapitulasi ringkas
 */
function syncBppbToHiddenInputsAndSummary() {
    let sSingkong = 0;
    let sMinyak = 0;
    let sBumbu = 0;
    let sKartonBaru = 0;
    let sKartonBekas = 0;
    let sPlastikHd = 0;
    let sLakbanBesar = 0;
    let sLakbanKecil = 0;
    let sTaliRafia = 0;
    let totalBahan = 0;
    let isAnyModified = false;

    bppbCurrentItems.forEach((item, idx) => {
        const subtotal = parseFloat(item.subtotal_nilai) || 0;
        totalBahan += subtotal;
        if (bppbOriginalItems[idx] && Math.abs(subtotal - (bppbOriginalItems[idx].subtotal_nilai || 0)) > 1) {
            isAnyModified = true;
        }

        const nmUpper = (item.barang_nm || '').toUpperCase();
        const cdUpper = (item.barang_cd || '').toUpperCase();

        if (nmUpper.includes('SINGKONG') || cdUpper.startsWith('BB-SK') || cdUpper.includes('SNG')) {
            sSingkong += subtotal;
        } else if (nmUpper.includes('MINYAK') || cdUpper.includes('MSW') || cdUpper.includes('MKL')) {
            sMinyak += subtotal;
        } else if (nmUpper.includes('KARTON') && (nmUpper.includes('BEKAS') || cdUpper.includes('BKS'))) {
            sKartonBekas += subtotal;
        } else if (nmUpper.includes('KARTON')) {
            sKartonBaru += subtotal;
        } else if (nmUpper.includes('PLASTIK')) {
            sPlastikHd += subtotal;
        } else if (nmUpper.includes('LAKBAN') && nmUpper.includes('BESAR')) {
            sLakbanBesar += subtotal;
        } else if (nmUpper.includes('LAKBAN') && (nmUpper.includes('KECIL') || nmUpper.includes('BENING') || nmUpper.includes('COKLAT'))) {
            sLakbanKecil += subtotal;
        } else if (nmUpper.includes('RAFIA') || nmUpper.includes('TALI')) {
            sTaliRafia += subtotal;
        } else {
            sBumbu += subtotal;
        }
    });

    // Update Hidden Inputs
    setVal('singkong_nilai', sSingkong);
    setVal('minyak_nilai', sMinyak);
    setVal('bumbu_nilai', sBumbu);
    setVal('karton_baru_nilai', sKartonBaru);
    setVal('karton_bekas_nilai', sKartonBekas);
    setVal('plastik_hd_nilai', sPlastikHd);
    setVal('lakban_besar_nilai', sLakbanBesar);
    setVal('lakban_kecil_nilai', sLakbanKecil);
    setVal('tali_rafia_nilai', sTaliRafia);

    // Update Table Foot
    setTxt('lblSubtotalPemakaian', `Rp ${formatRp(totalBahan)}`);

    // Update Summary Cards
    const totalUtama = sSingkong + sMinyak + sBumbu;
    const totalKemasan = sKartonBaru + sKartonBekas + sPlastikHd + sLakbanBesar + sLakbanKecil + sTaliRafia;
    setTxt('dispSumBahanUtama', `Rp ${formatRp(totalUtama)}`);
    setTxt('dispSumKemasan', `Rp ${formatRp(totalKemasan)}`);
    setTxt('dispSumTotalBahan', `Rp ${formatRp(totalBahan)}`);

    const stEl = document.getElementById('dispSumBppbStatus');
    if (stEl) {
        if (isAnyModified) {
            stEl.className = 'bppb-breakdown-status status-modified';
            stEl.textContent = '✎ Disesuaikan Manual';
        } else {
            stEl.className = 'bppb-breakdown-status';
            stEl.textContent = '✓ Sesuai BPPB Gudang';
        }
    }
}

/**
 * Isi formulir biaya bahan, gas CNG, dan tenaga kerja
 */
function populateCostingForm(data) {
    // Hidden inputs & summaries are already populated by syncBppbToHiddenInputsAndSummary()
    if (!bppbCurrentItems || bppbCurrentItems.length === 0) {
        setVal('singkong_nilai', data.singkong_nilai || 0);
        setVal('minyak_nilai', data.minyak_nilai || 0);
        setVal('bumbu_nilai', data.bumbu_nilai || 0);
        setVal('karton_baru_nilai', data.karton_baru_nilai || 0);
        setVal('karton_bekas_nilai', data.karton_bekas_nilai || 0);
        setVal('plastik_hd_nilai', data.plastik_hd_nilai || 0);
        setVal('lakban_besar_nilai', data.lakban_besar_nilai || 0);
        setVal('lakban_kecil_nilai', data.lakban_kecil_nilai || 0);
        setVal('tali_rafia_nilai', data.tali_rafia_nilai || 0);
    }

    // Utilitas Gas CNG
    document.getElementById('cng_mmbtu').value = data.cng_mmbtu || 0;
    document.getElementById('cng_tarif_usd').value = data.cng_tarif_usd || window.appConfig.defaultCngTarifUsd || 14.50;
    document.getElementById('cng_kurs_jisdor').value = data.cng_kurs_jisdor || window.appConfig.defaultCngKursJisdor || 18138.00;
    document.getElementById('cng_tarif').value = data.cng_tarif || window.appConfig.defaultCngTarif || 263001.00;
    const cngNilai = data.cng_nilai || 0;
    document.getElementById('cng_nilai').value = cngNilai;
    const elDispCng = document.getElementById('disp_cng_nilai');
    if (elDispCng) elDispCng.textContent = formatRp(cngNilai);
    updateCngFormulaLabel(data.cng_mmbtu || 0, data.cng_tarif || window.appConfig.defaultCngTarif || 263001.00);

    // Tenaga Kerja Langsung Produksi (BTKL) dari Manajemen Karyawan
    const langsungOrg = parseInt(data.tk_langsung_org) || parseInt(data.tk_jumlah_org) || (data.labor_sync?.tk_langsung_org ?? 0);
    const langsungNilai = parseFloat(data.tk_langsung_nilai) || parseFloat(data.tk_total_nilai) || (data.labor_sync?.tk_langsung_nilai ?? 0);

    const elLangsungOrg = document.getElementById('tk_langsung_org');
    const elLangsungNilai = document.getElementById('tk_langsung_nilai');
    const elTidakOrg = document.getElementById('tk_tidak_langsung_org');
    const elTidakNilai = document.getElementById('tk_tidak_langsung_nilai');

    if (elLangsungOrg) elLangsungOrg.value = langsungOrg;
    if (elLangsungNilai) elLangsungNilai.value = langsungNilai;
    if (elTidakOrg) elTidakOrg.value = 0;
    if (elTidakNilai) elTidakNilai.value = 0;

    if (document.getElementById('lblLangsungGroup') && data.labor_sync?.tk_langsung_label) {
        document.getElementById('lblLangsungGroup').textContent = data.labor_sync.tk_langsung_label;
    }

    calcLaborSplit();

    const helpTk = document.getElementById('helpTkSync');
    if (helpTk) {
        if (data.labor_sync && data.labor_sync.is_synced && (data.labor_sync.tk_langsung_org > 0 || data.labor_sync.tk_langsung_nilai > 0)) {
            helpTk.innerHTML = `✓ Tersinkron dari Modul Absensi: <strong>${data.labor_sync.tk_langsung_org || 0} orang</strong> (Rp ${formatRp(data.labor_sync.tk_langsung_nilai || 0)})`;
            helpTk.style.color = '#059669';
        } else if (langsungOrg > 0 || langsungNilai > 0) {
            helpTk.innerHTML = `Tenaga Kerja Langsung: <strong>${langsungOrg} orang</strong> (Rp ${formatRp(langsungNilai)})`;
            helpTk.style.color = '#64748b';
        } else {
            helpTk.innerHTML = `Data absensi belum tercatat di Modul Karyawan untuk tanggal ini (nilai 0, dapat diisi manual).`;
            helpTk.style.color = '#64748b';
        }
    }

    updateMultiplierPreview();
}

/**
 * Render tabel alokasi HPP per jenis produk WIP
 */
function renderAllocationTable(outputs, defaultHppPerKg) {
    const tbody = document.getElementById('allocationTableBody');
    tbody.innerHTML = '';

    if (!outputs || outputs.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="table-empty-row">
                    <div class="empty-info-msg">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Tidak ada item output WIP pada SPK ini untuk dialokasikan.</span>
                    </div>
                </td>
            </tr>
        `;
        return;
    }

    // Jika belum difinalisasi oleh Finance, hitung rata-rata segar dari total bahan terbaru agar selalu 100% balance
    const isAlreadyFinalized = currentProduksiData && (
        parseFloat(currentProduksiData.cng_nilai || 0) > 0 || 
        parseFloat(currentProduksiData.tk_total_nilai || 0) > 0
    );

    const freshAvgHpp = (currentProduksiData && parseFloat(currentProduksiData.total_wip_qty || 0) > 0)
        ? Math.round(((parseFloat(currentProduksiData.total_bahan_nilai || currentProduksiData.total_biaya_produksi || 0)) / parseFloat(currentProduksiData.total_wip_qty)) * 100) / 100
        : defaultHppPerKg;

    outputs.forEach((item, idx) => {
        const bNmUpper = (item.barang_nm || '').toUpperCase();
        const bCdUpper = (item.barang_cd || '').toUpperCase();
        const kUpper = (item.kategori_output || '').toUpperCase();
        const gUpper = (item.grade_cd || '').toUpperCase();

        const isKw2 = gUpper === 'KW2' || 
                      bNmUpper.includes('BERKO') || bNmUpper.includes('LENGKET') || bNmUpper.includes('REMAHAN') ||
                      bCdUpper.includes('BRK') || bCdUpper.includes('LGT') || bCdUpper.includes('RMH') ||
                      kUpper.includes('BERKO') || kUpper.includes('LENGKET') || kUpper.includes('REMAHAN');

        const itemHpp = isAlreadyFinalized && item.hpp_satuan > 0 ? item.hpp_satuan : freshAvgHpp;
        const itemNilai = Math.round(item.qty_kg * itemHpp);

        const categoryLabel = item.kategori_output || (isKw2 ? 'WIP-BRK' : 'WIP-ASB');

        const tr = document.createElement('tr');
        tr.setAttribute('data-output-id', item.output_id);
        tr.setAttribute('data-qty-kg', item.qty_kg);
        tr.setAttribute('data-grade', isKw2 ? 'KW2' : 'KW1');
        tr.setAttribute('data-is-kw2', isKw2 ? '1' : '0');

        tr.innerHTML = `
            <td>${idx + 1}</td>
            <td>
                <strong>${item.barang_nm}</strong>
                <div style="font-size: 0.725rem; color: #64748b;">${item.barang_cd}</div>
            </td>
            <td>
                <span class="badge-grade ${isKw2 ? 'badge-kw2' : 'badge-kw1'}">
                    ${categoryLabel}
                </span>
            </td>
            <td>
                <span style="font-family: monospace; font-size: 0.775rem;">${item.batch_no || '-'}</span>
            </td>
            <td style="text-align: right; font-weight: 700;">
                ${formatNum(item.qty_kg)} kg
            </td>
            <td>
                <input type="number" step="any" min="0" 
                       name="output_hpp[${item.output_id}]" 
                       id="input_hpp_${item.output_id}" 
                       class="input-table-hpp" 
                       value="${itemHpp}" 
                       oninput="onOutputHppChange(${item.output_id})">
            </td>
            <td style="text-align: right; font-weight: 700; color: #0f172a;" id="lbl_nilai_${item.output_id}">
                Rp ${formatRp(itemNilai)}
            </td>
        `;
        tbody.appendChild(tr);
    });
}

/**
 * Kalkulasi CNG dari MMBTU
 */
function calcCngFromMmbtu() {
    const mmbtu = parseFloat(document.getElementById('cng_mmbtu').value) || 0;
    const tarif = parseFloat(document.getElementById('cng_tarif').value) || 0;
    const total = Math.round(mmbtu * tarif);

    document.getElementById('cng_nilai').value = total;
    const elDispCng = document.getElementById('disp_cng_nilai');
    if (elDispCng) elDispCng.textContent = formatRp(total);

    updateCngFormulaLabel(mmbtu, tarif);
    calcAllHpp();
}

/**
 * Kalkulasi CNG dari Tarif USD & Kurs JISDOR
 */
function calcCngFromTarif() {
    const usd = parseFloat(document.getElementById('cng_tarif_usd').value) || 0;
    const jisdor = parseFloat(document.getElementById('cng_kurs_jisdor').value) || 0;
    const mmbtu = parseFloat(document.getElementById('cng_mmbtu').value) || 0;

    const tarifRp = Math.round(usd * jisdor * 100) / 100;
    document.getElementById('cng_tarif').value = tarifRp;

    const total = Math.round(mmbtu * tarifRp);
    document.getElementById('cng_nilai').value = total;
    const elDispCng = document.getElementById('disp_cng_nilai');
    if (elDispCng) elDispCng.textContent = formatRp(total);

    updateCngFormulaLabel(mmbtu, tarifRp);
    calcAllHpp();
}

function updateCngFormulaLabel(mmbtu, tarifRp) {
    const lbl = document.getElementById('lblCngFormula');
    if (lbl) {
        lbl.textContent = `${formatNum(mmbtu)} MMBTU × Rp ${formatRp(tarifRp)}`;
    }
}

/**
 * Kalkulasi Tenaga Kerja Langsung (BTKL Produksi)
 */
function calcLaborSplit() {
    const langsungOrg = parseInt(document.getElementById('tk_langsung_org')?.value) || 0;
    const langsungNilai = parseFloat(document.getElementById('tk_langsung_nilai')?.value) || 0;

    const elJml = document.getElementById('tk_jumlah_org');
    const elTotal = document.getElementById('tk_total_nilai');
    const elTarif = document.getElementById('tk_tarif_per_org');
    const elTidakOrg = document.getElementById('tk_tidak_langsung_org');
    const elTidakNilai = document.getElementById('tk_tidak_langsung_nilai');

    const tarifRata = langsungOrg > 0 ? Math.round(langsungNilai / langsungOrg) : 0;

    if (elJml) elJml.value = langsungOrg;
    if (elTotal) elTotal.value = langsungNilai;
    if (elTarif) elTarif.value = tarifRata;
    if (elTidakOrg) elTidakOrg.value = 0;
    if (elTidakNilai) elTidakNilai.value = 0;

    const elDispTk = document.getElementById('disp_tk_total_nilai');
    if (elDispTk) elDispTk.textContent = formatRp(langsungNilai);

    updateLaborFormulaLabel(langsungOrg, langsungNilai);
    calcAllHpp();
}

function calcLaborFromOrg() {
    calcLaborSplit();
}

function calcLaborBreakdown() {
    calcLaborSplit();
}

function updateLaborFormulaLabel(langsungOrg, langsungNilai) {
    const lbl = document.getElementById('lblTkFormula');
    if (lbl) {
        if (langsungOrg > 0 || langsungNilai > 0) {
            lbl.textContent = `${langsungOrg} Orang Produksi`;
        } else {
            lbl.textContent = `0 Orang Produksi`;
        }
    }
}

/**
 * Sinkronisasi Data Tenaga Kerja Langsung dari Modul Absensi Manajemen Karyawan
 * @param {boolean} isManualClick - jika true (diklik user) tampilkan dialog feedback; jika false (auto sync), senyap.
 */
function syncLaborFromKaryawan(isManualClick = false) {
    const tgl = currentProduksiData?.produksi_tgl || '';
    const lini = currentProduksiData?.lini_produksi || '';
    const btn = document.querySelector('.btn-sync-tk');
    if (btn && isManualClick) btn.disabled = true;

    if (!window.appConfig?.syncKaryawanUrl) {
        if (btn && isManualClick) btn.disabled = false;
        return;
    }

    const url = `${window.appConfig.syncKaryawanUrl}?tgl=${encodeURIComponent(tgl)}&lini=${encodeURIComponent(lini)}`;

    fetch(url, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(res => {
        if (res.status === 'success' && res.data) {
            const data = res.data;
            const help = document.getElementById('helpTkSync');

            if (data.is_synced && (data.tk_langsung_org > 0 || data.tk_langsung_nilai > 0 || data.tk_total_nilai > 0)) {
                const org = data.tk_langsung_org || data.tk_jumlah_org || 0;
                const nilai = data.tk_langsung_nilai || data.tk_total_nilai || 0;

                if (document.getElementById('tk_langsung_org')) {
                    document.getElementById('tk_langsung_org').value = org;
                }
                if (document.getElementById('tk_langsung_nilai')) {
                    document.getElementById('tk_langsung_nilai').value = nilai;
                }
                if (document.getElementById('tk_tidak_langsung_org')) {
                    document.getElementById('tk_tidak_langsung_org').value = 0;
                }
                if (document.getElementById('tk_tidak_langsung_nilai')) {
                    document.getElementById('tk_tidak_langsung_nilai').value = 0;
                }
                if (document.getElementById('lblLangsungGroup') && data.tk_langsung_label) {
                    document.getElementById('lblLangsungGroup').textContent = data.tk_langsung_label;
                }

                calcLaborSplit();

                if (help) {
                    help.innerHTML = `✓ Tersinkron dari Modul Absensi: <strong>${org} orang</strong> (Rp ${formatRp(nilai)})`;
                    help.style.color = '#059669';
                }

                if (isManualClick) {
                    alert(`✅ Data presensi berhasil ditarik dari Manajemen Karyawan:\n• Karyawan Produksi Langsung: ${org} orang\n• Total Biaya BTKL: Rp ${formatRp(nilai)}\n\n(Catatan: Karyawan packaging & operasional tidak langsung dihitung terpisah).`);
                }
            } else {
                // Sesuai instruksi: Jika data absensi kosong / belum tercatat, biarkan 0 (jangan diisi estimasi)
                if (document.getElementById('tk_langsung_org')) document.getElementById('tk_langsung_org').value = 0;
                if (document.getElementById('tk_langsung_nilai')) document.getElementById('tk_langsung_nilai').value = 0;
                if (document.getElementById('tk_tidak_langsung_org')) document.getElementById('tk_tidak_langsung_org').value = 0;
                if (document.getElementById('tk_tidak_langsung_nilai')) document.getElementById('tk_tidak_langsung_nilai').value = 0;

                calcLaborSplit();

                if (help) {
                    help.innerHTML = `Data absensi belum tercatat di Modul Karyawan untuk tanggal ini (nilai dibiarkan 0, dapat diisi manual).`;
                    help.style.color = '#64748b';
                }
                if (isManualClick) {
                    alert(`Informasi Absensi:\n${data.message || 'Data presensi karyawan belum tercatat untuk tanggal ini. Nilai dibiarkan 0.'}`);
                }
            }

            calcAllHpp();
        }
    })
    .catch(err => {
        console.warn('Gagal sync karyawan:', err);
        calcLaborSplit();
        if (isManualClick) {
            alert('Terjadi kendala saat memeriksa data absensi: ' + err.message);
        }
    })
    .finally(() => {
        if (btn && isManualClick) btn.disabled = false;
    });
}

/**
 * Sinkronisasi Kurs JISDOR Bank Indonesia
 * @param {boolean} isManualClick - jika true (diklik user) tampilkan dialog feedback; jika false (auto sync), senyap.
 */
function fetchLiveJisdor(isManualClick = false) {
    const btn = document.querySelector('.btn-sync-kurs');
    if (btn && isManualClick) btn.disabled = true;

    const tgl = currentProduksiData?.produksi_tgl || '';
    const url = window.appConfig.jisdorUrl + (tgl ? `?date=${encodeURIComponent(tgl)}&tgl=${encodeURIComponent(tgl)}` : '');

    fetch(url, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(res => {
        const rate = res.data?.rate || res.rate || res.kurs;
        const dateStr = res.data?.date || res.tanggal_acuan || res.date || '';

        if (rate && rate > 0) {
            document.getElementById('cng_kurs_jisdor').value = rate;
            calcCngFromTarif();

            const help = document.getElementById('helpKursJisdor');
            if (help) {
                help.innerHTML = `Referensi resmi BI: <strong>Rp ${formatRp(rate)}</strong> (${dateStr})`;
            }

            if (isManualClick) {
                alert(`✅ Kurs JISDOR Bank Indonesia berhasil disinkronkan:\nRp ${formatRp(rate)} (Acuan BI: ${dateStr})`);
            }
        } else {
            if (isManualClick) {
                alert('Gagal mengambil kurs JISDOR Bank Indonesia terkini.');
            }
        }
    })
    .catch(err => {
        if (isManualClick) {
            alert('Terjadi kesalahan jaringan saat sync JISDOR: ' + err.message);
        }
    })
    .finally(() => {
        if (btn && isManualClick) btn.disabled = false;
    });
}

/**
 * Hitung Ulang Seluruh Komponen HPP (Bahan + Gas + TK + WIP)
 */
function calcAllHpp() {
    // 1. Hitung Bahan Kemasan
    const kBaru = parseFloat(document.getElementById('karton_baru_nilai')?.value) || 0;
    const kBekas = parseFloat(document.getElementById('karton_bekas_nilai')?.value) || 0;
    const pHd = parseFloat(document.getElementById('plastik_hd_nilai')?.value) || 0;
    const lBesar = parseFloat(document.getElementById('lakban_besar_nilai')?.value) || 0;
    const lKecil = parseFloat(document.getElementById('lakban_kecil_nilai')?.value) || 0;
    const tRafia = parseFloat(document.getElementById('tali_rafia_nilai')?.value) || 0;
    const totalKemasan = kBaru + kBekas + pHd + lBesar + lKecil + tRafia;

    const badgeKemasan = document.getElementById('badgeTotalKemasan');
    if (badgeKemasan) badgeKemasan.textContent = `Rp ${formatRp(totalKemasan)}`;

    // 2. Hitung Bahan Baku Utama
    const singkong = parseFloat(document.getElementById('singkong_nilai')?.value) || 0;
    const minyak = parseFloat(document.getElementById('minyak_nilai')?.value) || 0;
    const bumbu = parseFloat(document.getElementById('bumbu_nilai')?.value) || 0;
    const totalBahan = singkong + minyak + bumbu + totalKemasan;

    // 3. Energi Gas CNG & Tenaga Kerja
    const cngNilai = parseFloat(document.getElementById('cng_nilai')?.value) || 0;
    const cngMmbtu = parseFloat(document.getElementById('cng_mmbtu')?.value) || 0;
    const tkNilai = parseFloat(document.getElementById('tk_total_nilai')?.value) || 0;
    const tkOrg = parseInt(document.getElementById('tk_jumlah_org')?.value) || 0;

    const elDispCng = document.getElementById('disp_cng_nilai');
    if (elDispCng) elDispCng.textContent = formatRp(cngNilai);
    const elDispTk = document.getElementById('disp_tk_total_nilai');
    if (elDispTk) elDispTk.textContent = formatRp(tkNilai);

    // 4. Total Biaya Produksi Manufaktur Shift
    const totalBiayaProduksi = totalBahan + cngNilai + tkNilai;

    // 5. Total Output WIP & HPP Rata-rata
    const totalWipKg = currentProduksiData ? parseFloat(currentProduksiData.total_wip_qty || 0) : 0;
    const rendemenPersen = currentProduksiData ? parseFloat(currentProduksiData.rendemen_persen || 0) : 0;
    const hppRataRata = totalWipKg > 0 ? (totalBiayaProduksi / totalWipKg) : 0;

    // Update KPI Stat Boxes
    document.getElementById('statBahanNilai').textContent = `Rp ${formatRp(totalBahan)}`;
    document.getElementById('statCngNilai').textContent = `Rp ${formatRp(cngNilai)}`;
    document.getElementById('statCngMeta').textContent = `${formatNum(cngMmbtu)} MMBTU (JISDOR BI)`;
    document.getElementById('statTkNilai').textContent = `Rp ${formatRp(tkNilai)}`;
    document.getElementById('statTkMeta').textContent = `${tkOrg} Orang Regu Shift`;
    document.getElementById('statTotalBiaya').textContent = `Rp ${formatRp(totalBiayaProduksi)}`;
    document.getElementById('statTotalWipKg').textContent = `${formatNum(totalWipKg)} kg`;
    document.getElementById('statRendemenMeta').textContent = `Rendemen: ${formatNum(rendemenPersen)}%`;
    document.getElementById('statHppRataRata').textContent = `Rp ${formatRp(Math.round(hppRataRata))}/kg`;

    // 6. Recalculate Table Alokasi
    recalcAllocationTotals(totalBiayaProduksi);
    updateMultiplierPreview();
}

/**
 * Event Listener saat input HPP pada salah satu baris produk berubah
 */
function onOutputHppChange(outputId) {
    const input = document.getElementById(`input_hpp_${outputId}`);
    const tr = input?.closest('tr');
    if (!tr) return;

    const qtyKg = parseFloat(tr.getAttribute('data-qty-kg')) || 0;
    const rawVal = (input.value || '').trim();
    const itemHpp = rawVal === '' ? 0 : (parseFloat(rawVal) || 0);
    const itemNilai = Math.round(qtyKg * itemHpp);

    const lbl = document.getElementById(`lbl_nilai_${outputId}`);
    if (lbl) lbl.textContent = `Rp ${formatRp(itemNilai)}`;

    // Selalu hitung ulang total alokasi dan status seimbang (balance) di footer tanpa menimpa input baris lain
    const totalBiaya = getParsedTotalBiaya();
    recalcAllocationTotals(totalBiaya);
}

/**
 * Hitung Total Nilai Alokasi dari Tabel dan Bandingkan dengan Total Biaya Shift
 */
function recalcAllocationTotals(targetTotalBiaya) {
    const rows = document.querySelectorAll('#allocationTableBody tr[data-output-id]');
    let totalKg = 0;
    let totalNilaiAlokasi = 0;

    rows.forEach(tr => {
        const qty = parseFloat(tr.getAttribute('data-qty-kg')) || 0;
        const outId = tr.getAttribute('data-output-id');
        const input = document.getElementById(`input_hpp_${outId}`);
        const hpp = parseFloat(input?.value) || 0;

        totalKg += qty;
        totalNilaiAlokasi += Math.round(qty * hpp);
    });

    // Update Foot
    const footKg = document.getElementById('footTotalKg');
    const footNilai = document.getElementById('footTotalNilai');
    const footStatus = document.getElementById('footStatusMatch');

    if (footKg) footKg.textContent = `${formatNum(totalKg)} kg`;
    if (footNilai) footNilai.textContent = `Rp ${formatRp(totalNilaiAlokasi)}`;

    const selisih = totalNilaiAlokasi - targetTotalBiaya;
    const alertBox = document.getElementById('balanceAlertBox');
    const alertText = document.getElementById('balanceAlertText');

    if (alertBox && alertText && rows.length > 0 && targetTotalBiaya > 0) {
        alertBox.style.display = 'flex';

        if (Math.abs(selisih) <= 150) { // Toleransi pembulatan Rp 150
            alertBox.className = 'balance-status-alert alert-matched';
            alertText.textContent = `✓ Total alokasi produk WIP seimbang (Balance) dengan Total Biaya Manufaktur: Rp ${formatRp(targetTotalBiaya)}.`;
            if (footStatus) {
                footStatus.style.color = '#059669';
                footStatus.textContent = '✓ Balance (Seimbang)';
            }
        } else if (selisih > 0) {
            alertBox.className = 'balance-status-alert alert-over';
            alertText.textContent = `⚠ Total alokasi lebih tinggi Rp ${formatRp(selisih)} dibanding Total Biaya Produksi (Rp ${formatRp(targetTotalBiaya)}).`;
            if (footStatus) {
                footStatus.style.color = '#dc2626';
                footStatus.textContent = `+Rp ${formatRp(selisih)}`;
            }
        } else {
            alertBox.className = 'balance-status-alert alert-under';
            alertText.textContent = `⚠ Total alokasi kurang Rp ${formatRp(Math.abs(selisih))} dibanding Total Biaya Produksi (Rp ${formatRp(targetTotalBiaya)}).`;
            if (footStatus) {
                footStatus.style.color = '#d97706';
                footStatus.textContent = `-Rp ${formatRp(Math.abs(selisih))}`;
            }
        }
    } else if (alertBox) {
        alertBox.style.display = 'none';
    }
}

/**
 * Mode 1: Bagi Rata Sesuai Berat (Weighted Average)
 */
function resetModeBagiRata() {
    const totalBiaya = getParsedTotalBiaya();
    const totalWipKg = currentProduksiData ? parseFloat(currentProduksiData.total_wip_qty || 0) : 0;
    if (totalWipKg <= 0) return;

    const avgHpp = Math.round((totalBiaya / totalWipKg) * 100) / 100;
    const rows = document.querySelectorAll('#allocationTableBody tr[data-output-id]');

    rows.forEach(tr => {
        const outId = tr.getAttribute('data-output-id');
        const input = document.getElementById(`input_hpp_${outId}`);
        const qty = parseFloat(tr.getAttribute('data-qty-kg')) || 0;

        if (input) {
            input.value = avgHpp;
            const lbl = document.getElementById(`lbl_nilai_${outId}`);
            if (lbl) lbl.textContent = `Rp ${formatRp(Math.round(qty * avgHpp))}`;
        }
    });

    recalcAllocationTotals(totalBiaya);
}

/**
 * Preview nominal hasil perkalian faktor pengali x harga dasar singkong mentah
 */
function updateMultiplierPreview() {
    const sNilai = parseFloat(document.getElementById('singkong_nilai')?.value) || 0;
    const sQty = currentProduksiData ? parseFloat(currentProduksiData.singkong_qty || 0) : 0;
    const hargaSingkongKg = sQty > 0 ? (sNilai / sQty) : 4275;

    const factor = parseFloat(document.getElementById('factorMultiplier')?.value) || 2.5;
    const hasil = Math.round(hargaSingkongKg * factor);

    const lblHarga = document.getElementById('lblSingkongHargaKg');
    if (lblHarga) lblHarga.textContent = formatRp(Math.round(hargaSingkongKg));

    const lblHasil = document.getElementById('lblHasilMultiplier');
    if (lblHasil) lblHasil.textContent = formatRp(hasil);
}

/**
 * Metode 2: Diferensiasi Standar (Kali 2,5)
 * Sesuai metode standar sebelumnya: Rasio Bobot Mutu Produk Utama (KW 1) = 2.5, Produk Sampingan (KW 2) = 1.0
 */
function applyMetodeKaliDuaSetengah() {
    const totalBiaya = getParsedTotalBiaya();
    const rows = document.querySelectorAll('#allocationTableBody tr[data-output-id]');
    if (rows.length === 0) return;

    if (totalBiaya <= 0) {
        alert('Total Biaya Produksi masih Rp 0. Masukkan nilai pemakaian bahan terlebih dahulu.');
        return;
    }

    // Rasio Bobot Mutu: KW1 = 2.5, KW2 / BS / Reject / Remahan = 1.0
    let totalWeightedKg = 0;
    const items = [];

    rows.forEach(r => {
        const outId = r.getAttribute('data-output-id');
        const qtyKg = parseFloat(r.getAttribute('data-qty-kg')) || 0;
        const grade = (r.getAttribute('data-grade') || 'KW1').toUpperCase();
        const text = r.innerText.toUpperCase();

        const isKw2 = grade.includes('KW2') || grade.includes('BS') || grade.includes('REJECT') ||
                      text.includes('BERKO') || text.includes('LENGKET') || text.includes('REMAHAN') ||
                      grade.includes('BRK') || grade.includes('LGT') || grade.includes('RMH');

        const weightFactor = isKw2 ? 1.0 : 2.5;

        totalWeightedKg += (qtyKg * weightFactor);
        items.push({ outId, qtyKg, weightFactor, isKw2 });
    });

    if (totalWeightedKg <= 0) return;

    // Unit dasar per bobot
    const baseCostPerWeightedUnit = totalBiaya / totalWeightedKg;

    items.forEach(it => {
        const hppPerKg = Math.round(baseCostPerWeightedUnit * it.weightFactor * 100) / 100;
        const input = document.getElementById(`input_hpp_${it.outId}`);
        if (input) {
            input.value = hppPerKg;
            const itemNilai = Math.round(it.qtyKg * hppPerKg);
            const lbl = document.getElementById(`lbl_nilai_${it.outId}`);
            if (lbl) lbl.textContent = `Rp ${formatRp(itemNilai)}`;
        }
    });

    recalcAllocationTotals(totalBiaya);
}

/**
 * Metode 3: Alokasi Pakai Sisa ke Produk Utama (Residual Costing)
 * Produk utama diambilkan dari sisa total biaya shift setelah penentuan HPP produk KW 2
 */
function applyMetodePakaiSisa() {
    const totalBiaya = getParsedTotalBiaya();
    const rows = Array.from(document.querySelectorAll('#allocationTableBody tr[data-output-id]'));
    if (rows.length === 0 || totalBiaya <= 0) {
        alert('Total Biaya Produksi masih Rp 0. Masukkan nilai pemakaian bahan / SPK terlebih dahulu.');
        return;
    }

    // Ambil harga beli singkong mentah per kg (sebagai acuan jika KW2 masih kosong)
    const sNilai = parseFloat(document.getElementById('singkong_nilai')?.value) || 0;
    const sQty = currentProduksiData ? parseFloat(currentProduksiData.singkong_qty || 0) : 0;
    const hargaSingkongKg = sQty > 0 ? (sNilai / sQty) : 4275;

    let totalKw2Nilai = 0;
    let utamaKg = 0;
    const utamaRows = [];

    rows.forEach(tr => {
        const grade = (tr.getAttribute('data-grade') || '').toUpperCase();
        const text = tr.innerText.toUpperCase();
        const qty = parseFloat(tr.getAttribute('data-qty-kg')) || 0;
        const outId = tr.getAttribute('data-output-id');
        const input = document.getElementById(`input_hpp_${outId}`);

        const isKw2 = grade.includes('KW2') || grade.includes('BS') || grade.includes('REJECT') ||
                      text.includes('BERKO') || text.includes('LENGKET') || text.includes('REMAHAN') ||
                      grade.includes('BRK') || grade.includes('LGT') || grade.includes('RMH');

        if (isKw2) {
            // Nilai HPP dibaca apa adanya dari input (bisa 0 jika di-nol-kan pengguna)
            const currentHpp = input ? (parseFloat(input.value) || 0) : 0;
            const itemNilai = Math.round(qty * currentHpp);
            const lbl = document.getElementById(`lbl_nilai_${outId}`);
            if (lbl) lbl.textContent = `Rp ${formatRp(itemNilai)}`;
            totalKw2Nilai += itemNilai;
        } else {
            utamaKg += qty;
            utamaRows.push(tr);
        }
    });

    if (utamaRows.length > 0 && utamaKg > 0) {
        const sisaBiayaUtama = totalBiaya - totalKw2Nilai;
        const utamaHpp = Math.round((sisaBiayaUtama / utamaKg) * 100) / 100;

        utamaRows.forEach(tr => {
            const outId = tr.getAttribute('data-output-id');
            const input = document.getElementById(`input_hpp_${outId}`);
            const qty = parseFloat(tr.getAttribute('data-qty-kg')) || 0;
            if (input) {
                input.value = utamaHpp;
                const itemNilai = Math.round(qty * utamaHpp);
                const lbl = document.getElementById(`lbl_nilai_${outId}`);
                if (lbl) lbl.textContent = `Rp ${formatRp(itemNilai)}`;
            }
        });
    }

    recalcAllocationTotals(totalBiaya);
}

// Alias untuk kompatibilitas fungsi lama
function applyStandarRasio() {
    applyMetodeKaliDuaSetengah();
}

function balancePrimaryFromRemainder() {
    applyMetodePakaiSisa();
}

/**
 * Terapkan faktor pengali khusus ke satu baris produk KW 2 (misal 2.5x harga singkong)
 * dan otomatis seimbangkan sisa biaya ke Produk Utama
 */
function applyMultiplierToSingleRow(outputId, factor = 2.5) {
    const sNilai = parseFloat(document.getElementById('singkong_nilai')?.value) || 0;
    const sQty = currentProduksiData ? parseFloat(currentProduksiData.singkong_qty || 0) : 0;
    const hargaSingkongKg = sQty > 0 ? (sNilai / sQty) : 4275;

    const targetHpp = Math.round(hargaSingkongKg * factor * 100) / 100;
    const input = document.getElementById(`input_hpp_${outputId}`);
    if (input) {
        input.value = targetHpp;
        const tr = input.closest('tr');
        const qty = parseFloat(tr?.getAttribute('data-qty-kg')) || 0;
        const lbl = document.getElementById(`lbl_nilai_${outputId}`);
        if (lbl) lbl.textContent = `Rp ${formatRp(Math.round(qty * targetHpp))}`;
    }

    // Otomatis seimbangkan sisa ke Produk Utama
    balancePrimaryFromRemainder();
}

/**
 * Terapkan Rasio Mutu: HPP Produk Utama = (factor) × HPP Produk Sampingan (KW 2 / Berko / Lengket / Remahan)
 * Menghitung secara proporsional berbobot sehingga total alokasi 100% klop (Balance) dengan Total Biaya Shift
 */
function applyRasioProdukUtama() {
    const totalBiaya = getParsedTotalBiaya();
    const rows = Array.from(document.querySelectorAll('#allocationTableBody tr[data-output-id]'));
    if (rows.length === 0 || totalBiaya <= 0) {
        alert('Total Biaya Produksi masih Rp 0. Masukkan nilai pemakaian bahan / SPK terlebih dahulu.');
        return;
    }

    const factor = parseFloat(document.getElementById('factorMultiplier')?.value) || 2.5;
    const targetSelect = document.getElementById('targetMultiplierRow')?.value || 'all_kw2';

    const classifiedItems = [];

    rows.forEach(tr => {
        const grade = (tr.getAttribute('data-grade') || '').toUpperCase();
        const text = tr.innerText.toUpperCase();
        const qty = parseFloat(tr.getAttribute('data-qty-kg')) || 0;
        const outId = tr.getAttribute('data-output-id');
        const input = document.getElementById(`input_hpp_${outId}`);

        const isBerkoLengket = grade.includes('LENGKET') || text.includes('LENGKET') || grade.includes('LGT');
        const isRemahan = grade.includes('REMAHAN') || text.includes('REMAHAN') || grade.includes('RMH');
        const isBerkoBiasa = (grade.includes('BERKO') || text.includes('BERKO') || grade.includes('BRK')) && !isBerkoLengket;
        const isKw2 = isBerkoLengket || isRemahan || isBerkoBiasa || grade.includes('KW2') || grade.includes('BS');

        classifiedItems.push({
            tr,
            outId,
            qty,
            input,
            isKw2,
            isBerkoBiasa,
            isBerkoLengket,
            isRemahan
        });
    });

    const utamaItems = classifiedItems.filter(it => !it.isKw2);
    const kw2Items = classifiedItems.filter(it => it.isKw2);

    if (utamaItems.length === 0) {
        alert('Tidak ditemukan baris Produk Utama pada tabel alokasi.');
        return;
    }

    const totalUtamaKg = utamaItems.reduce((acc, it) => acc + it.qty, 0);

    if (targetSelect === 'all_kw2') {
        // Semua produk KW 2 berbobot 1.0, Produk Utama berbobot factor (misal 2.5)
        const totalKw2Kg = kw2Items.reduce((acc, it) => acc + it.qty, 0);
        const totalWeighted = (totalUtamaKg * factor) + totalKw2Kg;
        if (totalWeighted <= 0) return;

        const baseUnitCost = totalBiaya / totalWeighted;
        const kw2Hpp = Math.round(baseUnitCost * 100) / 100;

        let totalKw2Nilai = 0;
        kw2Items.forEach(it => {
            if (it.input) {
                it.input.value = kw2Hpp;
                const itemNilai = Math.round(it.qty * kw2Hpp);
                const lbl = document.getElementById(`lbl_nilai_${it.outId}`);
                if (lbl) lbl.textContent = `Rp ${formatRp(itemNilai)}`;
                totalKw2Nilai += itemNilai;
            }
        });

        // Bebankan sisa biaya ke Produk Utama agar 100% klop tanpa selisih pembulatan
        const sisaBiayaUtama = Math.max(0, totalBiaya - totalKw2Nilai);
        const utamaHpp = totalUtamaKg > 0 ? Math.round((sisaBiayaUtama / totalUtamaKg) * 100) / 100 : 0;

        utamaItems.forEach(it => {
            if (it.input) {
                it.input.value = utamaHpp;
                const itemNilai = Math.round(it.qty * utamaHpp);
                const lbl = document.getElementById(`lbl_nilai_${it.outId}`);
                if (lbl) lbl.textContent = `Rp ${formatRp(itemNilai)}`;
            }
        });
    } else {
        // Target spesifik (berko_only / lengket_only / remahan_only)
        let targetKw2Items = [];
        let otherKw2Items = [];

        kw2Items.forEach(it => {
            let isTarget = false;
            if (targetSelect === 'berko_only' && it.isBerkoBiasa) isTarget = true;
            else if (targetSelect === 'lengket_only' && it.isBerkoLengket) isTarget = true;
            else if (targetSelect === 'remahan_only' && it.isRemahan) isTarget = true;

            if (isTarget) {
                targetKw2Items.push(it);
            } else {
                otherKw2Items.push(it);
            }
        });

        if (targetKw2Items.length === 0) {
            targetKw2Items = kw2Items;
            otherKw2Items = [];
        }

        const targetKg = targetKw2Items.reduce((acc, it) => acc + it.qty, 0);

        // Pertahankan nilai nominal pada baris KW 2 lainnya jika sudah ada
        let otherKw2Nilai = 0;
        otherKw2Items.forEach(it => {
            const curHpp = parseFloat(it.input?.value) || 0;
            const itemNilai = Math.round(it.qty * curHpp);
            otherKw2Nilai += itemNilai;
            const lbl = document.getElementById(`lbl_nilai_${it.outId}`);
            if (lbl) lbl.textContent = `Rp ${formatRp(itemNilai)}`;
        });

        const sisaBiayaUntukTargetDanUtama = Math.max(0, totalBiaya - otherKw2Nilai);
        const weightedKg = (totalUtamaKg * factor) + targetKg;
        if (weightedKg <= 0) return;

        const baseTargetCost = sisaBiayaUntukTargetDanUtama / weightedKg;
        const targetHpp = Math.round(baseTargetCost * 100) / 100;

        let totalTargetNilai = 0;
        targetKw2Items.forEach(it => {
            if (it.input) {
                it.input.value = targetHpp;
                const itemNilai = Math.round(it.qty * targetHpp);
                const lbl = document.getElementById(`lbl_nilai_${it.outId}`);
                if (lbl) lbl.textContent = `Rp ${formatRp(itemNilai)}`;
                totalTargetNilai += itemNilai;
            }
        });

        const sisaBiayaUtama = Math.max(0, sisaBiayaUntukTargetDanUtama - totalTargetNilai);
        const utamaHpp = totalUtamaKg > 0 ? Math.round((sisaBiayaUtama / totalUtamaKg) * 100) / 100 : 0;

        utamaItems.forEach(it => {
            if (it.input) {
                it.input.value = utamaHpp;
                const itemNilai = Math.round(it.qty * utamaHpp);
                const lbl = document.getElementById(`lbl_nilai_${it.outId}`);
                if (lbl) lbl.textContent = `Rp ${formatRp(itemNilai)}`;
            }
        });
    }

    recalcAllocationTotals(totalBiaya);
}

function applyMultiplierSelectedAndBalance() {
    applyRasioProdukUtama();
}

/**
 * Terapkan faktor pengali (2,5x harga singkong) ke seluruh produk KW 2
 * dan langsung bebankan sisa total biaya shift ke Produk Utama (100% Klop)
 */
function applyMultiplierToAllKw2AndBalance(factor = 2.5) {
    const totalBiaya = getParsedTotalBiaya();
    const rows = Array.from(document.querySelectorAll('#allocationTableBody tr[data-output-id]'));
    if (rows.length === 0 || totalBiaya <= 0) return;

    const sNilai = parseFloat(document.getElementById('singkong_nilai')?.value) || 0;
    const sQty = currentProduksiData ? parseFloat(currentProduksiData.singkong_qty || 0) : 0;
    const hargaSingkongKg = sQty > 0 ? (sNilai / sQty) : 4275;

    const targetHppKw2 = Math.round(hargaSingkongKg * factor * 100) / 100;

    let totalKw2Nilai = 0;
    let utamaKg = 0;
    const utamaRows = [];

    rows.forEach(tr => {
        const grade = (tr.getAttribute('data-grade') || '').toUpperCase();
        const text = tr.innerText.toUpperCase();
        const qty = parseFloat(tr.getAttribute('data-qty-kg')) || 0;
        const outId = tr.getAttribute('data-output-id');
        const input = document.getElementById(`input_hpp_${outId}`);

        const isKw2 = grade.includes('KW2') || grade.includes('BS') || grade.includes('REJECT') ||
                      text.includes('BERKO') || text.includes('LENGKET') || text.includes('REMAHAN') ||
                      grade.includes('BRK') || grade.includes('LGT') || grade.includes('RMH');

        if (isKw2 && input) {
            input.value = targetHppKw2;
            const itemNilai = Math.round(qty * targetHppKw2);
            const lbl = document.getElementById(`lbl_nilai_${outId}`);
            if (lbl) lbl.textContent = `Rp ${formatRp(itemNilai)}`;
            totalKw2Nilai += itemNilai;
        } else {
            utamaKg += qty;
            utamaRows.push(tr);
        }
    });

    if (utamaRows.length > 0 && utamaKg > 0) {
        const sisaBiayaUtama = Math.max(0, totalBiaya - totalKw2Nilai);
        const utamaHpp = Math.round((sisaBiayaUtama / utamaKg) * 100) / 100;

        utamaRows.forEach(tr => {
            const outId = tr.getAttribute('data-output-id');
            const input = document.getElementById(`input_hpp_${outId}`);
            const qty = parseFloat(tr.getAttribute('data-qty-kg')) || 0;
            if (input) {
                input.value = utamaHpp;
                const itemNilai = Math.round(qty * utamaHpp);
                const lbl = document.getElementById(`lbl_nilai_${outId}`);
                if (lbl) lbl.textContent = `Rp ${formatRp(itemNilai)}`;
            }
        });
    }

    recalcAllocationTotals(totalBiaya);
}

/**
 * Modal Metode Diferensiasi HPP (Standar Enterprise)
 */
function openModalMetodeAlokasi() {
    const totalBiaya = getParsedTotalBiaya();
    if (totalBiaya <= 0) {
        alert('Total Biaya Produksi masih Rp 0. Masukkan nilai pemakaian bahan / SPK terlebih dahulu.');
        return;
    }

    const modal = document.getElementById('modalMetodeAlokasi');
    if (!modal) return;

    // Ambil harga dasar singkong mentah
    const sNilai = parseFloat(document.getElementById('singkong_nilai')?.value) || 0;
    const sQty = currentProduksiData ? parseFloat(currentProduksiData.singkong_qty || 0) : 0;
    const hargaSingkongKg = sQty > 0 ? (sNilai / sQty) : 4275;

    const elDispTotal = document.getElementById('modalDispTotalBiaya');
    if (elDispTotal) elDispTotal.textContent = `Rp ${formatRp(totalBiaya)}`;

    const elDispHarga = document.getElementById('modalDispHargaSingkong');
    if (elDispHarga) elDispHarga.textContent = `Rp ${formatRp(Math.round(hargaSingkongKg))} / kg`;

    calcModalSimulation();
    modal.style.display = 'flex';
}

function closeModalMetodeAlokasi() {
    const modal = document.getElementById('modalMetodeAlokasi');
    if (modal) modal.style.display = 'none';
}

/**
 * Kalkulasi Live Simulasi Alokasi di Modal
 */
function calcModalSimulation() {
    const totalBiaya = getParsedTotalBiaya();
    const rows = Array.from(document.querySelectorAll('#allocationTableBody tr[data-output-id]'));
    if (rows.length === 0 || totalBiaya <= 0) return;

    // 1. Harga Singkong
    const sNilai = parseFloat(document.getElementById('singkong_nilai')?.value) || 0;
    const sQty = currentProduksiData ? parseFloat(currentProduksiData.singkong_qty || 0) : 0;
    const hargaSingkongKg = sQty > 0 ? (sNilai / sQty) : 4275;

    // 2. Faktor Pengali
    const factor = parseFloat(document.getElementById('modalFactorInput')?.value) || 2.5;
    const targetHppKw2 = Math.round(hargaSingkongKg * factor * 100) / 100;

    // 3. Target Baris
    const targetSelect = document.getElementById('modalTargetSelect')?.value || 'all_kw2';

    let totalKw2Kg = 0;
    let totalKw2Nilai = 0;
    let utamaKg = 0;
    let utamaNama = 'Produk Utama';

    rows.forEach(tr => {
        const grade = (tr.getAttribute('data-grade') || '').toUpperCase();
        const text = tr.innerText.toUpperCase();
        const qty = parseFloat(tr.getAttribute('data-qty-kg')) || 0;

        const isBerkoLengket = grade.includes('LENGKET') || text.includes('LENGKET') || grade.includes('LGT');
        const isRemahan = grade.includes('REMAHAN') || text.includes('REMAHAN') || grade.includes('RMH');
        const isBerkoBiasa = (grade.includes('BERKO') || text.includes('BERKO') || grade.includes('BRK')) && !isBerkoLengket;
        const isKw2 = isBerkoLengket || isRemahan || isBerkoBiasa;

        let isTargetKw2 = false;
        if (targetSelect === 'all_kw2' && isKw2) {
            isTargetKw2 = true;
        } else if (targetSelect === 'berko_only' && isBerkoBiasa) {
            isTargetKw2 = true;
        } else if (targetSelect === 'lengket_only' && isBerkoLengket) {
            isTargetKw2 = true;
        } else if (targetSelect === 'remahan_only' && isRemahan) {
            isTargetKw2 = true;
        }

        if (isTargetKw2) {
            totalKw2Kg += qty;
            totalKw2Nilai += Math.round(qty * targetHppKw2);
        } else if (isKw2) {
            const outId = tr.getAttribute('data-output-id');
            const currentHpp = parseFloat(document.getElementById(`input_hpp_${outId}`)?.value) || targetHppKw2;
            totalKw2Kg += qty;
            totalKw2Nilai += Math.round(qty * currentHpp);
        } else {
            utamaKg += qty;
            const bNama = tr.querySelector('strong')?.textContent || 'Produk Utama';
            utamaNama = bNama;
        }
    });

    const sisaBiayaUtama = Math.max(0, totalBiaya - totalKw2Nilai);
    const utamaHpp = utamaKg > 0 ? (sisaBiayaUtama / utamaKg) : 0;

    // Update Label Simulasi Modal
    const elKw2Hpp = document.getElementById('modalSimKw2Hpp');
    if (elKw2Hpp) elKw2Hpp.textContent = `Rp ${formatRp(Math.round(targetHppKw2))} / kg`;

    const elKw2Kg = document.getElementById('modalSimKw2Kg');
    if (elKw2Kg) elKw2Kg.textContent = formatNum(totalKw2Kg);

    const elKw2Nilai = document.getElementById('modalSimKw2Nilai');
    if (elKw2Nilai) elKw2Nilai.textContent = `Rp ${formatRp(totalKw2Nilai)}`;

    const elUtamaNilai = document.getElementById('modalSimUtamaNilai');
    if (elUtamaNilai) elUtamaNilai.textContent = `Rp ${formatRp(sisaBiayaUtama)}`;

    const elUtamaNama = document.getElementById('modalSimUtamaNama');
    if (elUtamaNama) elUtamaNama.textContent = `${utamaNama} (${formatNum(utamaKg)} kg)`;

    const elUtamaHpp = document.getElementById('modalSimUtamaHpp');
    if (elUtamaHpp) elUtamaHpp.textContent = `Rp ${formatNum(utamaHpp)} / kg`;
}

/**
 * Terapkan Hasil Simulasi Modal ke Input Tabel HPP
 */
function applyModalDiferensiasiToTable() {
    const totalBiaya = getParsedTotalBiaya();
    const rows = Array.from(document.querySelectorAll('#allocationTableBody tr[data-output-id]'));
    if (rows.length === 0 || totalBiaya <= 0) {
        closeModalMetodeAlokasi();
        return;
    }

    const sNilai = parseFloat(document.getElementById('singkong_nilai')?.value) || 0;
    const sQty = currentProduksiData ? parseFloat(currentProduksiData.singkong_qty || 0) : 0;
    const hargaSingkongKg = sQty > 0 ? (sNilai / sQty) : 4275;

    const factor = parseFloat(document.getElementById('modalFactorInput')?.value) || 2.5;
    const targetHppKw2 = Math.round(hargaSingkongKg * factor * 100) / 100;
    const targetSelect = document.getElementById('modalTargetSelect')?.value || 'all_kw2';

    let totalKw2Nilai = 0;
    let utamaKg = 0;
    const utamaRows = [];

    rows.forEach(tr => {
        const grade = (tr.getAttribute('data-grade') || '').toUpperCase();
        const text = tr.innerText.toUpperCase();
        const qty = parseFloat(tr.getAttribute('data-qty-kg')) || 0;
        const outId = tr.getAttribute('data-output-id');
        const input = document.getElementById(`input_hpp_${outId}`);

        const isBerkoLengket = grade.includes('LENGKET') || text.includes('LENGKET') || grade.includes('LGT');
        const isRemahan = grade.includes('REMAHAN') || text.includes('REMAHAN') || grade.includes('RMH');
        const isBerkoBiasa = (grade.includes('BERKO') || text.includes('BERKO') || grade.includes('BRK')) && !isBerkoLengket;
        const isKw2 = isBerkoLengket || isRemahan || isBerkoBiasa;

        let isTarget = false;
        if (targetSelect === 'all_kw2' && isKw2) isTarget = true;
        else if (targetSelect === 'berko_only' && isBerkoBiasa) isTarget = true;
        else if (targetSelect === 'lengket_only' && isBerkoLengket) isTarget = true;
        else if (targetSelect === 'remahan_only' && isRemahan) isTarget = true;

        if (isTarget && input) {
            input.value = targetHppKw2;
            const itemNilai = Math.round(qty * targetHppKw2);
            const lbl = document.getElementById(`lbl_nilai_${outId}`);
            if (lbl) lbl.textContent = `Rp ${formatRp(itemNilai)}`;
            totalKw2Nilai += itemNilai;
        } else if (isKw2 && input) {
            const currentHpp = parseFloat(input.value) || targetHppKw2;
            totalKw2Nilai += Math.round(qty * currentHpp);
        } else {
            utamaKg += qty;
            utamaRows.push(tr);
        }
    });

    if (utamaRows.length > 0 && utamaKg > 0) {
        const sisaBiayaUtama = totalBiaya - totalKw2Nilai;
        const utamaHpp = Math.round((sisaBiayaUtama / utamaKg) * 100) / 100;

        utamaRows.forEach(tr => {
            const outId = tr.getAttribute('data-output-id');
            const input = document.getElementById(`input_hpp_${outId}`);
            const qty = parseFloat(tr.getAttribute('data-qty-kg')) || 0;
            if (input) {
                input.value = utamaHpp;
                const itemNilai = Math.round(qty * utamaHpp);
                const lbl = document.getElementById(`lbl_nilai_${outId}`);
                if (lbl) lbl.textContent = `Rp ${formatRp(itemNilai)}`;
            }
        });
    }

    recalcAllocationTotals(totalBiaya);
    closeModalMetodeAlokasi();
}

// Alias untuk kompatibilitas
function applyDifferentiatedHpp() {
    openModalMetodeAlokasi();
}

function getParsedTotalBiaya() {
    const sBahan = parseFloat(document.getElementById('singkong_nilai')?.value) || 0;
    const sMinyak = parseFloat(document.getElementById('minyak_nilai')?.value) || 0;
    const sBumbu = parseFloat(document.getElementById('bumbu_nilai')?.value) || 0;
    const sKemasan = (parseFloat(document.getElementById('karton_baru_nilai')?.value) || 0) +
                     (parseFloat(document.getElementById('karton_bekas_nilai')?.value) || 0) +
                     (parseFloat(document.getElementById('plastik_hd_nilai')?.value) || 0) +
                     (parseFloat(document.getElementById('lakban_besar_nilai')?.value) || 0) +
                     (parseFloat(document.getElementById('lakban_kecil_nilai')?.value) || 0) +
                     (parseFloat(document.getElementById('tali_rafia_nilai')?.value) || 0);

    const sCng = parseFloat(document.getElementById('cng_nilai')?.value) || 0;
    const sTk = parseFloat(document.getElementById('tk_total_nilai')?.value) || 0;

    return sBahan + sMinyak + sBumbu + sKemasan + sCng + sTk;
}

function toggleKemasanSection() {
    const el = document.getElementById('kemasanInputsBody');
    if (el) {
        el.style.display = (el.style.display === 'none' || el.style.display === '') ? 'grid' : 'none';
    }
}

/**
 * Utility Helpers
 */
function formatRp(num) {
    const n = Math.round(parseFloat(num) || 0);
    return n.toLocaleString('id-ID');
}

function formatNum(num) {
    const n = parseFloat(num) || 0;
    return n.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
