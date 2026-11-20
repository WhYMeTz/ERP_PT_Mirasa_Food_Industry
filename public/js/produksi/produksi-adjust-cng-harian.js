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
 * Event Handler: Saat User Mengubah MMBTU Baru
 * Otomatis menghitung dan memperbarui nominal Biaya Gas Baru (Rp)
 */
function onCngMmbtuChange(dateStr) {
    const row = document.getElementById('row_cng_' + dateStr);
    if (!row) return;

    const cngTarifRp = parseFloat(row.dataset.cngTarif || 0);
    const inputMmbtu = row.querySelector('.input-cng-mmbtu') || row.querySelector('.input-cng-edit');
    const inputNilai = row.querySelector('.input-cng-nilai');
    const mmbtuVal = parseFloat(inputMmbtu?.value || 0);

    if (inputNilai) {
        const calculatedNilai = Math.round(mmbtuVal * cngTarifRp);
        inputNilai.value = calculatedNilai;
    }

    recalculateCngBalancing();
}

/**
 * Event Handler: Saat User Mengubah Biaya Gas Baru (Nominal Rp)
 * Otomatis menghitung dan memperbarui kuantitas MMBTU Baru
 */
function onCngNilaiChange(dateStr) {
    const row = document.getElementById('row_cng_' + dateStr);
    if (!row) return;

    const cngTarifRp = parseFloat(row.dataset.cngTarif || 0);
    const inputMmbtu = row.querySelector('.input-cng-mmbtu') || row.querySelector('.input-cng-edit');
    const inputNilai = row.querySelector('.input-cng-nilai');
    const nilaiVal = parseFloat(inputNilai?.value || 0);

    if (inputMmbtu && cngTarifRp > 0) {
        const calculatedMmbtu = Math.round((nilaiVal / cngTarifRp) * 10000) / 10000;
        inputMmbtu.value = calculatedMmbtu;
    }

    recalculateCngBalancing();
}

/**
 * Kalkulasi Interaktif Real-Time Rekonsiliasi & Balancing Gas CNG
 */
