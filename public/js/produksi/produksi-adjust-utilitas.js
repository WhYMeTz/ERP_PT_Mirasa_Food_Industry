/**
 * ═════════════════════════════════════════════════════════════════
 * JAVASCRIPT LOGIKA PENYESUAIAN TOTAL FAKTUR GAS BULANAN
 * ERP PT Mirasa Food Industry - Modul Produksi
 * ═════════════════════════════════════════════════════════════════
 */

document.addEventListener('DOMContentLoaded', function () {
    // Jalankan kalkulasi simulasi pertama kali saat halaman dimuat
    recalculateLiveSimulation();
});

/**
 * Kalkulasi otomatis Tarif per MMBTU saat nilai USD atau Kurs JISDOR diubah
 */
function calcContractRateFromInputs() {
    const elUsd = document.getElementById('input_cng_tarif_usd');
    const elKurs = document.getElementById('input_cng_kurs_jisdor');
    const elTarif = document.getElementById('input_cng_tarif_resmi');
    if (!elTarif) return;

    const usd = parseFloat(elUsd?.value || 0);
    const kurs = parseFloat(elKurs?.value || 0);

    if (usd > 0 && kurs > 0) {
        elTarif.value = Math.round(usd * kurs);
    }
}

/**
 * Handler ketika input tarif resmi diubah manual oleh user
 */
function onManualTarifResmiChange() {
    // Memungkinkan user mengetik tarif khusus jika diperlukan
}

/**
 * Terapkan nilai acuan kontrak vendor gas ($ x Kurs x Total MMBTU) ke input total faktur
 */
function applyContractRateToInvoice() {
    const table = document.getElementById('simGridTable');
    const totalMmbtuMonth = parseFloat(table?.dataset.monthMmbtu || 0);
    const elTarif = document.getElementById('input_cng_tarif_resmi');
    const tarifKontrak = parseFloat(elTarif?.value || 0);

    const inputFaktur = document.getElementById('input_total_cng_tagihan');
    if (!inputFaktur) return;

    if (totalMmbtuMonth > 0 && tarifKontrak > 0) {
        inputFaktur.value = Math.round(tarifKontrak * totalMmbtuMonth);
    } else if (tarifKontrak > 0) {
        inputFaktur.value = Math.round(tarifKontrak);
    }

    recalculateLiveSimulation();
}

/**
 * Kalkulasi Pratinjau Interaktif Real-Time Alokasi Faktur Gas
 */
function recalculateLiveSimulation() {
    const table = document.getElementById('simGridTable');
    if (!table) return;

    const totalWipMonth = parseFloat(table.dataset.monthWip || 0);
    const totalMmbtuMonth = parseFloat(table.dataset.monthMmbtu || 0);
    const countDays = parseInt(table.dataset.monthDays || 0);

    // Ambil total tagihan faktur gas dari input
    const inputFaktur = document.getElementById('input_total_cng_tagihan');
    const totalTagihanCng = Math.max(0, parseFloat(inputFaktur?.value || 0));

    // Hitung tarif efektif per MMBTU (Total Faktur / Total Pemakaian MMBTU)
    let effectiveCngRate = 0;
    if (totalMmbtuMonth > 0) {
        effectiveCngRate = totalTagihanCng / totalMmbtuMonth;
    }

    // Update display tarif efektif aktual
    const rateDisplay = document.getElementById('effective_cng_rate_display');
    if (rateDisplay) {
        rateDisplay.textContent = formatRupiah(effectiveCngRate);
    }

    // Iterasi setiap baris tanggal produksi
    const rows = document.querySelectorAll('.sim-data-row');
    let sumNewCng = 0;
    let sumNewTotalBiaya = 0;
    let sumWip = 0;

    rows.forEach(row => {
        const dayWip = parseFloat(row.dataset.wip || 0);
        const dayMmbtu = parseFloat(row.dataset.mmbtu || 0);
        const oldCng = parseFloat(row.dataset.oldCng || 0);
        const oldTotalBiaya = parseFloat(row.dataset.oldTotalBiaya || 0);

        // Alokasi Gas Baru:
        // Jika ada MMBTU fisik, gunakan dayMmbtu * effectiveCngRate
        // Jika MMBTU = 0, alokasikan proporsional WIP
        let newCng = 0;
        if (totalMmbtuMonth > 0) {
            newCng = dayMmbtu * effectiveCngRate;
        } else if (totalWipMonth > 0) {
            newCng = totalTagihanCng * (dayWip / totalWipMonth);
        } else {
            newCng = totalTagihanCng / (countDays || 1);
        }

        // Total biaya produksi baru (kurangi beban gas lama, tambahkan beban gas baru)
        const newTotalBiaya = oldTotalBiaya - oldCng + newCng;
        const newHppPerKg = dayWip > 0 ? (newTotalBiaya / dayWip) : 0;

        sumNewCng += newCng;
        sumNewTotalBiaya += newTotalBiaya;
        sumWip += dayWip;

        // Render cell simulasi
        const cellCng = row.querySelector('.cell-sim-cng');
        if (cellCng) cellCng.textContent = formatRupiah(newCng);

        const cellHpp = row.querySelector('.cell-sim-hpp');
        if (cellHpp) cellHpp.textContent = formatRupiah(newHppPerKg);
    });

    // Update Footer Total & Rata-rata
    const footCng = document.getElementById('foot_total_cng');
    if (footCng) footCng.textContent = formatRupiah(sumNewCng);

    const footHpp = document.getElementById('foot_avg_hpp');
    const avgHppMonth = sumWip > 0 ? (sumNewTotalBiaya / sumWip) : 0;
    if (footHpp) footHpp.textContent = formatRupiah(avgHppMonth);
}

function formatRupiah(val) {
    if (isNaN(val) || val === null) val = 0;
    return 'Rp ' + Math.round(val).toLocaleString('id-ID');
}

/**
 * Modal Dialog Konfirmasi (Sesuai Standar AGENTS.md: Dilarang confirm browser)
 */
function openModalConfirmAdjust() {
    const inputFaktur = document.getElementById('input_total_cng_tagihan');
    const totalTagihanCng = parseFloat(inputFaktur?.value || 0);
    const table = document.getElementById('simGridTable');
    const totalMmbtuMonth = parseFloat(table?.dataset.monthMmbtu || 0);

    const summaryGas = document.getElementById('confirmSummaryGas');
    if (summaryGas) {
        summaryGas.textContent = formatRupiah(totalTagihanCng);
    }

    const summaryRate = document.getElementById('confirmSummaryRate');
    if (summaryRate) {
        const rate = totalMmbtuMonth > 0 ? (totalTagihanCng / totalMmbtuMonth) : 0;
        summaryRate.textContent = 'Rp ' + Math.round(rate).toLocaleString('id-ID') + ' / MMBTU';
    }

    const modal = document.getElementById('modalConfirmAdjust');
    if (modal) {
        modal.style.display = 'flex';
    }
}

function closeModalConfirmAdjust() {
    const modal = document.getElementById('modalConfirmAdjust');
    if (modal) {
        modal.style.display = 'none';
    }
}

function submitAdjustmentForm() {
    const form = document.getElementById('formMainAdjustUtilitas');
    if (form) {
        form.submit();
    }
}
