/**
 * ═════════════════════════════════════════════════════════════════
 * JAVASCRIPT LOGIKA PENYESUAIAN UTILITAS BULANAN (LISTRIK & GAS)
 * ERP PT Mirasa Food Industry - Modul Produksi
 * ═════════════════════════════════════════════════════════════════
 */

document.addEventListener('DOMContentLoaded', function () {
    initAdjustUtilitas();
});

function initAdjustUtilitas() {
    // 1. Jalankan kalkulasi simulasi pertama kali saat halaman dimuat
    recalculateLiveSimulation();

    // 2. Event listener untuk segmented pill mode listrik
    document.querySelectorAll('.mode-pill-container').forEach(container => {
        container.querySelectorAll('.mode-pill-item').forEach(pill => {
            pill.addEventListener('click', function () {
                const radio = this.querySelector('input[type="radio"]');
                if (!radio) return;

                radio.checked = true;

                // Update active state di container yang sama
                container.querySelectorAll('.mode-pill-item').forEach(p => p.classList.remove('active'));
                this.classList.add('active');

                // Trigger perubahan view box & kalkulasi
                if (radio.name === 'mode_alokasi_listrik') {
                    toggleListrikModeBox(radio.value);
                } else if (radio.name === 'mode_cng') {
                    toggleCngModeBox(radio.value);
                }

                recalculateLiveSimulation();
            });
        });
    });

    // 3. Event listener untuk checkbox aktivasi listrik
    const chkListrik = document.getElementById('chk_adjust_listrik');
    if (chkListrik) {
        chkListrik.addEventListener('change', function () {
            const container = document.getElementById('section_body_listrik');
            if (container) {
                container.style.opacity = this.checked ? '1' : '0.45';
                container.style.pointerEvents = this.checked ? 'auto' : 'none';
            }
            recalculateLiveSimulation();
        });
    }

    // 4. Event listener untuk checkbox aktivasi gas CNG
    const chkCng = document.getElementById('chk_adjust_cng');
    if (chkCng) {
        chkCng.addEventListener('change', function () {
            const container = document.getElementById('section_body_cng');
            if (container) {
                container.style.opacity = this.checked ? '1' : '0.45';
                container.style.pointerEvents = this.checked ? 'auto' : 'none';
            }
            recalculateLiveSimulation();
        });
    }
}

function toggleListrikModeBox(mode) {
    const boxTotal = document.getElementById('box_listrik_total_tagihan');
    const boxTarif = document.getElementById('box_listrik_tarif_kg');
    if (!boxTotal || !boxTarif) return;

    if (mode === 'total_tagihan' || mode === 'proporsional_wip') {
        boxTotal.style.display = 'block';
        boxTarif.style.display = 'none';
    } else {
        boxTotal.style.display = 'none';
        boxTarif.style.display = 'block';
    }
}

function toggleCngModeBox(mode) {
    const boxTarif = document.getElementById('box_cng_tarif_resmi');
    const boxTotal = document.getElementById('box_cng_total_tagihan');
    if (!boxTarif || !boxTotal) return;

    if (mode === 'update_tarif') {
        boxTarif.style.display = 'block';
        boxTotal.style.display = 'none';
    } else {
        boxTarif.style.display = 'none';
        boxTotal.style.display = 'block';
    }
}

/**
 * Kalkulasi Pratinjau Interaktif Real-Time
 */
