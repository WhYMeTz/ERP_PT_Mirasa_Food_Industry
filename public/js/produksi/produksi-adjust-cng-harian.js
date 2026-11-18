/**
 * ═════════════════════════════════════════════════════════════════
 * JAVASCRIPT: PENYESUAIAN GAS CNG HARIAN & BALANCING BULANAN
 * ERP PT Mirasa Food Industry - Modul Produksi
 * ═════════════════════════════════════════════════════════════════
 */

document.addEventListener('DOMContentLoaded', function () {
    // Inisialisasi kalkulasi saat halaman selesai dimuat
    recalculateCngBalancing();
});

/**
 * Format Angka Rupiah
 */
function formatRupiah(number) {
    const val = Math.round(number);
    return 'Rp ' + val.toLocaleString('id-ID');
}

/**
 * Format Angka Desimal MMBTU
 */
function formatMmbtu(number, decimals = 4) {
    return parseFloat(number || 0).toLocaleString('id-ID', {
        minimumFractionDigits: 2,
        maximumFractionDigits: decimals
    });
}

/**
 * Kalkulasi Interaktif Real-Time Rekonsiliasi & Balancing Gas CNG
 */
function recalculateCngBalancing() {
    const rows = document.querySelectorAll('.cng-daily-row');
    if (!rows || rows.length === 0) return;

    // Ambil Target Vendor dari Input
    const elTargetMmbtu = document.getElementById('target_vendor_mmbtu');
    const elTargetRp = document.getElementById('target_vendor_rp');
    const targetMmbtu = parseFloat(elTargetMmbtu?.value || 0);

    let sumOldMmbtu = 0;
    let sumNewMmbtu = 0;
    let sumOldNilai = 0;
    let sumNewNilai = 0;
    let sumWip = 0;
    let sumNewTotalBiaya = 0;
    let modifiedDaysCount = 0;

    rows.forEach(row => {
        const oldMmbtu = parseFloat(row.dataset.oldMmbtu || 0);
        const oldCngNilai = parseFloat(row.dataset.oldCngNilai || 0);
        const oldTotalBiaya = parseFloat(row.dataset.oldTotalBiaya || 0);
        const cngTarifRp = parseFloat(row.dataset.cngTarif || 0);
        const wipQty = parseFloat(row.dataset.wipQty || 0);

        const inputMmbtu = row.querySelector('.input-cng-edit');
        const newMmbtu = parseFloat(inputMmbtu?.value || 0);

        const deltaMmbtu = Math.round((newMmbtu - oldMmbtu) * 10000) / 10000;
        const newCngNilai = Math.round(newMmbtu * cngTarifRp);
        const deltaCngNilai = newCngNilai - oldCngNilai;

        const newTotalBiaya = Math.round(oldTotalBiaya + deltaCngNilai);
        const newHpp = wipQty > 0 ? (newTotalBiaya / wipQty) : 0;

        sumOldMmbtu += oldMmbtu;
        sumNewMmbtu += newMmbtu;
        sumOldNilai += oldCngNilai;
        sumNewNilai += newCngNilai;
        sumWip += wipQty;
        sumNewTotalBiaya += newTotalBiaya;

        // Update Indikator Perubahan pada Baris Ini
        const isModified = Math.abs(deltaMmbtu) > 0.0001;
        if (isModified) {
            modifiedDaysCount++;
            row.classList.add('row-modified');
            inputMmbtu?.classList.add('is-modified');
        } else {
            row.classList.remove('row-modified');
            inputMmbtu?.classList.remove('is-modified');
        }

        // Update Preview Nm3
        const elNm3Preview = row.querySelector('.new-nm3-preview');
        if (elNm3Preview) {
            elNm3Preview.textContent = '≈ ' + (newMmbtu * 27.921).toLocaleString('id-ID', { maximumFractionDigits: 2 }) + ' Nm³';
        }

        // 1. Update Badge Delta MMBTU
        const badgeMmbtu = row.querySelector('.badge-delta-mmbtu');
        if (badgeMmbtu) {
            if (deltaMmbtu > 0) {
                badgeMmbtu.className = 'badge-cng-variance badge-cng-pos';
                badgeMmbtu.textContent = '+' + formatMmbtu(deltaMmbtu, 4);
            } else if (deltaMmbtu < 0) {
                badgeMmbtu.className = 'badge-cng-variance badge-cng-neg';
                badgeMmbtu.textContent = formatMmbtu(deltaMmbtu, 4);
            } else {
                badgeMmbtu.className = 'badge-cng-variance badge-cng-zero';
                badgeMmbtu.textContent = '0';
            }
        }

        // 2. Update Nilai Biaya Gas Baru
        const cellBiayaBaru = row.querySelector('.cell-cng-nilai-baru');
        if (cellBiayaBaru) {
            cellBiayaBaru.textContent = formatRupiah(newCngNilai);
        }

        // 3. Update Badge Delta Biaya Gas
        const badgeBiaya = row.querySelector('.badge-delta-biaya');
        if (badgeBiaya) {
            if (deltaCngNilai > 0) {
                badgeBiaya.className = 'badge-cng-variance badge-cng-pos';
                badgeBiaya.textContent = '+' + formatRupiah(deltaCngNilai);
            } else if (deltaCngNilai < 0) {
                badgeBiaya.className = 'badge-cng-variance badge-cng-neg';
                badgeBiaya.textContent = formatRupiah(deltaCngNilai);
            } else {
                badgeBiaya.className = 'badge-cng-variance badge-cng-zero';
                badgeBiaya.textContent = 'Rp 0';
            }
        }

        // 4. Update Estimasi HPP/kg Baru
        const cellHppBaru = row.querySelector('.cell-hpp-baru');
        if (cellHppBaru) {
            cellHppBaru.textContent = formatRupiah(newHpp) + '/kg';
        }
    });

    // ─── UPDATE TFOOT TABEL TOTAL ───
    const footMmbtu = document.getElementById('foot_total_mmbtu_baru');
    if (footMmbtu) footMmbtu.textContent = formatMmbtu(sumNewMmbtu, 4) + ' MMBTU';

    const footBiaya = document.getElementById('foot_total_cng_baru');
    if (footBiaya) footBiaya.textContent = formatRupiah(sumNewNilai);

    const footHpp = document.getElementById('foot_avg_hpp_baru');
    const avgHpp = sumWip > 0 ? (sumNewTotalBiaya / sumWip) : 0;
    if (footHpp) footHpp.textContent = formatRupiah(avgHpp) + '/kg';

    // ─── UPDATE KARTU BALANCING BULANAN ───
    const cardActualMmbtu = document.getElementById('stat_actual_mmbtu');
    if (cardActualMmbtu) cardActualMmbtu.textContent = formatMmbtu(sumNewMmbtu, 4) + ' MMBTU';

    const cardActualRp = document.getElementById('stat_actual_rp');
    if (cardActualRp) cardActualRp.textContent = formatRupiah(sumNewNilai);

    // Hitung Selisih (Variance) Terhadap Target Vendor
    const diffMmbtu = Math.round((sumNewMmbtu - targetMmbtu) * 10000) / 10000;
    const cardStatus = document.getElementById('cardBalancingStatus');
    const badgeStatus = document.getElementById('badgeBalancingTitle');
    const textStatusVal = document.getElementById('textBalancingVal');
    const textStatusDesc = document.getElementById('textBalancingDesc');

    if (cardStatus && badgeStatus && textStatusVal && textStatusDesc) {
        if (targetMmbtu <= 0) {
            // Jika belum mengisi target vendor
            cardStatus.className = 'balancing-card card-status';
            badgeStatus.style.background = '#f1f5f9';
            badgeStatus.style.color = '#475569';
            badgeStatus.textContent = 'Menunggu Input Target Faktur';
            textStatusVal.style.color = '#0f172a';
            textStatusVal.textContent = 'Ketik Target MMBTU';
            textStatusDesc.textContent = 'Ketik kuantitas faktur vendor pada kolom Target di sebelah kiri.';
        } else if (Math.abs(diffMmbtu) < 0.0001) {
            // SEIMBANG / 100% BALANCE
            cardStatus.className = 'balancing-card card-status status-balanced';
            badgeStatus.style.background = '#dcfce7';
            badgeStatus.style.color = '#15803d';
            badgeStatus.textContent = '100% BALANCE / SEIMBANG';
            textStatusVal.style.color = '#16a34a';
            textStatusVal.textContent = 'Selisih: 0.0000 MMBTU';
            textStatusDesc.textContent = 'Total pemakaian fisik harian sudah sama persis dengan faktur vendor!';
        } else if (diffMmbtu > 0) {
            // LEBIH
            cardStatus.className = 'balancing-card card-status status-unbalanced';
            badgeStatus.style.background = '#fef3c7';
            badgeStatus.style.color = '#b45309';
            badgeStatus.textContent = 'SELISIH LEBIH DARI FAKTUR';
            textStatusVal.style.color = '#d97706';
            textStatusVal.textContent = '+' + formatMmbtu(diffMmbtu, 4) + ' MMBTU';
            textStatusDesc.textContent = 'Total harian lebih besar dari faktur vendor. Kurangi MMBTU pada tanggal terkait.';
        } else {
            // KURANG
            cardStatus.className = 'balancing-card card-status status-unbalanced';
            badgeStatus.style.background = '#fef2f2';
            badgeStatus.style.color = '#dc2626';
            badgeStatus.textContent = 'SELISIH KURANG DARI FAKTUR';
            textStatusVal.style.color = '#dc2626';
            textStatusVal.textContent = formatMmbtu(diffMmbtu, 4) + ' MMBTU';
            textStatusDesc.textContent = 'Total harian masih kurang dari faktur vendor. Tambahkan MMBTU pada tanggal terkait.';
        }
    }

    // ─── UPDATE BOTTOM SUMMARY TEXT & BUTTON STATE ───
    const bottomText = document.getElementById('bottomSummaryDesc');
    const netDeltaBiaya = sumNewNilai - sumOldNilai;
    const netDeltaMmbtu = sumNewMmbtu - sumOldMmbtu;

    if (bottomText) {
        if (modifiedDaysCount > 0) {
            const signM = netDeltaMmbtu > 0 ? '+' : '';
            const signB = netDeltaBiaya > 0 ? '+' : '';
            bottomText.innerHTML = `Terdapat <strong>${modifiedDaysCount} hari kerja</strong> yang disesuaikan &bull; Net Selisih: <strong>${signM}${formatMmbtu(netDeltaMmbtu, 4)} MMBTU</strong> (${signB}${formatRupiah(netDeltaBiaya)})`;
        } else {
            bottomText.innerHTML = 'Seluruh lembar kerja gas harian sesuai data fisik saat ini (belum ada perubahan).';
        }
    }

    const btnSubmit = document.getElementById('btnSubmitDailyCng');
    if (btnSubmit) {
        btnSubmit.disabled = (modifiedDaysCount === 0);
    }
}

