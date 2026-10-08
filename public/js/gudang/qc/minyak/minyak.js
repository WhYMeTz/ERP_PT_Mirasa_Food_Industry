/**
 * QC INBOUND - MODUL KOMODITAS: MINYAK GORENG
 * PT Mirasa Food Industry
 * Logika Parameter FFA COA, Tangki & Suhu Kedatangan Minyak
 */

(function () {
    'use strict';

    window.onMinyakBarangChanged = function (select) {
        if (!select) return;
        const opt = select.selectedOptions[0];
        if (opt) {
            const nama = opt.getAttribute('data-nama') || opt.text.split(' - ')[1]?.split(' (')[0] || opt.text;
            const namaInput = document.getElementById('namaJenisInput');
            if (namaInput) {
                namaInput.value = nama.trim();
            }
        }
    };

    window.checkMinyakAcceptance = function () {
        const ffaQc = parseFloat(document.getElementById('inputMinyakFfaQc')?.value || 0);
        const radioTerima = document.getElementById('minyakRadioTerima');
        const radioTolak = document.getElementById('minyakRadioTolak');

        // Batas standar FFA SNI / HACCP PT Mirasa: Max 0.15%
        if (ffaQc > 0.20) {
            if (radioTolak) radioTolak.checked = true;
            if (typeof window.showQcToast === 'function') {
                window.showQcToast('FFA Melebihi Ambang Batas', `Kadar FFA ${ffaQc}% melampaui batas maksimal (0.20%). Disarankan TOLAK.`, document.getElementById('inputMinyakFfaQc'), 2, true);
            }
        }
    };

    console.log('[QC Modul] Minyak Goreng logic loaded successfully.');
})();
