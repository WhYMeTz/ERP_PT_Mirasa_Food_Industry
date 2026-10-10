/**
 * QC INBOUND - MODUL KOMODITAS: PLASTIK KEMASAN
 * PT Mirasa Food Industry
 * Dedicated JS controller untuk halaman Mobile QC Plastik Kemasan
 * No. Dokumen: MFI/HACCP-04/FRM-03/030/VIII/2021
 */

(function () {
    'use strict';

    let currentPoCategory = 'PLASTIK';
    let currentPoSearch = '';
    let currentSupplierCategory = 'VENDOR';
    let currentSupplierSearch = '';
    let isSubmitting = false;

    // 1. Tab Navigation (Tahap 1: Dokumen & Armada, Tahap 2: Mutu Plastik)
    window.switchPlastikTab = function (tabNumber) {
        const sec1 = document.getElementById('qcSection1');
        const sec2 = document.getElementById('qcSection2');
        const btn1 = document.getElementById('tabBtn1');
        const btn2 = document.getElementById('tabBtn2');

        if (!sec1 || !sec2) return;

        if (tabNumber === 1) {
            sec1.style.display = 'block';
            sec2.style.display = 'none';

            if (btn1) {
                btn1.style.background = '#0d9488';
                btn1.style.borderColor = '#0d9488';
                btn1.style.color = '#ffffff';
            }
            if (btn2) {
                btn2.style.background = '#ffffff';
                btn2.style.borderColor = '#cbd5e1';
                btn2.style.color = '#475569';
            }
            updateDockingButtons(1);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } else {
            // Validasi awal tahap 1
            const sup = document.getElementById('supplierSelect')?.value;
            const gud = document.getElementById('gudangSelect')?.value;
            if (!sup) {
                alert('Silakan pilih Mitra Supplier / Produsen Plastik terlebih dahulu!');
                document.getElementById('supplierSelect')?.focus();
                return;
            }
            if (!gud) {
                alert('Silakan pilih Perusahaan / Gudang Tujuan Bongkar terlebih dahulu!');
                document.getElementById('gudangSelect')?.focus();
                return;
            }

            sec1.style.display = 'none';
            sec2.style.display = 'block';

            if (btn1) {
                btn1.style.background = '#ffffff';
                btn1.style.borderColor = '#cbd5e1';
                btn1.style.color = '#475569';
            }
            if (btn2) {
                btn2.style.background = '#0d9488';
                btn2.style.borderColor = '#0d9488';
                btn2.style.color = '#ffffff';
            }
            updateDockingButtons(2);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    };

    function updateDockingButtons(tab) {
        const btnPrev = document.getElementById('dockBtnPrev');
        const btnNext = document.getElementById('dockBtnNext');
        const nextText = document.getElementById('dockBtnNextText');
        const nextIcon = document.getElementById('dockBtnNextIcon');

        if (!btnNext) return;

        if (tab === 1) {
            if (btnPrev) btnPrev.style.display = 'none';
            btnNext.style.background = '#0d9488';
            btnNext.style.borderColor = '#0d9488';
            if (nextText) nextText.innerText = 'Lanjut: Mutu Plastik';
            if (nextIcon) nextIcon.style.display = 'inline-block';
            btnNext.onclick = function () { window.switchPlastikTab(2); };
        } else {
            if (btnPrev) btnPrev.style.display = 'inline-flex';
            btnNext.style.background = '#059669';
            btnNext.style.borderColor = '#059669';
            if (nextText) nextText.innerText = '💾 Simpan QC Plastik';
            if (nextIcon) nextIcon.style.display = 'none';
            btnNext.onclick = function () { window.submitPlastikForm(); };
        }
    }

    // 2. Auto-suggest Nama Jenis berdasarkan Master Barang Plastik
    window.onPlastikBarangChanged = function (select) {
        if (!select) return;
        const opt = select.selectedOptions ? select.selectedOptions[0] : select.options[select.selectedIndex];
        if (opt) {
            const nama = (opt.getAttribute('data-nama') || opt.text.split(' - ')[1]?.split(' (')[0] || opt.text).trim();
            const inputs = [
                document.getElementById('plastikNamaJenisInput'),
                document.getElementById('namaJenisInput')
            ];

            inputs.forEach(input => {
                if (input && (!input.value || input.dataset.autoFilled === 'true')) {
                    input.value = nama;
                    input.dataset.autoFilled = 'true';
                }
            });
        }
    };

    // 3. Kalkulasi Timbangan Netto & Sinkronisasi
    window.syncPlastikQuantity = function () {
        const inPabrik = document.getElementById('inputJumlahPabrik');
        const inSJ = document.getElementById('inputJumlahSJ');
        const grossInput = document.getElementById('plastikQtyGross');
        const rejectInput = document.getElementById('plastikQtyReject');
        const labelNetto = document.getElementById('labelPlastikNetto');

        if (grossInput && (!grossInput.value || grossInput.value == 0) && inPabrik && inPabrik.value) {
            grossInput.value = inPabrik.value;
        } else if (inPabrik && (!inPabrik.value || inPabrik.value == 0) && grossInput && grossInput.value) {
            inPabrik.value = grossInput.value;
        }

        const gross = parseFloat(grossInput?.value || inPabrik?.value || 0) || 0;
        const reject = parseFloat(rejectInput?.value || 0) || 0;
        const netto = Math.max(0, gross - reject);

        if (labelNetto) {
            labelNetto.innerText = `${netto.toLocaleString('id-ID')} Pcs`;
        }
    };

    // 4. Filter Supplier Dropdown
    window.setSupplierCategoryFilter = function (cat) {
        currentSupplierCategory = cat;
        ['ALL', 'VENDOR', 'RAW'].forEach(c => {
            const chip = document.getElementById('chipSupplier_' + c);
            if (chip) {
                if (c === cat) chip.classList.add('active-chip');
                else chip.classList.remove('active-chip');
            }
        });
        filterSupplierDropdown();
    };

    window.onSearchSupplier = function (query) {
        currentSupplierSearch = (query || '').toLowerCase().trim();
        const clearBtn = document.getElementById('btnClearSupplierSearch');
        if (clearBtn) clearBtn.style.display = currentSupplierSearch ? 'block' : 'none';
        filterSupplierDropdown();
    };

    window.clearSupplierSearch = function () {
        const input = document.getElementById('supplierSearchInput');
        if (input) input.value = '';
        window.onSearchSupplier('');
    };

    function filterSupplierDropdown() {
        const select = document.getElementById('supplierSelect');
        if (!select) return;

        const badge = document.getElementById('supplierFilterBadge');
        let visibleCount = 0;
        const currentVal = select.value;
        let isCurrentValVisible = false;

        const q = currentSupplierSearch;

        for (let i = 0; i < select.options.length; i++) {
            const opt = select.options[i];
            if (!opt.value) {
                opt.style.display = '';
                opt.disabled = false;
                continue;
            }

            const jenisCd = (opt.getAttribute('data-jenis-cd') || '').toUpperCase();
            const isPetani = opt.getAttribute('data-is-petani') === '1';
            const supNm = (opt.getAttribute('data-supplier-nm') || opt.text).toLowerCase();
            const supCd = (opt.getAttribute('data-supplier-cd') || '').toLowerCase();

            let matchCat = false;
            if (currentSupplierCategory === 'ALL') {
                matchCat = true;
            } else if (currentSupplierCategory === 'RAW') {
                matchCat = isPetani || jenisCd === 'RAW' || supCd.startsWith('skg-');
            } else {
                // VENDOR kemasan/plastik
                matchCat = !isPetani && !supCd.startsWith('skg-');
            }

            let matchSearch = true;
            if (q) matchSearch = supNm.includes(q) || supCd.includes(q);

            const isMatch = matchCat && matchSearch;
            if (isMatch) {
                opt.style.display = '';
                opt.disabled = false;
                opt.hidden = false;
                visibleCount++;
                if (opt.value === currentVal) isCurrentValVisible = true;
            } else {
                opt.style.display = 'none';
                opt.disabled = true;
                opt.hidden = true;
            }
        }

        if (badge) {
            badge.innerText = `Filter: 🛍️ Supplier Plastik (${visibleCount})`;
        }

        if (!isCurrentValVisible && currentVal) {
            const selectedPoId = document.getElementById('poSelect')?.value;
            if (!selectedPoId) select.value = '';
        }
    }

    // 5. Filter PO Dropdown
    window.setPoCategoryFilter = function (cat) {
        currentPoCategory = cat;
        ['PLASTIK', 'ALL'].forEach(c => {
            const chip = document.getElementById('chipPo_' + c);
            if (chip) {
                if (c === cat) chip.classList.add('active-chip');
                else chip.classList.remove('active-chip');
            }
        });
        filterPoDropdown();
    };

    window.onSearchPo = function (query) {
        currentPoSearch = (query || '').toLowerCase().trim();
        const clearBtn = document.getElementById('btnClearPoSearch');
        if (clearBtn) clearBtn.style.display = currentPoSearch ? 'block' : 'none';
        filterPoDropdown();
    };

    window.clearPoSearch = function () {
        const input = document.getElementById('poSearchInput');
        if (input) input.value = '';
        window.onSearchPo('');
    };

    function filterPoDropdown() {
        const select = document.getElementById('poSelect');
        if (!select) return;

        const badge = document.getElementById('poFilterBadge');
        let visibleCount = 0;
        const currentVal = select.value;
        let isCurrentValVisible = false;

        const q = currentPoSearch;

        for (let i = 0; i < select.options.length; i++) {
            const opt = select.options[i];
            if (!opt.value) {
                opt.style.display = '';
                opt.disabled = false;
                continue;
            }

            const rawKomoditas = opt.getAttribute('data-komoditas') || '';
            const komoditasList = rawKomoditas.split(',').map(s => s.trim().toUpperCase());
            const searchData = (opt.getAttribute('data-search') || opt.text).toLowerCase();

            let matchCat = false;
            if (currentPoCategory === 'ALL') {
                matchCat = true;
            } else {
                matchCat = komoditasList.includes('PLASTIK') || searchData.includes('plastik') || searchData.includes('kemasan') || searchData.includes('roll');
            }

            let matchSearch = true;
            if (q) matchSearch = searchData.includes(q);

            const isMatch = matchCat && matchSearch;
            if (isMatch) {
                opt.style.display = '';
                opt.disabled = false;
                opt.hidden = false;
                visibleCount++;
                if (opt.value === currentVal) isCurrentValVisible = true;
            } else {
                opt.style.display = 'none';
                opt.disabled = true;
                opt.hidden = true;
            }
        }

        if (badge) {
            badge.innerText = currentPoCategory === 'ALL'
                ? `Filter: Semua PO (${visibleCount})`
                : `Filter: 🛍️ Khusus PO Plastik (${visibleCount})`;
        }

        if (!isCurrentValVisible && currentVal) {
            select.value = '';
        }
    }

    // 6. Handle PO Terpilih
    window.onPoSelected = function (select) {
        const poId = select?.value;
        if (!poId) return;

        const opt = select.selectedOptions[0];
        const supplierId = opt.getAttribute('data-supplier-id');
        const gudangId = opt.getAttribute('data-gudang-id');

        const supplierSelect = document.getElementById('supplierSelect');
        if (supplierSelect && supplierId) {
            for (let i = 0; i < supplierSelect.options.length; i++) {
                if (supplierSelect.options[i].value == supplierId) {
                    supplierSelect.options[i].style.display = '';
                    supplierSelect.options[i].disabled = false;
                    supplierSelect.options[i].hidden = false;
                    break;
                }
            }
            supplierSelect.value = supplierId;
        }

        const gudangSelect = document.getElementById('gudangSelect');
        if (gudangSelect && gudangId) {
            gudangSelect.value = gudangId;
        }

        if (window.appConfig && window.appConfig.poDetailsUrl) {
            fetch(`${window.appConfig.poDetailsUrl}/${poId}`)
                .then(res => res.json())
                .then(data => {
                    if (data && data.success && data.po) {
                        applyPoDetailData(data.po);
                    }
                })
                .catch(err => console.warn('[QC Plastik] Gagal memuat detail PO:', err));
        }
    };

    function applyPoDetailData(poData) {
        if (!poData) return;

        if (poData.items && poData.items.length > 0) {
            const firstItem = poData.items[0];
            const bSel = document.getElementById('plastikBarangSelect');
            if (bSel && firstItem.barang_id) {
                bSel.value = firstItem.barang_id;
                window.onPlastikBarangChanged(bSel);
            }
            if (firstItem.sisa_qty > 0) {
                const inSJ = document.getElementById('inputJumlahSJ');
                const inPabrik = document.getElementById('inputJumlahPabrik');
                const grossInput = document.getElementById('plastikQtyGross');
                if (inSJ && (!inSJ.value || inSJ.value == 0)) inSJ.value = firstItem.sisa_qty;
                if (inPabrik && (!inPabrik.value || inPabrik.value == 0)) inPabrik.value = firstItem.sisa_qty;
                if (grossInput && (!grossInput.value || grossInput.value == 0)) grossInput.value = firstItem.sisa_qty;
                syncPlastikQuantity();
            }
        }
    }

    // 7. Validasi & Submit Form Khusus Plastik
    function injectHiddenInput(name, value) {
        let el = document.querySelector(`input[type="hidden"][name="${name}"]`);
        if (!el) {
            el = document.createElement('input');
            el.type = 'hidden';
            el.name = name;
            document.getElementById('qcPlastikForm').appendChild(el);
        }
        el.value = value;
    }

    function showSubmitLoading(title = 'Menyimpan Data QC Plastik...') {
        isSubmitting = true;
        const overlay = document.getElementById('qcSubmitOverlay');
        const overlayTitle = document.getElementById('overlayTitle');
        if (overlayTitle) overlayTitle.innerText = title;
        if (overlay) overlay.style.display = 'flex';

        document.querySelectorAll('button[type="submit"], #btnSimpanCepat, #dockBtnNext, #btnTopSimpanCepat').forEach(btn => {
            btn.disabled = true;
            btn.style.opacity = '0.5';
            btn.style.pointerEvents = 'none';
        });
    }

    window.submitPlastikForm = function () {
        if (isSubmitting) return;

        const supplierSelect = document.getElementById('supplierSelect');
        if (!supplierSelect || !supplierSelect.value) {
            alert('Silakan pilih Mitra Supplier / Produsen Plastik terlebih dahulu!');
            window.switchPlastikTab(1);
            supplierSelect?.focus();
            return;
        }

        const gudangSelect = document.getElementById('gudangSelect');
        if (!gudangSelect || !gudangSelect.value) {
            alert('Silakan pilih Perusahaan / Gudang Tujuan Bongkar!');
            window.switchPlastikTab(1);
            gudangSelect?.focus();
            return;
        }

        const inputJumlahSJ = document.getElementById('inputJumlahSJ');
        const sjVal = parseFloat(inputJumlahSJ?.value || 0);
        if (isNaN(sjVal) || sjVal <= 0) {
            alert('Jumlah kuantitas pada Surat Jalan wajib diisi dan lebih dari 0 Pcs / Roll!');
            window.switchPlastikTab(1);
            inputJumlahSJ?.focus();
            return;
        }

        const plastikSelect = document.getElementById('plastikBarangSelect');
        if (!plastikSelect || !plastikSelect.value) {
            alert('Pilih item komoditas plastik kemasan yang diuji!');
            window.switchPlastikTab(2);
            plastikSelect?.focus();
            return;
        }

        const grossInput = document.getElementById('plastikQtyGross');
        const grossVal = parseFloat(grossInput?.value || 0);
        const pabrikVal = parseFloat(document.getElementById('inputJumlahPabrik')?.value || 0);
        if ((isNaN(grossVal) || grossVal <= 0) && (isNaN(pabrikVal) || pabrikVal <= 0)) {
            alert('Kuantitas jumlah lolos kedatangan plastik kemasan wajib diisi lebih dari 0 Pcs!');
            window.switchPlastikTab(2);
            grossInput?.focus();
            return;
        }

        // Siapkan Payload khusus Plastik
        const bId = plastikSelect.value;
        const bOpt = plastikSelect.selectedOptions[0];
        const bNm = bOpt ? (bOpt.getAttribute('data-nama') || bOpt.text.split(' - ')[1]?.split(' (')[0] || bOpt.text) : 'Plastik Kemasan';
        const curNamaJenis = document.getElementById('plastikNamaJenisInput')?.value?.trim() || document.getElementById('namaJenisInput')?.value?.trim() || bNm;

        const gross = grossVal > 0 ? grossVal : pabrikVal;
        const reject = parseFloat(document.getElementById('plastikQtyReject')?.value || 0) || 0;
        const kesimpulan = document.querySelector('input[name="plastik_kesimpulan"]:checked')?.value || 'TERIMA';

        injectHiddenInput('nama_jenis', curNamaJenis);
        injectHiddenInput('items[0][barang_id]', bId);
        injectHiddenInput('items[0][qty_timbang_gross]', gross);
        injectHiddenInput('items[0][qty_reject]', reject);
        injectHiddenInput('items[0][qty_netto_lolos]', Math.max(0, gross - reject));
        injectHiddenInput('items[0][keputusan_qc]', kesimpulan === 'TERIMA' ? 'PASSED' : 'REJECT_TOTAL');
        injectHiddenInput('items[0][status_raw_material]', document.querySelector('input[name="plastik_status_raw_material"]:checked')?.value || 'OK');
        injectHiddenInput('items[0][kemasan_kondisi]', document.querySelector('input[name="plastik_kemasan_kondisi"]:checked')?.value || 'OK');
        injectHiddenInput('items[0][kemasan_kotor]', document.querySelector('input[name="plastik_kemasan_kotor"]')?.checked ? 1 : 0);
        injectHiddenInput('items[0][kemasan_apek]', document.querySelector('input[name="plastik_kemasan_apek"]')?.checked ? 1 : 0);
        injectHiddenInput('items[0][kemasan_basah]', document.querySelector('input[name="plastik_kemasan_basah"]')?.checked ? 1 : 0);
        injectHiddenInput('items[0][kemasan_sobek]', document.querySelector('input[name="plastik_kemasan_sobek"]')?.checked ? 1 : 0);
        injectHiddenInput('items[0][ketebalan_analisa]', document.querySelector('input[name="plastik_ketebalan_analisa"]')?.value || '');
        injectHiddenInput('items[0][ketebalan_standar]', document.querySelector('input[name="plastik_ketebalan_standar"]')?.value || '');
        injectHiddenInput('items[0][keutuhan_analisa]', document.querySelector('input[name="plastik_keutuhan_analisa"]')?.value || '');
        injectHiddenInput('items[0][keutuhan_standar]', document.querySelector('input[name="plastik_keutuhan_standar"]')?.value || '');
        injectHiddenInput('items[0][catatan_dtl]', document.querySelector('textarea[name="plastik_komentar"]')?.value || '');
        injectHiddenInput('catatan_umum', document.querySelector('textarea[name="plastik_komentar"]')?.value || '');

        // Header level
        injectHiddenInput('kesimpulan_qc', kesimpulan);
        injectHiddenInput('jumlah_di_pabrik', gross);
        injectHiddenInput('jumlah_surat_jalan', sjVal > 0 ? sjVal : gross);
        const samplePcs = document.querySelector('input[name="jumlah_sample_pcs"]');
        injectHiddenInput('jumlah_sample_pcs', parseInt(samplePcs?.value || 10, 10) || 10);

        injectHiddenInput('status_uji_goreng', 'SELESAI');
        injectHiddenInput('tahap_uji', 'PENGUJIAN_1');

        showSubmitLoading(kesimpulan === 'TOLAK' ? 'Menyimpan Penolakan Plastik Kemasan...' : 'Menyimpan QC Plastik & Menyiapkan Gudang...');
        document.getElementById('qcPlastikForm').submit();
    };

    // 8. Inisialisasi Saat Dimuat
    document.addEventListener('DOMContentLoaded', function () {
        window.switchPlastikTab(1);
        filterSupplierDropdown();
        filterPoDropdown();

        const poSelect = document.getElementById('poSelect');
        if (poSelect && poSelect.value) {
            window.onPoSelected(poSelect);
        } else {
            const bSel = document.getElementById('plastikBarangSelect');
            if (bSel) window.onPlastikBarangChanged(bSel);
        }

        const inputGross = document.getElementById('plastikQtyGross');
        const inputReject = document.getElementById('plastikQtyReject');
        const inputPabrik = document.getElementById('inputJumlahPabrik');
        const inputSJ = document.getElementById('inputJumlahSJ');

        if (inputGross) inputGross.addEventListener('input', syncPlastikQuantity);
        if (inputReject) inputReject.addEventListener('input', syncPlastikQuantity);
        if (inputPabrik) inputPabrik.addEventListener('input', syncPlastikQuantity);
        if (inputSJ) inputSJ.addEventListener('input', function () {
            if (inputPabrik && (!inputPabrik.value || inputPabrik.value == 0)) {
                inputPabrik.value = this.value;
                syncPlastikQuantity();
            }
        });

        console.log('[QC Mobile] Dedicated Plastik Kemasan module loaded successfully.');
    });
})();
