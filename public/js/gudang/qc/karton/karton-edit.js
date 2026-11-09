/**
 * QC INBOUND - EDIT KOREKSI DOKUMEN KARTON BOX
 * PT Mirasa Food Industry
 * Separated JavaScript for Clean MVC Architecture
 */

(function () {
    'use strict';

    // 1. Sinkronisasi Kuantitas Real-Time (Mendukung Split Grade A & Grade B)
    window.onKartonEditQtySplitChanged = function (source) {
        const inGradeA = document.getElementById('kartonQtyGradeA');
        const inGradeB = document.getElementById('kartonQtyGradeB');
        const rejectInput = document.getElementById('kartonQtyReject');
        const nettoLabel = document.getElementById('labelKartonEditNetto');
        const inputQtyTerima = document.getElementById('inputQtyTerima');
        const inputQtyTolak = document.getElementById('inputQtyTolak');

        let qtyA = parseFloat(inGradeA?.value) || 0;
        let qtyB = parseFloat(inGradeB?.value) || 0;
        let reject = parseFloat(rejectInput?.value) || 0;

        const prevTotal = parseFloat(inGradeA?.dataset?.prevTotal) || (qtyA + qtyB + reject);

        // Jika reject diubah, kurangi kuantitas diterima (prioritas kurangi Grade A lalu Grade B)
        if (source === 'reject') {
            if (prevTotal > 0) {
                if (reject > prevTotal) {
                    reject = prevTotal;
                    if (rejectInput) rejectInput.value = reject;
                }
                const remaining = Math.max(0, prevTotal - reject);
                if (qtyB > remaining) {
                    qtyB = remaining;
                    if (inGradeB) inGradeB.value = qtyB;
                }
                qtyA = Math.max(0, remaining - qtyB);
                if (inGradeA) inGradeA.value = qtyA;
            }
        } else if (source === 'grade_b') {
            if (prevTotal > 0) {
                const remaining = Math.max(0, prevTotal - reject);
                if (qtyB > remaining) {
                    qtyB = remaining;
                    if (inGradeB) inGradeB.value = qtyB;
                }
                qtyA = Math.max(0, remaining - qtyB);
                if (inGradeA) inGradeA.value = qtyA;
            }
        }

        const totalNetto = qtyA + qtyB;
        const totalGross = totalNetto + reject;

        if (nettoLabel) {
            nettoLabel.innerText = new Intl.NumberFormat('id-ID').format(totalNetto) + ' PCS';
        }

        const kesimpulanEl = document.getElementById('inputKartonKesimpulan');
        const isTolak = kesimpulanEl && kesimpulanEl.value === 'TOLAK';

        if (inputQtyTerima) inputQtyTerima.value = isTolak ? 0 : totalNetto;
        if (inputQtyTolak) inputQtyTolak.value = isTolak ? totalGross : reject;
    };

    window.calcKartonEditNetto = function () {
        window.onKartonEditQtySplitChanged('init');
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
        const hiddenKeputusan = document.getElementById('hiddenKeputusanQc');

        if (input1) input1.value = val;
        if (input2) input2.value = val;
        if (hiddenKeputusan) hiddenKeputusan.value = val === 'TERIMA' ? 'TERIMA' : 'REJECT_TOTAL';

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

        // Init kalkulasi netto & simpan snapshot awal untuk kalkulasi susut reject
        const inA = document.getElementById('kartonQtyGradeA');
        const inB = document.getElementById('kartonQtyGradeB');
        const inRej = document.getElementById('kartonQtyReject');
        if (inA) inA.dataset.prevA = inA.value || '0';
        if (inB) inB.dataset.prevB = inB.value || '0';
        if (inA) inA.dataset.prevTotal = String((parseFloat(inA?.value) || 0) + (parseFloat(inB?.value) || 0) + (parseFloat(inRej?.value) || 0));

        window.calcKartonEditNetto();

        // Submit Handler: Menyiapkan array items[] sesuai Grade A & Grade B
        const form = document.getElementById('qcEditKartonForm');
        form?.addEventListener('submit', function (e) {
            const qtyA = parseFloat(document.getElementById('kartonQtyGradeA')?.value) || 0;
            const qtyB = parseFloat(document.getElementById('kartonQtyGradeB')?.value) || 0;
            const reject = parseFloat(document.getElementById('kartonQtyReject')?.value) || 0;
            const totalNetto = qtyA + qtyB;
            const totalGross = totalNetto + reject;

            if (totalGross <= 0) {
                e.preventDefault();
                alert('Kuantitas karton (Grade A, Grade B, atau Reject) wajib diisi lebih dari 0!');
                return false;
            }

            // Bersihkan input hidden items lama jika ada
            form.querySelectorAll('input[name^="items["]').forEach(function (el) {
                el.remove();
            });

            function injectHiddenInput(name, value) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = name;
                input.value = value;
                form.appendChild(input);
            }

            const bId = document.getElementById('selectEditBarang')?.value;
            const podtlId = document.getElementById('editFirstPodtlId')?.value || '';
            const qcdtlIdA = document.getElementById('editQcdtlIdA')?.value || '';
            const qcdtlIdB = document.getElementById('editQcdtlIdB')?.value || '';
            const firstQcdtlId = document.getElementById('editFirstQcdtlId')?.value || '';
            const isTolak = document.getElementById('inputKartonKesimpulan')?.value === 'TOLAK';
            const catatanText = document.getElementById('inputCatatanDtl')?.value || '';

            function injectCommonFields(idx) {
                injectHiddenInput(`items[${idx}][barang_id]`, bId);
                if (podtlId) injectHiddenInput(`items[${idx}][podtl_id]`, podtlId);
                injectHiddenInput(`items[${idx}][refraksi_persen]`, 0);
                injectHiddenInput(`items[${idx}][status_raw_material]`, document.getElementById('inputRawMaterial')?.value || 'OK');
                injectHiddenInput(`items[${idx}][kemasan_kondisi]`, document.getElementById('inputKemasanKondisi')?.value || 'OK');

                // Checkbox 7 Cacat Kemasan
                injectHiddenInput(`items[${idx}][kemasan_kotor]`, document.getElementById('checkCacatKotor')?.checked ? '1' : '0');
                injectHiddenInput(`items[${idx}][kemasan_apek]`, document.getElementById('checkCacatApek')?.checked ? '1' : '0');
                injectHiddenInput(`items[${idx}][kemasan_basah]`, document.getElementById('checkCacatBasah')?.checked ? '1' : '0');
                injectHiddenInput(`items[${idx}][kemasan_jamur]`, document.getElementById('checkCacatJamur')?.checked ? '1' : '0');
                injectHiddenInput(`items[${idx}][kemasan_sobek]`, document.getElementById('checkCacatSobek')?.checked ? '1' : '0');
                injectHiddenInput(`items[${idx}][kemasan_berminyak]`, document.getElementById('checkCacatBerminyak')?.checked ? '1' : '0');
                injectHiddenInput(`items[${idx}][kemasan_berdebu]`, document.getElementById('checkCacatBerdebu')?.checked ? '1' : '0');

                // Dimensi & Spesifikasi
                injectHiddenInput(`items[${idx}][dimensi_panjang_analisa]`, document.getElementById('inputPanjangAnalisa')?.value || '');
                injectHiddenInput(`items[${idx}][dimensi_panjang_standar]`, document.querySelector('input[name*="dimensi_panjang_standar"]')?.value || '40 cm');
                injectHiddenInput(`items[${idx}][dimensi_lebar_analisa]`, document.getElementById('inputLebarAnalisa')?.value || '');
                injectHiddenInput(`items[${idx}][dimensi_lebar_standar]`, document.querySelector('input[name*="dimensi_lebar_standar"]')?.value || '30 cm');
                injectHiddenInput(`items[${idx}][dimensi_tinggi_analisa]`, document.getElementById('inputTinggiAnalisa')?.value || '');
                injectHiddenInput(`items[${idx}][dimensi_tinggi_standar]`, document.querySelector('input[name*="dimensi_tinggi_standar"]')?.value || '25 cm');
                injectHiddenInput(`items[${idx}][spesifikasi_analisa]`, document.getElementById('inputSpesifikasiAnalisa')?.value || '');
                injectHiddenInput(`items[${idx}][spesifikasi_standar]`, document.querySelector('input[name*="spesifikasi_standar"]')?.value || 'Sesuai Spesifikasi MFI');
            }

            if (qtyA > 0 && qtyB > 0) {
                // MULTI-GRADE: Terdapat Grade A dan Grade B
                // Item 0: Grade A (Dus Baru)
                if (qcdtlIdA) injectHiddenInput('items[0][qcdtl_id]', qcdtlIdA);
                injectHiddenInput('items[0][qty_timbang_gross]', qtyA);
                injectHiddenInput('items[0][qty_reject]', 0);
                injectHiddenInput('items[0][qty_netto_lolos]', isTolak ? 0 : qtyA);
                injectHiddenInput('items[0][grade_cd]', isTolak ? 'REJECT' : 'A');
                injectHiddenInput('items[0][keputusan_qc]', isTolak ? 'REJECT_TOTAL' : 'PASSED');
                injectHiddenInput('items[0][catatan_dtl]', (catatanText ? catatanText + ' | ' : '') + 'Grade A (Dus Baru)');
                injectCommonFields(0);

                // Item 1: Grade B (Dus Puteran/Reuse)
                if (qcdtlIdB) injectHiddenInput('items[1][qcdtl_id]', qcdtlIdB);
                injectHiddenInput('items[1][qty_timbang_gross]', qtyB + reject);
                injectHiddenInput('items[1][qty_reject]', reject);
                injectHiddenInput('items[1][qty_netto_lolos]', isTolak ? 0 : qtyB);
                injectHiddenInput('items[1][grade_cd]', isTolak ? 'REJECT' : 'B');
                injectHiddenInput('items[1][keputusan_qc]', isTolak ? 'REJECT_TOTAL' : (reject > 0 ? 'REJECT_PARTIAL' : 'PASSED'));
                injectHiddenInput('items[1][catatan_dtl]', (catatanText ? catatanText + ' | ' : '') + 'Grade B (Dus Puteran)');
                injectCommonFields(1);

                injectHiddenInput('grade_cd', 'A');
            } else {
                // SINGLE-GRADE: Hanya Grade A saja atau Grade B saja
                const singleGrade = (qtyB > 0 && qtyA === 0) ? 'B' : 'A';
                const lolosQty = (qtyB > 0 && qtyA === 0) ? qtyB : qtyA;
                const targetQcDtlId = (singleGrade === 'B' ? (qcdtlIdB || firstQcdtlId) : (qcdtlIdA || firstQcdtlId));

                if (targetQcDtlId) injectHiddenInput('items[0][qcdtl_id]', targetQcDtlId);
                injectHiddenInput('items[0][qty_timbang_gross]', totalGross);
                injectHiddenInput('items[0][qty_reject]', reject);
                injectHiddenInput('items[0][qty_netto_lolos]', isTolak ? 0 : lolosQty);
                injectHiddenInput('items[0][grade_cd]', isTolak ? 'REJECT' : singleGrade);
                injectHiddenInput('items[0][keputusan_qc]', isTolak ? 'REJECT_TOTAL' : (reject > 0 ? 'REJECT_PARTIAL' : 'PASSED'));
                injectHiddenInput('items[0][catatan_dtl]', catatanText);
                injectCommonFields(0);

                injectHiddenInput('grade_cd', singleGrade);
            }

            const overlay = document.getElementById('qcSubmitOverlay');
            if (overlay) overlay.style.display = 'flex';
        });
    });

})();
