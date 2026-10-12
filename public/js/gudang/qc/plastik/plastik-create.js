/**
 * QC INBOUND - MODUL KOMODITAS: PLASTIK KEMASAN
 * PT Mirasa Food Industry
 * Dedicated JS controller untuk halaman Mobile QC Plastik Kemasan
 * Standar Dokumen: MFI/HACCP-04/FRM-03/030/VIII/2021
 * Tema Desain: Modern Touch-Friendly Google Forms Style (Teal Industrial)
 */

(function () {
    'use strict';

    const DRAFT_KEY = 'mirasa_qc_plastik_draft';
    const ACTIVE_TAB_KEY = 'qc_plastik_active_tab';
    let currentTab = 1;
    let currentPlastikSatuan = 'PCS';
    let isSubmitting = false;
    let draftSaveTimer = null;

    function triggerSaveDraftDebounced() {
        if (isSubmitting) return;
        clearTimeout(draftSaveTimer);
        draftSaveTimer = setTimeout(savePlastikDraft, 250);
    }

    // 1. Tab Navigation (Tahap 1: Dokumen & Armada, Tahap 2: Mutu Plastik)
    window.switchPlastikTab = function (tabNumber, skipValidation = false) {
        const sec1 = document.getElementById('qcSection1');
        const sec2 = document.getElementById('qcSection2');
        const btn1 = document.getElementById('tabBtn1');
        const btn2 = document.getElementById('tabBtn2');

        if (!sec1 || !sec2) return;

        if (tabNumber === 1) {
            sec1.style.display = 'flex';
            sec2.style.display = 'none';
            currentTab = 1;

            if (btn1) {
                btn1.classList.add('active-tab-btn');
                btn1.style.background = '#0d9488';
                btn1.style.borderColor = '#0d9488';
                btn1.style.color = '#ffffff';
            }
            if (btn2) {
                btn2.classList.remove('active-tab-btn');
                btn2.style.background = '#ffffff';
                btn2.style.borderColor = '#cbd5e1';
                btn2.style.color = '#475569';
            }
            updateDockingButtons(1);
            if (!skipValidation) {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        } else {
            // Validasi awal tahap 1 jika dipicu user secara manual
            if (!skipValidation) {
                const sup = document.getElementById('supplierSelect')?.value;
                const gud = document.getElementById('gudangSelect')?.value;
                if (!sup) {
                    alert('Silakan pilih Mitra Supplier / Produsen Plastik terlebih dahulu!');
                    document.getElementById('supplierSelect')?.focus();
                    return;
                }
                if (!gud) {
                    alert('Silakan pilih Gudang Tujuan Bongkar terlebih dahulu!');
                    document.getElementById('gudangSelect')?.focus();
                    return;
                }
            }

            // Sinkronkan kuantitas ke Tab 2 jika belum terisi atau dari input pabrik
            const inPabrik = document.getElementById('inputJumlahPabrik');
            const grossInput = document.getElementById('plastikQtyGross');
            if (inPabrik && inPabrik.value && grossInput && (!grossInput.value || grossInput.value == 0 || grossInput.dataset.autoFilled === 'true')) {
                grossInput.value = inPabrik.value;
            }
            window.syncPlastikQuantity();

            sec1.style.display = 'none';
            sec2.style.display = 'block';
            currentTab = 2;

            if (btn1) {
                btn1.classList.remove('active-tab-btn');
                btn1.style.background = '#ffffff';
                btn1.style.borderColor = '#cbd5e1';
                btn1.style.color = '#475569';
            }
            if (btn2) {
                btn2.classList.add('active-tab-btn');
                btn2.style.background = '#0d9488';
                btn2.style.borderColor = '#0d9488';
                btn2.style.color = '#ffffff';
            }
            updateDockingButtons(2);
            if (!skipValidation) {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        }

        // Simpan status tab ke sessionStorage dan URL Hash agar saat reload tidak reset
        try {
            sessionStorage.setItem(ACTIVE_TAB_KEY, tabNumber);
            const url = new URL(window.location.href);
            url.hash = 'tab' + tabNumber;
            window.history.replaceState({}, '', url.toString());
        } catch (e) {}

        triggerSaveDraftDebounced();
    };

    function updateDockingButtons(tab) {
        const btnBack = document.getElementById('dockBtnBack') || document.getElementById('dockBtnPrev');
        const btnNext = document.getElementById('dockBtnNext');
        const backText = document.getElementById('dockBackText');
        const nextText = document.getElementById('dockNextText');
        const nextIcon = document.getElementById('dockNextIcon');

        if (!btnNext) return;

        if (tab === 1) {
            if (btnBack) btnBack.style.display = 'none';
            btnNext.style.display = 'inline-flex';
            btnNext.className = 'btn-dock-next btn-primary-state';
            btnNext.style.background = '';
            btnNext.style.borderColor = '';
            if (nextText) nextText.innerText = 'Lanjut ke Tahap 2';
            if (nextIcon) nextIcon.style.display = 'inline-block';
            btnNext.onclick = function () { window.switchPlastikTab(2); };
        } else {
            if (btnBack) {
                btnBack.style.display = 'inline-flex';
                if (backText) backText.innerText = 'Kembali';
                btnBack.onclick = function () { window.switchPlastikTab(1); };
            }

            btnNext.style.display = 'inline-flex';
            btnNext.className = 'btn-dock-next btn-success-state';
            btnNext.style.background = '';
            btnNext.style.borderColor = '';
            if (nextText) nextText.innerText = '💾 Simpan QC Plastik';
            if (nextIcon) nextIcon.style.display = 'none';
            btnNext.onclick = function () { window.submitPlastikForm(); };
        }
    }

    // 2. Set Current DateTime
    window.setCurrentDateTime = function () {
        const input = document.getElementById('inputTglDatang');
        if (!input) return;
        const now = new Date();
        const yyyy = now.getFullYear();
        const mm = String(now.getMonth() + 1).padStart(2, '0');
        const dd = String(now.getDate()).padStart(2, '0');
        const hh = String(now.getHours()).padStart(2, '0');
        const min = String(now.getMinutes()).padStart(2, '0');
        input.value = `${yyyy}-${mm}-${dd}T${hh}:${min}`;
        updateInspectionTimeDisplay(input.value);
        triggerSaveDraftDebounced();
    };

    function updateInspectionTimeDisplay(dtString) {
        const timeDisplay = document.getElementById('textInspectionBarTime');
        if (!timeDisplay || !dtString) return;
        try {
            const parts = dtString.split('T');
            if (parts.length === 2) {
                const dateParts = parts[0].split('-');
                const timeParts = parts[1].split(':');
                if (dateParts.length === 3 && timeParts.length >= 2) {
                    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                    const year = dateParts[0];
                    const monthIdx = parseInt(dateParts[1], 10) - 1;
                    const day = String(parseInt(dateParts[2], 10)).padStart(2, '0');
                    const hour = timeParts[0];
                    const min = timeParts[1];
                    timeDisplay.innerText = `${day} ${months[monthIdx] || ''} ${year} • ${hour}:${min} WIB`;
                }
            }
        } catch (e) {}
    }

    // 3. Transportasi & Halal Toggle Handlers
    window.updateTransportCard = function (radio) {
        const cardBebas = document.getElementById('cardTransportBebas');
        const cardCemar = document.getElementById('cardTransportCemar');
        if (!cardBebas || !cardCemar) return;

        if (radio.value === '1') {
            cardBebas.classList.add('active');
            cardCemar.classList.remove('active');
        } else {
            cardCemar.classList.add('active');
            cardBebas.classList.remove('active');
        }
        triggerSaveDraftDebounced();
    };

    window.setHalalToggle = function (field, value) {
        const hidden = document.getElementById('input_' + field);
        if (hidden) hidden.value = value;

        // Pertanyaan 1: Haram (0=Tidak [OK], 1=Ya [Danger])
        if (field === 'angkut_barang_haram') {
            const btnTidak = document.getElementById('btnHaramTidak');
            const btnYa = document.getElementById('btnHaramYa');
            if (btnTidak && btnYa) {
                if (value === 0) {
                    btnTidak.classList.add('active', 'ok');
                    btnTidak.classList.remove('danger');
                    btnYa.classList.remove('active', 'ok', 'danger');
                } else {
                    btnYa.classList.add('active', 'danger');
                    btnYa.classList.remove('ok');
                    btnTidak.classList.remove('active', 'ok', 'danger');
                }
            }
        }

        // Pertanyaan 2, 3, 4: (1=Ya [OK], 0=Tidak [Danger])
        const prefixMap = {
            'terdaftar_lppom': 'Lppom',
            'ada_sertifikat_halal': 'Sertifikat',
            'sertifikat_halal_berlaku': 'Berlaku'
        };
        const prefix = prefixMap[field];
        if (prefix) {
            const btnYa = document.getElementById('btn' + prefix + 'Ya');
            const btnTidak = document.getElementById('btn' + prefix + 'Tidak');
            if (btnYa && btnTidak) {
                if (value === 1) {
                    btnYa.classList.add('active', 'ok');
                    btnYa.classList.remove('danger');
                    btnTidak.classList.remove('active', 'ok', 'danger');
                } else {
                    btnTidak.classList.add('active', 'danger');
                    btnTidak.classList.remove('ok');
                    btnYa.classList.remove('active', 'ok', 'danger');
                }
            }
        }
        triggerSaveDraftDebounced();
    };

    // 4. Raw Material & Kemasan Toggle Handlers
    window.setRawMaterialStatus = function (val) {
        const input = document.getElementById('inputRawMaterial');
        const btnOk = document.getElementById('btnRawOk');
        const btnStd = document.getElementById('btnRawTdkStd');
        if (input) input.value = val;

        if (btnOk && btnStd) {
            if (val === 'OK') {
                btnOk.classList.add('active', 'ok');
                btnStd.classList.remove('active', 'danger');
            } else {
                btnStd.classList.add('active', 'danger');
                btnOk.classList.remove('active', 'ok');
            }
        }
        triggerSaveDraftDebounced();
    };

    window.setKemasanKondisi = function (val) {
        const input = document.getElementById('inputKemasanKondisi');
        const btnOk = document.getElementById('btnKemasanOk');
        const btnStd = document.getElementById('btnKemasanTdkStd');
        if (input) input.value = val;

        if (btnOk && btnStd) {
            if (val === 'OK') {
                btnOk.classList.add('active', 'ok');
                btnStd.classList.remove('active', 'danger');
            } else {
                btnStd.classList.add('active', 'danger');
                btnOk.classList.remove('active', 'ok');
            }
        }
        triggerSaveDraftDebounced();
    };

    // 5. Modern Checklist Toggle Handlers
    window.toggleModernCheck = function (id) {
        const cb = document.getElementById(id);
        if (!cb) return;
        cb.checked = !cb.checked;
        window.onModernCheckboxChanged(cb);
    };

    window.onModernCheckboxChanged = function (cb) {
        if (!cb) return;
        const card = cb.closest('.modern-check-card') || cb.closest('.cacat-check-card');
        if (card) {
            if (cb.checked) {
                card.classList.add('is-checked');
            } else {
                card.classList.remove('is-checked');
            }
        }
        triggerSaveDraftDebounced();
    };

    window.toggleCacatKemasan = function (checkbox) {
        window.onModernCheckboxChanged(checkbox);
    };

    // 5b. Quick Preset Value Handler untuk Parameter Analisa
    window.setParamValue = function (inputId, val) {
        const input = document.getElementById(inputId);
        if (input) {
            input.value = val;
            input.dispatchEvent(new Event('input', { bubbles: true }));
            triggerSaveDraftDebounced();
        }
    };

    // 6. Kesimpulan QC Toggle
    window.setKesimpulanPlastik = function (val) {
        const input = document.getElementById('inputKesimpulanQc');
        const cardTerima = document.getElementById('cardKesimpulanTerima');
        const cardTolak = document.getElementById('cardKesimpulanTolak');
        if (input) input.value = val;

        if (cardTerima && cardTolak) {
            if (val === 'TERIMA') {
                cardTerima.classList.add('active');
                cardTolak.classList.remove('active');
            } else {
                cardTolak.classList.add('active');
                cardTerima.classList.remove('active');
            }
        }
        triggerSaveDraftDebounced();
    };

    // 7. Pengaturan Satuan Dinamis (PCS vs KG)
    window.setPlastikSatuan = function (satuan) {
        if (!satuan) return;
        const upper = String(satuan).trim().toUpperCase();
        if (upper === 'KG' || upper === 'KILOGRAM') {
            currentPlastikSatuan = 'KG';
        } else {
            currentPlastikSatuan = 'PCS';
        }

        // 1. Update Hidden Input
        const hidden = document.getElementById('inputPlastikSatuan');
        if (hidden) hidden.value = currentPlastikSatuan;

        // 2. Update Toggle Pill Buttons
        const pillPcs = document.getElementById('pillSatuan_PCS');
        const pillKg = document.getElementById('pillSatuan_KG');
        if (pillPcs && pillKg) {
            if (currentPlastikSatuan === 'KG') {
                pillKg.classList.add('active');
                pillPcs.classList.remove('active');
            } else {
                pillPcs.classList.add('active');
                pillKg.classList.remove('active');
            }
        }

        // 3. Update step attributes input kuantitas (1 untuk PCS, 0.01 untuk KG)
        const stepVal = (currentPlastikSatuan === 'KG') ? '0.01' : '1';
        ['inputJumlahSJ', 'inputJumlahPabrik', 'plastikQtyGross', 'plastikQtyReject'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.step = stepVal;
        });

        // 4. Update semua label dan suffix di DOM
        const suffixText = currentPlastikSatuan;
        document.querySelectorAll('.label-satuan-plastik').forEach(el => {
            el.innerText = currentPlastikSatuan;
        });
        document.querySelectorAll('.suffix-satuan-plastik').forEach(el => {
            el.innerText = suffixText;
        });

        // 5. Kalkulasi ulang kuantitas real-time
        window.syncPlastikQuantity();
        triggerSaveDraftDebounced();
    };

    // 8. Auto-suggest Nama Jenis & Satuan berdasarkan Master Barang Plastik
    window.onPlastikBarangChanged = function (select) {
        if (!select) return;
        const opt = select.selectedOptions ? select.selectedOptions[0] : select.options[select.selectedIndex];
        if (opt) {
            // Deteksi Satuan Dasar Barang (KG vs PCS)
            const satCd = opt.getAttribute('data-satuan-cd') || opt.getAttribute('data-satuan') || '';
            if (satCd) {
                window.setPlastikSatuan(satCd);
            }

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
        triggerSaveDraftDebounced();
    };

    // 9. Sinkronisasi Kuantitas Real-Time
    window.syncPlastikQuantity = function () {
        const inPabrik = document.getElementById('inputJumlahPabrik');
        const inSJ = document.getElementById('inputJumlahSJ');
        const grossInput = document.getElementById('plastikQtyGross');
        const rejectInput = document.getElementById('plastikQtyReject');
        const labelNetto = document.getElementById('labelPlastikNetto');
        const labelSubcardNetto = document.getElementById('labelSubcardNetto');

        if (grossInput && (!grossInput.value || grossInput.value == 0) && inPabrik && inPabrik.value) {
            grossInput.value = inPabrik.value;
        } else if (inPabrik && (!inPabrik.value || inPabrik.value == 0) && grossInput && grossInput.value) {
            inPabrik.value = grossInput.value;
        }

        const gross = parseFloat(grossInput?.value || inPabrik?.value || 0) || 0;
        const reject = parseFloat(rejectInput?.value || 0) || 0;
        const netto = Math.max(0, gross - reject);

        const formatted = (currentPlastikSatuan === 'KG')
            ? `${netto.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 })} Kg`
            : `${Math.round(netto).toLocaleString('id-ID')} Pcs`;

        if (labelNetto) labelNetto.innerText = formatted;
        if (labelSubcardNetto) labelSubcardNetto.innerText = `Netto Lolos: ${formatted}`;
    };

    // 10. Handle PO Terpilih (Auto-Fill Instan Tanpa Delay & Bisa Diedit)
    window.onPoSelected = function (select) {
        if (!select || !select.value) return;
        const poId = select.value;

        // Cek data PO preloaded di window.qcConfig / window.appConfig
        const poData = window.qcConfig?.poList?.[poId] || window.appConfig?.poList?.[poId];
        if (poData) {
            applyPoDetailData(poData);
            return;
        }

        const opt = select.selectedOptions[0];
        const supplierId = opt?.getAttribute('data-supplier-id');
        const gudangId = opt?.getAttribute('data-gudang-id');

        const supplierSelect = document.getElementById('supplierSelect');
        if (supplierSelect && supplierId) {
            supplierSelect.value = supplierId;
            if (window.onSupplierSelected) window.onSupplierSelected(supplierSelect);
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

    window.onSupplierSelected = function (select) {
        if (!select) return;
        const opt = select.selectedOptions ? select.selectedOptions[0] : select.options[select.selectedIndex];
        const namaProdusenInput = document.getElementById('namaProdusenInput');
        if (opt && namaProdusenInput && (!namaProdusenInput.value || namaProdusenInput.dataset.autoFilled === 'true')) {
            const supNm = opt.getAttribute('data-supplier-nm') || opt.text.split(' (')[0];
            namaProdusenInput.value = supNm.trim();
            namaProdusenInput.dataset.autoFilled = 'true';
        }
    };

    function applyPoDetailData(poData) {
        if (!poData) return;

        // 1. Pilih Mitra Vendor & Auto-fill Nama Produsen (Bisa diedit)
        if (poData.supplier_id) {
            const supSelect = document.getElementById('supplierSelect');
            if (supSelect) {
                let targetOpt = supSelect.querySelector(`option[value="${poData.supplier_id}"]`);
                if (!targetOpt && poData.supplier_nm) {
                    targetOpt = document.createElement('option');
                    targetOpt.value = poData.supplier_id;
                    targetOpt.text = `${poData.supplier_nm}`;
                    targetOpt.setAttribute('data-supplier-nm', poData.supplier_nm);
                    supSelect.appendChild(targetOpt);
                }
                supSelect.value = poData.supplier_id;
                window.onSupplierSelected(supSelect);
            }
        }

        const produsenInput = document.getElementById('namaProdusenInput');
        if (produsenInput && poData.supplier_nm && (!produsenInput.value || produsenInput.dataset.autoFilled === 'true')) {
            produsenInput.value = poData.supplier_nm;
            produsenInput.dataset.autoFilled = 'true';
        }

        // 2. Auto-fill Gudang Tujuan Bongkar
        if (poData.gudang_id) {
            const gudangSelect = document.getElementById('gudangSelect');
            if (gudangSelect) gudangSelect.value = poData.gudang_id;
        }

        // 3. Auto-fill Item: Barang Master, Satuan, Spesifikasi Jenis & Kuantitas dari PO
        if (poData.items && poData.items.length > 0) {
            const firstItem = poData.items[0];
            const bSel = document.getElementById('plastikBarangSelect');
            if (bSel && firstItem.barang_id) {
                bSel.value = firstItem.barang_id;
                window.onPlastikBarangChanged(bSel);
            }

            // Sinkronkan Satuan dari Item PO (KG vs PCS)
            if (firstItem.satuan_cd || firstItem.satuan) {
                window.setPlastikSatuan(firstItem.satuan_cd || firstItem.satuan);
            }

            // Nama Jenis / Spesifikasi Kemasan (Bisa diedit)
            const namaJenisInputs = [
                document.getElementById('plastikNamaJenisInput'),
                document.getElementById('namaJenisInput')
            ];
            namaJenisInputs.forEach(input => {
                if (input && (!input.value || input.dataset.autoFilled === 'true')) {
                    input.value = firstItem.barang_nm || 'Plastik Kemasan Keripik Cap Payung';
                    input.dataset.autoFilled = 'true';
                }
            });

            // Kuantitas: Sisa Qty atau Pesan Qty (Langsung terisi & tetap bisa diedit)
            const sisaQty = firstItem.sisa_qty > 0 ? firstItem.sisa_qty : (firstItem.pesan_qty || 0);
            if (sisaQty > 0) {
                const inSJ = document.getElementById('inputJumlahSJ');
                const inPabrik = document.getElementById('inputJumlahPabrik');
                const grossInput = document.getElementById('plastikQtyGross');

                if (inSJ && (!inSJ.value || inSJ.dataset.autoFilled === 'true')) {
                    inSJ.value = sisaQty;
                    inSJ.dataset.autoFilled = 'true';
                }
                if (inPabrik && (!inPabrik.value || inPabrik.dataset.autoFilled === 'true')) {
                    inPabrik.value = sisaQty;
                    inPabrik.dataset.autoFilled = 'true';
                }
                if (grossInput && (!grossInput.value || grossInput.dataset.autoFilled === 'true')) {
                    grossInput.value = sisaQty;
                    grossInput.dataset.autoFilled = 'true';
                }
                window.syncPlastikQuantity();
            }
        }
        triggerSaveDraftDebounced();
    }

    // 10. Autosave & Restore Form Draft (Pencegahan Kehilangan Data Saat Reload / Refresh)
    function savePlastikDraft() {
        if (isSubmitting) return;
        try {
            const form = document.getElementById('qcPlastikForm');
            if (!form) return;

            const draft = {
                po_id: document.getElementById('poSelect')?.value || '',
                gudang_id: document.getElementById('gudangSelect')?.value || '',
                supplier_id: document.getElementById('supplierSelect')?.value || '',
                surat_jalan_no: document.getElementById('inputNoSJ')?.value || '',
                plat_nomor_truk: document.getElementById('inputPlatNomor')?.value || '',
                nama_supir: document.getElementById('inputNamaSupir')?.value || '',
                tanggal_terima: document.getElementById('inputTglDatang')?.value || '',
                tgl_kadaluarsa: document.getElementById('inputTglKadaluarsa')?.value || '',
                plastik_barang_id: document.getElementById('plastikBarangSelect')?.value || '',
                jumlah_surat_jalan: document.getElementById('inputJumlahSJ')?.value || '',
                jumlah_di_pabrik: document.getElementById('inputJumlahPabrik')?.value || '',
                jumlah_sample_pcs: document.getElementById('inputJumlahSamplePcs')?.value || '',
                nama_jenis: document.getElementById('plastikNamaJenisInput')?.value || '',

                bebas_cemaran_st: document.querySelector('input[name="bebas_cemaran_st"]:checked')?.value || '1',
                angkut_barang_haram_st: document.getElementById('input_angkut_barang_haram')?.value ?? '0',
                komentar_transportasi: document.querySelector('input[name="komentar_transportasi"]')?.value || '',
                terdaftar_lppom_st: document.getElementById('input_terdaftar_lppom')?.value ?? '1',
                komentar_lppom: document.querySelector('input[name="komentar_lppom"]')?.value || '',
                ada_sertifikat_halal_st: document.getElementById('input_ada_sertifikat_halal')?.value ?? '1',
                komentar_sertifikat: document.querySelector('input[name="komentar_sertifikat"]')?.value || '',
                sertifikat_halal_berlaku_st: document.getElementById('input_sertifikat_halal_berlaku')?.value ?? '1',
                komentar_berlaku: document.querySelector('input[name="komentar_berlaku"]')?.value || '',

                plastik_status_raw_material: document.getElementById('inputRawMaterial')?.value || 'OK',
                plastik_kemasan_kondisi: document.getElementById('inputKemasanKondisi')?.value || 'OK',
                plastik_kemasan_kotor: document.getElementById('checkCacatKotor')?.checked ? 1 : 0,
                plastik_kemasan_apek: document.getElementById('checkCacatApek')?.checked ? 1 : 0,
                plastik_kemasan_basah: document.getElementById('checkCacatBasah')?.checked ? 1 : 0,
                plastik_kemasan_sobek: document.getElementById('checkCacatSobek')?.checked ? 1 : 0,

                plastik_ketebalan_analisa: document.getElementById('inputKetebalanAnalisa')?.value || '',
                plastik_ketebalan_standar: document.getElementById('inputKetebalanStandar')?.value || '',
                plastik_keutuhan_analisa: document.getElementById('inputKeutuhanAnalisa')?.value || '',
                plastik_keutuhan_standar: document.getElementById('inputKeutuhanStandar')?.value || '',

                plastik_qty_gross: document.getElementById('plastikQtyGross')?.value || '',
                plastik_qty_reject: document.getElementById('plastikQtyReject')?.value || '0',
                plastik_satuan: currentPlastikSatuan,
                plastik_komentar: document.getElementById('inputPlastikKomentar')?.value || '',
                plastik_kesimpulan: document.getElementById('inputKesimpulanQc')?.value || 'TERIMA',
                petugas_qc_nama: document.getElementById('inputPetugasQc')?.value || '',
                qc_supervisor_nama: document.getElementById('inputSupervisorQc')?.value || '',

                active_tab: currentTab
            };

            sessionStorage.setItem(DRAFT_KEY, JSON.stringify(draft));
        } catch (e) {
            console.warn('[QC Plastik] Gagal menyimpan draft formulir:', e);
        }
    }

    function restorePlastikDraft() {
        try {
            const raw = sessionStorage.getItem(DRAFT_KEY);
            if (!raw) return false;
            const draft = JSON.parse(raw);
            if (!draft || typeof draft !== 'object') return false;

            // 1. Text & Select fields
            if (draft.po_id) {
                const el = document.getElementById('poSelect');
                if (el) el.value = draft.po_id;
            }
            if (draft.gudang_id) {
                const el = document.getElementById('gudangSelect');
                if (el) el.value = draft.gudang_id;
            }
            if (draft.supplier_id) {
                const el = document.getElementById('supplierSelect');
                if (el) el.value = draft.supplier_id;
            }
            if (draft.surat_jalan_no !== undefined) {
                const el = document.getElementById('inputNoSJ');
                if (el) el.value = draft.surat_jalan_no;
            }
            if (draft.plat_nomor_truk !== undefined) {
                const el = document.getElementById('inputPlatNomor');
                if (el) el.value = draft.plat_nomor_truk;
            }
            if (draft.nama_supir !== undefined) {
                const el = document.getElementById('inputNamaSupir');
                if (el) el.value = draft.nama_supir;
            }
            if (draft.tanggal_terima) {
                const el = document.getElementById('inputTglDatang');
                if (el) {
                    el.value = draft.tanggal_terima;
                    updateInspectionTimeDisplay(draft.tanggal_terima);
                }
            }
            if (draft.tgl_kadaluarsa !== undefined) {
                const el = document.getElementById('inputTglKadaluarsa');
                if (el) el.value = draft.tgl_kadaluarsa;
            }
            if (draft.plastik_barang_id) {
                const el = document.getElementById('plastikBarangSelect');
                if (el) el.value = draft.plastik_barang_id;
            }
            if (draft.jumlah_surat_jalan !== undefined) {
                const el = document.getElementById('inputJumlahSJ');
                if (el) el.value = draft.jumlah_surat_jalan;
            }
            if (draft.jumlah_di_pabrik !== undefined) {
                const el = document.getElementById('inputJumlahPabrik');
                if (el) el.value = draft.jumlah_di_pabrik;
            }
            if (draft.jumlah_sample_pcs !== undefined) {
                const el = document.getElementById('inputJumlahSamplePcs');
                if (el) el.value = draft.jumlah_sample_pcs;
            }
            if (draft.nama_jenis !== undefined) {
                const el = document.getElementById('plastikNamaJenisInput');
                if (el) el.value = draft.nama_jenis;
            }

            // 2. Transport Radio
            if (draft.bebas_cemaran_st !== undefined) {
                const radio = document.querySelector(`input[name="bebas_cemaran_st"][value="${draft.bebas_cemaran_st}"]`);
                if (radio) {
                    radio.checked = true;
                    window.updateTransportCard(radio);
                }
            }

            // 3. Halal Questions
            if (draft.angkut_barang_haram_st !== undefined) {
                window.setHalalToggle('angkut_barang_haram', parseInt(draft.angkut_barang_haram_st, 10));
            }
            if (draft.komentar_transportasi !== undefined) {
                const el = document.querySelector('input[name="komentar_transportasi"]');
                if (el) el.value = draft.komentar_transportasi;
            }

            if (draft.terdaftar_lppom_st !== undefined) {
                window.setHalalToggle('terdaftar_lppom', parseInt(draft.terdaftar_lppom_st, 10));
            }
            if (draft.komentar_lppom !== undefined) {
                const el = document.querySelector('input[name="komentar_lppom"]');
                if (el) el.value = draft.komentar_lppom;
            }

            if (draft.ada_sertifikat_halal_st !== undefined) {
                window.setHalalToggle('ada_sertifikat_halal', parseInt(draft.ada_sertifikat_halal_st, 10));
            }
            if (draft.komentar_sertifikat !== undefined) {
                const el = document.querySelector('input[name="komentar_sertifikat"]');
                if (el) el.value = draft.komentar_sertifikat;
            }

            if (draft.sertifikat_halal_berlaku_st !== undefined) {
                window.setHalalToggle('sertifikat_halal_berlaku', parseInt(draft.sertifikat_halal_berlaku_st, 10));
            }
            if (draft.komentar_berlaku !== undefined) {
                const el = document.querySelector('input[name="komentar_berlaku"]');
                if (el) el.value = draft.komentar_berlaku;
            }

            // 4. Tab 2: Mutu Plastik
            if (draft.plastik_status_raw_material) {
                window.setRawMaterialStatus(draft.plastik_status_raw_material);
            }
            if (draft.plastik_kemasan_kondisi) {
                window.setKemasanKondisi(draft.plastik_kemasan_kondisi);
            }

            const cacatMap = [
                { id: 'checkCacatKotor', val: draft.plastik_kemasan_kotor },
                { id: 'checkCacatApek', val: draft.plastik_kemasan_apek },
                { id: 'checkCacatBasah', val: draft.plastik_kemasan_basah },
                { id: 'checkCacatSobek', val: draft.plastik_kemasan_sobek }
            ];
            cacatMap.forEach(c => {
                const el = document.getElementById(c.id);
                if (el && c.val !== undefined) {
                    el.checked = Boolean(c.val);
                    window.onModernCheckboxChanged(el);
                }
            });

            if (draft.plastik_ketebalan_analisa !== undefined) {
                const el = document.getElementById('inputKetebalanAnalisa');
                if (el) el.value = draft.plastik_ketebalan_analisa;
            }
            if (draft.plastik_ketebalan_standar !== undefined) {
                const el = document.getElementById('inputKetebalanStandar');
                if (el) el.value = draft.plastik_ketebalan_standar;
            }
            if (draft.plastik_keutuhan_analisa !== undefined) {
                const el = document.getElementById('inputKeutuhanAnalisa');
                if (el) el.value = draft.plastik_keutuhan_analisa;
            }
            if (draft.plastik_keutuhan_standar !== undefined) {
                const el = document.getElementById('inputKeutuhanStandar');
                if (el) el.value = draft.plastik_keutuhan_standar;
            }

            if (draft.plastik_qty_gross !== undefined) {
                const el = document.getElementById('plastikQtyGross');
                if (el) el.value = draft.plastik_qty_gross;
            }
            if (draft.plastik_qty_reject !== undefined) {
                const el = document.getElementById('plastikQtyReject');
                if (el) el.value = draft.plastik_qty_reject;
            }
            if (draft.plastik_komentar !== undefined) {
                const el = document.getElementById('inputPlastikKomentar');
                if (el) el.value = draft.plastik_komentar;
            }
            if (draft.plastik_kesimpulan) {
                window.setKesimpulanPlastik(draft.plastik_kesimpulan);
            }
            if (draft.plastik_satuan) {
                window.setPlastikSatuan(draft.plastik_satuan);
            }
            if (draft.petugas_qc_nama !== undefined && draft.petugas_qc_nama !== '') {
                const el = document.getElementById('inputPetugasQc');
                if (el) el.value = draft.petugas_qc_nama;
            }
            if (draft.qc_supervisor_nama !== undefined && draft.qc_supervisor_nama !== '') {
                const el = document.getElementById('inputSupervisorQc');
                if (el) el.value = draft.qc_supervisor_nama;
            }

            window.syncPlastikQuantity();
            return true;
        } catch (e) {
            console.warn('[QC Plastik] Gagal memulihkan draft form:', e);
            return false;
        }
    }

    function clearPlastikDraft() {
        try {
            sessionStorage.removeItem(DRAFT_KEY);
            sessionStorage.removeItem(ACTIVE_TAB_KEY);
        } catch (e) {}
    }

    // 11. Validasi & Submit Payload Form Khusus Plastik
    function injectHiddenInput(name, value) {
        let el = document.querySelector(`input[name="${name}"]`);
        if (el) {
            el.value = value;
            return;
        }
        el = document.createElement('input');
        el.type = 'hidden';
        el.name = name;
        el.value = value;
        document.getElementById('qcPlastikForm').appendChild(el);
    }

    function showSubmitLoading(title = 'Menyimpan Data QC Plastik...') {
        isSubmitting = true;
        const overlay = document.getElementById('qcSubmitOverlay');
        const overlayTitle = document.getElementById('overlayTitle');
        if (overlayTitle) overlayTitle.innerText = title;
        if (overlay) overlay.style.display = 'flex';

        document.querySelectorAll('button[type="submit"], #dockBtnNext, #btnTopSimpanCepat').forEach(btn => {
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
            alert('Silakan pilih Gudang Tujuan Bongkar!');
            window.switchPlastikTab(1);
            gudangSelect?.focus();
            return;
        }

        const inputJumlahSJ = document.getElementById('inputJumlahSJ');
        const sjVal = parseFloat(inputJumlahSJ?.value || 0);
        if (isNaN(sjVal) || sjVal <= 0) {
            alert(`Jumlah kuantitas pada Surat Jalan wajib diisi dan lebih dari 0 ${currentPlastikSatuan}!`);
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

        const pabrikInput = document.getElementById('inputJumlahPabrik');
        const pabrikVal = parseFloat(pabrikInput?.value || 0);
        const grossInput = document.getElementById('plastikQtyGross');
        const grossVal = parseFloat(grossInput?.value || 0);
        if ((isNaN(grossVal) || grossVal <= 0) && (isNaN(pabrikVal) || pabrikVal <= 0)) {
            alert(`Kuantitas jumlah lolos kedatangan plastik kemasan wajib diisi lebih dari 0 ${currentPlastikSatuan}!`);
            window.switchPlastikTab(2);
            grossInput?.focus();
            return;
        }

        // Kuantitas kedatangan pabrik: prioritas input pabrik jika diisi > 0 oleh pengguna
        const finalPabrik = (pabrikVal > 0) ? pabrikVal : (grossVal > 0 ? grossVal : sjVal);
        const finalGross = (grossVal > 0 && pabrikVal <= 0) ? grossVal : finalPabrik;

        if (pabrikInput) pabrikInput.value = finalPabrik;
        if (grossInput) grossInput.value = finalGross;

        // Siapkan Payload khusus Plastik Sesuai HACCP
        const bId = plastikSelect.value;
        const bOpt = plastikSelect.selectedOptions[0];
        const bNm = bOpt ? (bOpt.getAttribute('data-nama') || bOpt.text.split(' - ')[1]?.split(' (')[0] || bOpt.text) : 'Plastik Kemasan';
        const curNamaJenis = document.getElementById('plastikNamaJenisInput')?.value?.trim() || document.getElementById('namaJenisInput')?.value?.trim() || bNm;

        const reject = parseFloat(document.getElementById('plastikQtyReject')?.value || 0) || 0;
        const kesimpulan = document.getElementById('inputKesimpulanQc')?.value || 'TERIMA';
        const rawMatStatus = document.getElementById('inputRawMaterial')?.value || 'OK';
        const kemasanKondisi = document.getElementById('inputKemasanKondisi')?.value || 'OK';

        injectHiddenInput('nama_jenis', curNamaJenis);
        injectHiddenInput('items[0][barang_id]', bId);
        injectHiddenInput('items[0][qty_timbang_gross]', finalGross);
        injectHiddenInput('items[0][qty_reject]', reject);
        injectHiddenInput('items[0][qty_netto_lolos]', Math.max(0, finalGross - reject));
        injectHiddenInput('items[0][keputusan_qc]', kesimpulan === 'TERIMA' ? 'PASSED' : 'REJECT_TOTAL');
        injectHiddenInput('items[0][status_raw_material]', rawMatStatus);
        injectHiddenInput('items[0][kemasan_kondisi]', kemasanKondisi);
        injectHiddenInput('items[0][kemasan_kotor]', document.getElementById('checkCacatKotor')?.checked ? 1 : 0);
        injectHiddenInput('items[0][kemasan_apek]', document.getElementById('checkCacatApek')?.checked ? 1 : 0);
        injectHiddenInput('items[0][kemasan_basah]', document.getElementById('checkCacatBasah')?.checked ? 1 : 0);
        injectHiddenInput('items[0][kemasan_sobek]', document.getElementById('checkCacatSobek')?.checked ? 1 : 0);
        injectHiddenInput('items[0][ketebalan_analisa]', document.getElementById('inputKetebalanAnalisa')?.value || '');
        injectHiddenInput('items[0][ketebalan_standar]', document.getElementById('inputKetebalanStandar')?.value || '0.08 mm');
        injectHiddenInput('items[0][keutuhan_analisa]', document.getElementById('inputKeutuhanAnalisa')?.value || 'Tidak Sobek');
        injectHiddenInput('items[0][keutuhan_standar]', document.getElementById('inputKeutuhanStandar')?.value || 'Tidak Sobek');
        
        const komentarText = document.getElementById('inputPlastikKomentar')?.value || '';
        injectHiddenInput('items[0][catatan_dtl]', komentarText);
        injectHiddenInput('catatan_umum', komentarText);

        // Header level
        injectHiddenInput('satuan', currentPlastikSatuan);
        injectHiddenInput('items[0][satuan]', currentPlastikSatuan);
        injectHiddenInput('kesimpulan_qc', kesimpulan);
        injectHiddenInput('jumlah_di_pabrik', finalPabrik);
        injectHiddenInput('jumlah_surat_jalan', sjVal > 0 ? sjVal : finalPabrik);
        const samplePcs = document.getElementById('inputJumlahSamplePcs');
        injectHiddenInput('jumlah_sample_pcs', parseInt(samplePcs?.value || 10, 10) || 10);

        injectHiddenInput('status_uji_goreng', 'SELESAI');
        injectHiddenInput('tahap_uji', 'PENGUJIAN_1');

        const petugasQc = document.getElementById('inputPetugasQc')?.value?.trim();
        if (petugasQc) injectHiddenInput('petugas_qc_nama', petugasQc);

        const supervisorQc = document.getElementById('inputSupervisorQc')?.value?.trim();
        if (supervisorQc) injectHiddenInput('qc_supervisor_nama', supervisorQc);

        showSubmitLoading(kesimpulan === 'TOLAK' ? 'Menyimpan Penolakan Plastik Kemasan...' : 'Menyimpan QC Plastik & Menyiapkan Gudang...');
        
        // Hapus draft tersimpan saat berhasil submit agar tidak tersisa untuk form berikutnya
        clearPlastikDraft();

        document.getElementById('qcPlastikForm').submit();
    };

    // 12. Inisialisasi Saat Dokumen Dimuat
    document.addEventListener('DOMContentLoaded', function () {
        // 1. Inisialisasi Satuan Default jika ada input hidden
        const initSatuanInput = document.getElementById('inputPlastikSatuan');
        if (initSatuanInput && initSatuanInput.value) {
            window.setPlastikSatuan(initSatuanInput.value);
        }

        // 2. Pulihkan draft data isian jika ada
        const hasDraft = restorePlastikDraft();

        // 3. Jika tidak ada draft tapi ada PO terpilih di form, inisialisasi PO
        if (!hasDraft) {
            const poSelect = document.getElementById('poSelect');
            if (poSelect && poSelect.value) {
                window.onPoSelected(poSelect);
            } else {
                const bSel = document.getElementById('plastikBarangSelect');
                if (bSel) window.onPlastikBarangChanged(bSel);
            }
        }

        // 3. Tentukan tab aktif (prioritas Hash URL > sessionStorage > default Tab 1)
        let targetTab = 1;
        const hashMatch = window.location.hash.match(/tab(\d+)/);
        if (hashMatch) {
            targetTab = parseInt(hashMatch[1], 10);
        } else {
            const savedTab = sessionStorage.getItem(ACTIVE_TAB_KEY);
            if (savedTab) targetTab = parseInt(savedTab, 10);
        }

        if (targetTab === 2) {
            window.switchPlastikTab(2, true); // true = lewati validasi saat restore
        } else {
            window.switchPlastikTab(1, true);
        }

        // 4. Pasang Event Listeners Form Autosave
        const form = document.getElementById('qcPlastikForm');
        if (form) {
            form.addEventListener('input', triggerSaveDraftDebounced);
            form.addEventListener('change', triggerSaveDraftDebounced);
        }

        const inputGross = document.getElementById('plastikQtyGross');
        const inputReject = document.getElementById('plastikQtyReject');
        const inputPabrik = document.getElementById('inputJumlahPabrik');
        const inputSJ = document.getElementById('inputJumlahSJ');
        const inputTgl = document.getElementById('inputTglDatang');

        if (inputTgl) {
            inputTgl.addEventListener('change', function () {
                updateInspectionTimeDisplay(this.value);
            });
        }

        if (inputPabrik) {
            inputPabrik.addEventListener('input', function () {
                this.dataset.autoFilled = 'false';
                if (inputGross) {
                    inputGross.value = this.value;
                    inputGross.dataset.autoFilled = 'false';
                }
                window.syncPlastikQuantity();
            });
        }
        if (inputGross) {
            inputGross.addEventListener('input', function () {
                this.dataset.autoFilled = 'false';
                if (inputPabrik) {
                    inputPabrik.value = this.value;
                    inputPabrik.dataset.autoFilled = 'false';
                }
                window.syncPlastikQuantity();
            });
        }
        if (inputReject) inputReject.addEventListener('input', window.syncPlastikQuantity);
        if (inputSJ) {
            inputSJ.addEventListener('input', function () {
                this.dataset.autoFilled = 'false';
                if (inputPabrik && (!inputPabrik.value || inputPabrik.value == 0 || inputPabrik.dataset.autoFilled === 'true')) {
                    inputPabrik.value = this.value;
                    if (inputGross) inputGross.value = this.value;
                    window.syncPlastikQuantity();
                }
            });
        }

        console.log('[QC Mobile] Modul Plastik Kemasan HACCP (Teal Theme) dengan Autosave & Memory Tab dimuat.');
    });
})();