/**
 * Terapkan Selisih Seragam (Bulk Offset) ke Semua Hari Kerja
 * Contoh: Tambah +0.005 MMBTU ke setiap hari kerja
 */
function applyBulkOffset() {
    const inputOffset = document.getElementById('input_bulk_offset');
    const offsetVal = parseFloat(inputOffset?.value || 0);

    if (isNaN(offsetVal) || offsetVal === 0) {
        alert('Silakan masukkan angka selisih penyesuaian terlebih dahulu.');
        inputOffset?.focus();
        return;
    }

    const rows = document.querySelectorAll('.cng-daily-row');
    rows.forEach(row => {
        const oldMmbtu = parseFloat(row.dataset.oldMmbtu || 0);
        const newMmbtu = Math.max(0, Math.round((oldMmbtu + offsetVal) * 10000) / 10000);

        const inputMmbtu = row.querySelector('.input-cng-edit');
        if (inputMmbtu) {
            inputMmbtu.value = newMmbtu.toFixed(4);
        }
    });

    recalculateCngBalancing();
}

/**
 * Otomatis Bagi Rata Selisih dari Target Faktur Vendor ke Seluruh Hari Kerja
 */
function autoDistributeVariance() {
    const elTargetMmbtu = document.getElementById('target_vendor_mmbtu');
    const targetMmbtu = parseFloat(elTargetMmbtu?.value || 0);
    const rows = document.querySelectorAll('.cng-daily-row');

    if (targetMmbtu <= 0 || rows.length === 0) {
        alert('Silakan masukkan Target Faktur Vendor (MMBTU) terlebih dahulu.');
        elTargetMmbtu?.focus();
        return;
    }

    // Hitung total MMBTU awal
    let sumOldMmbtu = 0;
    rows.forEach(row => {
        sumOldMmbtu += parseFloat(row.dataset.oldMmbtu || 0);
    });

    const totalDiff = targetMmbtu - sumOldMmbtu;
    const diffPerDay = Math.round((totalDiff / rows.length) * 10000) / 10000;

    const inputOffset = document.getElementById('input_bulk_offset');
    if (inputOffset) {
        inputOffset.value = diffPerDay;
    }

    applyBulkOffset();
}

