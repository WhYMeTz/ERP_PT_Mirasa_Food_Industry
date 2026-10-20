/**
 * QC INBOUND - EDIT KOREKSI DOKUMEN KARTON BOX
 * PT Mirasa Food Industry
 * Separated JavaScript for Clean MVC Architecture
 */

(function () {
    'use strict';

    // 1. Kalkulasi Netto Karton Box
    window.calcKartonEditNetto = function () {
        const grossInput = document.getElementById('editQtyGross');
        const rejectInput = document.getElementById('editQtyReject');
        const nettoLabel = document.getElementById('labelKartonEditNetto');
        const hiddenNetto = document.getElementById('inputQtyNettoLolos');
        const inputQtyTerima = document.getElementById('inputQtyTerima');
        const inputQtyTolak = document.getElementById('inputQtyTolak');

        const gross = parseFloat(grossInput?.value) || 0;
        const reject = parseFloat(rejectInput?.value) || 0;
        const netto = Math.max(0, gross - reject);

        if (hiddenNetto) hiddenNetto.value = netto;
        if (nettoLabel) nettoLabel.innerText = new Intl.NumberFormat('id-ID').format(netto) + ' Pcs';

        const kesimpulanEl = document.getElementById('inputKartonKesimpulan');
        const isTolak = kesimpulanEl && kesimpulanEl.value === 'TOLAK';

        if (inputQtyTerima) inputQtyTerima.value = isTolak ? 0 : netto;
        if (inputQtyTolak) inputQtyTolak.value = isTolak ? gross : reject;
    };

    // 2. Chip Parameter Helper (Panjang, Lebar, Tinggi, Spesifikasi)
    window.setParamValue = function (targetId, val) {
        const el = document.getElementById(targetId);
        if (el) {
            el.value = val;
            el.focus();
        }
    };

    // 3. Toggle Modern Checkbox (7 Titik Cacat Kemasan)
    window.toggleModernCheck = function (checkId) {
        const chk = document.getElementById(checkId);
        if (!chk) return;
        chk.checked = !chk.checked;
        window.updateModernCheckCard(chk);
    };

    window.updateModernCheckCard = function (chk) {
        const card = chk.closest('.modern-check-card');
        const indicator = document.getElementById('indicator_' + chk.id);
        if (chk.checked) {
            card?.classList.add('is-checked');
            if (indicator) indicator.style.color = '#ffffff';
        } else {
            card?.classList.remove('is-checked');
            if (indicator) indicator.style.color = 'transparent';
        }
    };

    // 4. Raw Material & Kemasan Choice
    window.setRawMaterialStatus = function (status) {
        const input = document.getElementById('inputRawMaterial');
        if (input) input.value = status;

        const btnOk = document.getElementById('btnRawOk');
        const btnTdk = document.getElementById('btnRawTdkStd');

        if (status === 'OK') {
            btnOk?.classList.add('active', 'ok');
            btnTdk?.classList.remove('active', 'danger');
        } else {
            btnOk?.classList.remove('active', 'ok');
            btnTdk?.classList.add('active', 'danger');
        }
    };

    window.setKemasanKondisi = function (status) {
        const input = document.getElementById('inputKemasanKondisi');
        if (input) input.value = status;

        const btnOk = document.getElementById('btnKemasanOk');
        const btnTdk = document.getElementById('btnKemasanTdkStd');

        if (status === 'OK') {
            btnOk?.classList.add('active', 'ok');
            btnTdk?.classList.remove('active', 'danger');
        } else {
            btnOk?.classList.remove('active', 'ok');
            btnTdk?.classList.add('active', 'danger');
        }
    };

    // 5. Segmented Toggle SJH / Halal
    window.setHalalToggle = function (field, val) {
        const map = {
            'bebas_cemaran': { input: 'input_bebas_cemaran', btn0: 'btnCemarAda', btn1: 'btnCemarBebas' },
            'angkut_barang_haram': { input: 'input_angkut_barang_haram', btn0: 'btnHaramTidak', btn1: 'btnHaramYa' },
            'terdaftar_lppom': { input: 'input_terdaftar_lppom', btn0: 'btnLppomTidak', btn1: 'btnLppomYa' },
            'ada_sertifikat': { input: 'input_ada_sertifikat', btn0: 'btnSertifikatTidak', btn1: 'btnSertifikatYa' },
            'sertifikat_berlaku': { input: 'input_sertifikat_berlaku', btn0: 'btnBerlakuTidak', btn1: 'btnBerlakuYa' }
        };

        const cfg = map[field];
        if (!cfg) return;

        const inputEl = document.getElementById(cfg.input);
        if (inputEl) inputEl.value = val;

        const btn0 = document.getElementById(cfg.btn0);
        const btn1 = document.getElementById(cfg.btn1);

        if (field === 'angkut_barang_haram') {
            // Nilai 0 = Aman/Bebas Haram, Nilai 1 = Berbahaya/Tercampur
            if (val === 0) {
                btn0?.classList.add('active', 'ok');
                btn0?.classList.remove('danger');
                btn1?.classList.remove('active', 'danger');
            } else {
                btn0?.classList.remove('active', 'ok');
                btn1?.classList.add('active', 'danger');
            }
        } else {
            // Nilai 1 = Aman/Ya, Nilai 0 = Tidak/Gagal
            if (val === 1) {
                btn1?.classList.add('active', 'ok');
                btn1?.classList.remove('danger');
                btn0?.classList.remove('active', 'danger');
            } else {
                btn1?.classList.remove('active', 'ok');
                btn0?.classList.add('active', 'danger');
            }
        }
    };

    // 6. Kesimpulan Disposisi TERIMA vs TOLAK
    window.setKesimpulanKarton = function (val) {
        const input1 = document.getElementById('inputKartonKesimpulan');
        const input2 = document.getElementById('inputKesimpulanQc');
        const cardTerima = document.getElementById('cardDisposisiTerima');
        const cardTolak = document.getElementById('cardDisposisiTolak');
        const radioTerima = document.getElementById('radioTerima');
        const radioTolak = document.getElementById('radioTolak');

        if (input1) input1.value = val;
        if (input2) input2.value = val;

        if (val === 'TERIMA') {
            cardTerima?.classList.add('active', 'terima');
            cardTolak?.classList.remove('active', 'tolak');
            if (radioTerima) radioTerima.checked = true;
        } else {
            cardTolak?.classList.add('active', 'tolak');
            cardTerima?.classList.remove('active', 'terima');
            if (radioTolak) radioTolak.checked = true;
        }

        window.calcKartonEditNetto();
    };

    // 7. Filter Mitra Supplier
    window.filterKartonSuppliers = function (query) {
        const q = (query || '').toLowerCase().trim();
        const select = document.getElementById('editSupplierSelect');
        if (!select) return;

        for (let i = 0; i < select.options.length; i++) {
            const opt = select.options[i];
            const text = (opt.text || '').toLowerCase();
            if (!q || text.includes(q)) {
                opt.style.display = '';
                opt.disabled = false;
            } else {
                opt.style.display = 'none';
                opt.disabled = true;
            }
        }
    };

    window.onEditSupplierChange = function (select) {
        const opt = select.options[select.selectedIndex];
        if (opt && opt.value) {
            const supNm = opt.getAttribute('data-supplier-nm') || opt.text.split('(')[0].trim();
            const produsenInput = document.getElementById('editNamaProdusen');
            if (produsenInput) produsenInput.value = supNm;
        }
    };

    window.onEditBarangChanged = function (select) {
        const opt = select.options[select.selectedIndex];
        if (opt && opt.value) {
            const bNm = opt.getAttribute('data-nama') || opt.text.split(' - ')[1]?.split(' (')[0] || opt.text;
            const namaEl = document.getElementById('editNamaJenis');
            if (namaEl && !namaEl.value) {
                namaEl.value = bNm;
            }
        }
    };

    // Inisialisasi saat DOM siap
    document.addEventListener('DOMContentLoaded', function () {
        // Init Checkbox indicators
        ['checkCacatKotor', 'checkCacatApek', 'checkCacatBasah', 'checkCacatJamur', 'checkCacatSobek', 'checkCacatBerminyak', 'checkCacatBerdebu'].forEach(function (id) {
            const chk = document.getElementById(id);
            if (chk) window.updateModernCheckCard(chk);
        });

        // Init kalkulasi netto
        window.calcKartonEditNetto();

        // Overlay submit handler
        document.getElementById('qcEditKartonForm')?.addEventListener('submit', function () {
            const overlay = document.getElementById('qcSubmitOverlay');
            if (overlay) overlay.style.display = 'flex';
        });
    });

})();