function recalculateCngBalancing() {
    const rows = document.querySelectorAll('.cng-daily-row');
    if (!rows || rows.length === 0) return;

    // Ambil Target Vendor dari Input jika ada
    const elTargetMmbtu = document.getElementById('target_vendor_mmbtu');
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

        const inputMmbtu = row.querySelector('.input-cng-mmbtu') || row.querySelector('.input-cng-edit');
        const inputNilai = row.querySelector('.input-cng-nilai');

        let newMmbtu = parseFloat(inputMmbtu?.value || 0);
        let newCngNilai = inputNilai ? parseFloat(inputNilai.value || 0) : Math.round(newMmbtu * cngTarifRp);

        const deltaMmbtu = Math.round((newMmbtu - oldMmbtu) * 10000) / 10000;
        const deltaCngNilai = Math.round(newCngNilai - oldCngNilai);

        const newTotalBiaya = Math.round(oldTotalBiaya + deltaCngNilai);
        const newHpp = wipQty > 0 ? (newTotalBiaya / wipQty) : 0;

        sumOldMmbtu += oldMmbtu;
        sumNewMmbtu += newMmbtu;
        sumOldNilai += oldCngNilai;
        sumNewNilai += newCngNilai;
        sumWip += wipQty;
        sumNewTotalBiaya += newTotalBiaya;

        // Update Indikator Perubahan pada Baris Ini
        const isModified = Math.abs(deltaMmbtu) > 0.0001 || Math.abs(deltaCngNilai) >= 1;
        if (isModified) {
            modifiedDaysCount++;
            row.classList.add('row-modified');
            inputMmbtu?.classList.add('is-modified');
            inputNilai?.classList.add('is-modified');
        } else {
            row.classList.remove('row-modified');
            inputMmbtu?.classList.remove('is-modified');
            inputNilai?.classList.remove('is-modified');
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
                badgeMmbtu.className = 'badge-cng-variance badge-cng-pos badge-delta-mmbtu';
                badgeMmbtu.textContent = '+' + formatMmbtu(deltaMmbtu, 4);
            } else if (deltaMmbtu < 0) {
                badgeMmbtu.className = 'badge-cng-variance badge-cng-neg badge-delta-mmbtu';
                badgeMmbtu.textContent = formatMmbtu(deltaMmbtu, 4);
            } else {
                badgeMmbtu.className = 'badge-cng-variance badge-cng-zero badge-delta-mmbtu';
                badgeMmbtu.textContent = '0';
            }
        }

        // 2. Update Cell Nilai Biaya Gas Baru jika bukan input
        const cellBiayaBaru = row.querySelector('.cell-cng-nilai-baru');
        if (cellBiayaBaru) {
            cellBiayaBaru.textContent = formatRupiah(newCngNilai);
        }

        // 3. Update Badge Delta Biaya Gas
        const badgeBiaya = row.querySelector('.badge-delta-rp') || row.querySelector('.badge-delta-biaya');
        if (badgeBiaya) {
            if (deltaCngNilai > 0) {
                badgeBiaya.className = 'badge-cng-variance badge-cng-pos badge-delta-rp badge-delta-biaya';
                badgeBiaya.textContent = '+' + formatRupiah(deltaCngNilai);
            } else if (deltaCngNilai < 0) {
                badgeBiaya.className = 'badge-cng-variance badge-cng-neg badge-delta-rp badge-delta-biaya';
                badgeBiaya.textContent = formatRupiah(deltaCngNilai);
            } else {
                badgeBiaya.className = 'badge-cng-variance badge-cng-zero badge-delta-rp badge-delta-biaya';
                badgeBiaya.textContent = 'Rp 0';
            }
        }

        // 4. Update Estimasi HPP/kg Baru
        const cellHppBaru = row.querySelector('.cell-cng-hpp-baru') || row.querySelector('.cell-hpp-baru');
        if (cellHppBaru) {
            cellHppBaru.textContent = formatRupiah(newHpp) + '/kg';
        }
    });

    // ─── UPDATE TFOOT TABEL TOTAL ───
    const footMmbtu = document.getElementById('footNewMmbtu') || document.getElementById('foot_total_mmbtu_baru');
    if (footMmbtu) footMmbtu.textContent = formatMmbtu(sumNewMmbtu, 4);

    const footDeltaMmbtu = document.getElementById('footDeltaMmbtu');
    const totalDeltaMmbtu = sumNewMmbtu - sumOldMmbtu;
    if (footDeltaMmbtu) {
        if (totalDeltaMmbtu > 0) {
            footDeltaMmbtu.innerHTML = `<span class="badge-cng-variance badge-cng-pos">+${formatMmbtu(totalDeltaMmbtu, 4)}</span>`;
        } else if (totalDeltaMmbtu < 0) {
            footDeltaMmbtu.innerHTML = `<span class="badge-cng-variance badge-cng-neg">${formatMmbtu(totalDeltaMmbtu, 4)}</span>`;
        } else {
            footDeltaMmbtu.innerHTML = `<span class="badge-cng-variance badge-cng-zero">0</span>`;
        }
    }

    const footBiaya = document.getElementById('footNewNilai') || document.getElementById('foot_total_cng_baru');
    if (footBiaya) footBiaya.textContent = formatRupiah(sumNewNilai);

    const footDeltaRp = document.getElementById('footDeltaRp');
    const totalDeltaNilai = sumNewNilai - sumOldNilai;
    if (footDeltaRp) {
        if (totalDeltaNilai > 0) {
            footDeltaRp.innerHTML = `<span class="badge-cng-variance badge-cng-pos">+${formatRupiah(totalDeltaNilai)}</span>`;
        } else if (totalDeltaNilai < 0) {
            footDeltaRp.innerHTML = `<span class="badge-cng-variance badge-cng-neg">${formatRupiah(totalDeltaNilai)}</span>`;
        } else {
            footDeltaRp.innerHTML = `<span class="badge-cng-variance badge-cng-zero">Rp 0</span>`;
        }
    }

    const footHpp = document.getElementById('footAvgHpp') || document.getElementById('foot_avg_hpp_baru');
    const avgHpp = sumWip > 0 ? (sumNewTotalBiaya / sumWip) : 0;
    if (footHpp) footHpp.textContent = formatRupiah(avgHpp) + '/kg';

    // ─── UPDATE KARTU BALANCING BULANAN ───
    const cardActualMmbtu = document.getElementById('stat_actual_mmbtu');
    if (cardActualMmbtu) cardActualMmbtu.textContent = formatMmbtu(sumNewMmbtu, 4) + ' MMBTU';

    const cardActualRp = document.getElementById('stat_actual_rp');
    if (cardActualRp) cardActualRp.textContent = formatRupiah(sumNewNilai);

    // Hitung Selisih (Variance) Terhadap Target Vendor jika ada
    const diffMmbtu = Math.round((sumNewMmbtu - targetMmbtu) * 10000) / 10000;
    const cardStatus = document.getElementById('cardBalancingStatus');
    const badgeStatus = document.getElementById('badgeBalancingTitle');
    const textStatusVal = document.getElementById('textBalancingVal');
    const textStatusDesc = document.getElementById('textBalancingDesc');

    if (cardStatus && badgeStatus && textStatusVal && textStatusDesc) {
        if (targetMmbtu <= 0) {
            cardStatus.className = 'balancing-card card-status';
            badgeStatus.style.background = '#f1f5f9';
            badgeStatus.style.color = '#475569';
            badgeStatus.textContent = 'Menunggu Input Target Faktur';
            textStatusVal.style.color = '#0f172a';
            textStatusVal.textContent = 'Ketik Target MMBTU';
            textStatusDesc.textContent = 'Ketik kuantitas faktur vendor pada kolom Target di sebelah kiri.';
        } else if (Math.abs(diffMmbtu) < 0.0001) {
            cardStatus.className = 'balancing-card card-status status-balanced';
            badgeStatus.style.background = '#dcfce7';
            badgeStatus.style.color = '#15803d';
            badgeStatus.textContent = '100% BALANCE / SEIMBANG';
            textStatusVal.style.color = '#16a34a';
            textStatusVal.textContent = 'Selisih: 0.0000 MMBTU';
            textStatusDesc.textContent = 'Total pemakaian fisik harian sudah sama persis dengan faktur vendor!';
        } else if (diffMmbtu > 0) {
            cardStatus.className = 'balancing-card card-status status-unbalanced';
            badgeStatus.style.background = '#fef3c7';
            badgeStatus.style.color = '#b45309';
            badgeStatus.textContent = 'SELISIH LEBIH DARI FAKTUR';
            textStatusVal.style.color = '#d97706';
            textStatusVal.textContent = '+' + formatMmbtu(diffMmbtu, 4) + ' MMBTU';
            textStatusDesc.textContent = 'Total harian lebih besar dari faktur vendor. Kurangi pada tanggal terkait.';
        } else {
            cardStatus.className = 'balancing-card card-status status-unbalanced';
            badgeStatus.style.background = '#fef2f2';
            badgeStatus.style.color = '#dc2626';
            badgeStatus.textContent = 'SELISIH KURANG DARI FAKTUR';
            textStatusVal.style.color = '#dc2626';
            textStatusVal.textContent = formatMmbtu(diffMmbtu, 4) + ' MMBTU';
            textStatusDesc.textContent = 'Total harian masih kurang dari faktur vendor. Tambahkan pada tanggal terkait.';
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
 * Mendukung satuan MMBTU maupun Rupiah (Nominal)
 */
function applyBulkOffset() {
    const inputOffset = document.getElementById('input_bulk_offset');
    const offsetVal = parseFloat(inputOffset?.value || 0);
    const unitSelect = document.getElementById('select_bulk_unit');
    const unitMode = unitSelect ? unitSelect.value : 'MMBTU';

    if (isNaN(offsetVal) || offsetVal === 0) {
        alert('Silakan masukkan angka selisih penyesuaian terlebih dahulu.');
        inputOffset?.focus();
        return;
    }

    const rows = document.querySelectorAll('.cng-daily-row');
    rows.forEach(row => {
        const oldMmbtu = parseFloat(row.dataset.oldMmbtu || 0);
        const oldCngNilai = parseFloat(row.dataset.oldCngNilai || 0);
        const cngTarifRp = parseFloat(row.dataset.cngTarif || 0);

        const inputMmbtu = row.querySelector('.input-cng-mmbtu') || row.querySelector('.input-cng-edit');
        const inputNilai = row.querySelector('.input-cng-nilai');

        if (unitMode === 'RP') {
            const newNilai = Math.max(0, Math.round(oldCngNilai + offsetVal));
            const newMmbtu = cngTarifRp > 0 ? (newNilai / cngTarifRp) : 0;
            if (inputNilai) inputNilai.value = newNilai;
            if (inputMmbtu) inputMmbtu.value = parseFloat(newMmbtu.toFixed(4));
        } else {
            const newMmbtu = Math.max(0, Math.round((oldMmbtu + offsetVal) * 10000) / 10000);
            const newNilai = Math.round(newMmbtu * cngTarifRp);
            if (inputMmbtu) inputMmbtu.value = newMmbtu.toFixed(4);
            if (inputNilai) inputNilai.value = newNilai;
        }
    });

    recalculateCngBalancing();
}

/**
 * Kembalikan Semua Nilai MMBTU & Nominal ke Catatan Awal
 */
function resetAllDailyInputs() {
    const rows = document.querySelectorAll('.cng-daily-row');
    rows.forEach(row => {
        const oldMmbtu = parseFloat(row.dataset.oldMmbtu || 0);
        const oldCngNilai = parseFloat(row.dataset.oldCngNilai || 0);

        const inputMmbtu = row.querySelector('.input-cng-mmbtu') || row.querySelector('.input-cng-edit');
        const inputNilai = row.querySelector('.input-cng-nilai');

        if (inputMmbtu) inputMmbtu.value = oldMmbtu.toFixed(4);
        if (inputNilai) inputNilai.value = Math.round(oldCngNilai);
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

        const inputMmbtu = row.querySelector('.input-cng-mmbtu') || row.querySelector('.input-cng-edit');
        const inputNilai = row.querySelector('.input-cng-nilai');

        const newMmbtu = parseFloat(inputMmbtu?.value || 0);
        const newCngNilai = inputNilai ? parseFloat(inputNilai.value || 0) : Math.round(newMmbtu * cngTarifRp);

        const deltaMmbtu = Math.round((newMmbtu - oldMmbtu) * 10000) / 10000;
        const deltaCngNilai = Math.round(newCngNilai - oldCngNilai);

        if (Math.abs(deltaMmbtu) > 0.0001 || Math.abs(deltaCngNilai) >= 1) {
            modifiedDaysCount++;
            netDeltaMmbtu += deltaMmbtu;
            netDeltaBiaya += deltaCngNilai;
        }
    });

    if (modifiedDaysCount === 0) {
        alert('Belum ada perubahan kuantitas MMBTU atau nominal gas yang dilakukan.');
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
 * Alias untuk kompatibilitas trigger tombol modal
 */
function confirmOpenBalancingModal() {
    openModalConfirmAdjustCng();
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
