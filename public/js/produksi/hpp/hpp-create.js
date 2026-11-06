/**
 * public/js/produksi/hpp/hpp-create.js
 * Logika Interaktif Formulir Kalkulasi Lembar HPP Produksi (Tahap 3 - Finance)
 * PT Mirasa Food Industry
 */

let currentProduksiData = null;

document.addEventListener('DOMContentLoaded', function () {
    const selectedId = window.appConfig?.selectedProduksiId;
    if (selectedId) {
        const sel = document.getElementById('produksi_id');
        if (sel) {
            sel.value = selectedId;
            loadProduksiData(selectedId);
        }
    }
});

function onProduksiSelectChange(produksiId) {
    if (!produksiId) {
        document.getElementById('previewFisikSection').style.display = 'none';
        document.getElementById('financeCostingContainer').style.display = 'none';
        currentProduksiData = null;
        return;
    }
    loadProduksiData(produksiId);
}

function loadProduksiData(produksiId) {
    const previewBox = document.getElementById('previewFisikSection');
    const costingContainer = document.getElementById('financeCostingContainer');
    const badgeStatus = document.getElementById('pvStatusHppBadge');

    previewBox.style.display = 'block';
    badgeStatus.className = 'badge-status-calc';
    badgeStatus.textContent = 'Memuat data...';

    const url = `${window.appConfig.produksiDataUrl}/${produksiId}`;

    fetch(url, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => {
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        return res.json();
    })
    .then(res => {
        if (res.status !== 'success' || !res.data) {
            throw new Error(res.message || 'Format respon tidak valid');
        }

        currentProduksiData = res.data;
        renderPreviewFisik(currentProduksiData);
        populateCostingForm(currentProduksiData);
        costingContainer.style.display = 'block';
        calcAllHpp();
    })
    .catch(err => {
        console.error('Gagal memuat data produksi:', err);
        badgeStatus.className = 'badge-status-calc';
        badgeStatus.textContent = 'Gagal Memuat';
        alert('Gagal memuat rincian produksi: ' + err.message);
    });
}

