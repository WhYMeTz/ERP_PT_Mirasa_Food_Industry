/**
 * QC INBOUND - MODUL KOMODITAS: BAHAN PENOLONG (MSG, GARAM, PERENYAH)
 * PT Mirasa Food Industry
 * Logika Parameter Organoleptik, Gumpalan, & Sertifikasi Halal
 */

(function () {
    'use strict';

    window.onBpBarangChanged = function (select) {
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

    window.checkBpAcceptance = function () {
        const halalAda = document.querySelector('input[name="bp_halal_logo"]:checked')?.value;
        const radioTerima = document.getElementById('bpRadioTerima');
        const radioTolak = document.getElementById('bpRadioTolak');

        if (halalAda === 'TIDAK_ADA') {
            if (radioTolak) radioTolak.checked = true;
            if (typeof window.showQcToast === 'function') {
                window.showQcToast('Logo Halal Tidak Ditemukan', 'Bahan Penolong wajib memiliki logo dan sertifikasi Halal resmi.', null, 2, true);
            }
        }
    };

    console.log('[QC Modul] Bahan Penolong logic loaded successfully.');
})();
