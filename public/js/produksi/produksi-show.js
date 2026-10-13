/**
 * JavaScript Detail Produksi Harian (Show)
 * Path: public/js/produksi/produksi-show.js
 * PT Mirasa Food Industry
 */

function updateLiveStickerCard(kartonNum) {
    const el = document.getElementById('previewStickerKartonNo');
    if (el && kartonNum) {
        el.textContent = kartonNum;
    }
}

/**
 * Modal Penyesuaian HPP Controls (Standar Akuntansi)
 */
function openModalAdjustHpp() {
    const modal = document.getElementById('modalAdjustHpp');
    if (modal) {
        modal.style.display = 'block';
        recalculateHppSimulation();
    }
}

function closeModalAdjustHpp() {
    const modal = document.getElementById('modalAdjustHpp');
    if (modal) {
        modal.style.display = 'none';
    }
}

window.addEventListener('click', function(event) {
    const modal = document.getElementById('modalAdjustHpp');
    if (modal && event.target === modal) {
        closeModalAdjustHpp();
    }
});

/**
 * Kalkulasi Live Simulasi HPP Baru pada Modal Penyesuaian
 */
function recalculateHppSimulation() {
    const cfg = window.adjProdConfig || {};
    const wipQty = parseFloat(cfg.totalWipQty) || 0;
    const biayaAwal = parseFloat(cfg.totalBiayaAwal) || 0;
    const hppAwal = parseFloat(cfg.hppAwal) || 0;

    const singkongBaru = parseFloat(document.getElementById('input_adj_singkong')?.value) || 0;
    const minyakBaru = parseFloat(document.getElementById('input_adj_minyak')?.value) || 0;
    const bumbuBaru = parseFloat(document.getElementById('input_adj_bumbu')?.value) || 0;
    const tkBaru = parseFloat(document.getElementById('input_adj_tk')?.value) || 0;
    const listrikBaru = parseFloat(document.getElementById('input_adj_listrik')?.value) || 0;
    const cngBaru = parseFloat(document.getElementById('input_adj_cng')?.value) || 0;

    const deltaSingkong = singkongBaru - (parseFloat(cfg.singkongAwal) || 0);
    const deltaMinyak = minyakBaru - (parseFloat(cfg.minyakAwal) || 0);
    const deltaBumbu = bumbuBaru - (parseFloat(cfg.bumbuAwal) || 0);
    const deltaTk = tkBaru - (parseFloat(cfg.tkAwal) || 0);
    const deltaListrik = listrikBaru - (parseFloat(cfg.listrikAwal) || 0);
    const deltaCng = cngBaru - (parseFloat(cfg.cngAwal) || 0);

    const deltaTotal = deltaSingkong + deltaMinyak + deltaBumbu + deltaTk + deltaListrik + deltaCng;
    const biayaBaru = Math.round((biayaAwal + deltaTotal) * 100) / 100;
    const hppBaru = wipQty > 0 ? Math.round((biayaBaru / wipQty) * 100) / 100 : 0;
    const deltaHpp = Math.round((hppBaru - hppAwal) * 100) / 100;

    const hppEl = document.getElementById('simulasiHppBaruText');
    const badgeEl = document.getElementById('simulasiSelisihHppBadge');
    const totalEl = document.getElementById('simulasiTotalBiayaBaruText');

    if (hppEl) {
        hppEl.textContent = 'Rp ' + formatRupiahLocal(hppBaru);
    }
    if (totalEl) {
        totalEl.textContent = 'Rp ' + formatRupiahLocal(biayaBaru);
    }
    if (badgeEl) {
        if (deltaHpp > 0) {
            badgeEl.style.background = '#fef2f2';
            badgeEl.style.color = '#dc2626';
            badgeEl.textContent = 'Selisih HPP: +Rp ' + formatRupiahLocal(deltaHpp) + ' / kg (Naik)';
        } else if (deltaHpp < 0) {
            badgeEl.style.background = '#ecfdf5';
            badgeEl.style.color = '#059669';
            badgeEl.textContent = 'Selisih HPP: -Rp ' + formatRupiahLocal(Math.abs(deltaHpp)) + ' / kg (Turun)';
        } else {
            badgeEl.style.background = '#f1f5f9';
            badgeEl.style.color = '#64748b';
            badgeEl.textContent = 'Selisih HPP: Rp 0 / kg (Tidak Ada Perubahan)';
        }
    }
}

function formatRupiahLocal(num) {
    return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(num);
}
