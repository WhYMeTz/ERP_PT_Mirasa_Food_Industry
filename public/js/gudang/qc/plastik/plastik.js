/**
 * QC INBOUND - MODUL KOMODITAS: PLASTIK KEMASAN
 * PT Mirasa Food Industry
 * Logika Parameter Ketebalan Mikrometer, Sealing & Cacat Cetak
 */

(function () {
    'use strict';

    window.onPlastikBarangChanged = function (select) {
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

    window.checkPlastikAcceptance = function () {
        const cacat = parseFloat(document.getElementById('plastikQtyCacat')?.value || 0);
        const radioTerima = document.getElementById('plastikRadioTerima');
        const radioTolak = document.getElementById('plastikRadioTolak');

        if (cacat > 50) {
            if (radioTolak) radioTolak.checked = true;
            if (typeof window.showQcToast === 'function') {
                window.showQcToast('Jumlah Cacat Plastik Tinggi', `Ditemukan ${cacat} pcs cacat cetak/sobek. Periksa kembali sebelum menerima.`, null, 2, true);
            }
        }
    };

    console.log('[QC Modul] Plastik Kemasan logic loaded successfully.');
})();