function renderPreviewFisik(data) {
    document.getElementById('pvProduksiNo').textContent = data.produksi_no || '-';
    document.getElementById('pvTgl').textContent = `${data.produksi_tgl_fmt || '-'} (${data.hari_nm || '-'})`;
    document.getElementById('pvShiftLini').textContent = `Shift ${data.shift_cd || '-'} | Lini ${data.lini_produksi || '-'}`;
    document.getElementById('pvGudang').textContent = data.gudang_nm || '-';
    document.getElementById('pvBppb').textContent = data.pakai_no || '-';
    document.getElementById('pvSingkongQty').textContent = `${formatNum(data.singkong_qty)} kg`;
    document.getElementById('pvWipQty').textContent = `${formatNum(data.total_wip_qty)} kg`;
    document.getElementById('pvRendemen').textContent = `${formatNum(data.rendemen_persen)} %`;
    document.getElementById('pvBatch').textContent = data.batch_wip_no || '-';

    const badgeStatus = document.getElementById('pvStatusHppBadge');
    if (data.total_biaya_produksi > 0) {
        badgeStatus.className = 'badge-status-calc done';
        badgeStatus.textContent = `HPP Sudah Ditetapkan: Rp ${formatRp(data.hpp_per_kg)}/kg`;
    } else {
        badgeStatus.className = 'badge-status-calc';
        badgeStatus.textContent = 'HPP Belum Ditetapkan (Draft Biaya Rp 0)';
    }

    // Render output fisik preview
    const tbody = document.getElementById('pvOutputsTableBody');
    tbody.innerHTML = '';

    if (!data.outputs || data.outputs.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" style="text-align: center; color: #64748b; padding: 1rem;">Tidak ada rincian output barang.</td></tr>`;
        return;
    }

    data.outputs.forEach((item, idx) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${idx + 1}</td>
            <td><strong>${item.barang_nm}</strong></td>
            <td><span style="font-weight: 600; color: #0284c7;">${item.kategori_output || item.grade_cd || '-'}</span></td>
            <td><span style="font-family: monospace; font-size: 0.775rem;">${item.batch_no || '-'}</span></td>
            <td style="text-align: right;">${formatNum(item.qty_hasil)} ${item.satuan_cd}</td>
            <td style="text-align: right; font-weight: 700;">${formatNum(item.qty_kg)} kg</td>
        `;
        tbody.appendChild(tr);
    });
}

function populateCostingForm(data) {
    // Bahan Baku
    document.getElementById('singkong_nilai').value = data.singkong_nilai || 0;
    document.getElementById('lblSingkongQty').textContent = `${formatNum(data.singkong_qty)} kg`;

    document.getElementById('minyak_nilai').value = data.minyak_nilai || 0;
    const totalMinyak = (data.minyak_sawit_qty || 0) + (data.minyak_kelapa_qty || 0);
    document.getElementById('lblMinyakQty').textContent = `${formatNum(totalMinyak)} kg`;

    document.getElementById('bumbu_nilai').value = data.bumbu_nilai || 0;
    document.getElementById('lblBumbuQty').textContent = `${formatNum(data.bumbu_qty)} kg`;

    // Kemasan
    document.getElementById('karton_baru_nilai').value = data.karton_baru_nilai || 0;
    document.getElementById('karton_bekas_nilai').value = data.karton_bekas_nilai || 0;
    document.getElementById('plastik_hd_nilai').value = data.plastik_hd_nilai || 0;
    document.getElementById('lakban_besar_nilai').value = data.lakban_besar_nilai || 0;
    document.getElementById('lakban_kecil_nilai').value = data.lakban_kecil_nilai || 0;
    document.getElementById('tali_rafia_nilai').value = data.tali_rafia_nilai || 0;

    // Utilitas CNG
    document.getElementById('cng_mmbtu').value = data.cng_mmbtu || 0;
    document.getElementById('cng_tarif_usd').value = data.cng_tarif_usd || window.appConfig.defaultCngTarifUsd || 14.50;
    document.getElementById('cng_kurs_jisdor').value = data.cng_kurs_jisdor || window.appConfig.defaultCngKursJisdor || 18138.00;
    document.getElementById('cng_tarif').value = data.cng_tarif || window.appConfig.defaultCngTarif || 263001.00;
    document.getElementById('cng_nilai').value = data.cng_nilai || 0;

    // Tenaga Kerja
    document.getElementById('tk_jumlah_org').value = data.tk_jumlah_org || 0;
    document.getElementById('tk_total_nilai').value = data.tk_total_nilai || 0;

    // Render Tabel Alokasi Output
    renderAllocationTable(data.outputs, data.hpp_per_kg);
}

function renderAllocationTable(outputs, defaultHppPerKg) {
    const tbody = document.getElementById('allocationTableBody');
    tbody.innerHTML = '';

    if (!outputs || outputs.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" style="text-align: center; color: #64748b; padding: 1.5rem;">Tidak ada item output untuk dialokasikan.</td></tr>`;
        return;
    }

    outputs.forEach((item, idx) => {
        const itemHpp = item.hpp_satuan > 0 ? item.hpp_satuan : (defaultHppPerKg > 0 ? defaultHppPerKg : 0);
        const itemNilai = Math.round(item.qty_kg * itemHpp);

        const tr = document.createElement('tr');
        tr.setAttribute('data-output-id', item.output_id);
        tr.setAttribute('data-qty-kg', item.qty_kg);
        tr.setAttribute('data-grade', item.grade_cd || item.kategori_output || 'KW1');

        tr.innerHTML = `
            <td>${idx + 1}</td>
            <td>
                <strong>${item.barang_nm}</strong>
                <div style="font-size: 0.725rem; color: #64748b;">${item.barang_cd}</div>
            </td>
            <td>
                <span class="badge-grade ${item.grade_cd === 'KW2' ? 'badge-kw2' : 'badge-kw1'}">
                    ${item.kategori_output || item.grade_cd || 'KW1'}
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

function calcCngFromMmbtu() {
    const mmbtu = parseFloat(document.getElementById('cng_mmbtu').value) || 0;
    const tarif = parseFloat(document.getElementById('cng_tarif').value) || 0;
    const total = Math.round(mmbtu * tarif);

    document.getElementById('cng_nilai').value = total;
    updateCngFormulaLabel(mmbtu, tarif);
    calcAllHpp();
}

function calcCngFromTarif() {
    const usd = parseFloat(document.getElementById('cng_tarif_usd').value) || 0;
    const jisdor = parseFloat(document.getElementById('cng_kurs_jisdor').value) || 0;
    const mmbtu = parseFloat(document.getElementById('cng_mmbtu').value) || 0;

    const tarifRp = Math.round(usd * jisdor * 100) / 100;
    document.getElementById('cng_tarif').value = tarifRp;

    const total = Math.round(mmbtu * tarifRp);
    document.getElementById('cng_nilai').value = total;

    updateCngFormulaLabel(mmbtu, tarifRp);
    calcAllHpp();
}

function updateCngFormulaLabel(mmbtu, tarifRp) {
    const lbl = document.getElementById('lblCngFormula');
    if (lbl) {
        lbl.textContent = `${formatNum(mmbtu)} MMBTU × Rp ${formatRp(tarifRp)} / MMBTU`;
    }
}

function fetchLiveJisdor() {
    const btn = event?.currentTarget;
    if (btn) btn.disabled = true;

    fetch(window.appConfig.jisdorUrl, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(res => {
        if (res.status === 'success' && res.data && res.data.rate) {
            document.getElementById('cng_kurs_jisdor').value = res.data.rate;
            calcCngFromTarif();
            alert(`✅ Kurs JISDOR berhasil diperbarui: Rp ${formatRp(res.data.rate)} (Tanggal: ${res.data.date})`);
        } else {
            alert('Gagal mengambil kurs JISDOR Bank Indonesia terkini.');
        }
    })
    .catch(err => {
        alert('Terjadi kesalahan jaringan saat sync JISDOR: ' + err.message);
    })
    .finally(() => {
        if (btn) btn.disabled = false;
    });
}

function calcTkFromOrg() {
    const org = parseInt(document.getElementById('tk_jumlah_org').value, 10) || 0;
    const currentNilai = parseFloat(document.getElementById('tk_total_nilai').value) || 0;

    // Jika upah masih 0, terapkan standar estimasi shift misal Rp 85.000 / org
    if (currentNilai <= 0 && org > 0) {
        const rateEst = 85000;
        document.getElementById('tk_total_nilai').value = org * rateEst;
    }
    calcAllHpp();
}

function toggleKemasanSection() {
    const body = document.getElementById('kemasanInputsBody');
    body.style.display = body.style.display === 'none' ? 'grid' : 'none';
}

function onOutputHppChange(outputId) {
    const row = document.querySelector(`tr[data-output-id="${outputId}"]`);
    if (!row) return;

    const qtyKg = parseFloat(row.getAttribute('data-qty-kg')) || 0;
    const inputHpp = document.getElementById(`input_hpp_${outputId}`);
    const hppVal = parseFloat(inputHpp?.value) || 0;
    const totalNilai = Math.round(qtyKg * hppVal);

    const lblNilai = document.getElementById(`lbl_nilai_${outputId}`);
    if (lblNilai) {
        lblNilai.textContent = `Rp ${formatRp(totalNilai)}`;
    }

    recalcAllocationTotals();
}

function calcAllHpp() {
    // 1. Biaya Bahan Baku
    const singkong = parseFloat(document.getElementById('singkong_nilai').value) || 0;
    const minyak = parseFloat(document.getElementById('minyak_nilai').value) || 0;
    const bumbu = parseFloat(document.getElementById('bumbu_nilai').value) || 0;

    // 2. Kemasan
    const kBaru = parseFloat(document.getElementById('karton_baru_nilai').value) || 0;
    const kBekas = parseFloat(document.getElementById('karton_bekas_nilai').value) || 0;
    const pHD = parseFloat(document.getElementById('plastik_hd_nilai').value) || 0;
    const lBesar = parseFloat(document.getElementById('lakban_besar_nilai').value) || 0;
    const lKecil = parseFloat(document.getElementById('lakban_kecil_nilai').value) || 0;
    const tRafia = parseFloat(document.getElementById('tali_rafia_nilai').value) || 0;
    const totalKemasan = kBaru + kBekas + pHD + lBesar + lKecil + tRafia;

    document.getElementById('badgeTotalKemasan').textContent = `Rp ${formatRp(totalKemasan)}`;

    // 3. Utilitas CNG
    const cngNilai = parseFloat(document.getElementById('cng_nilai').value) || 0;

    // 4. Tenaga Kerja
    const tkNilai = parseFloat(document.getElementById('tk_total_nilai').value) || 0;

    // Total Biaya Produksi Shift
    const totalBiaya = singkong + minyak + bumbu + totalKemasan + cngNilai + tkNilai;
    document.getElementById('lblTotalBiayaProduksi').textContent = `Rp ${formatRp(totalBiaya)}`;

    // Total Output Kg
    let totalKg = 0;
    const rows = document.querySelectorAll('#allocationTableBody tr[data-output-id]');
    rows.forEach(r => {
        totalKg += parseFloat(r.getAttribute('data-qty-kg')) || 0;
    });

    if (totalKg <= 0 && currentProduksiData) {
        totalKg = parseFloat(currentProduksiData.total_wip_qty) || 0;
    }

    document.getElementById('lblTotalOutputKg').textContent = `${formatNum(totalKg)} kg`;
    document.getElementById('footTotalKg').textContent = `${formatNum(totalKg)} kg`;

    const hppRata = totalKg > 0 ? (totalBiaya / totalKg) : 0;
    document.getElementById('lblHppRataRata').textContent = `Rp ${formatNum(hppRata, 2)} / kg`;

    recalcAllocationTotals();
}

function recalcAllocationTotals() {
    let totalAllocatedNilai = 0;
    const rows = document.querySelectorAll('#allocationTableBody tr[data-output-id]');

    rows.forEach(r => {
        const outId = r.getAttribute('data-output-id');
        const qtyKg = parseFloat(r.getAttribute('data-qty-kg')) || 0;
        const inputHpp = document.getElementById(`input_hpp_${outId}`);
        const hppVal = parseFloat(inputHpp?.value) || 0;
        const itemNilai = Math.round(qtyKg * hppVal);

        totalAllocatedNilai += itemNilai;

        const lbl = document.getElementById(`lbl_nilai_${outId}`);
        if (lbl) lbl.textContent = `Rp ${formatRp(itemNilai)}`;
    });

    document.getElementById('footTotalNilai').textContent = `Rp ${formatRp(totalAllocatedNilai)}`;

    // Cek kesesuaian dengan total biaya produksi
    const singkong = parseFloat(document.getElementById('singkong_nilai').value) || 0;
    const minyak = parseFloat(document.getElementById('minyak_nilai').value) || 0;
    const bumbu = parseFloat(document.getElementById('bumbu_nilai').value) || 0;
    const kBaru = parseFloat(document.getElementById('karton_baru_nilai').value) || 0;
    const kBekas = parseFloat(document.getElementById('karton_bekas_nilai').value) || 0;
    const pHD = parseFloat(document.getElementById('plastik_hd_nilai').value) || 0;
    const lBesar = parseFloat(document.getElementById('lakban_besar_nilai').value) || 0;
    const lKecil = parseFloat(document.getElementById('lakban_kecil_nilai').value) || 0;
    const tRafia = parseFloat(document.getElementById('tali_rafia_nilai').value) || 0;
    const cngNilai = parseFloat(document.getElementById('cng_nilai').value) || 0;
    const tkNilai = parseFloat(document.getElementById('tk_total_nilai').value) || 0;

    const totalBiaya = singkong + minyak + bumbu + kBaru + kBekas + pHD + lBesar + lKecil + tRafia + cngNilai + tkNilai;
    const selisih = Math.abs(totalAllocatedNilai - totalBiaya);

    const alertBox = document.getElementById('balanceAlertBox');
    const alertText = document.getElementById('balanceAlertText');
    const footStatus = document.getElementById('footStatusMatch');

    if (totalBiaya <= 0 && totalAllocatedNilai <= 0) {
        alertBox.style.display = 'none';
        footStatus.textContent = 'Menunggu Input';
        footStatus.style.color = '#64748b';
    } else if (selisih <= 100) {
        alertBox.style.display = 'flex';
        alertBox.className = 'balance-status-alert alert-matched';
        alertText.textContent = `✅ Total nilai alokasi output (Rp ${formatRp(totalAllocatedNilai)}) seimbang dengan Total Biaya Produksi (Rp ${formatRp(totalBiaya)}).`;
        footStatus.textContent = 'Balance 100%';
        footStatus.style.color = '#059669';
    } else {
        alertBox.style.display = 'flex';
        alertBox.className = 'balance-status-alert alert-mismatched';
        const diffText = totalAllocatedNilai > totalBiaya ? 'Surplus' : 'Defisit';
        alertText.textContent = `⚠️ Nilai alokasi output memiliki selisih ${diffText} Rp ${formatRp(selisih)} terhadap Total Biaya Produksi (Rp ${formatRp(totalBiaya)}). Klik tombol "Alokasi Rasio Mutu" atau "Bagi Rata" untuk menyeimbangkan.`;
        footStatus.textContent = `Selisih: Rp ${formatRp(selisih)}`;
        footStatus.style.color = '#d97706';
    }
}

function applyStandarRasio() {
    const rows = document.querySelectorAll('#allocationTableBody tr[data-output-id]');
    if (rows.length === 0) return;

    // Ambil total biaya
    const singkong = parseFloat(document.getElementById('singkong_nilai').value) || 0;
    const minyak = parseFloat(document.getElementById('minyak_nilai').value) || 0;
    const bumbu = parseFloat(document.getElementById('bumbu_nilai').value) || 0;
    const kBaru = parseFloat(document.getElementById('karton_baru_nilai').value) || 0;
    const kBekas = parseFloat(document.getElementById('karton_bekas_nilai').value) || 0;
    const pHD = parseFloat(document.getElementById('plastik_hd_nilai').value) || 0;
    const lBesar = parseFloat(document.getElementById('lakban_besar_nilai').value) || 0;
    const lKecil = parseFloat(document.getElementById('lakban_kecil_nilai').value) || 0;
    const tRafia = parseFloat(document.getElementById('tali_rafia_nilai').value) || 0;
    const cngNilai = parseFloat(document.getElementById('cng_nilai').value) || 0;
    const tkNilai = parseFloat(document.getElementById('tk_total_nilai').value) || 0;
    const totalBiaya = singkong + minyak + bumbu + kBaru + kBekas + pHD + lBesar + lKecil + tRafia + cngNilai + tkNilai;

    if (totalBiaya <= 0) {
        alert('Total Biaya Produksi masih Rp 0. Masukkan nilai biaya terlebih dahulu.');
        return;
    }

    // Rasio Bobot Mutu: KW1 = 2.5, KW2 / BS / Reject = 1.0
    let totalWeightedKg = 0;
    const items = [];

    rows.forEach(r => {
        const outId = r.getAttribute('data-output-id');
        const qtyKg = parseFloat(r.getAttribute('data-qty-kg')) || 0;
        const grade = (r.getAttribute('data-grade') || 'KW1').toUpperCase();
        const weightFactor = (grade.includes('KW2') || grade.includes('BS') || grade.includes('REJECT')) ? 1.0 : 2.5;

        totalWeightedKg += (qtyKg * weightFactor);
        items.push({ outId, qtyKg, weightFactor });
    });

    if (totalWeightedKg <= 0) return;

    // Unit dasar per bobot
    const baseCostPerWeightedUnit = totalBiaya / totalWeightedKg;

    items.forEach(it => {
        const hppPerKg = Math.round(baseCostPerWeightedUnit * it.weightFactor * 100) / 100;
        const input = document.getElementById(`input_hpp_${it.outId}`);
        if (input) input.value = hppPerKg;
    });

    recalcAllocationTotals();
}

function resetModeBagiRata() {
    const rows = document.querySelectorAll('#allocationTableBody tr[data-output-id]');
    if (rows.length === 0) return;

    const singkong = parseFloat(document.getElementById('singkong_nilai').value) || 0;
    const minyak = parseFloat(document.getElementById('minyak_nilai').value) || 0;
    const bumbu = parseFloat(document.getElementById('bumbu_nilai').value) || 0;
    const kBaru = parseFloat(document.getElementById('karton_baru_nilai').value) || 0;
    const kBekas = parseFloat(document.getElementById('karton_bekas_nilai').value) || 0;
    const pHD = parseFloat(document.getElementById('plastik_hd_nilai').value) || 0;
    const lBesar = parseFloat(document.getElementById('lakban_besar_nilai').value) || 0;
    const lKecil = parseFloat(document.getElementById('lakban_kecil_nilai').value) || 0;
    const tRafia = parseFloat(document.getElementById('tali_rafia_nilai').value) || 0;
    const cngNilai = parseFloat(document.getElementById('cng_nilai').value) || 0;
    const tkNilai = parseFloat(document.getElementById('tk_total_nilai').value) || 0;
    const totalBiaya = singkong + minyak + bumbu + kBaru + kBekas + pHD + lBesar + lKecil + tRafia + cngNilai + tkNilai;

    let totalKg = 0;
    rows.forEach(r => {
        totalKg += parseFloat(r.getAttribute('data-qty-kg')) || 0;
    });

    if (totalKg <= 0 || totalBiaya <= 0) return;

    const averageHpp = Math.round((totalBiaya / totalKg) * 100) / 100;

    rows.forEach(r => {
        const outId = r.getAttribute('data-output-id');
        const input = document.getElementById(`input_hpp_${outId}`);
        if (input) input.value = averageHpp;
    });

    recalcAllocationTotals();
}

function formatRp(val) {
    if (isNaN(val)) return '0';
    return Math.round(val).toLocaleString('id-ID');
}

function formatNum(val, decimals = 2) {
    if (isNaN(val)) return '0';
    return parseFloat(val).toLocaleString('id-ID', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals
    });
}

