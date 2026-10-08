/**
 * QC INBOUND - MODUL KOMODITAS: SINGKONG
 * PT Mirasa Food Industry
 * Logika Kartu Inspeksi, Kalkulasi Timbangan & Pengujian Fryer
 */

(function () {
    'use strict';

    window.onGradeChanged = function (idx, grade) {
        const warnEl = document.getElementById(`gradeB_warning_${idx}`);
        if (warnEl) {
            warnEl.style.display = (grade === 'B') ? 'block' : 'none';
        }
    };

    window.quickRejectGradeB = function (idx) {
        const radioTolak = document.getElementById('radioTolak');
        if (radioTolak) {
            radioTolak.checked = true;
            if (typeof window.onKesimpulanChange === 'function') {
                window.onKesimpulanChange('TOLAK');
            }
        }
        if (typeof window.showQcToast === 'function') {
            window.showQcToast('Truk Singkong Ditolak', 'Keputusan dialihkan ke TOLAK TOTAL karena stok Grade B gudang sudah penuh.', radioTolak, 3, true);
        }
    };

    window.calculateCard = function (idx) {
        const grossInput = document.getElementById(`gross_${idx}`);
        const refraksiInput = document.getElementById(`refraksi_${idx}`);
        const rejectInput = document.getElementById(`reject_${idx}`);
        const nettoDisplay = document.getElementById(`netto_display_${idx}`);
        const potonganKgDisplay = document.getElementById(`potongan_kg_display_${idx}`);

        if (!grossInput) return;

        const gross = parseFloat(grossInput.value) || 0;
        const refraksiPersen = parseFloat(refraksiInput ? refraksiInput.value : 0) || 0;
        const reject = parseFloat(rejectInput ? rejectInput.value : 0) || 0;

        const potonganKg = (gross * refraksiPersen) / 100;
        let netto = gross - potonganKg - reject;
        if (netto < 0) netto = 0;

        if (potonganKgDisplay) {
            potonganKgDisplay.innerText = potonganKg.toLocaleString('id-ID', { maximumFractionDigits: 1 }) + ' kg';
        }
        if (nettoDisplay) {
            nettoDisplay.innerText = netto.toLocaleString('id-ID', { maximumFractionDigits: 1 }) + ' kg';
        }

        if (typeof window.updateTotalCalculations === 'function') {
            window.updateTotalCalculations();
        }
    };

    window.onItemBarangChanged = function (idx) {
        const sel = document.getElementById(`barang_select_${idx}`);
        if (!sel) return;
        const opt = sel.options[sel.selectedIndex];
        if (opt) {
            const nama = opt.getAttribute('data-nama') || '';
            const hiddenNama = document.getElementById(`nama_jenis_${idx}`);
            if (hiddenNama) hiddenNama.value = nama;
        }
    };

    console.log('[QC Modul] Singkong logic loaded successfully.');
})();
