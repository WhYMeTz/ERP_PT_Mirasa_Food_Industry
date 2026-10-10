/**
 * QC INBOUND - MODUL KOMODITAS: MINYAK GORENG
 * PT Mirasa Food Industry
 * Logika Parameter FFA COA, Tangki & Suhu Kedatangan Minyak
 */

(function () {
    'use strict';

    window.onMinyakBarangChanged = function (select) {
        if (!select) return;
        const opt = select.selectedOptions ? select.selectedOptions[0] : select.options[select.selectedIndex];
        if (opt) {
            const nama = (opt.getAttribute('data-nama') || opt.text.split(' - ')[1]?.split(' (')[0] || opt.text).trim();
            const upper = nama.toUpperCase();

            let saranJenis = 'RBD Palm Olein / Curah Sawit';
            if (upper.includes('KELAPA') && !upper.includes('SAWIT')) {
                saranJenis = 'Minyak Kelapa (RBD CNO)';
            } else if (upper.includes('SAWIT')) {
                saranJenis = 'RBD Palm Olein / Curah Sawit';
            }

            const inputs = [
                document.getElementById('minyakNamaJenisInput'),
                document.getElementById('namaJenisInput'),
                document.getElementById('editNamaJenis')
            ];

            inputs.forEach(input => {
                if (input) {
                    if (!input.value || input.dataset.autoFilled === 'true') {
                        input.value = saranJenis;
                        input.dataset.autoFilled = 'true';
                    }
                }
            });
        }
    };

    window.syncMinyakQuantity = function (val) {
        const grossInput = document.getElementById('minyakQtyGross');
        const rejectInput = document.getElementById('minyakQtyReject');
        const labelNetto = document.getElementById('labelMinyakNetto');
        const inPabrik = document.getElementById('inputJumlahPabrik');

        const gross = parseFloat(grossInput ? grossInput.value : (val || 0)) || 0;
        const reject = parseFloat(rejectInput ? rejectInput.value : 0) || 0;
        const netto = Math.max(0, gross - reject);

        if (labelNetto) {
            labelNetto.innerText = `${netto.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} KG`;
        }

        if (inPabrik && gross > 0 && document.activeElement === grossInput) {
            inPabrik.value = gross;
        }
    };

    window.checkMinyakAcceptance = function () {
        const ffaInput = document.getElementById('inputMinyakFfaQc');
        const ffaQc = parseFloat(ffaInput?.value || 0);
        const radioTerima = document.getElementById('minyakRadioTerima');
        const radioTolak = document.getElementById('minyakRadioTolak');

        // Batas standar FFA SNI / HACCP PT Mirasa: Max 0.20%
        if (ffaQc > 0.20) {
            if (radioTolak) radioTolak.checked = true;
            if (typeof window.showQcToast === 'function') {
                window.showQcToast(
                    'FFA Melebihi Ambang Batas',
                    `Kadar FFA ${ffaQc}% melampaui batas maksimal HACCP (0.20%). Disarankan TOLAK kedatangan minyak ini.`,
                    ffaInput,
                    2,
                    true
                );
            }
        } else if (ffaQc > 0 && ffaQc <= 0.20) {
            if (radioTerima && !radioTerima.checked && radioTolak && !radioTolak.dataset.userManual) {
                radioTerima.checked = true;
            }
        }
    };

    console.log('[QC Modul] Minyak Goreng logic loaded successfully.');
})();
