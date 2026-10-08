/**
 * QC INBOUND - MODUL KOMODITAS: KARTON BOX
 * PT Mirasa Food Industry
 * Logika Parameter Dimensi PxLxT, Gramatur & Burst Test
 */

(function () {
    'use strict';

    window.onKartonBarangChanged = function (select) {
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

    window.checkKartonAcceptance = function () {
        const cacat = parseFloat(document.getElementById('kartonQtyCacat')?.value || 0);
        const radioTerima = document.getElementById('kartonRadioTerima');
        const radioTolak = document.getElementById('kartonRadioTolak');

        if (cacat > 20) {
            if (radioTolak) radioTolak.checked = true;
            if (typeof window.showQcToast === 'function') {
                window.showQcToast('Karton Rusak / Cacat Terlalu Banyak', `Ditemukan ${cacat} box penyok/basah. Periksa keputusan penerimaan.`, null, 2, true);
            }
        }
    };

    console.log('[QC Modul] Karton Box logic loaded successfully.');
})();
