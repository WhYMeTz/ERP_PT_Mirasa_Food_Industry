/**
 * QC MOBILE CREATE JAVASCRIPT - PT MIRASA FOOD INDUSTRY
 * Modul Interaksi & Kalkulasi Form QC Inbound Multi-Komoditas Lapangan
 */

(function () {
    'use strict';

    // 1. Inisialisasi Data Global dari window.qcConfig
    const config = window.qcConfig || {};
    const RAW_MATERIALS = config.rawMaterials || [];
    const PO_LIST = config.poList || {};
    const STOK_GRADE_A = parseFloat(config.stokGradeA || 0);
    const STOK_GRADE_B = parseFloat(config.stokGradeB || 0);

    let itemIndex = 0;
    let currentKomoditas = config.initialKomoditas || 'SINGKONG';
    let currentTabNumber = 1;
    let isSubmitting = false;

    // Filter Supplier & PO state
    let currentSupplierCategory = 'AUTO';
    let currentSupplierSearch = '';
    let currentPoCategory = 'AUTO';
    let currentPoSearch = '';

    // 2. Kamus Spesifikasi & Konfigurasi 7 Komoditas HACCP
    const KOMODITAS_CONFIG = {
        'SINGKONG': {
            docNo: 'MFI/HACCP-04/FRM-03/048/VIII/2021',
            title: 'Sampling Mutu Singkong',
            desc: 'Pemeriksaan Fisik Timbangan & Uji Kematangan Fryer',
            labelNamaJenis: 'Nama Bahan / Jenis Singkong',
            hasPanenFields: true,
            sampleUnit: 'KG',
            showTab3: true,
        },
        'MINYAK': {
            docNo: 'MFI/HACCP-04/FRM-03/029/VIII/2021',
            title: 'Sampling Mutu Minyak Goreng',
            desc: 'Pemeriksaan FFA di COA, Kebersihan Tangki & Suhu',
            labelNamaJenis: 'NAMA JENIS (Contoh: Minyak Sawit Curah)',
            hasPanenFields: false,
            sampleUnit: 'gr',
            showTab3: false,
        },
        'PLASTIK': {
            docNo: 'MFI/HACCP-04/FRM-03/030/VIII/2021',
            title: 'Sampling Mutu Plastik Kemasan',
            desc: 'Pemeriksaan Ketebalan, Cacat Kemasan & Bebas Sobek',
            labelNamaJenis: 'NAMA JENIS (Contoh: PP 08, OPP, Pouch)',
            hasPanenFields: false,
            sampleUnit: 'pcs',
            showTab3: false,
        },
        'KARTON': {
            docNo: 'MFI/HACCP-04/FRM-03/031/VIII/2021',
            title: 'Sampling Mutu Karton Box',
            desc: 'Pemeriksaan Dimensi (P x L x T), Cacat & Kekuatan',
            labelNamaJenis: 'NAMA JENIS KARTON (Contoh: Master Box Balado)',
            hasPanenFields: false,
            sampleUnit: 'pcs',
            showTab3: false,
        },
        'MSG': {
            docNo: 'MFI/HACCP-04/FRM-03/032/VIII/2021',
            title: 'Sampling Mutu MSG',
            desc: 'Uji Fisik Bahan Penolong (Kering, Gumpal) & Keutuhan Kemasan',
            labelNamaJenis: 'NAMA JENIS (Contoh: MSG Miku / Ajinomoto / Miwon)',
            hasPanenFields: false,
            sampleUnit: 'gr',
            showTab3: false,
        },
        'GARAM': {
            docNo: 'MFI/HACCP-04/FRM-03/033/VIII/2021',
            title: 'Sampling Mutu Garam',
            desc: 'Uji Fisik Garam (Kering, Bebas Kotoran) & Keutuhan Kemasan',
            labelNamaJenis: 'NAMA JENIS (Contoh: Garam Halus Beryodium)',
            hasPanenFields: false,
            sampleUnit: 'gr',
            showTab3: false,
        },
        'PERENYAH': {
            docNo: 'MFI/HACCP-04/FRM-03/063/IX/2023',
            title: 'Sampling Mutu Perenyah',
            desc: 'Uji Fisik Perenyah (Kering, Homogen) & Label Halal / Kadaluarsa',
            labelNamaJenis: 'NAMA JENIS (Contoh: Perenyah Keripik Singkong)',
            hasPanenFields: false,
            sampleUnit: 'gr',
            showTab3: false,
        }
    };

    // 3. Fungsi Utama Pemilihan Komoditas (Pills & Form Switcher)
    window.selectKomoditas = function (type) {
        currentKomoditas = type;
        const katInput = document.getElementById('kategoriBarangInput');
        if (katInput) katInput.value = type;

        // Update styling tombol pilihan komoditas
        ['SINGKONG', 'MINYAK', 'PLASTIK', 'KARTON', 'MSG', 'GARAM', 'PERENYAH'].forEach(k => {
            const btn = document.getElementById(`btnKomoditas_${k}`);
            if (btn) {
                if (k === type) {
                    btn.style.background = '#0284c7';
                    btn.style.borderColor = '#0284c7';
                    btn.style.color = '#ffffff';
                } else {
                    btn.style.background = '#ffffff';
                    btn.style.borderColor = '#cbd5e1';
                    btn.style.color = '#334155';
                }
            }
        });

        const cfg = KOMODITAS_CONFIG[type] || KOMODITAS_CONFIG['SINGKONG'];
        const badgeDoc = document.getElementById('badgeDocNo');
        const titleHaccp = document.getElementById('titleFormHaccp');
        const descHaccp = document.getElementById('descFormHaccp');
        const labelNamaJenis = document.getElementById('labelNamaJenis');
        const labelPabrik = document.getElementById('labelJumlahPabrik');
        const hintPabrik = document.getElementById('hintJumlahPabrik');
        const btnNextTab2 = document.getElementById('btnNextToTab2');

        if (badgeDoc) {
            badgeDoc.style.display = (type === 'SINGKONG' && !cfg.docNo) ? 'none' : 'inline-block';
            badgeDoc.innerText = 'No. Dok: ' + cfg.docNo;
        }
        if (titleHaccp) titleHaccp.innerText = cfg.title;
        if (descHaccp) descHaccp.innerText = cfg.desc;
        if (labelNamaJenis) {
            labelNamaJenis.innerText = (type === 'MINYAK') ? 'NAMA JENIS (Varian / Fraksi Minyak) :' : cfg.labelNamaJenis;
        }
        if (labelPabrik) {
            labelPabrik.innerText = (type === 'MINYAK') ? 'Jumlah di Pabrik (Netto Masuk Pabrik)' : 'Jumlah di Pabrik (Muatan Uji Ini)';
        }
        if (hintPabrik) {
            if (type === 'MINYAK') {
                hintPabrik.innerHTML = 'Timbangan muatan tangki / jerigen minyak yang masuk pabrik (KG).';
            } else if (type === 'SINGKONG') {
                const curTahap = document.getElementById('tahapUjiInput')?.value || 'PENGUJIAN_1';
                hintPabrik.innerHTML = (curTahap === 'PENGUJIAN_2')
                    ? 'Pengujian 2: Sisa muatan setengah bak yang dibongkar tuntas (contoh: <strong>4.000 KG</strong>).'
                    : 'Pengujian 1: Muatan setengah bak pertama yang turun (contoh: <strong>3.000 KG</strong>).';
            } else {
                hintPabrik.innerHTML = 'Kuantitas fisik riil yang diterima di pabrik.';
            }
        }
        if (btnNextTab2) {
            btnNextTab2.innerText = (type === 'MINYAK') ? 'Lanjut ke Mutu Minyak & FFA \u2192' : 'Lanjut ke Pemeriksaan Parameter \u2192';
        }

        // Auto filter PO & Supplier saat komoditas dipilih
        if (type === 'MINYAK') {
            window.setPoCategoryFilter('MINYAK');
            window.setSupplierCategoryFilter('VENDOR');
        } else if (type === 'SINGKONG') {
            window.setPoCategoryFilter('SINGKONG');
            window.setSupplierCategoryFilter('RAW');
        } else {
            window.setPoCategoryFilter('AUTO');
            window.setSupplierCategoryFilter('AUTO');
        }

        // Toggle Tahap Pengujian 1 & 2 (HANYA UNTUK SINGKONG)
        const groupTahap = document.getElementById('groupTahapPengujian');
        if (groupTahap) {
            if (type === 'SINGKONG') {
                groupTahap.style.display = 'block';
            } else {
                groupTahap.style.display = 'none';
                const inputTahap = document.getElementById('tahapUjiInput');
                if (inputTahap) inputTahap.value = 'PENGUJIAN_1';
            }
        }

        // TOGGLE FIELD PANEN KHUSUS SINGKONG (LOKASI, UMUR, TGL PANEN)
        const harvestContainer = document.getElementById('singkongHarvestContainer');
        if (harvestContainer) {
            harvestContainer.style.display = cfg.hasPanenFields ? 'block' : 'none';
        }
        const grpLokasi = document.getElementById('groupLokasiPanen');
        const grpUmur = document.getElementById('groupUmurSingkong');
        const grpTgl = document.getElementById('groupTglPanen');
        const displayPanen = cfg.hasPanenFields ? 'block' : 'none';
        if (grpLokasi) grpLokasi.style.display = displayPanen;
        if (grpUmur) grpUmur.style.display = displayPanen;
        if (grpTgl) grpTgl.style.display = displayPanen;

        // Toggle pertanyaan audit halal tambahan & Produsen Pabrik
        const halalCertGroup = document.getElementById('auditHalalNonSingkong');
        if (halalCertGroup) {
            halalCertGroup.style.display = (type === 'SINGKONG') ? 'none' : 'flex';
        }

        const wrapProdusenNegara = document.getElementById('wrapProdusenNegara');
        if (wrapProdusenNegara) {
            wrapProdusenNegara.style.display = (type === 'SINGKONG') ? 'none' : 'grid';
        }

        // Toggle sample input unit (KG vs gr vs pcs)
        const grpSampleKg = document.getElementById('groupSampleKg');
        const grpSampleGr = document.getElementById('groupSampleGr');
        const grpSamplePcs = document.getElementById('groupSamplePcs');
        if (grpSampleKg) grpSampleKg.style.setProperty('display', (cfg.sampleUnit === 'KG') ? 'flex' : 'none', 'important');
        if (grpSampleGr) grpSampleGr.style.setProperty('display', (cfg.sampleUnit === 'gr') ? 'flex' : 'none', 'important');
        if (grpSamplePcs) grpSamplePcs.style.setProperty('display', (cfg.sampleUnit === 'pcs') ? 'flex' : 'none', 'important');

        // Toggle Tab 3 (Fryer) & Navigation
        const tabBtn3 = document.getElementById('tabBtn3');
        const qcTabNav = document.getElementById('qcTabNav');
        const btnNextFryer = document.getElementById('btnNextFryer');
        const btnAddItemRow = document.getElementById('btnAddItemRow');
        const btnSimpanCepat = document.getElementById('btnSimpanCepat');
        const btnTopNextFryer = document.getElementById('btnTopNextFryer');
        const btnTopSimpanCepat = document.getElementById('btnTopSimpanCepat');
        const sec2Title = document.getElementById('section2Title');
        const sec2Sub = document.getElementById('section2Sub');
        const tabBtn2 = document.getElementById('tabBtn2');

        if (cfg.showTab3) {
            if (tabBtn3) tabBtn3.style.display = 'block';
            if (qcTabNav) qcTabNav.style.gridTemplateColumns = 'repeat(3, 1fr)';
            if (btnNextFryer) {
                btnNextFryer.style.display = 'inline-block';
                btnNextFryer.innerText = 'Lanjut ke Uji Rasa Fryer & Keputusan \u2192';
            }
            if (btnTopNextFryer) btnTopNextFryer.style.display = 'inline-block';
            if (btnAddItemRow) btnAddItemRow.style.display = 'inline-block';
            if (btnSimpanCepat) btnSimpanCepat.style.display = 'none';
            if (btnTopSimpanCepat) btnTopSimpanCepat.style.display = 'none';
            if (sec2Title) sec2Title.innerText = 'Tahap 2: Pengujian I \u2022 Sampling Fisik & Diameter';
            if (sec2Sub) sec2Sub.innerText = 'Standar diameter, kebersihan tanah & kondisi visual singkong';
            if (tabBtn2) tabBtn2.innerText = '📏 2. Fisik & Diameter';
        } else {
            if (tabBtn3) tabBtn3.style.display = 'none';
            if (qcTabNav) qcTabNav.style.gridTemplateColumns = 'repeat(2, 1fr)';
            if (btnNextFryer) btnNextFryer.style.display = 'none';
            if (btnTopNextFryer) btnTopNextFryer.style.display = 'none';
            if (btnAddItemRow) btnAddItemRow.style.display = 'none';
            if (btnSimpanCepat) {
                btnSimpanCepat.style.display = 'inline-block';
                btnSimpanCepat.innerText = '💾 Simpan & Teruskan ke Gudang';
            }
            if (btnTopSimpanCepat) btnTopSimpanCepat.style.display = 'inline-block';
            if (sec2Title) {
                sec2Title.innerText = (type === 'MINYAK') 
                    ? 'Tahap 2: Sampling Mutu Minyak Goreng & FFA' 
                    : 'Tahap 2: Pemeriksaan Mutu & Parameter Kedatangan';
            }
            if (sec2Sub) {
                sec2Sub.innerText = (type === 'MINYAK')
                    ? 'Formulir Cheklist HACCP PT Mirasa Food Industry (FFA, Wadah & Netto)'
                    : 'Formulir Cheklist HACCP PT Mirasa Food Industry';
            }
            if (tabBtn2) {
                tabBtn2.innerText = (type === 'MINYAK') ? '🛢️ 2. Mutu Minyak & FFA' : '🔬 2. Mutu & Parameter';
            }
        }

        // Toggle Stock Widgets (Singkong vs Minyak)
        const singkongStockWidget = document.getElementById('singkongStockWidget');
        const minyakStockWidget = document.getElementById('minyakStockWidget');
        if (singkongStockWidget) singkongStockWidget.style.display = (type === 'SINGKONG') ? 'block' : 'none';
        if (minyakStockWidget) minyakStockWidget.style.display = (type === 'MINYAK') ? 'block' : 'none';

        const isBP = ['MSG', 'GARAM', 'PERENYAH'].includes(type);

        // Toggle containers inputan komoditas di Tahap 2
        const cSingkong = document.getElementById('singkongContainer');
        const cMinyak = document.getElementById('minyakContainer');
        const cPlastik = document.getElementById('plastikContainer');
        const cKarton = document.getElementById('kartonContainer');
        const cBP = document.getElementById('bahanPenolongContainer');
        const fContainer = document.getElementById('fryerParamsContainer');

        if (cSingkong) {
            cSingkong.style.display = (type === 'SINGKONG') ? 'flex' : 'none';
            cSingkong.querySelectorAll('input, select, textarea').forEach(el => {
                el.disabled = (type !== 'SINGKONG');
            });
            if (type === 'SINGKONG') {
                const itemCards = cSingkong.querySelectorAll('.qc-item-card');
                if (itemCards.length === 0 && typeof window.createItemCard === 'function') {
                    window.createItemCard();
                }
            }
        }
        if (fContainer) {
            fContainer.querySelectorAll('input, select, textarea').forEach(el => {
                el.disabled = (type !== 'SINGKONG');
            });
        }

        if (cMinyak) {
            cMinyak.style.display = (type === 'MINYAK') ? 'flex' : 'none';
            if (type === 'MINYAK' && typeof syncMinyakQuantity === 'function') {
                syncMinyakQuantity();
            }
        }
        if (cPlastik) cPlastik.style.display = (type === 'PLASTIK') ? 'flex' : 'none';
        if (cKarton) cKarton.style.display = (type === 'KARTON') ? 'flex' : 'none';
        if (cBP) cBP.style.display = isBP ? 'flex' : 'none';

        if (isBP) {
            const lblBp = document.getElementById('labelBahanPenolongItem');
            if (lblBp) lblBp.innerText = `Komoditas Bahan Penolong (${type}) *`;
            const selectEl = document.getElementById('bpBarangSelect');
            if (selectEl) {
                let firstMatchedIndex = -1;
                for (let i = 0; i < selectEl.options.length; i++) {
                    const opt = selectEl.options[i];
                    const comm = opt.getAttribute('data-commodity');
                    const optText = opt.text.toUpperCase();
                    const isMatch = (comm === type) || 
                                    (type === 'GARAM' && (comm === 'GARAM' || optText.includes('GARAM') || optText.includes('SALT'))) ||
                                    (type === 'MSG' && (comm === 'MSG' || optText.includes('MSG') || optText.includes('MONOSODIUM'))) ||
                                    (type === 'PERENYAH' && (comm === 'PERENYAH' || optText.includes('PERENYAH')));

                    if (isMatch) {
                        opt.style.display = '';
                        opt.disabled = false;
                        opt.hidden = false;
                        if (firstMatchedIndex === -1) firstMatchedIndex = i;
                    } else {
                        opt.style.display = 'none';
                        opt.disabled = true;
                        opt.hidden = true;
                    }
                }
                if (firstMatchedIndex !== -1) {
                    selectEl.selectedIndex = firstMatchedIndex;
                }
            }
        }

        syncQuantityFields();
        filterSupplierDropdown();
        filterPoDropdown();
        syncNamaRmOnKomoditasSwitch(type);

        if (typeof updateFloatingDock === 'function') {
            updateFloatingDock(currentTabNumber);
        }
    };

    // 4. Sinkronisasi Nama Bahan Baku / Spesifikasi
    window.syncNamaRmFromSelect = function (selectEl) {
        if (!selectEl) return;
        const opt = selectEl.selectedOptions[0];
        if (!opt) return;
        const nama = opt.getAttribute('data-nama') || opt.text.split(' - ')[1]?.split(' (')[0] || opt.text;
        const namaJenisEl = document.getElementById('namaJenisInput');
        if (namaJenisEl && nama) {
            namaJenisEl.value = nama.trim();
        }
    };

    window.syncNamaRmOnKomoditasSwitch = function (type) {
        const isBP = ['MSG', 'GARAM', 'PERENYAH'].includes(type);
        const namaJenisEl = document.getElementById('namaJenisInput');
        if (!namaJenisEl) return;

        if (type === 'SINGKONG') {
            const firstSel = document.querySelector('.item-barang-select');
            if (firstSel && firstSel.value) {
                const mat = RAW_MATERIALS.find(b => b.barang_id == firstSel.value);
                if (mat) namaJenisEl.value = mat.barang_nm;
            } else {
                const defaultMat = RAW_MATERIALS.find(b => b.kategori === 'SINGKONG');
                if (defaultMat) namaJenisEl.value = defaultMat.barang_nm;
            }
        } else if (type === 'MINYAK') {
            window.syncNamaRmFromSelect(document.getElementById('minyakBarangSelect'));
        } else if (type === 'PLASTIK') {
            window.syncNamaRmFromSelect(document.getElementById('plastikBarangSelect'));
        } else if (type === 'KARTON') {
            window.syncNamaRmFromSelect(document.getElementById('kartonBarangSelect'));
        } else if (isBP) {
            window.syncNamaRmFromSelect(document.getElementById('bpBarangSelect'));
        }
    };

    // 5. Filter & Pencarian Cepat Supplier / Produsen
    window.setSupplierCategoryFilter = function (cat) {
        currentSupplierCategory = cat;
        ['AUTO', 'RAW', 'VENDOR', 'ALL'].forEach(c => {
            const chip = document.getElementById(`chipSupplier_${c}`);
            if (chip) {
                if (c === cat) {
                    chip.classList.add('active-chip');
                } else {
                    chip.classList.remove('active-chip');
                }
            }
        });
        filterSupplierDropdown();
    };

    window.onSearchSupplier = function (query) {
        currentSupplierSearch = (query || '').toLowerCase().trim();
        const clearBtn = document.getElementById('btnClearSupplierSearch');
        if (clearBtn) {
            clearBtn.style.display = currentSupplierSearch ? 'block' : 'none';
        }
        filterSupplierDropdown();
    };

    window.clearSupplierSearch = function () {
        const input = document.getElementById('supplierSearchInput');
        if (input) input.value = '';
        window.onSearchSupplier('');
    };

    window.onSupplierSelected = function (select) {
        if (!select) return;
        const opt = select.options[select.selectedIndex];
        if (opt && opt.value) {
            const supNm = opt.getAttribute('data-supplier-nm') || opt.text.split('(')[0].trim();
            const produsenInput = document.getElementById('namaProdusenInput');
            if (produsenInput) {
                produsenInput.value = supNm;
            }
        }
    };

    function filterSupplierDropdown() {
        const select = document.getElementById('supplierSelect');
        if (!select) return;

        const badge = document.getElementById('supplierFilterBadge');
        let visibleCount = 0;
        let firstVisibleIndex = -1;
        const currentVal = select.value;
        let isCurrentValVisible = false;

        let targetJenis = null;
        let filterLabel = '';

        if (currentSupplierCategory === 'AUTO') {
            if (currentKomoditas === 'SINGKONG') {
                targetJenis = 'RAW';
                filterLabel = 'Filter: 🥔 Petani Singkong';
            } else if (currentKomoditas === 'MINYAK') {
                targetJenis = 'BP';
                filterLabel = 'Filter: 🛢️ Vendor Minyak';
            } else if (currentKomoditas === 'PLASTIK') {
                targetJenis = 'KEMASAN';
                filterLabel = 'Filter: 🛍️ Vendor Plastik';
            } else if (currentKomoditas === 'KARTON') {
                targetJenis = 'KEMASAN';
                filterLabel = 'Filter: 📦 Vendor Karton';
            } else if (['MSG', 'GARAM', 'PERENYAH'].includes(currentKomoditas)) {
                targetJenis = 'BUMBU_BP';
                filterLabel = `Filter: 🧂 Bumbu & ${currentKomoditas}`;
            }
        } else if (currentSupplierCategory === 'RAW') {
            targetJenis = 'RAW';
            filterLabel = 'Filter: 🌾 Petani Singkong';
        } else if (currentSupplierCategory === 'VENDOR') {
            targetJenis = 'VENDOR';
            filterLabel = 'Filter: 🏭 Vendor Industri';
        } else {
            targetJenis = 'ALL';
            filterLabel = 'Filter: 🌐 Semua Supplier';
        }

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
            if (targetJenis === 'ALL') {
                matchCat = true;
            } else if (targetJenis === 'RAW') {
                matchCat = isPetani || jenisCd === 'RAW' || supCd.startsWith('skg-');
            } else if (targetJenis === 'VENDOR') {
                matchCat = !isPetani && !supCd.startsWith('skg-');
            } else if (targetJenis === 'BP') {
                matchCat = jenisCd === 'BP' || (!isPetani && (supNm.includes('smart') || supNm.includes('barco') || supNm.includes('karacoco') || supNm.includes('minyak')));
            } else if (targetJenis === 'KEMASAN') {
                matchCat = jenisCd === 'KEMASAN' || (!isPetani && (supNm.includes('plast') || supNm.includes('karton') || supNm.includes('purinusa') || supNm.includes('sriwahana') || supNm.includes('print') || supNm.includes('tunas')));
            } else if (targetJenis === 'BUMBU_BP') {
                matchCat = jenisCd === 'BUMBU' || jenisCd === 'BP' || (!isPetani && !supCd.startsWith('skg-'));
            } else {
                matchCat = true;
            }

            let matchSearch = true;
            if (q) {
                matchSearch = supNm.includes(q) || supCd.includes(q);
            }

            const isMatch = matchCat && matchSearch;
            if (isMatch) {
                opt.style.display = '';
                opt.disabled = false;
                opt.hidden = false;
                visibleCount++;
                if (firstVisibleIndex === -1) firstVisibleIndex = i;
                if (opt.value === currentVal) isCurrentValVisible = true;
            } else {
                opt.style.display = 'none';
                opt.disabled = true;
                opt.hidden = true;
            }
        }

        if (badge) {
            badge.innerText = `${filterLabel} (${visibleCount})`;
        }

        if (!isCurrentValVisible && currentVal) {
            const selectedPoId = document.getElementById('poSelect')?.value;
            if (!selectedPoId) {
                select.value = '';
                const produsenInput = document.getElementById('namaProdusenInput');
                if (produsenInput) produsenInput.value = '';
            }
        } else if (isCurrentValVisible) {
            window.onSupplierSelected(select);
        }
    }

    // 6. Filter & Pencarian Cepat Purchase Order (PO)
    window.setPoCategoryFilter = function (cat) {
        currentPoCategory = cat;
        ['AUTO', 'SINGKONG', 'MINYAK', 'KEMASAN', 'BP', 'ALL'].forEach(k => {
            const btn = document.getElementById(`chipPo_${k}`);
            if (btn) {
                if (k === cat) {
                    btn.classList.add('active-chip');
                } else {
                    btn.classList.remove('active-chip');
                }
            }
        });
        filterPoDropdown();
    };

    window.onSearchPo = function (query) {
        currentPoSearch = (query || '').toLowerCase().trim();
        const clearBtn = document.getElementById('btnClearPoSearch');
        if (clearBtn) {
            clearBtn.style.display = currentPoSearch ? 'block' : 'none';
        }
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

        let targetKomoditas = [];
        let filterLabel = '';

        if (currentPoCategory === 'AUTO') {
            if (currentKomoditas === 'SINGKONG') {
                targetKomoditas = ['SINGKONG'];
                filterLabel = 'Filter: 🥔 Singkong & Bahan Baku';
            } else if (currentKomoditas === 'MINYAK') {
                targetKomoditas = ['MINYAK'];
                filterLabel = 'Filter: 🛢️ Minyak Goreng';
            } else if (currentKomoditas === 'PLASTIK') {
                targetKomoditas = ['PLASTIK'];
                filterLabel = 'Filter: 🛍️ Plastik Kemasan';
            } else if (currentKomoditas === 'KARTON') {
                targetKomoditas = ['KARTON'];
                filterLabel = 'Filter: 📦 Karton Box';
            } else if (['MSG', 'GARAM', 'PERENYAH'].includes(currentKomoditas)) {
                targetKomoditas = [currentKomoditas, 'BUMBU'];
                filterLabel = `Filter: 🧂 Bahan Penolong (${currentKomoditas})`;
            }
        } else if (currentPoCategory === 'SINGKONG') {
            targetKomoditas = ['SINGKONG'];
            filterLabel = 'Filter: 🥔 Singkong & Bahan Baku';
        } else if (currentPoCategory === 'MINYAK') {
            targetKomoditas = ['MINYAK'];
            filterLabel = 'Filter: 🛢️ Minyak Goreng';
        } else if (currentPoCategory === 'KEMASAN') {
            targetKomoditas = ['PLASTIK', 'KARTON'];
            filterLabel = 'Filter: 📦 Kemasan (Plastik/Dus)';
        } else if (currentPoCategory === 'BP') {
            targetKomoditas = ['MSG', 'GARAM', 'PERENYAH', 'BUMBU'];
            filterLabel = 'Filter: 🧂 Bahan Penolong & Bumbu';
        } else {
            targetKomoditas = null; // ALL
            filterLabel = 'Filter: 🌐 Semua PO';
        }

        const q = currentPoSearch;

        for (let i = 0; i < select.options.length; i++) {
            const opt = select.options[i];
            if (!opt.value) {
                opt.style.display = '';
                opt.disabled = false;
                continue;
            }

            const komoditasAttr = opt.getAttribute('data-komoditas') || '';
            const komoditasList = komoditasAttr.split(',').map(s => s.trim().toUpperCase());
            const searchText = (opt.getAttribute('data-search') || opt.text).toLowerCase();

            let matchCat = false;
            if (!targetKomoditas) {
                matchCat = true;
            } else {
                matchCat = targetKomoditas.some(tk => komoditasList.includes(tk));
            }

            let matchSearch = true;
            if (q) matchSearch = searchText.includes(q);

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

        const defaultOpt = select.options[0];
        if (defaultOpt) {
            if (targetKomoditas && targetKomoditas.includes('SINGKONG')) {
                defaultOpt.text = `-- Tanpa PO / Kiriman Langsung Singkong (${visibleCount} PO Tersedia) --`;
            } else if (visibleCount > 0) {
                defaultOpt.text = `-- Tanpa PO / Kiriman Langsung (${visibleCount} PO Tersedia) --`;
            } else {
                defaultOpt.text = `-- Tanpa PO / Kiriman Langsung (0 PO untuk Komoditas Ini) --`;
            }
        }

        if (badge) {
            badge.innerText = `${filterLabel} (${visibleCount} PO)`;
        }

        if (currentVal && !isCurrentValVisible) {
            select.value = '';
        }
    }

    // 7. Sinkronisasi Kuantitas Muatan
    window.syncQuantityFields = function () {
        const inputSJ = document.getElementById('inputJumlahSJ');
        const inputPabrik = document.getElementById('inputJumlahPabrik');
        const sj = parseFloat(inputSJ ? inputSJ.value : 0) || 0;
        const pabrik = parseFloat(inputPabrik ? inputPabrik.value : 0) || sj;

        if (currentKomoditas === 'SINGKONG') {
            const gross0 = document.getElementById('gross_0');
            if (gross0 && inputPabrik && inputPabrik.value !== '') {
                gross0.value = pabrik > 0 ? pabrik : '';
                window.calculateCard(0);
            }
        } else if (currentKomoditas === 'MINYAK') {
            const el = document.getElementById('minyakQtyGross');
            if (el && (!el.value || el.value == 0)) {
                el.value = pabrik > 0 ? pabrik : '';
            }
            if (typeof syncMinyakQuantity === 'function') syncMinyakQuantity();
        } else if (currentKomoditas === 'PLASTIK') {
            const el = document.getElementById('plastikQtyGross');
            if (el && (!el.value || el.value == 0)) el.value = pabrik > 0 ? Math.round(pabrik) : '';
        } else if (currentKomoditas === 'KARTON') {
            const el = document.getElementById('kartonQtyGross');
            if (el && (!el.value || el.value == 0)) el.value = pabrik > 0 ? Math.round(pabrik) : '';
        } else if (['MSG', 'GARAM', 'PERENYAH'].includes(currentKomoditas)) {
            const el = document.getElementById('bpQtyGross');
            if (el && (!el.value || el.value == 0)) el.value = pabrik > 0 ? pabrik : '';
        }
    };

    // 8. Logika Tahap Pengujian QC (Pengujian 1 vs Pengujian 2)
    window.selectTahapUji = function (tahap) {
        const tahapInput = document.getElementById('tahapUjiInput');
        if (tahapInput) tahapInput.value = tahap;

        const btn1 = document.getElementById('btnTahap_1');
        const btn2 = document.getElementById('btnTahap_2');
        const badge = document.getElementById('labelTahapBadge');
        const desc = document.getElementById('descTahapUji');
        const wrapPending = document.getElementById('wrapPendingP1');
        const titleHaccp = document.getElementById('titleFormHaccp');
        const descHaccp = document.getElementById('descFormHaccp');
        const sec2Title = document.getElementById('section2Title');
        const sec2Sub = document.getElementById('section2Sub');
        const btnSubmit = document.getElementById('btnSubmitPengujian1');
        const dockNext = document.getElementById('dockNextText');
        const hintPabrik = document.getElementById('hintJumlahPabrik');

        const labelSJ = document.getElementById('labelJumlahSJ');
        const hintSJ = document.getElementById('hintJumlahSJ');
        const labelPabrik = document.getElementById('labelJumlahPabrik');
        const hintSample = document.getElementById('hintSampleKg');
        const badgeTab1 = document.getElementById('badgeTab1Step');

        if (tahap === 'PENGUJIAN_2') {
            if (badgeTab1) {
                badgeTab1.innerText = 'PENGUJIAN 2 • LANJUTAN';
                badgeTab1.style.background = '#f3e8ff';
                badgeTab1.style.color = '#7e22ce';
            }
            if (labelSJ) labelSJ.innerHTML = '📄 Jumlah di Surat Jalan (KG) <span style="font-size:0.75rem; color:#7e22ce; font-weight:normal;">(Auto Uji 1)</span>';
            if (hintSJ) hintSJ.innerHTML = 'Total muatan Surat Jalan truk terisi dari Pengujian 1.';
            if (labelPabrik) labelPabrik.innerHTML = '⚖️ Muatan Uji 2 / Sisa Bak (KG) <span style="color:#ef4444;">*</span>';
            if (hintPabrik) {
                hintPabrik.innerHTML = 'Pengujian 2: Sisa muatan setengah bak yang dibongkar tuntas (contoh: <strong>3.500 KG</strong>).';
            }
            if (hintSample) hintSample.innerHTML = 'Cuplikan sampel ~7.0 kg dari lapisan dalam/bawah bak yang tersisa.';
            if (btn1) {
                btn1.classList.remove('active', 'blue');
                btn1.style.background = '#ffffff';
                btn1.style.borderColor = '#cbd5e1';
                btn1.style.color = '#334155';
            }
            if (btn2) {
                btn2.classList.add('active', 'purple');
                btn2.style.background = '#9333ea';
                btn2.style.borderColor = '#9333ea';
                btn2.style.color = '#ffffff';
            }
            if (badge) {
                badge.innerText = '🍟 Pengujian 2 (Lanjutan)';
                badge.style.color = '#7e22ce';
                badge.style.background = '#f3e8ff';
            }
            if (desc) {
                desc.innerHTML = '🍟 <strong>Pengujian 2:</strong> Pengujian lanjutan saat pembongkaran sisa setengah bak kedua. Sampel cuplikan ~7 kg dari lapisan bawah/dalam bak untuk verifikasi sebelum bongkar tuntas.';
                desc.style.borderLeftColor = '#9333ea';
            }
            if (wrapPending) wrapPending.style.display = 'block';
            if (currentKomoditas === 'SINGKONG') {
                if (titleHaccp) titleHaccp.innerText = 'Sampling Mutu Singkong - Pengujian 2';
                if (descHaccp) descHaccp.innerText = 'Pemeriksaan fisik & uji fryer lanjutan setelah pembongkaran setengah bak';
                if (sec2Title) sec2Title.innerText = 'Tahap 2: Pengujian 2 • Sampling Fisik Lapisan Dalam Bak';
                if (sec2Sub) sec2Sub.innerText = 'Verifikasi kondisi visual, diameter, dan tanah dari setengah bak yang tersisa';
                if (btnSubmit) {
                    btnSubmit.innerHTML = '💾 Simpan Pengujian 2 (Lolos &amp; Bongkar Tuntas ke Gudang)';
                }
                if (dockNext && currentTabNumber === 3) {
                    dockNext.innerText = '💾 Simpan Pengujian 2 (Tuntas)';
                }
            }
        } else {
            if (badgeTab1) {
                badgeTab1.innerText = 'PENGUJIAN 1 • AWAL';
                badgeTab1.style.background = '#e0f2fe';
                badgeTab1.style.color = '#0284c7';
            }
            if (labelSJ) labelSJ.innerHTML = '📄 Jumlah di Surat Jalan (KG) <span style="color:#ef4444;">*</span>';
            if (hintSJ) hintSJ.innerHTML = 'Total muatan seluruh armada pada Surat Jalan (contoh: <strong>7.000 KG</strong>).';
            if (labelPabrik) labelPabrik.innerHTML = '⚖️ Muatan Uji 1 / Setengah Bak 1 (KG) <span style="color:#ef4444;">*</span>';
            if (hintPabrik) {
                hintPabrik.innerHTML = 'Pengujian 1: Muatan setengah bak pertama yang turun ditimbang (contoh: <strong>3.500 KG</strong>). Sisa muatan akan diuji pada Pengujian 2.';
            }
            if (hintSample) hintSample.innerHTML = 'Format standar <strong>7.0 KG</strong> (sampel gabungan cuplikan bak depan, tengah, belakang).';
            if (btn1) {
                btn1.classList.add('active', 'blue');
                btn1.style.background = '#0284c7';
                btn1.style.borderColor = '#0284c7';
                btn1.style.color = '#ffffff';
            }
            if (btn2) {
                btn2.classList.remove('active', 'purple');
                btn2.style.background = '#ffffff';
                btn2.style.borderColor = '#cbd5e1';
                btn2.style.color = '#334155';
            }
            if (badge) {
                badge.innerText = '🚛 Pengujian 1 (Awal)';
                badge.style.color = '#0284c7';
                badge.style.background = '#e0f2fe';
            }
            if (desc) {
                desc.innerHTML = '🚛 <strong>Pengujian 1:</strong> Pengujian awal saat truk singkong tiba di pos penerimaan. Sampel cuplikan ~7 kg dari lapisan awal/atas bak sebelum mulai pembongkaran.';
                desc.style.borderLeftColor = '#0284c7';
            }
            if (wrapPending) wrapPending.style.display = 'none';
            if (currentKomoditas === 'SINGKONG') {
                if (titleHaccp) titleHaccp.innerText = 'Sampling Mutu Singkong';
                if (descHaccp) descHaccp.innerText = 'Pencatatan sampling mutu kedatangan bahan baku di lapangan';
                if (sec2Title) sec2Title.innerText = 'Tahap 2: Pengujian 1 • Sampling Fisik & Diameter';
                if (sec2Sub) sec2Sub.innerText = 'Standar diameter, kebersihan tanah & kondisi visual singkong';
                if (btnSubmit) {
                    btnSubmit.innerHTML = '💾 Simpan Pengujian 1 (Selesai Inspeksi &amp; Siap Bongkar Setengah Bak)';
                }
                if (dockNext && currentTabNumber === 3) {
                    dockNext.innerText = '💾 Simpan Pengujian 1 (Selesai)';
                }
            }
        }

        try {
            sessionStorage.setItem('qc_singkong_tahap', tahap);
            const url = new URL(window.location.href);
            url.searchParams.set('tahap', (tahap === 'PENGUJIAN_2') ? '2' : '1');
            window.history.replaceState({}, '', url.toString());
        } catch (e) {}
    };

    window.setTahapUji = function (tahap) {
        window.selectTahapUji(tahap);
    };

    window.onSelectPendingArrival = function (selectEl) {
        if (!selectEl) return;
        const opt = selectEl.selectedOptions[0];
        if (!opt || !opt.value) {
            window.clearSelectedPendingArrival();
            return;
        }

        const dataStr = opt.getAttribute('data-json');
        if (!dataStr) return;

        let d = {};
        try {
            d = JSON.parse(dataStr);
        } catch (e) {
            console.error('Invalid json data', e);
            return;
        }

        const parentQcId = document.getElementById('parentQcIdInput');
        const batchNo = document.getElementById('batchNoInput');
        if (parentQcId) parentQcId.value = d.qc_id || '';
        if (batchNo) batchNo.value = d.batch_no || '';

        // Auto-fill Supplier
        if (d.supplier_id) {
            const sSelect = document.getElementById('supplierSelect');
            if (sSelect) {
                window.setSupplierCategoryFilter('RAW');
                sSelect.value = d.supplier_id;
                window.onSupplierSelected(sSelect);
            }
        }

        // Auto-fill Gudang
        if (d.gudang_id) {
            const gSelect = document.getElementById('gudangSelect');
            if (gSelect) gSelect.value = d.gudang_id;
        }

        // Auto-fill PO (or Non-PO)
        const pSelect = document.getElementById('poSelect');
        if (pSelect) {
            pSelect.value = d.po_id || '';
            if (typeof window.onPoSelected === 'function') window.onPoSelected(pSelect);
        }

        // Auto-fill Plat & Sopir
        const platInput = document.querySelector('input[name="plat_nomor_truk"]');
        if (platInput && d.plat) platInput.value = d.plat;

        const sopirInput = document.querySelector('input[name="sopir_nama"]');
        if (sopirInput && d.sopir) sopirInput.value = d.sopir;

        // Auto-fill Dokumen
        const sjInput = document.querySelector('input[name="surat_jalan_supplier"]');
        if (sjInput && d.sj) sjInput.value = d.sj;

        const doInput = document.querySelector('input[name="nomor_do"]');
        if (doInput && d.do) doInput.value = d.do;

        // Auto-fill Panen
        const lokasiInput = document.querySelector('input[name="lokasi_panen"]');
        if (lokasiInput && d.lokasi_panen) lokasiInput.value = d.lokasi_panen;

        const umurInput = document.querySelector('input[name="umur_singkong_bln"]');
        if (umurInput && d.umur_singkong) umurInput.value = d.umur_singkong;

        const tglPanenInput = document.querySelector('input[name="tgl_panen"]');
        if (tglPanenInput && d.tgl_panen) tglPanenInput.value = d.tgl_panen;

        const totalSJ = parseFloat(d.sj_qty || 0);
        const p1Gross = parseFloat(d.gross || 0);
        
        let estimasiGrossUji2 = 0;
        let isFullUnloaded = false;

        if (totalSJ > 0 && p1Gross >= totalSJ) {
            // Muatan sudah turun 100% atau bahkan lebih di Pengujian 1
            isFullUnloaded = true;
            estimasiGrossUji2 = 0;
        } else if (totalSJ > 0 && p1Gross < totalSJ) {
            // Masih ada sisa setengah bak
            estimasiGrossUji2 = Math.max(0, totalSJ - p1Gross);
        } else {
            // totalSJ tidak diisi atau 0: fallback ke estimasi p1Gross atau default
            estimasiGrossUji2 = p1Gross > 0 ? p1Gross : 4000;
        }

        const inputPabrik = document.getElementById('inputJumlahPabrik');
        if (inputPabrik) inputPabrik.value = estimasiGrossUji2;

        // Auto-fill Singkong Item 0
        if (d.barang_id) {
            const bSel = document.getElementById('barang_select_0');
            if (bSel) {
                bSel.value = d.barang_id;
                window.onItemBarangChanged(0);
            }
        }

        const gross0 = document.getElementById('gross_0');
        if (gross0) {
            gross0.value = estimasiGrossUji2;
            window.calculateCard(0);
        }
        if (d.refraksi) {
            const ref0 = document.getElementById('refraksi_0');
            if (ref0) {
                ref0.value = d.refraksi;
                window.calculateCard(0);
            }
        }

        // Tampilkan banner informatif hasil Pengujian 1 vs Pengujian 2
        const banner = document.getElementById('bannerSelectedP1');
        const descBanner = document.getElementById('labelSelectedP1Desc');
        if (banner) banner.style.display = 'flex';
        if (descBanner) {
            const p1GradeLabel = d.grade_cd === 'B' ? '🟡 Grade B' : '🟢 Grade A';
            if (isFullUnloaded) {
                descBanner.innerHTML = `<strong>#${d.qc_no}</strong> &bull; 🚛 ${d.plat || 'Plat -'} &bull; Hasil Uji 1: <span style="background:#e0f2fe; color:#0369a1; padding:1px 6px; border-radius:4px; font-weight:800;">${p1GradeLabel} (${p1Gross.toLocaleString('id-ID')} KG)</span> &bull; Muatan Uji 2: <span style="background:#dcfce7; color:#15803d; padding:1px 6px; border-radius:4px; font-weight:800;">✅ Muatan Sudah Turun Penuh (Sisa 0 KG)</span>`;
            } else {
                descBanner.innerHTML = `<strong>#${d.qc_no}</strong> &bull; 🚛 ${d.plat || 'Plat -'} &bull; Hasil Uji 1: <span style="background:#e0f2fe; color:#0369a1; padding:1px 6px; border-radius:4px; font-weight:800;">${p1GradeLabel} (${p1Gross.toLocaleString('id-ID')} KG)</span> &bull; Muatan Uji 2: <span style="background:#f3e8ff; color:#7e22ce; padding:1px 6px; border-radius:4px; font-weight:800;">Sisa Setengah Bak (${estimasiGrossUji2.toLocaleString('id-ID')} KG)</span>`;
            }
        }

        if (isFullUnloaded) {
            window.showQcToast('Muatan Sudah Turun Penuh', `Truk #${d.qc_no} sudah membongkar seluruh muatan (${p1Gross.toLocaleString('id-ID')} KG) pada Pengujian 1. Tidak perlu Pengujian 2 lagi, tiket ini sudah bisa langsung diterima Gudang!`, null, 1);
        } else {
            window.showQcToast('Data Pengujian 1 Terisi', `Data kedatangan #${d.qc_no} dimuat. Sisa muatan bak: ${estimasiGrossUji2.toLocaleString('id-ID')} KG.`, null, 1);
        }
    };

    window.clearSelectedPendingArrival = function () {
        const parentQcId = document.getElementById('parentQcIdInput');
        const batchNo = document.getElementById('batchNoInput');
        const selectP1 = document.getElementById('selectPendingP1');
        const banner = document.getElementById('bannerSelectedP1');

        if (parentQcId) parentQcId.value = '';
        if (batchNo) batchNo.value = '';
        if (selectP1) selectP1.value = '';
        if (banner) banner.style.display = 'none';

        window.showQcToast('Pilihan Direset', 'Mode input kedatangan Pengujian 2 mandiri diaktifkan.', null, 1, true);
    };

    // 9. Floating Action Dock & Tab Navigasi
    window.updateFloatingDock = function (tabNumber) {
        currentTabNumber = tabNumber;
        const btnBack = document.getElementById('dockBtnBack');
        const btnNext = document.getElementById('dockBtnNext');
        const backText = document.getElementById('dockBackText');
        const nextText = document.getElementById('dockNextText');
        const nextIcon = document.getElementById('dockNextIcon');
        if (!btnBack || !btnNext) return;

        if (tabNumber === 1) {
            btnBack.style.display = 'none';
            btnNext.style.display = 'inline-flex';
            btnNext.className = 'btn-dock-next btn-primary-state';
            btnNext.style.background = '';
            btnNext.style.borderColor = '';
            if (nextText) {
                nextText.innerText = (currentKomoditas === 'MINYAK') ? 'Lanjut ke Tahap 2' : 'Lanjut ke 2. Parameter';
            }
            if (nextIcon) nextIcon.style.display = 'inline-block';
            btnNext.onclick = function () { window.switchQcTab(2); };
        } else if (tabNumber === 2) {
            btnBack.style.display = 'inline-flex';
            if (backText) backText.innerText = 'Kembali';
            btnBack.onclick = function () { window.switchQcTab(1); };

            if (currentKomoditas === 'SINGKONG') {
                btnNext.style.display = 'inline-flex';
                btnNext.className = 'btn-dock-next btn-primary-state';
                btnNext.style.background = '';
                btnNext.style.borderColor = '';
                if (nextText) nextText.innerText = 'Lanjut ke 3. Uji Fryer';
                if (nextIcon) nextIcon.style.display = 'inline-block';
                btnNext.onclick = function () { window.switchQcTab(3); };
            } else {
                btnNext.style.display = 'inline-flex';
                btnNext.className = 'btn-dock-next btn-success-state';
                btnNext.style.background = '';
                btnNext.style.borderColor = '';
                if (nextText) {
                    nextText.innerText = (currentKomoditas === 'MINYAK') ? '💾 Simpan QC Minyak' : '💾 Simpan & Teruskan ke Gudang';
                }
                if (nextIcon) nextIcon.style.display = 'none';
                btnNext.onclick = function () { window.submitNonSingkong(); };
            }
        } else if (tabNumber === 3) {
            btnBack.style.display = 'inline-flex';
            if (backText) backText.innerText = 'Kembali';
            btnBack.onclick = function () { window.switchQcTab(2); };

            btnNext.style.display = 'inline-flex';
            btnNext.className = 'btn-dock-next btn-success-state';
            btnNext.style.background = '';
            btnNext.style.borderColor = '';
            if (nextText) nextText.innerText = '💾 Simpan Pengujian (Selesai)';
            if (nextIcon) nextIcon.style.display = 'none';
            btnNext.onclick = function () {
                const subBtn = document.getElementById('btnSubmitPengujian1');
                if (subBtn) subBtn.click();
            };
        }
    };

    window.switchQcTab = function (tabNumber) {
        const sec1 = document.getElementById('qcSection1');
        const sec2 = document.getElementById('qcSection2');
        const sec3 = document.getElementById('qcSection3');

        if (sec1) {
            sec1.style.setProperty('display', (tabNumber === 1) ? 'flex' : 'none', 'important');
            sec1.style.setProperty('flex-direction', 'column', 'important');
        }
        if (sec2) {
            sec2.style.setProperty('display', (tabNumber === 2) ? 'flex' : 'none', 'important');
            sec2.style.setProperty('flex-direction', 'column', 'important');
        }
        if (sec3) {
            sec3.style.setProperty('display', (tabNumber === 3) ? 'flex' : 'none', 'important');
            sec3.style.setProperty('flex-direction', 'column', 'important');
        }

        for (let i = 1; i <= 3; i++) {
            const btn = document.getElementById(`tabBtn${i}`);
            if (!btn) continue;
            if (i === tabNumber) {
                btn.style.background = '#0284c7';
                btn.style.borderColor = '#0284c7';
                btn.style.color = '#ffffff';
            } else {
                btn.style.background = '#ffffff';
                btn.style.borderColor = '#cbd5e1';
                btn.style.color = '#475569';
            }
        }

        window.updateFloatingDock(tabNumber);

        try {
            sessionStorage.setItem('qc_active_tab', tabNumber);
            const url = new URL(window.location.href);
            url.hash = 'tab' + tabNumber;
            window.history.replaceState({}, '', url.toString());
        } catch (e) {}

        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    // 10. Toast Notifikasi Interaktif
    window.showQcToast = function (title, message, targetEl = null, tabNumber = null, isWarning = false) {
        let container = document.getElementById('qcToastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'qcToastContainer';
            container.className = 'qc-toast-container';
            document.body.appendChild(container);
        }

        container.innerHTML = '';

        const toast = document.createElement('div');
        toast.className = `qc-toast ${isWarning ? 'qc-toast-warning' : ''}`;
        toast.innerHTML = `
            <div style="display: flex; gap: 0.6rem; align-items: flex-start;">
                <div style="font-size: 1.25rem;">${isWarning ? '⚠️' : '🚫'}</div>
                <div style="flex: 1;">
                    <div class="qc-toast-title">${title}</div>
                    <div class="qc-toast-body">${message}</div>
                </div>
                <button type="button" class="qc-toast-close" onclick="this.closest('.qc-toast').remove()" style="background: none; border: none; font-size: 1rem; color: #94a3b8; cursor: pointer;">✕</button>
            </div>
        `;
        container.appendChild(toast);

        if (tabNumber && typeof window.switchQcTab === 'function') {
            window.switchQcTab(tabNumber);
        }

        if (targetEl) {
            setTimeout(() => {
                targetEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                targetEl.classList.add('field-error-pulse');
                setTimeout(() => targetEl.classList.remove('field-error-pulse'), 2500);
                if (typeof targetEl.focus === 'function') targetEl.focus();
            }, tabNumber ? 200 : 0);
        }

        setTimeout(() => {
            if (toast.parentElement) {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-10px)';
                setTimeout(() => toast.remove(), 250);
            }
        }, 4500);
    };

    // 11. Validasi Form
    function validateFormQc1() {
        const supplierSelect = document.getElementById('supplierSelect');
        if (!supplierSelect || !supplierSelect.value) {
            window.showQcToast('Supplier Belum Dipilih', 'Silakan pilih Mitra Rekanan / Produsen pemasok bahan baku!', supplierSelect, 1);
            return false;
        }

        const gudangSelect = document.getElementById('gudangSelect');
        if (!gudangSelect || !gudangSelect.value) {
            window.showQcToast('Gudang Belum Dipilih', 'Silakan pilih lokasi cabang / gudang tujuan pembongkaran!', gudangSelect, 1);
            return false;
        }

        const inputJumlahSJ = document.getElementById('inputJumlahSJ');
        if (inputJumlahSJ) {
            const sjVal = parseFloat(inputJumlahSJ.value || 0);
            if (isNaN(sjVal) || sjVal <= 0) {
                window.showQcToast('Kuantitas Surat Jalan Kosong', 'Kuantitas kedatangan pada Surat Jalan wajib diisi dan lebih dari 0!', inputJumlahSJ, 1);
                return false;
            }
        }

        if (currentKomoditas === 'SINGKONG') {
            const itemCards = document.querySelectorAll('#singkongContainer .qc-item-card');
            if (itemCards.length === 0) {
                window.showQcToast('Item Singkong Kosong', 'Minimal harus ada 1 item komoditas singkong yang diinspeksi!', null, 2);
                return false;
            }

            for (let i = 0; i < itemCards.length; i++) {
                const card = itemCards[i];
                const selectBarang = card.querySelector('select[name^="items["][name$="[barang_id]"]');
                if (selectBarang && !selectBarang.value) {
                    window.showQcToast('Jenis Singkong Kosong', 'Pilih varian / jenis singkong pada kartu inspeksi!', selectBarang, 2);
                    return false;
                }

                const inputGross = card.querySelector('input[name^="items["][name$="[qty_timbang_gross]"]');
                if (inputGross) {
                    const gross = parseFloat(inputGross.value || 0);
                    if (isNaN(gross) || gross <= 0) {
                        window.showQcToast('Timbangan Kotor Kosong', 'Kuantitas timbangan kotor (gross kg) wajib diisi lebih dari 0!', inputGross, 2);
                        return false;
                    }
                }
            }

            const kesimpulanChecked = document.querySelector('input[name="kesimpulan_qc"]:checked')?.value || 'TERIMA';
            let hasPahit = false;
            document.querySelectorAll('select[name$="[fryer_rasa]"]').forEach(sel => {
                if (sel.value === 'PAHIT') hasPahit = true;
            });

            if (kesimpulanChecked === 'TERIMA' && hasPahit) {
                window.showQcToast('Singkong Pahit Dilarang Diterima!', 'Terdeteksi sampel singkong berasa PAHIT (racun sianida). Keputusan harus diubah ke TOLAK TOTAL!', document.getElementById('radioTolak'), 3);
                return false;
            }
        } else if (currentKomoditas === 'MINYAK') {
            const minyakSelect = document.getElementById('minyakBarangSelect');
            if (!minyakSelect || !minyakSelect.value) {
                window.showQcToast('Item Minyak Kosong', 'Pilih item komoditas minyak goreng yang diuji!', minyakSelect, 2);
                return false;
            }
            const grossInput = document.getElementById('minyakQtyGross');
            const grossVal = parseFloat(grossInput?.value || 0);
            const pabrikVal = parseFloat(document.getElementById('inputJumlahPabrik')?.value || 0);
            if ((isNaN(grossVal) || grossVal <= 0) && (isNaN(pabrikVal) || pabrikVal <= 0)) {
                window.showQcToast('Timbangan Netto Minyak Kosong', 'Kuantitas timbangan netto kedatangan minyak wajib diisi lebih dari 0!', grossInput, 2);
                return false;
            }
        }

        return true;
    }

    function showSubmitLoading(title = 'Menyimpan Data QC...') {
        isSubmitting = true;
        const overlay = document.getElementById('qcSubmitOverlay');
        const overlayTitle = document.getElementById('overlayTitle');
        if (overlayTitle) overlayTitle.innerText = title;
        if (overlay) overlay.style.display = 'flex';

        document.querySelectorAll('button[type="submit"], #btnSimpanCepat, #btnSubmitPengujian1, #dockBtnNext, #btnTopSimpanCepat').forEach(btn => {
            btn.disabled = true;
            btn.style.opacity = '0.5';
            btn.style.pointerEvents = 'none';
            btn.style.cursor = 'not-allowed';
        });
    }

    function injectHiddenInput(name, value) {
        let el = document.querySelector(`input[type="hidden"][name="${name}"]`);
        if (!el) {
            el = document.createElement('input');
            el.type = 'hidden';
            el.name = name;
            document.getElementById('qcForm').appendChild(el);
        }
        el.value = value;
    }

    function prepareCommoditySubmission() {
        const curNamaJenis = document.getElementById('namaJenisInput')?.value?.trim();

        if (currentKomoditas === 'MINYAK') {
            const bId = document.getElementById('minyakBarangSelect').value;
            const bOpt = document.getElementById('minyakBarangSelect')?.selectedOptions[0];
            const bNm = bOpt ? (bOpt.getAttribute('data-nama') || bOpt.text.split(' - ')[1]?.split(' (')[0] || bOpt.text) : 'Minyak Goreng Kelapa Sawit';
            injectHiddenInput('nama_jenis', curNamaJenis || bNm);

            const gross = parseFloat(document.getElementById('minyakQtyGross')?.value) || parseFloat(document.getElementById('inputJumlahPabrik')?.value) || 0;
            const reject = parseFloat(document.getElementById('minyakQtyReject')?.value) || 0;
            const kesimpulan = document.querySelector('input[name="minyak_kesimpulan"]:checked')?.value || 'TERIMA';

            injectHiddenInput('items[0][barang_id]', bId);
            injectHiddenInput('items[0][qty_timbang_gross]', gross);
            injectHiddenInput('items[0][qty_reject]', reject);
            injectHiddenInput('items[0][qty_netto_lolos]', Math.max(0, gross - reject));
            injectHiddenInput('items[0][keputusan_qc]', kesimpulan === 'TERIMA' ? 'PASSED' : 'REJECT_TOTAL');
            injectHiddenInput('items[0][status_raw_material]', document.querySelector('input[name="minyak_status_raw_material"]:checked')?.value || 'OK');
            injectHiddenInput('items[0][tipe_wadah_minyak]', document.querySelector('input[name="minyak_tipe_wadah"]:checked')?.value || 'TANGKI');
            injectHiddenInput('items[0][kondisi_tangki_jerigen]', document.querySelector('input[name="minyak_kondisi_wadah"]:checked')?.value || 'OK');
            injectHiddenInput('items[0][ffa_coa]', document.querySelector('input[name="minyak_ffa_coa"]')?.value || '');
            injectHiddenInput('items[0][ffa_qc]', document.querySelector('input[name="minyak_ffa_qc"]')?.value || '');
            injectHiddenInput('items[0][minyak_jernih_st]', document.querySelector('input[name="minyak_jernih_st"]')?.checked ? 1 : 0);
            injectHiddenInput('items[0][tangki_bersih_st]', document.querySelector('input[name="minyak_tangki_bersih_st"]')?.checked ? 1 : 0);
            injectHiddenInput('items[0][catatan_dtl]', document.querySelector('textarea[name="minyak_komentar"]')?.value || '');
            injectHiddenInput('catatan_umum', document.querySelector('textarea[name="minyak_komentar"]')?.value || '');

            // Header-level inputs
            injectHiddenInput('kesimpulan_qc', kesimpulan);
            injectHiddenInput('jumlah_di_pabrik', gross);
            const inSJ = document.getElementById('inputJumlahSJ');
            if (inSJ && inSJ.value) {
                injectHiddenInput('jumlah_surat_jalan', parseFloat(inSJ.value) || gross);
            }
            const sampleGr = document.querySelector('input[name="jumlah_sample_gr"]');
            if (sampleGr && sampleGr.value) {
                injectHiddenInput('jumlah_sample_gr', parseFloat(sampleGr.value) || 250);
            }
        } else if (currentKomoditas === 'PLASTIK') {
            const bId = document.getElementById('plastikBarangSelect').value;
            const bOpt = document.getElementById('plastikBarangSelect')?.selectedOptions[0];
            const bNm = bOpt ? (bOpt.getAttribute('data-nama') || bOpt.text.split(' - ')[1]?.split(' (')[0] || bOpt.text) : 'Plastik Kemasan';
            injectHiddenInput('nama_jenis', curNamaJenis || bNm);

            const gross = parseFloat(document.getElementById('plastikQtyGross').value) || parseFloat(document.getElementById('inputJumlahPabrik').value) || 0;
            const reject = parseFloat(document.getElementById('plastikQtyReject').value) || 0;
            const kesimpulan = document.querySelector('input[name="plastik_kesimpulan"]:checked')?.value || 'TERIMA';

            injectHiddenInput('items[0][barang_id]', bId);
            injectHiddenInput('items[0][qty_timbang_gross]', gross);
            injectHiddenInput('items[0][qty_reject]', reject);
            injectHiddenInput('items[0][keputusan_qc]', kesimpulan === 'TERIMA' ? 'PASSED' : 'REJECT_TOTAL');
            injectHiddenInput('items[0][status_raw_material]', document.querySelector('input[name="plastik_status_raw_material"]:checked')?.value || 'OK');
            injectHiddenInput('items[0][kemasan_kondisi]', document.querySelector('input[name="plastik_kemasan_kondisi"]:checked')?.value || 'OK');
            injectHiddenInput('items[0][kemasan_kotor]', document.querySelector('input[name="plastik_kemasan_kotor"]')?.checked ? 1 : 0);
            injectHiddenInput('items[0][kemasan_apek]', document.querySelector('input[name="plastik_kemasan_apek"]')?.checked ? 1 : 0);
            injectHiddenInput('items[0][kemasan_basah]', document.querySelector('input[name="plastik_kemasan_basah"]')?.checked ? 1 : 0);
            injectHiddenInput('items[0][kemasan_sobek]', document.querySelector('input[name="plastik_kemasan_sobek"]')?.checked ? 1 : 0);
            injectHiddenInput('items[0][ketebalan_analisa]', document.querySelector('input[name="plastik_ketebalan_analisa"]')?.value || '');
            injectHiddenInput('items[0][ketebalan_standar]', document.querySelector('input[name="plastik_ketebalan_standar"]')?.value || '');
            injectHiddenInput('items[0][keutuhan_analisa]', document.querySelector('input[name="plastik_keutuhan_analisa"]')?.value || 'Tidak Sobek');
            injectHiddenInput('items[0][keutuhan_standar]', document.querySelector('input[name="plastik_keutuhan_standar"]')?.value || 'Tidak Sobek');
            injectHiddenInput('items[0][catatan_dtl]', document.querySelector('textarea[name="plastik_komentar"]')?.value || '');
            injectHiddenInput('catatan_umum', document.querySelector('textarea[name="plastik_komentar"]')?.value || '');
        } else if (currentKomoditas === 'KARTON') {
            const bId = document.getElementById('kartonBarangSelect').value;
            const bOpt = document.getElementById('kartonBarangSelect')?.selectedOptions[0];
            const bNm = bOpt ? (bOpt.getAttribute('data-nama') || bOpt.text.split(' - ')[1]?.split(' (')[0] || bOpt.text) : 'Karton Box';
            injectHiddenInput('nama_jenis', curNamaJenis || bNm);

            const gross = parseFloat(document.getElementById('kartonQtyGross').value) || parseFloat(document.getElementById('inputJumlahPabrik').value) || 0;
            const reject = parseFloat(document.getElementById('kartonQtyReject').value) || 0;
            const kesimpulan = document.querySelector('input[name="karton_kesimpulan"]:checked')?.value || 'TERIMA';

            injectHiddenInput('items[0][barang_id]', bId);
            injectHiddenInput('items[0][qty_timbang_gross]', gross);
            injectHiddenInput('items[0][qty_reject]', reject);
            injectHiddenInput('items[0][keputusan_qc]', kesimpulan === 'TERIMA' ? 'PASSED' : 'REJECT_TOTAL');
            injectHiddenInput('items[0][status_raw_material]', document.querySelector('input[name="karton_status_raw_material"]:checked')?.value || 'OK');
            injectHiddenInput('items[0][kemasan_kondisi]', document.querySelector('input[name="karton_kemasan_kondisi"]:checked')?.value || 'OK');
            injectHiddenInput('items[0][kemasan_kotor]', document.querySelector('input[name="karton_kemasan_kotor"]')?.checked ? 1 : 0);
            injectHiddenInput('items[0][kemasan_apek]', document.querySelector('input[name="karton_kemasan_apek"]')?.checked ? 1 : 0);
            injectHiddenInput('items[0][kemasan_basah]', document.querySelector('input[name="karton_kemasan_basah"]')?.checked ? 1 : 0);
            injectHiddenInput('items[0][kemasan_jamur]', document.querySelector('input[name="karton_kemasan_jamur"]')?.checked ? 1 : 0);
            injectHiddenInput('items[0][kemasan_sobek]', document.querySelector('input[name="karton_kemasan_sobek"]')?.checked ? 1 : 0);
            injectHiddenInput('items[0][kemasan_berminyak]', document.querySelector('input[name="karton_kemasan_berminyak"]')?.checked ? 1 : 0);
            injectHiddenInput('items[0][kemasan_berdebu]', document.querySelector('input[name="karton_kemasan_berdebu"]')?.checked ? 1 : 0);
            injectHiddenInput('items[0][dimensi_panjang_analisa]', document.querySelector('input[name="karton_dimensi_panjang_analisa"]')?.value || '');
            injectHiddenInput('items[0][dimensi_panjang_standar]', document.querySelector('input[name="karton_dimensi_panjang_standar"]')?.value || '');
            injectHiddenInput('items[0][dimensi_lebar_analisa]', document.querySelector('input[name="karton_dimensi_lebar_analisa"]')?.value || '');
            injectHiddenInput('items[0][dimensi_lebar_standar]', document.querySelector('input[name="karton_dimensi_lebar_standar"]')?.value || '');
            injectHiddenInput('items[0][dimensi_tinggi_analisa]', document.querySelector('input[name="karton_dimensi_tinggi_analisa"]')?.value || '');
            injectHiddenInput('items[0][dimensi_tinggi_standar]', document.querySelector('input[name="karton_dimensi_tinggi_standar"]')?.value || '');
            injectHiddenInput('items[0][spesifikasi_analisa]', document.querySelector('input[name="karton_spesifikasi_analisa"]')?.value || '');
            injectHiddenInput('items[0][spesifikasi_standar]', document.querySelector('input[name="karton_spesifikasi_standar"]')?.value || '');
            injectHiddenInput('items[0][catatan_dtl]', document.querySelector('textarea[name="karton_komentar"]')?.value || '');
            injectHiddenInput('catatan_umum', document.querySelector('textarea[name="karton_komentar"]')?.value || '');
        } else if (['MSG', 'GARAM', 'PERENYAH'].includes(currentKomoditas)) {
            const bId = document.getElementById('bpBarangSelect').value;
            const bOpt = document.getElementById('bpBarangSelect')?.selectedOptions[0];
            const bNm = bOpt ? (bOpt.getAttribute('data-nama') || bOpt.text.split(' - ')[1]?.split(' (')[0] || bOpt.text) : currentKomoditas;
            injectHiddenInput('nama_jenis', curNamaJenis || bNm);

            const gross = parseFloat(document.getElementById('bpQtyGross').value) || parseFloat(document.getElementById('inputJumlahPabrik').value) || 0;
            const reject = parseFloat(document.getElementById('bpQtyReject').value) || 0;
            const kesimpulan = document.querySelector('input[name="bp_kesimpulan"]:checked')?.value || 'TERIMA';

            injectHiddenInput('items[0][barang_id]', bId);
            injectHiddenInput('items[0][qty_timbang_gross]', gross);
            injectHiddenInput('items[0][qty_reject]', reject);
            injectHiddenInput('items[0][keputusan_qc]', kesimpulan === 'TERIMA' ? 'PASSED' : 'REJECT_TOTAL');
            injectHiddenInput('items[0][status_raw_material]', document.querySelector('input[name="bp_status_raw_material"]:checked')?.value || 'OK');
            injectHiddenInput('items[0][isi_kering]', document.querySelector('input[name="bp_isi_kering"]')?.checked ? 1 : 0);
            injectHiddenInput('items[0][isi_basah]', document.querySelector('input[name="bp_isi_basah"]')?.checked ? 1 : 0);
            injectHiddenInput('items[0][isi_gumpal]', document.querySelector('input[name="bp_isi_gumpal"]')?.checked ? 1 : 0);
            injectHiddenInput('items[0][isi_berminyak]', document.querySelector('input[name="bp_isi_berminyak"]')?.checked ? 1 : 0);
            injectHiddenInput('items[0][kemasan_kondisi]', document.querySelector('input[name="bp_kemasan_kondisi"]:checked')?.value || 'OK');
            injectHiddenInput('items[0][kemasan_kotor]', document.querySelector('input[name="bp_kemasan_kotor"]')?.checked ? 1 : 0);
            injectHiddenInput('items[0][kemasan_apek]', document.querySelector('input[name="bp_kemasan_apek"]')?.checked ? 1 : 0);
            injectHiddenInput('items[0][kemasan_jamur]', document.querySelector('input[name="bp_kemasan_jamur"]')?.checked ? 1 : 0);
            injectHiddenInput('items[0][kemasan_sobek]', document.querySelector('input[name="bp_kemasan_sobek"]')?.checked ? 1 : 0);
            injectHiddenInput('items[0][catatan_dtl]', document.querySelector('textarea[name="bp_komentar"]')?.value || '');
            injectHiddenInput('catatan_umum', document.querySelector('textarea[name="bp_komentar"]')?.value || '');
        } else if (currentKomoditas === 'SINGKONG') {
            const firstSel = document.querySelector('.item-barang-select');
            const firstMat = firstSel ? RAW_MATERIALS.find(b => b.barang_id == firstSel.value) : null;
            injectHiddenInput('nama_jenis', curNamaJenis || (firstMat ? firstMat.barang_nm : 'Singkong Basah Curah'));
        }
    }

    window.submitNonSingkong = function () {
        if (isSubmitting) return;
        if (!validateFormQc1()) return;

        prepareCommoditySubmission();
        const statUji = document.getElementById('statusUjiGorengInput');
        const tahapUji = document.getElementById('tahapUjiInput');
        if (statUji) statUji.value = 'SELESAI';
        if (tahapUji) tahapUji.value = 'PENGUJIAN_1';
        showSubmitLoading('Menyimpan & Meneruskan ke Gudang...');
        document.getElementById('qcForm').submit();
    };

    // 12. Uji Rasa Fryer & Kesimpulan Singkong
    // 12. Uji Rasa Fryer & Kesimpulan Singkong (Sensori & Defect Frying)
    window.setFryerRasa = function (idx, val) {
        const input = document.getElementById(`fryer_rasa_${idx}`);
        if (input) input.value = val;

        const btnTidak = document.getElementById(`btn_rasa_tidak_${idx}`);
        const btnAgak = document.getElementById(`btn_rasa_agak_${idx}`);
        const btnPahit = document.getElementById(`btn_rasa_pahit_${idx}`);

        btnTidak?.classList.remove('active', 'pass', 'warn', 'fail');
        btnAgak?.classList.remove('active', 'pass', 'warn', 'fail');
        btnPahit?.classList.remove('active', 'pass', 'warn', 'fail');

        if (val === 'TIDAK_PAHIT') {
            btnTidak?.classList.add('active', 'pass');
        } else if (val === 'AGAK_PAHIT') {
            btnAgak?.classList.add('active', 'warn');
        } else if (val === 'PAHIT') {
            btnPahit?.classList.add('active', 'fail');
        }

        window.onFryerRasaChanged(val);
    };

    window.setFryerTekstur = function (idx, val) {
        const input = document.getElementById(`fryer_tekstur_${idx}`);
        if (input) input.value = val;

        const btnRenyah = document.getElementById(`btn_tekstur_renyah_${idx}`);
        const btnKeras = document.getElementById(`btn_tekstur_keras_${idx}`);
        const btnLembek = document.getElementById(`btn_tekstur_lembek_${idx}`);

        btnRenyah?.classList.remove('active', 'pass', 'warn', 'fail');
        btnKeras?.classList.remove('active', 'pass', 'warn', 'fail');
        btnLembek?.classList.remove('active', 'pass', 'warn', 'fail');

        if (val === 'RENYAH') {
            btnRenyah?.classList.add('active', 'pass');
        } else if (val === 'KERAS') {
            btnKeras?.classList.add('active', 'warn');
        } else if (val === 'LEMBEK') {
            btnLembek?.classList.add('active', 'warn');
        }
    };

    window.setFryerPenampakan = function (idx, val) {
        const input = document.getElementById(`fryer_penampakan_${idx}`);
        if (input) input.value = val;

        const btnBersih = document.getElementById(`btn_penampakan_bersih_${idx}`);
        const btnGelap = document.getElementById(`btn_penampakan_gelap_${idx}`);
        const btnOil = document.getElementById(`btn_penampakan_oil_${idx}`);

        btnBersih?.classList.remove('active', 'pass', 'warn', 'fail');
        btnGelap?.classList.remove('active', 'pass', 'warn', 'fail');
        btnOil?.classList.remove('active', 'pass', 'warn', 'fail');

        if (val === 'TIDAK_OILSOAKED') {
            btnBersih?.classList.add('active', 'pass');
        } else if (val === 'AGAK_GELAP') {
            btnGelap?.classList.add('active', 'warn');
        } else if (val === 'OILSOAKED') {
            btnOil?.classList.add('active', 'fail');
        }
    };

    window.onDefectFryingChanged = function (idx) {
        const b = parseFloat(document.getElementById(`defect_breakage_${idx}`)?.value || 0);
        const c = parseFloat(document.getElementById(`defect_cluster_${idx}`)?.value || 0);
        const f = parseFloat(document.getElementById(`defect_foldover_${idx}`)?.value || 0);
        const o = parseFloat(document.getElementById(`defect_oilsoaked_${idx}`)?.value || 0);
        const g = parseFloat(document.getElementById(`defect_gambos_${idx}`)?.value || 0);
        const total = b + c + f + o + g;
        const badge = document.getElementById(`defect_total_badge_${idx}`);
        if (badge) {
            badge.innerText = `Total: ${total.toFixed(1)}%`;
        }
    };

    window.onFryerRasaChanged = function (val) {
        const curTahap = document.getElementById('tahapUjiInput')?.value || 'PENGUJIAN_1';
        const bannerWarning = document.getElementById('bannerPahitWarning');
        const textWarning = document.getElementById('textPahitWarning');
        const boxDiskusi = document.getElementById('boxDiskusiAtasan');

        if (val === 'PAHIT') {
            if (bannerWarning) bannerWarning.style.display = 'block';
            if (boxDiskusi && curTahap === 'PENGUJIAN_2') boxDiskusi.style.display = 'block';

            if (textWarning) {
                textWarning.innerHTML = (curTahap === 'PENGUJIAN_2')
                    ? 'Singkong terdeteksi rasa <strong>PAHIT</strong> pada lapisan dalam bak! Sesuai instruksi Direktur, sisa muatan di atas truk <strong>DITOLAK TOTAL</strong>. Pembongkaran dihentikan segera dan lakukan koordinasi/diskusi dengan atasan (QC Supervisor).'
                    : 'Singkong beracun sianida / tidak layak konsumsi pabrik. Keputusan otomatis dialihkan ke <strong>TOLAK TOTAL</strong>. Truk tidak diizinkan bongkar ke gudang dan Admin Gudang akan menerbitkan Berita Acara Penolakan.';
            }
            window.selectDisposisi('TOLAK');
        } else {
            let hasAnyPahit = false;
            document.querySelectorAll('input[id^="fryer_rasa_"]').forEach(inp => {
                if (inp.value === 'PAHIT') hasAnyPahit = true;
            });

            if (!hasAnyPahit) {
                if (bannerWarning) bannerWarning.style.display = 'none';
                if (boxDiskusi) boxDiskusi.style.display = 'none';
                window.selectDisposisi('TERIMA');
            }
        }
    };

    window.setHalalToggle = function (field, value) {
        const hidden = document.getElementById(`input_${field}`);
        if (hidden) hidden.value = value;

        if (field === 'bebas_cemaran') {
            const btnTidak = document.getElementById('btnBebasCemaranTidak');
            const btnYa = document.getElementById('btnBebasCemaranYa');
            if (value == 1) {
                btnYa?.classList.add('active', 'ok');
                btnYa?.classList.remove('danger');
                btnTidak?.classList.remove('active', 'ok', 'danger');
            } else {
                btnTidak?.classList.add('active', 'danger');
                btnTidak?.classList.remove('ok');
                btnYa?.classList.remove('active', 'ok', 'danger');
            }
        } else if (field === 'angkut_barang_haram') {
            const btnTidak = document.getElementById('btnHaramTidak');
            const btnYa = document.getElementById('btnHaramYa');
            if (value == 0) {
                btnTidak?.classList.add('active', 'ok');
                btnTidak?.classList.remove('danger');
                btnYa?.classList.remove('active', 'ok', 'danger');
            } else {
                btnYa?.classList.add('active', 'danger');
                btnYa?.classList.remove('ok');
                btnTidak?.classList.remove('active', 'ok', 'danger');
            }
        }
    };

    window.updateTransportCard = function (radio) {
        const cardBebas = document.getElementById('cardTransportBebas');
        const cardCemar = document.getElementById('cardTransportCemar');
        const val = typeof radio === 'string' ? radio : radio.value;
        if (val === '1') {
            cardBebas?.classList.add('active');
            cardCemar?.classList.remove('active');
        } else {
            cardCemar?.classList.add('active');
            cardBebas?.classList.remove('active');
        }
    };

    window.selectDisposisi = function (val) {
        const hidden = document.getElementById('inputKesimpulanQc');
        if (hidden) hidden.value = val;

        const cardTerima = document.getElementById('cardDisposisiTerima');
        const cardTolak = document.getElementById('cardDisposisiTolak');
        const curTahap = document.getElementById('tahapUjiInput')?.value || 'PENGUJIAN_1';
        const btnSubmit = document.getElementById('btnSubmitPengujian1');
        const boxDiskusi = document.getElementById('boxDiskusiAtasan');

        if (val === 'TERIMA') {
            cardTerima?.classList.add('active', 'terima');
            cardTolak?.classList.remove('active', 'tolak');
            if (boxDiskusi) boxDiskusi.style.display = 'none';
            if (btnSubmit) {
                btnSubmit.style.background = '#059669';
                btnSubmit.innerHTML = (curTahap === 'PENGUJIAN_2')
                    ? '💾 Simpan Pengujian 2 (Lolos &amp; Bongkar Tuntas ke Gudang)'
                    : '💾 Simpan Pengujian 1 (Selesai Inspeksi &amp; Siap Bongkar Setengah Bak)';
            }
        } else {
            cardTolak?.classList.add('active', 'tolak');
            cardTerima?.classList.remove('active', 'terima');
            if (btnSubmit) {
                btnSubmit.style.background = '#dc2626';
                btnSubmit.innerHTML = (curTahap === 'PENGUJIAN_2')
                    ? '❌ Simpan Penolakan Pengujian 2 (Diskusi Atasan)'
                    : '❌ Simpan Keputusan Penolakan (Ditolak Total)';
            }
            if (boxDiskusi && curTahap === 'PENGUJIAN_2') {
                boxDiskusi.style.display = 'block';
            }
        }

        window.calculateAll();
    };

    window.updateKesimpulanCard = function (el) {
        const val = typeof el === 'string' ? el : el.value;
        window.selectDisposisi(val);
    };

    window.onKesimpulanChange = function (val) {
        window.selectDisposisi(val);
    };

    window.recalculateAllCards = function () {
        window.calculateAll();
    };

    window.setDiskusiAction = function (action) {
        const radioTerima = document.getElementById('radioTerima');
        const radioTolak = document.getElementById('radioTolak');
        const commentEl = document.querySelector('textarea[name="catatan_umum"]');

        if (action === 'TOLAK_SISA') {
            if (radioTolak) {
                radioTolak.checked = true;
                window.onKesimpulanChange('TOLAK');
            }
            if (commentEl) {
                const note = "[PENGUJIAN 2 - DISKUSI ATASAN] Hasil sampling lapisan dalam tidak memenuhi standar (cacat/pahit). Sesuai diskusi dengan atasan, sisa muatan di atas truk DITOLAK TOTAL dan truk dipulangkan.";
                if (!commentEl.value.includes('[PENGUJIAN 2 - DISKUSI ATASAN]')) {
                    commentEl.value = (commentEl.value ? commentEl.value + "\n" : '') + note;
                }
            }
            window.showQcToast('Tolak Sisa Muatan Dipilih', 'Kesimpulan dialihkan ke TOLAK TOTAL berdasarkan hasil diskusi atasan.', radioTolak, 3, true);
        } else if (action === 'PENYESUAIAN_REFRAKSI') {
            if (radioTerima) {
                radioTerima.checked = true;
                window.onKesimpulanChange('TERIMA');
            }
            const refInput = document.getElementById('refraksi_0');
            if (refInput) {
                const curRef = parseFloat(refInput.value || 0);
                refInput.value = (curRef + 5.0).toFixed(1);
                window.calculateCard(0);
            }
            if (commentEl) {
                const note = "[PENGUJIAN 2 - DISKUSI ATASAN] Disetujui atasan untuk DITERIMA BERSYARAT dengan kompensasi penambahan potongan refraksi tanah/cacat afkir.";
                if (!commentEl.value.includes('[PENGUJIAN 2 - DISKUSI ATASAN]')) {
                    commentEl.value = (commentEl.value ? commentEl.value + "\n" : '') + note;
                }
            }
            window.showQcToast('Penyesuaian Refraksi', 'Potongan refraksi ditambahkan dan dicatat pada lembar berita acara.', null, 3);
        } else if (action === 'DOWNGRADE_B') {
            const gSel = document.getElementById('grade_select_0');
            if (gSel) {
                gSel.value = 'B';
                window.onGradeChanged(0, 'B');
            }
            if (commentEl) {
                const note = "[PENGUJIAN 2 - DISKUSI ATASAN] Kualitas singkong diturunkan menjadi Grade B atas persetujuan atasan/supervisor.";
                if (!commentEl.value.includes('[PENGUJIAN 2 - DISKUSI ATASAN]')) {
                    commentEl.value = (commentEl.value ? commentEl.value + "\n" : '') + note;
                }
            }
            window.showQcToast('Downgrade ke Grade B', 'Mutu singkong diubah ke Grade B. Periksa stok gudang.', null, 3, true);
        }
    };

    // 13. Dynamic Item Card Builder untuk Singkong (Tunggal / Single Commodity)
    window.createItemCard = function (data = {}) {
        const sContainer = document.getElementById('singkongContainer');
        if (currentKomoditas === 'SINGKONG' && sContainer) {
            const existingCards = sContainer.querySelectorAll('.qc-item-card');
            if (existingCards.length >= 1) {
                // Singkong Pengujian 1 SELALU TEPAT 1 KOMODITAS: Jangan tambah kartu kedua!
                if (data.barang_id) {
                    const sel = existingCards[0].querySelector('.item-barang-select');
                    if (sel) sel.value = data.barang_id;
                }
                if (data.gross) {
                    const gInp = existingCards[0].querySelector('input[name*="[qty_timbang_gross]"]');
                    if (gInp) gInp.value = data.gross;
                }
                window.calculateCard(0);
                return;
            }
        }

        const idx = itemIndex++;
        const card = document.createElement('div');
        card.className = 'qc-item-card';
        card.id = `item_card_${idx}`;
        card.style.cssText = 'background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 1.15rem; box-shadow: 0 1px 3px rgba(0,0,0,0.03); position: relative;';

        const selectedBarangId = data.barang_id || '';
        const grossVal = data.gross || '';
        const kadarAirVal = data.kadar_air || '12.0';
        const refraksiPersenVal = data.refraksi_persen || '0.0';
        const rejectVal = data.reject || '0';
        const podtlId = data.podtl_id || '';

        let singkongMaterials = RAW_MATERIALS.filter(b => b.kategori === 'SINGKONG' || b.barang_nm.toUpperCase().includes('SINGKONG'));
        if (selectedBarangId) {
            const targetMat = RAW_MATERIALS.find(b => b.barang_id == selectedBarangId);
            if (targetMat && !singkongMaterials.some(b => b.barang_id == selectedBarangId)) {
                singkongMaterials.push(targetMat);
            }
        }
        const materialsToUse = singkongMaterials.length > 0 ? singkongMaterials : RAW_MATERIALS;

        let effectiveBarangId = selectedBarangId;
        if (!effectiveBarangId && materialsToUse.length > 0) {
            const defaultSk = materialsToUse.find(b => b.barang_cd === 'BB-SK001') || materialsToUse[0];
            effectiveBarangId = defaultSk.barang_id;
        }

        let barangOptions = '<option value="">-- Pilih Komoditas Singkong --</option>';
        materialsToUse.forEach(b => {
            const isSel = (b.barang_id == effectiveBarangId) ? 'selected' : '';
            barangOptions += `<option value="${b.barang_id}" data-nama="${b.barang_nm}" ${isSel}>${b.barang_cd} - ${b.barang_nm} (${b.satuan})</option>`;
        });

        card.innerHTML = `
            <input type="hidden" name="items[${idx}][podtl_id]" value="${podtlId}">

            <!-- 1. KONDISI FISIK RAW MATERIAL & PARAMETER DIAMETER SINGKONG -->
            <div class="qc-kondisi-card" style="margin-bottom: 1.15rem;">
                <input type="hidden" name="items[${idx}][status_raw_material]" id="status_raw_material_${idx}" value="OK">
                <div class="qc-kondisi-header">
                    <div class="qc-kondisi-title-wrap">
                        <span class="qc-kondisi-badge-num">1</span>
                        <span class="qc-kondisi-title-main">KONDISI FISIK RAW MATERIAL</span>
                    </div>
                    <div class="qc-kondisi-toggle">
                        <button type="button" class="qc-kondisi-toggle-btn active ok" id="btn_kondisi_ok_${idx}" onclick="setKondisiRawMaterial(${idx}, 'OK')">
                            OK
                        </button>
                        <button type="button" class="qc-kondisi-toggle-btn" id="btn_kondisi_tdk_${idx}" onclick="setKondisiRawMaterial(${idx}, 'TDK_STD')">
                            TDK STD
                        </button>
                    </div>
                </div>

                <div class="qc-checklist-section-title">Checklist Karakteristik Fisik Batang Singkong:</div>

                <div class="qc-check-grid">
                    <!-- Kiri: Segar -->
                    <label class="qc-check-pill" id="pill_segar_${idx}">
                        <input type="checkbox" name="items[${idx}][kondisi_segar]" value="1" checked onchange="onSingkongCheckChanged(${idx}, this, 'pill_segar_${idx}')">
                        <span>🍃 SEGAR</span>
                    </label>

                    <!-- Kanan: Busuk -->
                    <label class="qc-check-pill is-danger" id="pill_busuk_${idx}">
                        <input type="checkbox" name="items[${idx}][kondisi_busuk]" value="1" onchange="onSingkongCheckChanged(${idx}, this, 'pill_busuk_${idx}', true)">
                        <span>☠️ BUSUK</span>
                    </label>

                    <!-- Kiri: Layu -->
                    <label class="qc-check-pill" id="pill_layu_${idx}">
                        <input type="checkbox" name="items[${idx}][kondisi_layu]" value="1" onchange="onSingkongCheckChanged(${idx}, this, 'pill_layu_${idx}')">
                        <span>💨 LAYU</span>
                    </label>

                    <!-- Kanan: Berjamur -->
                    <label class="qc-check-pill is-danger" id="pill_berjamur_${idx}">
                        <input type="checkbox" name="items[${idx}][kondisi_berjamur]" value="1" onchange="onSingkongCheckChanged(${idx}, this, 'pill_berjamur_${idx}', true)">
                        <span>🧫 BERJAMUR</span>
                    </label>

                    <!-- Kiri: Basah -->
                    <label class="qc-check-pill" id="pill_basah_${idx}">
                        <input type="checkbox" name="items[${idx}][kondisi_basah]" value="1" onchange="onSingkongCheckChanged(${idx}, this, 'pill_basah_${idx}')">
                        <span>💧 BASAH</span>
                    </label>

                    <!-- Kanan: Lembek -->
                    <label class="qc-check-pill is-danger" id="pill_lembek_${idx}">
                        <input type="checkbox" name="items[${idx}][kondisi_lembek]" value="1" onchange="onSingkongCheckChanged(${idx}, this, 'pill_lembek_${idx}', true)">
                        <span>✋ LEMBEK</span>
                    </label>

                    <!-- Full Width: Kulit Terkelupas (Ringan) -->
                    <label class="qc-check-pill full-width" id="pill_terkelupas_${idx}">
                        <div style="display: flex; align-items: center; gap: 0.55rem;">
                            <input type="checkbox" name="items[${idx}][kondisi_terkelupas]" value="1" checked onchange="onSingkongCheckChanged(${idx}, this, 'pill_terkelupas_${idx}')">
                            <span>KULIT TERKELUPAS (Ringan)</span>
                        </div>
                        <span class="qc-check-subtext">Normal bongkar</span>
                    </label>
                </div>

                <!-- Uji Parameter Diameter Singkong Box -->
                <div class="qc-diameter-box">
                    <div class="qc-diameter-header">
                        <div class="qc-diameter-title">Uji Parameter Diameter Singkong:</div>
                        <div class="qc-diameter-sample" id="diameter_sample_label_${idx}">Sample 50 kg</div>
                    </div>
                    <table class="qc-diameter-table">
                        <thead>
                            <tr>
                                <th>PARAMETER</th>
                                <th class="col-center">STANDAR</th>
                                <th class="col-right">HASIL UJI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="qc-diameter-name">1. Diameter &lt; 4 cm</div>
                                    <div class="qc-diameter-desc">Ukuran terlalu kecil</div>
                                </td>
                                <td class="col-center">
                                    <span class="qc-diameter-std">Max 5.0%</span>
                                </td>
                                <td class="col-right">
                                    <div class="qc-diameter-input-wrap">
                                        <input type="number" step="0.1" min="0" max="100" name="items[${idx}][diameter_kurang_4cm_persen]" id="diameter_kecil_${idx}" value="2.8" class="qc-diameter-input" oninput="onDiameterChanged(${idx})">
                                        <span class="qc-diameter-unit">%</span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="qc-diameter-name">2. Diameter &ge; 4 cm</div>
                                    <div class="qc-diameter-desc">Ukuran standar pabrik</div>
                                </td>
                                <td class="col-center">
                                    <span class="qc-diameter-std">Min 95.0%</span>
                                </td>
                                <td class="col-right">
                                    <div class="qc-diameter-input-wrap">
                                        <input type="number" step="0.1" min="0" max="100" name="items[${idx}][diameter_lebih_4cm_persen]" id="diameter_standar_${idx}" value="97.2" class="qc-diameter-input" oninput="onDiameterChanged(${idx})" style="font-weight: 800; color: #15803d;">
                                        <span class="qc-diameter-unit">%</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="qc-diameter-footer" id="diameter_status_footer_${idx}">
                        <span id="diameter_status_text_${idx}">✓ Lolos Uji Diameter</span>
                        <span id="diameter_total_text_${idx}">Total: 100% Valid</span>
                    </div>
                </div>
            </div>

            <!-- 2. PENENTUAN GRADE MUTU SINGKONG & BAHAN BAKU -->
            <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 1rem; margin-bottom: 1.15rem;">
                <div class="qc-kondisi-header" style="margin-bottom: 0.85rem;">
                    <div class="qc-kondisi-title-wrap">
                        <span class="qc-kondisi-badge-num" style="background: #fef3c7; color: #b45309; border: 1px solid #fde68a;">2</span>
                        <span class="qc-kondisi-title-main">PENENTUAN GRADE MUTU SINGKONG</span>
                    </div>
                    <span style="font-size: 0.72rem; color: #64748b; font-weight: 700;">Berdasarkan Hasil Uji Fisik &amp; Diameter</span>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.85rem; margin-bottom: 0.5rem;">
                    <div>
                        <label class="form-label" style="font-size: 0.8rem; font-weight: 800; color: #0f172a; margin-bottom: 0.35rem; display: block;">
                            Nama Bahan Baku <span style="color:red;">*</span>
                        </label>
                        <select name="items[${idx}][barang_id]" id="barang_select_${idx}" class="form-control item-barang-select" required onchange="onItemBarangChanged(${idx})" style="font-weight: 700;">
                            ${barangOptions}
                        </select>
                    </div>
                    <div>
                        <label class="form-label" style="font-size: 0.8rem; font-weight: 800; color: #0f172a; margin-bottom: 0.35rem; display: block;">
                            Grade Mutu Singkong <span style="color:red;">*</span>
                        </label>
                        <input type="hidden" name="items[${idx}][grade_cd]" id="grade_input_${idx}" value="A">
                        <div class="qc-grade-pills">
                            <button type="button" class="qc-grade-btn grade-a active" id="btn_grade_a_${idx}" onclick="selectItemGrade(${idx}, 'A')">
                                <span class="qc-grade-dot green"></span>
                                <div class="qc-grade-text">
                                    <div class="qc-grade-title">Grade A</div>
                                    <div class="qc-grade-sub">Super / Renyah</div>
                                </div>
                            </button>
                            <button type="button" class="qc-grade-btn grade-b" id="btn_grade_b_${idx}" onclick="selectItemGrade(${idx}, 'B')">
                                <span class="qc-grade-dot yellow"></span>
                                <div class="qc-grade-text">
                                    <div class="qc-grade-title">Grade B</div>
                                    <div class="qc-grade-sub">Standar / Campur</div>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>

                <div id="gradeB_warning_${idx}" style="display: none; background: #fffbeb; border: 1.5px solid #fcd34d; border-radius: 8px; padding: 0.75rem 0.85rem; margin-top: 0.75rem; color: #92400e; font-size: 0.8rem;">
                    <div style="font-weight: 800; display: flex; align-items: center; justify-content: space-between; gap: 0.4rem; margin-bottom: 0.35rem; flex-wrap: wrap;">
                        <span style="display: flex; align-items: center; gap: 0.35rem;">
                            <span>⚠️</span> <span>PERINGATAN OPERASIONAL: SINGKONG GRADE B TERPILIH</span>
                        </span>
                        <span style="font-size: 0.7rem; font-weight: 700; background: #fef3c7; color: #b45309; padding: 2px 7px; border-radius: 4px;">
                            Stok Gudang: ${STOK_GRADE_B.toLocaleString('id-ID')} KG
                        </span>
                    </div>
                    <div style="line-height: 1.4;">
                        Stok Grade B di gudang saat ini tercatat <strong>${STOK_GRADE_B.toLocaleString('id-ID')} KG</strong>. 
                        Jika stok Grade B di gudang sudah banyak/menumpuk, instruksi atasan adalah <strong>MENOLAK KEDATANGAN INI</strong> atau meminta konfirmasi QC Supervisor terlebih dahulu sebelum dibongkar.
                    </div>
                    <div style="margin-top: 0.5rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
                        <button type="button" onclick="quickRejectGradeB(${idx})" style="background: #dc2626; color: #ffffff; border: none; font-size: 0.75rem; font-weight: 800; padding: 0.35rem 0.65rem; border-radius: 6px; cursor: pointer;">
                            🚨 Tolak Truk Ini (Stok Grade B Penuh)
                        </button>
                        <button type="button" onclick="selectItemGrade(${idx}, 'A')" style="background: #ffffff; color: #15803d; border: 1px solid #86efac; font-size: 0.75rem; font-weight: 700; padding: 0.35rem 0.65rem; border-radius: 6px; cursor: pointer;">
                            Kembalikan ke Grade A
                        </button>
                    </div>
                </div>
            </div>

            <!-- 3. HASIL TIMBANGAN & REFRAKSI -->
            <div class="qc-timbangan-card">
                <div class="qc-kondisi-header">
                    <div class="qc-kondisi-title-wrap">
                        <span class="qc-kondisi-badge-num" style="background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd;">3</span>
                        <span class="qc-kondisi-title-main">HASIL TIMBANGAN &amp; REFRAKSI</span>
                    </div>
                    <div class="satuan-toggle-pills" style="margin: 0;">
                        <div class="btn-satuan-pill active" style="padding: 0.25rem 0.75rem; font-size: 0.725rem; cursor: default;">
                            <span class="pill-icon">⚖️</span>
                            <span class="pill-text">Satuan KG</span>
                        </div>
                    </div>
                </div>

                <!-- BOX PANEL UTAMA TIMBANGAN & REFRAKSI -->
                <div class="qc-diameter-box" style="margin-top: 0; border: 1.5px solid #cbd5e1; border-radius: 10px;">
                    <div class="qc-diameter-header" style="background: #f8fafc; border-bottom: 1.5px solid #e2e8f0; padding: 0.75rem 1rem;">
                        <div style="display: flex; align-items: center; gap: 0.45rem;">
                            <span style="font-size: 0.95rem;">⚖️</span>
                            <span class="qc-diameter-title" style="font-size: 0.825rem; font-weight: 800; color: #0f172a;">Input Timbangan &amp; Analisa Sortir Lapangan</span>
                        </div>
                        <span class="qc-diameter-sample" style="color: #0284c7; background: #e0f2fe; padding: 2px 8px; border-radius: 6px; font-weight: 800; font-size: 0.7rem;">
                            Formula Otomatis
                        </span>
                    </div>

                    <div style="padding: 0.85rem 1rem; display: flex; flex-direction: column; gap: 0.75rem;">
                        <!-- BARIS 1: Timbangan Kotor (Gross) & Kadar Air -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                            <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 0.65rem 0.75rem; display: flex; flex-direction: column; justify-content: space-between;">
                                <div>
                                    <label class="form-label" style="font-size: 0.775rem; font-weight: 800; color: #0f172a; margin-bottom: 0.35rem; display: block;">
                                        Timbangan Kotor (Gross) <span style="color:#ef4444;">*</span>
                                    </label>
                                    <div style="position: relative;">
                                        <input type="number" step="0.01" min="0.01" name="items[${idx}][qty_timbang_gross]" id="gross_${idx}" value="${grossVal}" class="form-control" placeholder="0.00" required oninput="calculateCard(${idx})" style="font-weight: 900; font-size: 1.15rem; padding-right: 38px; color: #0f172a; background: #ffffff;">
                                        <span style="position: absolute; right: 10px; top: 9px; font-size: 0.75rem; color: #64748b; font-weight: 800;">KG</span>
                                    </div>
                                </div>
                                <div style="font-size: 0.68rem; color: #64748b; margin-top: 0.35rem;">Muatan truk saat timbang pabrik</div>
                            </div>

                            <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 0.65rem 0.75rem; display: flex; flex-direction: column; justify-content: space-between;">
                                <div>
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                                        <label class="form-label" style="font-size: 0.775rem; font-weight: 800; color: #0f172a; margin: 0;">
                                            Kadar Air (%)
                                        </label>
                                        <span style="font-size: 0.68rem; color: #0284c7; background: #e0f2fe; padding: 1px 6px; border-radius: 4px; font-weight: 700;">Std &le;14%</span>
                                    </div>
                                    <div style="position: relative;">
                                        <input type="number" step="0.1" min="0" max="100" name="items[${idx}][kadar_air_persen]" id="kadar_air_${idx}" value="${kadarAirVal}" class="form-control" placeholder="12.0" oninput="calculateCard(${idx})" style="font-weight: 900; font-size: 1.15rem; padding-right: 30px; color: #0284c7; background: #ffffff;">
                                        <span style="position: absolute; right: 10px; top: 9px; font-size: 0.75rem; color: #64748b; font-weight: 800;">%</span>
                                    </div>
                                </div>
                                <div style="font-size: 0.68rem; color: #64748b; margin-top: 0.35rem;">Hasil analisa oven / moisture lab</div>
                            </div>
                        </div>

                        <!-- BARIS 2: Refraksi Tanah & Afkir Reject -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                            <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 0.65rem 0.75rem; display: flex; flex-direction: column; justify-content: space-between;">
                                <div>
                                    <label class="form-label" style="font-size: 0.775rem; font-weight: 800; color: #0f172a; margin-bottom: 0.35rem; display: block;">
                                        Refraksi Tanah / Kotoran
                                    </label>
                                    <div style="position: relative;">
                                        <input type="number" step="0.05" min="0" max="100" name="items[${idx}][refraksi_persen]" id="refraksi_${idx}" value="${refraksiPersenVal}" class="form-control" placeholder="0.0" oninput="calculateCard(${idx})" style="font-weight: 900; font-size: 1.15rem; padding-right: 30px; color: #b45309; background: #ffffff;">
                                        <span style="position: absolute; right: 10px; top: 9px; font-size: 0.75rem; color: #64748b; font-weight: 800;">%</span>
                                    </div>
                                </div>
                                <div style="display: flex; justify-content: space-between; font-size: 0.68rem; color: #64748b; margin-top: 0.35rem;">
                                    <span>Potongan:</span>
                                    <strong id="label_qty_refraksi_${idx}" style="color: #b45309; font-weight: 800;">0.00 KG</strong>
                                </div>
                            </div>

                            <div style="background: #fef2f2; border: 1.5px solid #fecaca; border-radius: 8px; padding: 0.65rem 0.75rem; display: flex; flex-direction: column; justify-content: space-between;">
                                <div>
                                    <label class="form-label" style="font-size: 0.775rem; font-weight: 800; color: #dc2626; margin-bottom: 0.35rem; display: block;">
                                        Afkir / Busuk (Reject)
                                    </label>
                                    <div style="position: relative;">
                                        <input type="number" step="0.01" min="0" name="items[${idx}][qty_reject]" id="reject_${idx}" value="${rejectVal}" class="form-control" placeholder="0.00" oninput="calculateCard(${idx})" style="font-weight: 900; font-size: 1.15rem; padding-right: 38px; color: #dc2626; border-color: #fca5a5; background: #ffffff;">
                                        <span style="position: absolute; right: 10px; top: 9px; font-size: 0.75rem; color: #dc2626; font-weight: 800;">KG</span>
                                    </div>
                                </div>
                                <div style="font-size: 0.68rem; color: #ef4444; margin-top: 0.35rem;">Singkong afkir dibuang / tolak</div>
                            </div>
                        </div>
                    </div>

                    <!-- FOOTER STATUS BANNER: RINGKASAN NETTO GUDANG -->
                    <div class="qc-diameter-footer" style="padding: 0.75rem 1rem; border-top: 1.5px solid #bbf7d0; background: #f0fdf4; display: flex; justify-content: space-between; align-items: center; gap: 0.5rem;">
                        <div style="display: flex; flex-direction: column; gap: 2px;">
                            <div style="font-size: 0.75rem; font-weight: 800; color: #166534; text-transform: uppercase; letter-spacing: 0.04em; display: flex; align-items: center; gap: 0.35rem;">
                                <span>✓</span> <span>Netto Lolos Diterima Pabrik</span>
                            </div>
                            <div style="font-size: 0.68rem; color: #475569;">
                                Formula: Gross - Refraksi Tanah - Afkir
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <strong id="label_netto_${idx}" style="color: #15803d; font-size: 1.35rem; font-weight: 900; letter-spacing: -0.02em; display: block; line-height: 1.1;">0.00 KG</strong>
                            <span style="font-size: 0.68rem; color: #166534; font-weight: 700;">Masuk Stok Gudang</span>
                        </div>
                    </div>
                </div>
            </div>
        `;

        const container = document.getElementById('singkongContainer');
        if (container) container.appendChild(card);
        renderFryerParamsForCard(idx);
        window.calculateCard(idx);

        if (effectiveBarangId && currentKomoditas === 'SINGKONG') {
            const chosenMat = materialsToUse.find(b => b.barang_id == effectiveBarangId) || RAW_MATERIALS.find(b => b.barang_id == effectiveBarangId);
            const namaEl = document.getElementById('namaJenisInput');
            if (namaEl && chosenMat && (idx === 0 || !namaEl.value || namaEl.value === 'Singkong Basah Curah')) {
                namaEl.value = chosenMat.barang_nm;
            }
        }
    };

    window.selectItemGrade = function (idx, grade) {
        const inputEl = document.getElementById(`grade_input_${idx}`);
        if (inputEl) inputEl.value = grade;

        const btnA = document.getElementById(`btn_grade_a_${idx}`);
        const btnB = document.getElementById(`btn_grade_b_${idx}`);
        if (btnA && btnB) {
            if (grade === 'A') {
                btnA.classList.add('active');
                btnB.classList.remove('active');
            } else {
                btnA.classList.remove('active');
                btnB.classList.add('active');
            }
        }
        window.onGradeChanged(idx, grade);
    };

    window.setKondisiRawMaterial = function (idx, status) {
        const inputEl = document.getElementById(`status_raw_material_${idx}`);
        if (inputEl) inputEl.value = status;

        const btnOk = document.getElementById(`btn_kondisi_ok_${idx}`);
        const btnTdk = document.getElementById(`btn_kondisi_tdk_${idx}`);
        if (btnOk && btnTdk) {
            if (status === 'OK') {
                btnOk.className = 'qc-kondisi-toggle-btn active ok';
                btnTdk.className = 'qc-kondisi-toggle-btn';
            } else {
                btnOk.className = 'qc-kondisi-toggle-btn';
                btnTdk.className = 'qc-kondisi-toggle-btn active tdk-std';
            }
        }
    };

    window.onSingkongCheckChanged = function (idx, el, pillId, isDanger) {
        const pill = document.getElementById(pillId);
        if (pill) {
            if (el.checked) {
                if (isDanger) {
                    pill.classList.add('active-danger');
                } else {
                    pill.classList.add('active-ok');
                }
            } else {
                pill.classList.remove('active-ok', 'active-danger');
            }
        }

        if (isDanger && el.checked) {
            window.setKondisiRawMaterial(idx, 'TDK_STD');
        }
    };

    window.onDiameterChanged = function (idx) {
        const kecilEl = document.getElementById(`diameter_kecil_${idx}`);
        const standarEl = document.getElementById(`diameter_standar_${idx}`);
        const footerEl = document.getElementById(`diameter_status_footer_${idx}`);
        const statusTextEl = document.getElementById(`diameter_status_text_${idx}`);
        const totalTextEl = document.getElementById(`diameter_total_text_${idx}`);

        if (!kecilEl || !standarEl) return;

        const valKecil = parseFloat(kecilEl.value) || 0;
        const valStandar = parseFloat(standarEl.value) || 0;
        const total = valKecil + valStandar;

        const isStandardOk = (valKecil <= 5.0) && (valStandar >= 95.0);
        const isTotalValid = Math.abs(total - 100) < 0.2;

        if (footerEl) {
            if (isStandardOk && isTotalValid) {
                footerEl.className = 'qc-diameter-footer';
                if (statusTextEl) statusTextEl.textContent = '✓ Lolos Uji Diameter';
                if (totalTextEl) totalTextEl.textContent = `Total: ${total.toFixed(1).replace('.0', '')}% Valid`;
            } else {
                footerEl.className = 'qc-diameter-footer is-warning';
                let reason = '⚠️ Menyimpang dari Standar';
                if (valKecil > 5.0) reason = `⚠️ Diameter < 4cm Tinggi (${valKecil}%)`;
                else if (valStandar < 95.0) reason = `⚠️ Diameter ≥ 4cm Rendah (${valStandar}%)`;
                else if (!isTotalValid) reason = `⚠️ Total Persentase (${total.toFixed(1)}%) ≠ 100%`;

                if (statusTextEl) statusTextEl.textContent = reason;
                if (totalTextEl) totalTextEl.textContent = `Total: ${total.toFixed(1)}%`;
            }
        }
    };

    window.onGradeChanged = function (idx, val) {
        const warnEl = document.getElementById(`gradeB_warning_${idx}`);
        if (warnEl) {
            warnEl.style.display = (val === 'B') ? 'block' : 'none';
        }
        if (val === 'B' && STOK_GRADE_B > 1000) {
            window.showQcToast('Stok Grade B Tinggi', `Stok Grade B di gudang saat ini ${STOK_GRADE_B.toLocaleString('id-ID')} KG. Periksa kapasitas gudang sebelum menerima.`, warnEl, 2, true);
        }
    };

    window.quickRejectGradeB = function (idx) {
        const radioTolak = document.getElementById('radioTolak');
        if (radioTolak) {
            radioTolak.checked = true;
            window.onKesimpulanChange('TOLAK');
        }
        const commentEl = document.querySelector('textarea[name="catatan_umum"]');
        if (commentEl) {
            const rejectMsg = `[TOLAK TOTAL] Kedatangan Singkong Grade B ditolak karena stok Grade B di gudang sudah penuh/menumpuk (${STOK_GRADE_B.toLocaleString('id-ID')} KG).`;
            if (!commentEl.value.includes('[TOLAK TOTAL]')) {
                commentEl.value = (commentEl.value ? commentEl.value + "\n" : '') + rejectMsg;
            }
        }
        window.showQcToast('Keputusan Dialihkan ke Tolak', 'Status QC diubah menjadi TOLAK TOTAL karena kapasitas Grade B penuh.', null, 3, true);
        window.switchQcTab(3);
    };

    function renderFryerParamsForCard(idx) {
        let container = document.getElementById('fryerParamsContainer');
        if (!container) return;
        let block = document.getElementById(`fryer_block_${idx}`);
        if (!block) {
            block = document.createElement('div');
            block.id = `fryer_block_${idx}`;
            block.className = 'fryer-param-card';
            block.style.cssText = 'display: flex; flex-direction: column; gap: 0.85rem;';
            container.appendChild(block);
        }

        const sel = document.getElementById(`barang_select_${idx}`);
        const barangNm = sel && sel.selectedOptions[0] ? sel.selectedOptions[0].text : `Item Singkong`;

        block.innerHTML = `
            <!-- 1. PARAMETER RASA (Standar: Tidak Pahit) -->
            <div class="qc-fryer-group">
                <input type="hidden" name="items[${idx}][fryer_rasa]" id="fryer_rasa_${idx}" value="TIDAK_PAHIT">
                <div class="qc-fryer-group-header">
                    <div class="qc-fryer-label-wrap">
                        <span class="qc-fryer-dot"></span>
                        <span>RASA</span>
                    </div>
                    <span class="qc-fryer-std-badge">Standar: Tidak Pahit</span>
                </div>
                <div class="qc-fryer-pills">
                    <button type="button" class="qc-fryer-btn active pass" id="btn_rasa_tidak_${idx}" onclick="setFryerRasa(${idx}, 'TIDAK_PAHIT')">
                        <span class="qc-fryer-btn-title">Tidak Pahit</span>
                        <span class="qc-fryer-btn-sub">PASS</span>
                    </button>
                    <button type="button" class="qc-fryer-btn" id="btn_rasa_agak_${idx}" onclick="setFryerRasa(${idx}, 'AGAK_PAHIT')">
                        <span class="qc-fryer-btn-title">Agak Pahit</span>
                        <span class="qc-fryer-btn-sub">Warning</span>
                    </button>
                    <button type="button" class="qc-fryer-btn" id="btn_rasa_pahit_${idx}" onclick="setFryerRasa(${idx}, 'PAHIT')">
                        <span class="qc-fryer-btn-title">Pahit</span>
                        <span class="qc-fryer-btn-sub" style="color: #dc2626;">Fail</span>
                    </button>
                </div>
            </div>

            <!-- 2. PARAMETER TEKSTUR (Standar: Renyah) -->
            <div class="qc-fryer-group">
                <input type="hidden" name="items[${idx}][fryer_tekstur]" id="fryer_tekstur_${idx}" value="RENYAH">
                <div class="qc-fryer-group-header">
                    <div class="qc-fryer-label-wrap">
                        <span class="qc-fryer-dot"></span>
                        <span>TEKSTUR</span>
                    </div>
                    <span class="qc-fryer-std-badge">Standar: Renyah</span>
                </div>
                <div class="qc-fryer-pills">
                    <button type="button" class="qc-fryer-btn active pass" id="btn_tekstur_renyah_${idx}" onclick="setFryerTekstur(${idx}, 'RENYAH')">
                        <span class="qc-fryer-btn-title">Renyah</span>
                        <span class="qc-fryer-btn-sub">PASS</span>
                    </button>
                    <button type="button" class="qc-fryer-btn" id="btn_tekstur_keras_${idx}" onclick="setFryerTekstur(${idx}, 'KERAS')">
                        <span class="qc-fryer-btn-title">Keras</span>
                        <span class="qc-fryer-btn-sub">Keras/Ulet</span>
                    </button>
                    <button type="button" class="qc-fryer-btn" id="btn_tekstur_lembek_${idx}" onclick="setFryerTekstur(${idx}, 'LEMBEK')">
                        <span class="qc-fryer-btn-title">Lembek</span>
                        <span class="qc-fryer-btn-sub">Bantat</span>
                    </button>
                </div>
            </div>

            <!-- 3. PARAMETER PENAMPAKAN (Standar: Tidak Oilsoaked) -->
            <div class="qc-fryer-group">
                <input type="hidden" name="items[${idx}][fryer_penampakan]" id="fryer_penampakan_${idx}" value="TIDAK_OILSOAKED">
                <div class="qc-fryer-group-header">
                    <div class="qc-fryer-label-wrap">
                        <span class="qc-fryer-dot"></span>
                        <span>PENAMPAKAN</span>
                    </div>
                    <span class="qc-fryer-std-badge">Standar: Tidak Oilsoaked</span>
                </div>
                <div class="qc-fryer-pills">
                    <button type="button" class="qc-fryer-btn active pass" id="btn_penampakan_bersih_${idx}" onclick="setFryerPenampakan(${idx}, 'TIDAK_OILSOAKED')">
                        <span class="qc-fryer-btn-title">Tidak Oilsoaked</span>
                        <span class="qc-fryer-btn-sub">Cerah/Kering</span>
                    </button>
                    <button type="button" class="qc-fryer-btn" id="btn_penampakan_gelap_${idx}" onclick="setFryerPenampakan(${idx}, 'AGAK_GELAP')">
                        <span class="qc-fryer-btn-title">Agak Gelap</span>
                        <span class="qc-fryer-btn-sub">Kecokelatan</span>
                    </button>
                    <button type="button" class="qc-fryer-btn" id="btn_penampakan_oil_${idx}" onclick="setFryerPenampakan(${idx}, 'OILSOAKED')">
                        <span class="qc-fryer-btn-title">Oilsoaked</span>
                        <span class="qc-fryer-btn-sub" style="color: #dc2626;">Menyerap Minyak</span>
                    </button>
                </div>
            </div>

            <!-- 4. DEFECT FRYING ANALYSIS (%) -->
            <div class="qc-defect-box">
                <div class="qc-defect-header">
                    <div>
                        <div class="qc-defect-title">DEFECT FRYING ANALYSIS (%)</div>
                        <div class="qc-defect-sub">Persentase cacat penggorengan per sampel</div>
                    </div>
                    <span class="qc-defect-total-badge" id="defect_total_badge_${idx}">Total: 4.8%</span>
                </div>

                <div class="qc-defect-grid">
                    <div class="qc-defect-item">
                        <label class="qc-defect-label">Breakage (Remuk)</label>
                        <div class="input-suffix-wrap">
                            <input type="number" step="0.1" min="0" max="100" name="items[${idx}][defect_breakage_persen]" id="defect_breakage_${idx}" value="1.2" class="form-control" oninput="onDefectFryingChanged(${idx})" style="font-weight: 800; text-align: right;">
                            <span class="input-suffix-text">%</span>
                        </div>
                    </div>
                    <div class="qc-defect-item">
                        <label class="qc-defect-label">Cluster (Nempel)</label>
                        <div class="input-suffix-wrap">
                            <input type="number" step="0.1" min="0" max="100" name="items[${idx}][defect_cluster_persen]" id="defect_cluster_${idx}" value="0.8" class="form-control" oninput="onDefectFryingChanged(${idx})" style="font-weight: 800; text-align: right;">
                            <span class="input-suffix-text">%</span>
                        </div>
                    </div>
                    <div class="qc-defect-item">
                        <label class="qc-defect-label">Foldover (Terlipat)</label>
                        <div class="input-suffix-wrap">
                            <input type="number" step="0.1" min="0" max="100" name="items[${idx}][defect_foldover_persen]" id="defect_foldover_${idx}" value="1.5" class="form-control" oninput="onDefectFryingChanged(${idx})" style="font-weight: 800; text-align: right;">
                            <span class="input-suffix-text">%</span>
                        </div>
                    </div>
                    <div class="qc-defect-item">
                        <label class="qc-defect-label">Oilsoaked - Polos</label>
                        <div class="input-suffix-wrap">
                            <input type="number" step="0.1" min="0" max="100" name="items[${idx}][defect_oilsoaked_persen]" id="defect_oilsoaked_${idx}" value="0.9" class="form-control" oninput="onDefectFryingChanged(${idx})" style="font-weight: 800; text-align: right;">
                            <span class="input-suffix-text">%</span>
                        </div>
                    </div>
                </div>

                <div class="qc-defect-full">
                    <div>
                        <div class="qc-defect-label">Gambos / Gabus (%)</div>
                        <div class="qc-defect-sub">Tekstur spon tidak padat</div>
                    </div>
                    <div class="input-suffix-wrap" style="max-width: 140px;">
                        <input type="number" step="0.1" min="0" max="100" name="items[${idx}][defect_gambos_persen]" id="defect_gambos_${idx}" value="0.4" class="form-control" oninput="onDefectFryingChanged(${idx})" style="font-weight: 800; text-align: right;">
                        <span class="input-suffix-text">%</span>
                    </div>
                </div>
            </div>
        `;

        window.onDefectFryingChanged(idx);
    }

    window.onItemBarangChanged = function (idx) {
        renderFryerParamsForCard(idx);
        window.calculateCard(idx);

        const sel = document.getElementById(`barang_select_${idx}`);
        if (sel) {
            const opt = sel.selectedOptions[0];
            const matNama = opt?.getAttribute('data-nama') || (opt?.text.split(' - ')[1]?.split(' (')[0]);
            const namaEl = document.getElementById('namaJenisInput');
            if (namaEl && matNama) {
                namaEl.value = matNama.trim();
            }
        }
    };

    window.removeItemCard = function (idx) {
        const card = document.getElementById(`item_card_${idx}`);
        if (card) {
            card.remove();
            const block = document.getElementById(`fryer_block_${idx}`);
            if (block) block.remove();
            window.calculateAll();
        }
    };

    window.addItemRow = function () {
        window.createItemCard();
    };

    window.calculateCard = function (idx) {
        const grossInput = document.getElementById(`gross_${idx}`);
        const refraksiInput = document.getElementById(`refraksi_${idx}`);
        const rejectInput = document.getElementById(`reject_${idx}`);
        const labelRefraksi = document.getElementById(`label_qty_refraksi_${idx}`);
        const labelNetto = document.getElementById(`label_netto_${idx}`);

        if (!grossInput) return;

        const gross = parseFloat(grossInput.value) || 0;
        const refraksiPersen = parseFloat(refraksiInput ? refraksiInput.value : 0) || 0;
        const reject = parseFloat(rejectInput ? rejectInput.value : 0) || 0;

        const qtyRefraksi = (gross * (refraksiPersen / 100));
        const netto = Math.max(0, gross - qtyRefraksi - reject);

        if (labelRefraksi) labelRefraksi.innerText = `${qtyRefraksi.toFixed(2)} KG`;
        if (labelNetto) labelNetto.innerText = `${netto.toFixed(2)} KG`;

        if (currentKomoditas === 'SINGKONG' && idx === 0) {
            const inputPabrik = document.getElementById('inputJumlahPabrik');
            if (inputPabrik && gross > 0 && document.activeElement === grossInput) {
                inputPabrik.value = gross;
            }
        }

        window.calculateAll();
    };

    window.calculateAll = function () {
        let totalGross = 0;
        let totalRefraksi = 0;
        let totalReject = 0;
        let totalNetto = 0;

        document.querySelectorAll('.qc-item-card').forEach(card => {
            const grossInput = card.querySelector('input[id^="gross_"]');
            const refraksiInput = card.querySelector('input[id^="refraksi_"]');
            const rejectInput = card.querySelector('input[id^="reject_"]');

            if (grossInput) {
                const gross = parseFloat(grossInput.value) || 0;
                const refraksiPersen = parseFloat(refraksiInput ? refraksiInput.value : 0) || 0;
                const reject = parseFloat(rejectInput ? rejectInput.value : 0) || 0;

                const qtyRefraksi = (gross * (refraksiPersen / 100));
                const netto = Math.max(0, gross - qtyRefraksi - reject);

                totalGross += gross;
                totalRefraksi += qtyRefraksi;
                totalReject += reject;
                totalNetto += netto;
            }
        });

        const kesimpulanVal = document.getElementById('inputKesimpulanQc')?.value || document.querySelector('input[name="kesimpulan_qc"]:checked')?.value || 'TERIMA';
        const isTolakTotal = (kesimpulanVal === 'TOLAK');

        const sumGross = document.getElementById('summaryGross');
        const sumRef = document.getElementById('summaryRefraksi');
        const sumRej = document.getElementById('summaryReject');
        const sumNet = document.getElementById('summaryNetto');

        const inputQtyTerima = document.getElementById('inputQtyTerima');
        const inputQtyTolak = document.getElementById('inputQtyTolak');

        if (isTolakTotal) {
            if (sumGross) sumGross.innerText = `${totalGross.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} KG`;
            if (sumRef) sumRef.innerText = `- 0.00 KG`;
            if (sumRej) sumRej.innerText = `- ${totalGross.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} KG`;
            if (sumNet) {
                sumNet.innerText = `0.00 KG (DITOLAK)`;
                sumNet.style.color = '#dc2626';
            }
            if (inputQtyTerima) inputQtyTerima.value = 0;
            if (inputQtyTolak) inputQtyTolak.value = Math.round(totalGross);
        } else {
            if (sumGross) sumGross.innerText = `${totalGross.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} KG`;
            if (sumRef) sumRef.innerText = `- ${totalRefraksi.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} KG`;
            if (sumRej) sumRej.innerText = `- ${totalReject.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} KG`;
            if (sumNet) {
                sumNet.innerText = `${totalNetto.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} KG`;
                sumNet.style.color = '#15803d';
            }
            if (inputQtyTerima) inputQtyTerima.value = Math.round(totalNetto);
            if (inputQtyTolak) inputQtyTolak.value = Math.round(totalReject);
        }

        const subcardNetto = document.getElementById('labelSubcardNetto');
        if (subcardNetto) {
            subcardNetto.innerText = `Netto: ${totalNetto.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} KG`;
        }
    };

    window.setCurrentDateTime = function () {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const formatted = `${year}-${month}-${day}T${hours}:${minutes}`;

        const inputTgl = document.getElementById('inputTglDatang');
        if (inputTgl) inputTgl.value = formatted;
        const textBar = document.getElementById('textInspectionBarTime');
        if (textBar) {
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            textBar.innerText = `${day} ${months[now.getMonth()]} ${year} • ${hours}:${minutes} WIB`;
        }
    };

    window.onSingkongBarangChanged = function (select) {
        if (!select) return;
        const opt = select.options[select.selectedIndex];
        const barangId = select.value;
        const barangNm = opt ? opt.getAttribute('data-nama') : '';
        
        const bSelect0 = document.getElementById('barang_select_0');
        if (bSelect0) {
            bSelect0.value = barangId;
        }
        
        const namaJenis = document.getElementById('namaJenisInput');
        if (namaJenis && (!namaJenis.value || namaJenis.value === 'Singkong Basah Curah')) {
            namaJenis.value = barangNm;
        }
    };

    // 14. Pemilihan PO & Auto-fill Komoditas
    window.onPoSelected = function (select) {
        const poId = select.value;
        if (!poId || !PO_LIST[poId]) return;

        const po = PO_LIST[poId];
        if (po.supplier_id) {
            const sSelect = document.getElementById('supplierSelect');
            if (sSelect) {
                window.setSupplierCategoryFilter('ALL');
                sSelect.value = po.supplier_id;
                window.onSupplierSelected(sSelect);
            }
        }
        if (po.gudang_id) {
            const gSelect = document.getElementById('gudangSelect');
            if (gSelect) gSelect.value = po.gudang_id;
        }

        if (po.items && po.items.length > 0) {
            const firstItemNm = (po.items[0].barang_nm || '').toLowerCase();
            const firstItemBarangNm = po.items[0].barang_nm || '';
            const namaEl = document.getElementById('namaJenisInput');

            if (firstItemNm.includes('minyak')) {
                window.selectKomoditas('MINYAK');
                const mSelect = document.getElementById('minyakBarangSelect');
                if (mSelect) {
                    mSelect.value = po.items[0].barang_id;
                    window.syncNamaRmFromSelect(mSelect);
                }
                const qty = po.items[0].sisa_qty > 0 ? po.items[0].sisa_qty : po.items[0].pesan_qty;
                const inSJ = document.getElementById('inputJumlahSJ');
                const inPabrik = document.getElementById('inputJumlahPabrik');
                const mGross = document.getElementById('minyakQtyGross');
                if (inSJ) inSJ.value = qty;
                if (inPabrik) inPabrik.value = qty;
                if (mGross) mGross.value = qty;
            } else if (firstItemNm.includes('karton') || firstItemNm.includes('dus') || firstItemNm.includes('box')) {
                window.selectKomoditas('KARTON');
                const kSelect = document.getElementById('kartonBarangSelect');
                if (kSelect) {
                    kSelect.value = po.items[0].barang_id;
                    window.syncNamaRmFromSelect(kSelect);
                }
                const qty = po.items[0].sisa_qty > 0 ? po.items[0].sisa_qty : po.items[0].pesan_qty;
                const inSJ = document.getElementById('inputJumlahSJ');
                const inPabrik = document.getElementById('inputJumlahPabrik');
                const kGross = document.getElementById('kartonQtyGross');
                if (inSJ) inSJ.value = qty;
                if (inPabrik) inPabrik.value = qty;
                if (kGross) kGross.value = qty;
            } else if (firstItemNm.includes('plastik') || firstItemNm.includes('kemasan') || firstItemNm.includes('opp') || firstItemNm.includes('pp')) {
                window.selectKomoditas('PLASTIK');
                const pSelect = document.getElementById('plastikBarangSelect');
                if (pSelect) {
                    pSelect.value = po.items[0].barang_id;
                    window.syncNamaRmFromSelect(pSelect);
                }
                const qty = po.items[0].sisa_qty > 0 ? po.items[0].sisa_qty : po.items[0].pesan_qty;
                const inSJ = document.getElementById('inputJumlahSJ');
                const inPabrik = document.getElementById('inputJumlahPabrik');
                const pGross = document.getElementById('plastikQtyGross');
                if (inSJ) inSJ.value = qty;
                if (inPabrik) inPabrik.value = qty;
                if (pGross) pGross.value = qty;
            } else if (firstItemNm.includes('msg') || firstItemNm.includes('micin') || firstItemNm.includes('glutamat')) {
                window.selectKomoditas('MSG');
                const bpSelect = document.getElementById('bpBarangSelect');
                if (bpSelect) {
                    bpSelect.value = po.items[0].barang_id;
                    window.syncNamaRmFromSelect(bpSelect);
                }
                const qty = po.items[0].sisa_qty > 0 ? po.items[0].sisa_qty : po.items[0].pesan_qty;
                const inSJ = document.getElementById('inputJumlahSJ');
                const inPabrik = document.getElementById('inputJumlahPabrik');
                const bpGross = document.getElementById('bpQtyGross');
                if (inSJ) inSJ.value = qty;
                if (inPabrik) inPabrik.value = qty;
                if (bpGross) bpGross.value = qty;
            } else if (firstItemNm.includes('garam') || firstItemNm.includes('salt')) {
                window.selectKomoditas('GARAM');
                const bpSelect = document.getElementById('bpBarangSelect');
                if (bpSelect) {
                    bpSelect.value = po.items[0].barang_id;
                    window.syncNamaRmFromSelect(bpSelect);
                }
                const qty = po.items[0].sisa_qty > 0 ? po.items[0].sisa_qty : po.items[0].pesan_qty;
                const inSJ = document.getElementById('inputJumlahSJ');
                const inPabrik = document.getElementById('inputJumlahPabrik');
                const bpGross = document.getElementById('bpQtyGross');
                if (inSJ) inSJ.value = qty;
                if (inPabrik) inPabrik.value = qty;
                if (bpGross) bpGross.value = qty;
            } else if (firstItemNm.includes('perenyah') || firstItemNm.includes('bumbu') || firstItemNm.includes('balado')) {
                window.selectKomoditas('PERENYAH');
                const bpSelect = document.getElementById('bpBarangSelect');
                if (bpSelect) {
                    bpSelect.value = po.items[0].barang_id;
                    window.syncNamaRmFromSelect(bpSelect);
                }
                const qty = po.items[0].sisa_qty > 0 ? po.items[0].sisa_qty : po.items[0].pesan_qty;
                const inSJ = document.getElementById('inputJumlahSJ');
                const inPabrik = document.getElementById('inputJumlahPabrik');
                const bpGross = document.getElementById('bpQtyGross');
                if (inSJ) inSJ.value = qty;
                if (inPabrik) inPabrik.value = qty;
                if (bpGross) bpGross.value = qty;
            } else {
                window.selectKomoditas('SINGKONG');
                const sContainer = document.getElementById('singkongContainer');
                const fContainer = document.getElementById('fryerParamsContainer');
                if (sContainer) sContainer.innerHTML = '';
                if (fContainer) fContainer.innerHTML = '';
                itemIndex = 0;

                // Singkong Pengujian 1: Tepat 1 komoditas singkong yang diperiksa
                const it = (po.items && po.items.length > 0) ? po.items[0] : null;
                if (it) {
                    window.createItemCard({
                        podtl_id: it.podtl_id,
                        barang_id: it.barang_id,
                        gross: it.sisa_qty > 0 ? it.sisa_qty : it.pesan_qty,
                        kadar_air: '12.0',
                        refraksi_persen: '0.0',
                        reject: '0',
                    });
                } else {
                    window.createItemCard();
                }
            }

            if (namaEl && firstItemBarangNm) {
                namaEl.value = firstItemBarangNm;
            }
        }

        if (select) {
            select.value = poId;
        }
    };

    // 15. Form Submit Event Listener
    const qcForm = document.getElementById('qcForm');
    if (qcForm) {
        qcForm.addEventListener('submit', function (e) {
            if (isSubmitting) {
                e.preventDefault();
                return false;
            }

            if (!validateFormQc1()) {
                e.preventDefault();
                return false;
            }

            prepareCommoditySubmission();
            const statUji = document.getElementById('statusUjiGorengInput');
            const tahapUji = document.getElementById('tahapUjiInput');
            if (statUji) statUji.value = 'SELESAI';
            if (tahapUji && !tahapUji.value) {
                tahapUji.value = 'PENGUJIAN_1';
            }

            try {
                sessionStorage.removeItem('qc_active_tab');
                sessionStorage.removeItem('qc_singkong_tahap');
            } catch (e) {}

            const isTolak = document.querySelector('input[name="kesimpulan_qc"]:checked')?.value === 'TOLAK';
            showSubmitLoading(isTolak ? 'Menyimpan Keputusan Penolakan...' : 'Menyimpan Pengujian I & Menyiapkan Gudang...');
        });
    }

    // 16. Inisialisasi Saat Halaman Selesai Dimuat
    document.addEventListener('DOMContentLoaded', function () {
        const urlParams = new URLSearchParams(window.location.search);
        let defTahap = config.defaultTahap || 'PENGUJIAN_1';
        if (urlParams.get('tahap') === '2') {
            defTahap = 'PENGUJIAN_2';
        } else if (urlParams.get('tahap') === '1') {
            defTahap = 'PENGUJIAN_1';
        } else {
            const savedTahap = sessionStorage.getItem('qc_singkong_tahap');
            if (savedTahap) defTahap = savedTahap;
        }
        window.selectTahapUji(defTahap);

        const initKomoditas = config.initialKomoditas || 'SINGKONG';
        window.selectKomoditas(initKomoditas);

        // Pulihkan tab aktif terakhir agar saat refresh tidak kembali paksa ke Tab 1
        let activeTab = 1;
        const hashMatch = window.location.hash.match(/tab(\d+)/);
        if (hashMatch) {
            activeTab = parseInt(hashMatch[1], 10);
        } else {
            const savedTab = sessionStorage.getItem('qc_active_tab');
            if (savedTab) activeTab = parseInt(savedTab, 10);
        }
        window.switchQcTab(activeTab || 1);

        if (initKomoditas === 'SINGKONG') {
            const sContainer = document.getElementById('singkongContainer');
            const hasCard = sContainer && sContainer.querySelectorAll('.qc-item-card').length > 0;

            if (config.hasParentQc) {
                const p1Select = document.getElementById('selectPendingP1');
                if (p1Select && p1Select.value) {
                    window.onSelectPendingArrival(p1Select);
                } else if (config.parentQcItem && !hasCard) {
                    window.createItemCard(config.parentQcItem);
                } else if (!hasCard) {
                    window.createItemCard();
                }
            } else {
                const poSelect = document.getElementById('poSelect');
                if (poSelect && poSelect.value) {
                    window.onPoSelected(poSelect);
                } else if (!hasCard) {
                    window.createItemCard();
                }
            }
        } else {
            const poSelect = document.getElementById('poSelect');
            if (poSelect && poSelect.value) {
                window.onPoSelected(poSelect);
            }
        }
    });

})();