function recalculateLiveSimulation() {
    const table = document.getElementById('simGridTable');
    if (!table) return;

    const totalWipMonth = parseFloat(table.dataset.monthWip || 0);
    const totalMmbtuMonth = parseFloat(table.dataset.monthMmbtu || 0);
    const countDays = parseInt(table.dataset.monthDays || 0);

    // 1. Parameter Listrik PLN
    const isListrikActive = document.getElementById('chk_adjust_listrik')?.checked ?? true;
    const modeListrik = document.querySelector('input[name="mode_alokasi_listrik"]:checked')?.value || 'total_tagihan';
    const totalTagihanListrik = parseFloat(document.getElementById('input_total_listrik_tagihan')?.value || 0);
    const tarifListrikKg = parseFloat(document.getElementById('input_listrik_tarif_kg')?.value || 0);

    // 2. Parameter Gas CNG
    const isCngActive = document.getElementById('chk_adjust_cng')?.checked ?? false;
    const modeCng = document.querySelector('input[name="mode_cng"]:checked')?.value || 'update_tarif';
    const tarifCngResmi = parseFloat(document.getElementById('input_cng_tarif_resmi')?.value || 0);
    const totalTagihanCng = parseFloat(document.getElementById('input_total_cng_tagihan')?.value || 0);

    let effectiveCngRate = 0;
    if (isCngActive && modeCng === 'total_tagihan' && totalMmbtuMonth > 0) {
        effectiveCngRate = totalTagihanCng / totalMmbtuMonth;
    }

    // 3. Iterasi setiap baris tanggal produksi
    const rows = document.querySelectorAll('.sim-data-row');
    let sumNewListrik = 0;
    let sumNewCng = 0;
    let sumNewTotalBiaya = 0;
    let sumWip = 0;

    rows.forEach(row => {
        const dayWip = parseFloat(row.dataset.wip || 0);
        const dayMmbtu = parseFloat(row.dataset.mmbtu || 0);
        const oldListrik = parseFloat(row.dataset.oldListrik || 0);
        const oldCng = parseFloat(row.dataset.oldCng || 0);
        const oldTotalBiaya = parseFloat(row.dataset.oldTotalBiaya || 0);

        // Hitung Listrik Baru
        let newListrik = oldListrik;
        if (isListrikActive) {
            if (modeListrik === 'total_tagihan' || modeListrik === 'proporsional_wip') {
                newListrik = totalWipMonth > 0 ? (totalTagihanListrik * (dayWip / totalWipMonth)) : (totalTagihanListrik / (countDays || 1));
            } else if (modeListrik === 'tarif_per_kg') {
                newListrik = dayWip * tarifListrikKg;
            }
        }

        // Hitung Gas CNG Baru
        let newCng = oldCng;
        if (isCngActive) {
            if (modeCng === 'update_tarif') {
                newCng = dayMmbtu * tarifCngResmi;
            } else if (modeCng === 'total_tagihan') {
                newCng = dayMmbtu * effectiveCngRate;
            }
        }

        // Hitung Total Biaya & HPP Baru
        const newTotalBiaya = oldTotalBiaya - oldListrik - oldCng + newListrik + newCng;
        const newHppPerKg = dayWip > 0 ? (newTotalBiaya / dayWip) : 0;

        sumNewListrik += newListrik;
        sumNewCng += newCng;
        sumNewTotalBiaya += newTotalBiaya;
        sumWip += dayWip;

        // Render ke tabel
        const cellListrik = row.querySelector('.cell-sim-listrik');
        if (cellListrik) cellListrik.textContent = formatRupiah(newListrik);

        const cellCng = row.querySelector('.cell-sim-cng');
        if (cellCng) cellCng.textContent = formatRupiah(newCng);

        const cellHpp = row.querySelector('.cell-sim-hpp');
        if (cellHpp) cellHpp.textContent = formatRupiah(newHppPerKg);
    });

    // 4. Update Footer Total & Rata-rata
    const footListrik = document.getElementById('foot_total_listrik');
    if (footListrik) footListrik.textContent = formatRupiah(sumNewListrik);

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
    const isListrikActive = document.getElementById('chk_adjust_listrik')?.checked ?? false;
    const isCngActive = document.getElementById('chk_adjust_cng')?.checked ?? false;
    const alertBox = document.getElementById('alertValidateAdjust');

    if (!isListrikActive && !isCngActive) {
        if (alertBox) {
            alertBox.style.display = 'flex';
            alertBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        return;
    }

    if (alertBox) {
        alertBox.style.display = 'none';
    }

    // Isi ringkasan di modal konfirmasi
    const summaryListrik = document.getElementById('confirmSummaryListrik');
    if (summaryListrik) {
        if (isListrikActive) {
            const mode = document.querySelector('input[name="mode_alokasi_listrik"]:checked')?.value;
            if (mode === 'tarif_per_kg') {
                const tarif = parseFloat(document.getElementById('input_listrik_tarif_kg')?.value || 0);
                summaryListrik.textContent = 'Tarif Flat: Rp ' + tarif.toLocaleString('id-ID') + ' / Kg';
            } else {
                const total = parseFloat(document.getElementById('input_total_listrik_tagihan')?.value || 0);
                summaryListrik.textContent = 'Tagihan: ' + formatRupiah(total) + ' (Proporsional WIP)';
            }
            summaryListrik.style.color = '#0284c7';
        } else {
            summaryListrik.textContent = 'Tidak Disesuaikan';
            summaryListrik.style.color = '#94a3b8';
        }
    }

    const summaryGas = document.getElementById('confirmSummaryGas');
    if (summaryGas) {
        if (isCngActive) {
            const mode = document.querySelector('input[name="mode_cng"]:checked')?.value;
            if (mode === 'total_tagihan') {
                const total = parseFloat(document.getElementById('input_total_cng_tagihan')?.value || 0);
                summaryGas.textContent = 'Faktur Tagihan: ' + formatRupiah(total);
            } else {
                const tarif = parseFloat(document.getElementById('input_cng_tarif_resmi')?.value || 0);
                summaryGas.textContent = 'Tarif: Rp ' + tarif.toLocaleString('id-ID') + ' / MMBTU';
            }
            summaryGas.style.color = '#d97706';
        } else {
            summaryGas.textContent = 'Tidak Disesuaikan';
            summaryGas.style.color = '#94a3b8';
        }
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