/**
 * Kembalikan Semua Nilai MMBTU ke Catatan Awal
 */
function resetAllDailyInputs() {
    const rows = document.querySelectorAll('.cng-daily-row');
    rows.forEach(row => {
        const oldMmbtu = parseFloat(row.dataset.oldMmbtu || 0);
        const inputMmbtu = row.querySelector('.input-cng-edit');
        if (inputMmbtu) {
            inputMmbtu.value = oldMmbtu.toFixed(4);
        }
    });

    const inputOffset = document.getElementById('input_bulk_offset');
    if (inputOffset) inputOffset.value = '';

    recalculateCngBalancing();
}

/**
 * Buka Modal Konfirmasi Penyesuaian Gas Harian (Sesuai AGENTS.md Rule 2)
 */
function openModalConfirmAdjustCng() {
    const rows = document.querySelectorAll('.cng-daily-row');
    let modifiedDaysCount = 0;
    let netDeltaBiaya = 0;
    let netDeltaMmbtu = 0;

    rows.forEach(row => {
        const oldMmbtu = parseFloat(row.dataset.oldMmbtu || 0);
        const oldCngNilai = parseFloat(row.dataset.oldCngNilai || 0);
        const cngTarifRp = parseFloat(row.dataset.cngTarif || 0);

        const inputMmbtu = row.querySelector('.input-cng-edit');
        const newMmbtu = parseFloat(inputMmbtu?.value || 0);

        const deltaMmbtu = Math.round((newMmbtu - oldMmbtu) * 10000) / 10000;
        const newCngNilai = Math.round(newMmbtu * cngTarifRp);
        const deltaCngNilai = newCngNilai - oldCngNilai;

        if (Math.abs(deltaMmbtu) > 0.0001) {
            modifiedDaysCount++;
            netDeltaMmbtu += deltaMmbtu;
            netDeltaBiaya += deltaCngNilai;
        }
    });

    if (modifiedDaysCount === 0) {
        alert('Belum ada perubahan kuantitas MMBTU gas yang dilakukan.');
        return;
    }

    // Populate data ke dalam modal konfirmasi
    const modalDaysCount = document.getElementById('modalDaysCount');
    if (modalDaysCount) modalDaysCount.textContent = modifiedDaysCount + ' Hari';

    const modalDeltaMmbtu = document.getElementById('modalDeltaMmbtu');
    if (modalDeltaMmbtu) {
        const signM = netDeltaMmbtu > 0 ? '+' : '';
        modalDeltaMmbtu.textContent = signM + formatMmbtu(netDeltaMmbtu, 4) + ' MMBTU';
    }

    const modalDeltaBiaya = document.getElementById('modalDeltaBiaya');
    if (modalDeltaBiaya) {
        const signB = netDeltaBiaya > 0 ? '+' : '';
        modalDeltaBiaya.textContent = signB + formatRupiah(netDeltaBiaya);
    }

    const modal = document.getElementById('modalConfirmAdjustCng');
    if (modal) {
        modal.style.display = 'flex';
    }
}

/**
 * Tutup Modal Konfirmasi
 */
function closeModalConfirmAdjustCng() {
    const modal = document.getElementById('modalConfirmAdjustCng');
    if (modal) {
        modal.style.display = 'none';
    }
}

/**
 * Eksekusi Submit Form setelah Konfirmasi Disetujui
 */
function submitFormDailyCng() {
    const form = document.getElementById('formDailyCngBalancing');
    if (form) {
        const btnSave = document.getElementById('btnModalSaveConfirm');
        if (btnSave) {
            btnSave.disabled = true;
            btnSave.textContent = 'Memproses...';
        }
        form.submit();
    }
}
