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

        if (badgeDoc) badgeDoc.innerText = 'No. Dok: ' + cfg.docNo;
        if (titleHaccp) titleHaccp.innerText = cfg.title;
        if (descHaccp) descHaccp.innerText = cfg.desc;
        if (labelNamaJenis) labelNamaJenis.innerText = cfg.labelNamaJenis;

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

        // Toggle pertanyaan audit halal tambahan
        const halalCertGroup = document.getElementById('auditHalalNonSingkong');
        if (halalCertGroup) {
            halalCertGroup.style.display = (type === 'SINGKONG') ? 'none' : 'flex';
        }

        // Toggle sample input unit (KG vs gr vs pcs)
        const grpSampleKg = document.getElementById('groupSampleKg');
        const grpSampleGr = document.getElementById('groupSampleGr');
        const grpSamplePcs = document.getElementById('groupSamplePcs');
        if (grpSampleKg) grpSampleKg.style.display = (cfg.sampleUnit === 'KG') ? 'block' : 'none';
        if (grpSampleGr) grpSampleGr.style.display = (cfg.sampleUnit === 'gr') ? 'block' : 'none';
        if (grpSamplePcs) grpSamplePcs.style.display = (cfg.sampleUnit === 'pcs') ? 'block' : 'none';

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
            if (sec2Sub) sec2Sub.innerText = 'Formulir Cheklist HACCP PT Mirasa Food Industry';
            if (tabBtn2) {
                tabBtn2.innerText = (type === 'MINYAK') ? '🧪 2. Mutu Fisik & FFA' : '🔬 2. Mutu & Parameter';
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

        if (cSingkong) cSingkong.style.display = (type === 'SINGKONG') ? 'flex' : 'none';
        if (cMinyak) cMinyak.style.display = (type === 'MINYAK') ? 'flex' : 'none';
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
            if (el && (!el.value || el.value == 0)) el.value = pabrik > 0 ? pabrik : '';
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

        if (tahap === 'PENGUJIAN_2') {
            if (hintPabrik) {
                hintPabrik.innerHTML = 'Pengujian 2: Sisa muatan setengah bak yang dibongkar tuntas (contoh: <strong>4.000 KG</strong>).';
            }
            if (btn1) {
                btn1.style.background = '#ffffff';
                btn1.style.borderColor = '#cbd5e1';
                btn1.style.color = '#334155';
            }
            if (btn2) {
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
                desc.innerHTML = '🍟 <strong>Pengujian 2:</strong> Pengujian lanjutan setelah setengah bak diturunkan. Petugas mengambil sampel gabungan ~7 kg dari lapisan dalam/bawah bak untuk verifikasi sebelum bongkar tuntas.';
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
            if (hintPabrik) {
                hintPabrik.innerHTML = 'Pengujian 1: Muatan setengah bak pertama yang turun (contoh: <strong>3.000 KG</strong>). Sisa (4.000 KG) akan otomatis di Pengujian 2.';
            }
            if (btn1) {
                btn1.style.background = '#0284c7';
                btn1.style.borderColor = '#0284c7';
                btn1.style.color = '#ffffff';
            }
            if (btn2) {
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
                desc.innerHTML = '🚛 <strong>Pengujian 1:</strong> Pengujian awal saat truk singkong tiba di pos penerimaan (sebelum bongkar muatan). Sampel gabungan ~7 kg diambil dari bak belakang, tengah, depan.';
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
        const sisaSetengahBak = Math.max(0, totalSJ - p1Gross);
        const estimasiGrossUji2 = sisaSetengahBak > 0 ? sisaSetengahBak : (p1Gross > 0 ? p1Gross : (totalSJ > 0 ? totalSJ / 2 : 4000));

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
            descBanner.innerHTML = `<strong>#${d.qc_no}</strong> &bull; 🚛 ${d.plat || 'Plat -'} &bull; Hasil Uji 1: <span style="background:#e0f2fe; color:#0369a1; padding:1px 6px; border-radius:4px; font-weight:800;">${p1GradeLabel} (${p1Gross.toLocaleString('id-ID')} KG)</span> &bull; Muatan Uji 2: <span style="background:#f3e8ff; color:#7e22ce; padding:1px 6px; border-radius:4px; font-weight:800;">Sisa Setengah Bak (${estimasiGrossUji2.toLocaleString('id-ID')} KG)</span>`;
        }

        window.showQcToast('Data Pengujian 1 Terisi', `Data kedatangan #${d.qc_no} dimuat. Sisa muatan bak: ${estimasiGrossUji2.toLocaleString('id-ID')} KG.`, null, 1);
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
            if (nextText) nextText.innerText = 'Lanjut ke 2. Parameter';
            if (nextIcon) nextIcon.style.display = 'inline-block';
            btnNext.onclick = function () { window.switchQcTab(2); };
        } else if (tabNumber === 2) {
            btnBack.style.display = 'inline-flex';
            if (backText) backText.innerText = 'Tahap 1';
            btnBack.onclick = function () { window.switchQcTab(1); };

            if (currentKomoditas === 'SINGKONG') {
                btnNext.style.display = 'inline-flex';
                btnNext.className = 'btn-dock-next btn-primary-state';
                if (nextText) nextText.innerText = 'Lanjut ke 3. Uji Fryer';
                if (nextIcon) nextIcon.style.display = 'inline-block';
                btnNext.onclick = function () { window.switchQcTab(3); };
            } else {
                btnNext.style.display = 'inline-flex';
                btnNext.className = 'btn-dock-next btn-success-state';
                if (nextText) nextText.innerText = '💾 Simpan & Teruskan ke Gudang';
                if (nextIcon) nextIcon.style.display = 'none';
                btnNext.onclick = function () { window.submitNonSingkong(); };
            }
        } else if (tabNumber === 3) {
            btnBack.style.display = 'inline-flex';
            if (backText) backText.innerText = 'Tahap 2';
            btnBack.onclick = function () { window.switchQcTab(2); };

            btnNext.style.display = 'inline-flex';
            btnNext.className = 'btn-dock-next btn-success-state';
            if (nextText) nextText.innerText = '💾 Simpan Pengujian I (Selesai)';
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

        if (sec1) sec1.style.display = (tabNumber === 1) ? 'block' : 'none';
        if (sec2) sec2.style.display = (tabNumber === 2) ? 'block' : 'none';
        if (sec3) sec3.style.display = (tabNumber === 3) ? 'block' : 'none';

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
            if (minyakSelect && !minyakSelect.value) {
                window.showQcToast('Item Minyak Kosong', 'Pilih item komoditas minyak goreng yang diuji!', minyakSelect, 2);
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

            const gross = parseFloat(document.getElementById('minyakQtyGross').value) || parseFloat(document.getElementById('inputJumlahPabrik').value) || 0;
            const reject = parseFloat(document.getElementById('minyakQtyReject').value) || 0;
            const kesimpulan = document.querySelector('input[name="minyak_kesimpulan"]:checked')?.value || 'TERIMA';

            injectHiddenInput('items[0][barang_id]', bId);
            injectHiddenInput('items[0][qty_timbang_gross]', gross);
            injectHiddenInput('items[0][qty_reject]', reject);
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
    window.onFryerRasaChanged = function (val) {
        const curTahap = document.getElementById('tahapUjiInput')?.value || 'PENGUJIAN_1';
        const radioTerima = document.getElementById('radioTerima');
        const radioTolak = document.getElementById('radioTolak');
        const bannerWarning = document.getElementById('bannerPahitWarning');
        const textWarning = document.getElementById('textPahitWarning');
        const boxDiskusi = document.getElementById('boxDiskusiAtasan');
        const btnSubmit = document.getElementById('btnSubmitPengujian1');

        if (val === 'PAHIT') {
            if (radioTolak) radioTolak.checked = true;
            if (bannerWarning) bannerWarning.style.display = 'block';
            if (boxDiskusi && curTahap === 'PENGUJIAN_2') boxDiskusi.style.display = 'block';

            if (textWarning) {
                textWarning.innerHTML = (curTahap === 'PENGUJIAN_2')
                    ? 'Singkong terdeteksi rasa <strong>PAHIT</strong> pada lapisan dalam bak! Sesuai instruksi Direktur, sisa muatan di atas truk <strong>DITOLAK TOTAL</strong>. Pembongkaran dihentikan segera dan lakukan koordinasi/diskusi dengan atasan (QC Supervisor).'
                    : 'Singkong beracun sianida / tidak layak konsumsi pabrik. Keputusan otomatis dialihkan ke <strong>TOLAK TOTAL</strong>. Truk tidak diizinkan bongkar ke gudang dan Admin Gudang akan menerbitkan Berita Acara Penolakan.';
            }

            if (btnSubmit) {
                btnSubmit.style.background = '#dc2626';
                btnSubmit.innerHTML = (curTahap === 'PENGUJIAN_2')
                    ? '❌ Simpan Penolakan Pengujian 2 (Diskusi Atasan)'
                    : '❌ Simpan Keputusan Penolakan (Ditolak Total)';
            }
            window.onKesimpulanChange('TOLAK');
        } else {
            let hasAnyPahit = false;
            document.querySelectorAll('select[name$="[fryer_rasa]"]').forEach(sel => {
                if (sel.value === 'PAHIT') hasAnyPahit = true;
            });

            if (!hasAnyPahit) {
                if (radioTerima) radioTerima.checked = true;
                if (bannerWarning) bannerWarning.style.display = 'none';
                if (boxDiskusi) boxDiskusi.style.display = 'none';
                if (btnSubmit) {
                    btnSubmit.style.background = '#059669';
                    btnSubmit.innerHTML = (curTahap === 'PENGUJIAN_2')
                        ? '💾 Simpan Pengujian 2 (Lolos &amp; Bongkar Tuntas ke Gudang)'
                        : '💾 Simpan Pengujian 1 (Selesai Inspeksi &amp; Siap Bongkar Setengah Bak)';
                }
                window.onKesimpulanChange('TERIMA');
            }
        }
    };

    window.onKesimpulanChange = function (val) {
        const curTahap = document.getElementById('tahapUjiInput')?.value || 'PENGUJIAN_1';
        const btnSubmit = document.getElementById('btnSubmitPengujian1');
        const bannerWarning = document.getElementById('bannerPahitWarning');
        const boxDiskusi = document.getElementById('boxDiskusiAtasan');

        if (val === 'TOLAK') {
            if (btnSubmit) {
                btnSubmit.style.background = '#dc2626';
                btnSubmit.innerHTML = (curTahap === 'PENGUJIAN_2')
                    ? '❌ Simpan Penolakan Pengujian 2 (Diskusi Atasan)'
                    : '❌ Simpan Keputusan Penolakan (Ditolak Total)';
            }
            if (boxDiskusi && curTahap === 'PENGUJIAN_2') {
                boxDiskusi.style.display = 'block';
            }
        } else {
            if (bannerWarning) bannerWarning.style.display = 'none';
            if (boxDiskusi) boxDiskusi.style.display = 'none';
            if (btnSubmit) {
                btnSubmit.style.background = '#059669';
                btnSubmit.innerHTML = (curTahap === 'PENGUJIAN_2')
                    ? '💾 Simpan Pengujian 2 (Lolos &amp; Bongkar Tuntas ke Gudang)'
                    : '💾 Simpan Pengujian 1 (Selesai Inspeksi &amp; Siap Bongkar Setengah Bak)';
            }
        }
        window.calculateAll();
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

    // 13. Dynamic Item Card Builder untuk Singkong
    window.createItemCard = function (data = {}) {
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
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.85rem;">
                <span style="font-weight: 800; font-size: 0.825rem; color: #0284c7; background: #e0f2fe; padding: 0.25rem 0.65rem; border-radius: 6px;">
                    Komoditas #${idx + 1}
                </span>
                <button type="button" onclick="removeItemCard(${idx})" style="background: none; border: none; color: #ef4444; font-size: 0.8rem; font-weight: 700; cursor: pointer;">
                    ✕ Hapus
                </button>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 0.75rem; margin-bottom: 1rem;">
                <div>
                    <label class="form-label" style="font-size: 0.825rem; font-weight: 700;">Nama Bahan Baku <span style="color:red;">*</span></label>
                    <select name="items[${idx}][barang_id]" id="barang_select_${idx}" class="form-control item-barang-select" required onchange="onItemBarangChanged(${idx})">
                        ${barangOptions}
                    </select>
                </div>
                <div>
                    <label class="form-label" style="font-size: 0.825rem; font-weight: 700;">Grade Mutu Singkong <span style="color:red;">*</span></label>
                    <select name="items[${idx}][grade_cd]" id="grade_select_${idx}" class="form-control" style="font-weight: 800; color: #0f172a;" onchange="onGradeChanged(${idx}, this.value)">
                        <option value="A" selected>🟢 Grade A (Super / Renyah)</option>
                        <option value="B">🟡 Grade B (Standar / Campur)</option>
                    </select>
                </div>
            </div>

            <div id="gradeB_warning_${idx}" style="display: none; background: #fffbeb; border: 1.5px solid #fcd34d; border-radius: 8px; padding: 0.75rem 0.85rem; margin-bottom: 1rem; color: #92400e; font-size: 0.8rem;">
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
                    <button type="button" onclick="document.getElementById('grade_select_${idx}').value='A'; onGradeChanged(${idx}, 'A');" style="background: #ffffff; color: #15803d; border: 1px solid #86efac; font-size: 0.75rem; font-weight: 700; padding: 0.35rem 0.65rem; border-radius: 6px; cursor: pointer;">
                        Kembalikan ke Grade A
                    </button>
                </div>
            </div>

            <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.85rem; margin-bottom: 1rem;">
                <div style="font-weight: 800; font-size: 0.825rem; color: #0f172a; margin-bottom: 0.5rem; display: flex; justify-content: space-between; align-items: center;">
                    <span>📐 1. Parameter Diameter Singkong</span>
                    <span style="font-size: 0.75rem; color: #64748b;">Standar Kebeterimaan</span>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 0.75rem;">
                    <div>
                        <label class="form-label" style="font-size: 0.775rem;">Diameter < 4 cm (Max 5.0%)</label>
                        <div style="position: relative;">
                            <input type="number" step="0.1" min="0" max="100" name="items[${idx}][diameter_kurang_4cm_persen]" value="2.0" class="form-control" style="font-size: 0.85rem;">
                            <span style="position: absolute; right: 10px; top: 7px; font-size: 0.75rem; color: #94a3b8; font-weight: 700;">%</span>
                        </div>
                    </div>
                    <div>
                        <label class="form-label" style="font-size: 0.775rem;">Diameter &ge; 4 cm (Min 95.0%)</label>
                        <div style="position: relative;">
                            <input type="number" step="0.1" min="0" max="100" name="items[${idx}][diameter_lebih_4cm_persen]" value="98.0" class="form-control" style="font-size: 0.85rem; font-weight: 700; color: #15803d;">
                            <span style="position: absolute; right: 10px; top: 7px; font-size: 0.75rem; color: #94a3b8; font-weight: 700;">%</span>
                        </div>
                    </div>
                </div>
            </div>

            <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.85rem; margin-bottom: 1rem;">
                <div style="font-weight: 800; font-size: 0.825rem; color: #0f172a; margin-bottom: 0.5rem;">
                    👁️ 2. Pemeriksaan Visual Singkong (Pilih yang sesuai):
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 0.5rem; font-size: 0.8rem;">
                    <label style="display: flex; align-items: center; gap: 0.35rem; cursor: pointer; color: #15803d; font-weight: 700;">
                        <input type="checkbox" name="items[${idx}][kondisi_segar]" value="1" checked> SEGAR
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.35rem; cursor: pointer;">
                        <input type="checkbox" name="items[${idx}][kondisi_layu]" value="1"> LAYU
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.35rem; cursor: pointer;">
                        <input type="checkbox" name="items[${idx}][kondisi_basah]" value="1"> BASAH
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.35rem; cursor: pointer;">
                        <input type="checkbox" name="items[${idx}][kondisi_terkelupas]" value="1"> TERKELUPAS
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.35rem; cursor: pointer; color: #dc2626; font-weight: 700;">
                        <input type="checkbox" name="items[${idx}][kondisi_busuk]" value="1"> BUSUK
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.35rem; cursor: pointer; color: #b45309; font-weight: 700;">
                        <input type="checkbox" name="items[${idx}][kondisi_berjamur]" value="1"> BERJAMUR
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.35rem; cursor: pointer;">
                        <input type="checkbox" name="items[${idx}][kondisi_lembek]" value="1"> TEKSTUR LEMBEK
                    </label>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 0.75rem; margin-bottom: 0.85rem;">
                <div>
                    <label class="form-label" style="font-size: 0.8rem; font-weight: 700; color: #0f172a;">
                        Timbangan Kotor (Gross) <span style="color:red;">*</span>
                    </label>
                    <div style="position: relative;">
                        <input type="number" step="0.01" min="0.01" name="items[${idx}][qty_timbang_gross]" id="gross_${idx}" value="${grossVal}" class="form-control" placeholder="0.00" required oninput="calculateCard(${idx})" style="font-weight: 800; font-size: 1rem;">
                        <span style="position: absolute; right: 10px; top: 8px; font-size: 0.75rem; color: #94a3b8; font-weight: 700;">KG</span>
                    </div>
                </div>

                <div>
                    <label class="form-label" style="font-size: 0.8rem; font-weight: 700; color: #0f172a;">
                        Kadar Air (%)
                    </label>
                    <div style="position: relative;">
                        <input type="number" step="0.1" min="0" max="100" name="items[${idx}][kadar_air_persen]" id="kadar_air_${idx}" value="${kadarAirVal}" class="form-control" placeholder="12.0" oninput="calculateCard(${idx})">
                        <span style="position: absolute; right: 10px; top: 8px; font-size: 0.75rem; color: #94a3b8; font-weight: 700;">%</span>
                    </div>
                </div>

                <div>
                    <label class="form-label" style="font-size: 0.8rem; font-weight: 700; color: #0f172a;">
                        Refraksi Kotoran / Tanah
                    </label>
                    <div style="position: relative;">
                        <input type="number" step="0.05" min="0" max="100" name="items[${idx}][refraksi_persen]" id="refraksi_${idx}" value="${refraksiPersenVal}" class="form-control" placeholder="0.0" oninput="calculateCard(${idx})">
                        <span style="position: absolute; right: 10px; top: 8px; font-size: 0.75rem; color: #94a3b8; font-weight: 700;">%</span>
                    </div>
                </div>

                <div>
                    <label class="form-label" style="font-size: 0.8rem; font-weight: 700; color: #ef4444;">
                        Afkir / Busuk (Reject)
                    </label>
                    <div style="position: relative;">
                        <input type="number" step="0.01" min="0" name="items[${idx}][qty_reject]" id="reject_${idx}" value="${rejectVal}" class="form-control" placeholder="0.00" oninput="calculateCard(${idx})">
                        <span style="position: absolute; right: 10px; top: 8px; font-size: 0.75rem; color: #94a3b8; font-weight: 700;">KG</span>
                    </div>
                </div>
            </div>

            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 0.65rem 0.85rem; font-size: 0.825rem; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 0.5rem;">
                <div>
                    <span style="color: #64748b;">Potongan Refraksi Tanah:</span> 
                    <strong id="label_qty_refraksi_${idx}" style="color: #b45309;">0.00 KG</strong>
                </div>
                <div>
                    <span style="color: #166534; font-weight: 700;">Netto Lolos Diterima:</span> 
                    <strong id="label_netto_${idx}" style="color: #15803d; font-size: 0.95rem;">0.00 KG</strong>
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
            block.style.cssText = 'background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 1.1rem;';
            container.appendChild(block);
        }

        const sel = document.getElementById(`barang_select_${idx}`);
        const barangNm = sel && sel.selectedOptions[0] ? sel.selectedOptions[0].text : `Item #${idx + 1}`;

        block.innerHTML = `
            <div style="font-weight: 800; font-size: 0.9rem; color: #0284c7; margin-bottom: 0.75rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.35rem;">
                🍟 Uji Cepat Rasa Fryer (Di Depan) &bull; ${barangNm}
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 0.85rem; margin-bottom: 1rem;">
                <div>
                    <label class="form-label" style="font-size: 0.8rem; font-weight: 700;">RASA (Standar: Wajib Tidak Pahit)</label>
                    <select name="items[${idx}][fryer_rasa]" class="form-control" onchange="onFryerRasaChanged(this.value)" style="font-size: 0.85rem; font-weight: 700;">
                        <option value="TIDAK_PAHIT" selected>✅ Gurih / Tidak Pahit (Standar)</option>
                        <option value="PAHIT">❌ Pahit (Reject / Tolak Total)</option>
                    </select>
                </div>

                <div>
                    <label class="form-label" style="font-size: 0.8rem; font-weight: 700;">TEKSTUR (Standar: Renyah)</label>
                    <select name="items[${idx}][fryer_tekstur]" class="form-control" style="font-size: 0.85rem; font-weight: 600;">
                        <option value="RENYAH" selected>✅ Renyah (Lolos)</option>
                        <option value="ALOT">❌ Alot / Keras (Tolak)</option>
                        <option value="LEMBEK">⚠️ Kurang Kering / Lembek</option>
                    </select>
                </div>

                <div>
                    <label class="form-label" style="font-size: 0.8rem; font-weight: 700;">PENAMPAKAN (Standar: Tidak Oilsoaked)</label>
                    <select name="items[${idx}][fryer_penampakan]" class="form-control" style="font-size: 0.85rem; font-weight: 600;">
                        <option value="TIDAK_OILSOAKED" selected>✅ Tidak Oilsoaked (Bagus)</option>
                        <option value="OILSOAKED">❌ Oilsoaked (Serap Minyak)</option>
                    </select>
                </div>
            </div>

            <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 0.85rem;">
                <div style="font-weight: 800; font-size: 0.8rem; color: #b45309; margin-bottom: 0.5rem;">
                    DEFECT FRYING (%) CACAT PENGGORENGAN:
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(110px, 1fr)); gap: 0.6rem; font-size: 0.775rem;">
                    <div>
                        <label class="form-label" style="font-size: 0.725rem;">Breakage (Patah)</label>
                        <input type="number" step="0.1" name="items[${idx}][defect_breakage_persen]" value="0.0" class="form-control" style="font-size: 0.8rem;">
                    </div>
                    <div>
                        <label class="form-label" style="font-size: 0.725rem;">Cluster (Gumpal)</label>
                        <input type="number" step="0.1" name="items[${idx}][defect_cluster_persen]" value="0.0" class="form-control" style="font-size: 0.8rem;">
                    </div>
                    <div>
                        <label class="form-label" style="font-size: 0.725rem;">Foldover (Terlipat)</label>
                        <input type="number" step="0.1" name="items[${idx}][defect_foldover_persen]" value="0.0" class="form-control" style="font-size: 0.8rem;">
                    </div>
                    <div>
                        <label class="form-label" style="font-size: 0.725rem;">Oilsoaked Polos</label>
                        <input type="number" step="0.1" name="items[${idx}][defect_oilsoaked_persen]" value="0.0" class="form-control" style="font-size: 0.8rem;">
                    </div>
                    <div>
                        <label class="form-label" style="font-size: 0.725rem;">Gambos / Kopong (%)</label>
                        <input type="number" step="0.1" name="items[${idx}][defect_gambos_persen]" value="0.0" class="form-control" style="font-size: 0.8rem;">
                    </div>
                </div>
            </div>
        `;
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

        const kesimpulanEl = document.querySelector('input[name="kesimpulan_qc"]:checked');
        const isTolakTotal = kesimpulanEl && kesimpulanEl.value === 'TOLAK';

        const sumGross = document.getElementById('summaryGross');
        const sumRef = document.getElementById('summaryRefraksi');
        const sumRej = document.getElementById('summaryReject');
        const sumNet = document.getElementById('summaryNetto');

        if (isTolakTotal) {
            if (sumGross) sumGross.innerText = `${totalGross.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} KG`;
            if (sumRef) sumRef.innerText = `- 0.00 KG`;
            if (sumRej) sumRej.innerText = `- ${totalGross.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} KG`;
            if (sumNet) {
                sumNet.innerText = `0.00 KG (DITOLAK)`;
                sumNet.style.color = '#dc2626';
            }
        } else {
            if (sumGross) sumGross.innerText = `${totalGross.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} KG`;
            if (sumRef) sumRef.innerText = `- ${totalRefraksi.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} KG`;
            if (sumRej) sumRej.innerText = `- ${totalReject.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} KG`;
            if (sumNet) {
                sumNet.innerText = `${totalNetto.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} KG`;
                sumNet.style.color = '#15803d';
            }
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
                po.items.forEach(it => {
                    window.createItemCard({
                        podtl_id: it.podtl_id,
                        barang_id: it.barang_id,
                        gross: it.sisa_qty > 0 ? it.sisa_qty : it.pesan_qty,
                        kadar_air: '12.0',
                        refraksi_persen: '0.0',
                        reject: '0',
                    });
                });
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

            const isTolak = document.querySelector('input[name="kesimpulan_qc"]:checked')?.value === 'TOLAK';
            showSubmitLoading(isTolak ? 'Menyimpan Keputusan Penolakan...' : 'Menyimpan Pengujian I & Menyiapkan Gudang...');
        });
    }

    // 16. Inisialisasi Saat Halaman Selesai Dimuat
    document.addEventListener('DOMContentLoaded', function () {
        const defTahap = config.defaultTahap || 'PENGUJIAN_1';
        window.selectTahapUji(defTahap);

        const initKomoditas = config.initialKomoditas || 'SINGKONG';
        window.selectKomoditas(initKomoditas);
        window.updateFloatingDock(1);

        if (config.hasParentQc) {
            const p1Select = document.getElementById('selectPendingP1');
            if (p1Select && p1Select.value) {
                window.onSelectPendingArrival(p1Select);
            } else if (config.parentQcItem) {
                window.createItemCard(config.parentQcItem);
            } else {
                window.createItemCard();
            }
        } else {
            const poSelect = document.getElementById('poSelect');
            if (poSelect && poSelect.value) {
                window.onPoSelected(poSelect);
            } else {
                window.createItemCard();
            }
        }
    });

})();
