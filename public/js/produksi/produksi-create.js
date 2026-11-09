/**
 * JavaScript Logika Hasil Produksi & Kalkulasi HPP Harian - ERP PT Mirasa
 * Mengatur interaksi Shift A/B, kalkulasi rentang nomor karton, stiker fisik WIP-FCC, dan HPP
 */

(function () {
    'use strict';

    // Helper: Pad angka dengan nol di depan (4 digit)
    function pad4(num) {
        return String(num).padStart(4, '0');
    }

    // Format Rupiah
    function formatRupiah(val) {
        return 'Rp ' + Math.round(val).toLocaleString('id-ID');
    }

    // Format Angka Desimal
    function formatNumber(val, decimals = 2) {
        return parseFloat(val || 0).toLocaleString('id-ID', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        });
    }

    // Toggle Section Finansial & Costing HPP (Khusus Bagian Keuangan)
    window.toggleFinanceSection = function () {
        const body = document.getElementById('financeSectionBody');
        const text = document.getElementById('financeSectionToggleText');
        const topText = document.getElementById('btnFinanceToggleTopText');
        if (!body) return;
        const isHidden = body.style.display === 'none' || body.style.display === '';
        if (isHidden) {
            body.style.display = 'block';
            if (text) text.textContent = '[-] Tutup Rincian Finansial';
            if (topText) topText.textContent = 'Tutup Rincian Finansial';
        } else {
            body.style.display = 'none';
            if (text) text.textContent = '[+ Buka Rincian Finansial]';
            if (topText) topText.textContent = 'Rincian Finansial & HPP';
        }
    };

    // 1. Pilih Shift A / B
    window.selectShift = function (shift) {
        const shiftUpper = shift.toUpperCase();
        const inputShift = document.getElementById('shift_cd');
        if (inputShift) inputShift.value = shiftUpper;

        const btnA = document.getElementById('btnShiftA');
        const btnB = document.getElementById('btnShiftB');

        if (btnA && btnB) {
            if (shiftUpper === 'A') {
                btnA.classList.add('shift-segmented-active', 'active-a');
                btnA.classList.remove('shift-segmented-inactive');
                btnB.classList.add('shift-segmented-inactive');
                btnB.classList.remove('shift-segmented-active', 'active-b');
            } else {
                btnB.classList.add('shift-segmented-active', 'active-b');
                btnB.classList.remove('shift-segmented-inactive');
                btnA.classList.add('shift-segmented-inactive');
                btnA.classList.remove('shift-segmented-active', 'active-a');
            }
        }

        // Ambil nomor karton yang disarankan via AJAX
        window.fetchNextKarton();
    };

    // 2. Fetch Nomor Karton Awal dari Server
    window.fetchNextKarton = function () {
        const tgl = document.getElementById('produksi_tgl')?.value || '';
        const shift = document.getElementById('shift_cd')?.value || 'A';
        const url = `${window.appConfig.nextKartonUrl}?tgl=${encodeURIComponent(tgl)}&shift=${encodeURIComponent(shift)}`;

        const infoSource = document.getElementById('kartonSourceInfo');
        if (infoSource) infoSource.textContent = 'Memeriksa riwayat karton...';

        fetch(url)
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    const data = res.data;
                    const inputAwal = document.getElementById('no_karton_awal');
                    if (inputAwal) {
                        inputAwal.value = data.next_no_awal;
                    }
                    if (infoSource) {
                        infoSource.textContent = data.source_desc || '';
                    }
                    window.updateKartonRangeAndBatch();
                }
            })
            .catch(err => {
                console.error('Error fetching next karton:', err);
                if (infoSource) infoSource.textContent = 'Gagal memuat otomatis, silakan isi manual.';
                window.updateKartonRangeAndBatch();
            });
    };

    // Helper: Hitung Exp Date (6 bulan untuk IFM, 1 tahun - 1 hari untuk Barang Jadi Reguler)
    function calcExpDateFormatted(tglStr, isIfm) {
        if (!tglStr) return '-';
        const parts = tglStr.split('-');
        if (parts.length !== 3) return '-';
        
        const y = parseInt(parts[0]);
        const m = parseInt(parts[1]) - 1;
        const d = parseInt(parts[2]);
        const dateObj = new Date(y, m, d);

        if (isIfm) {
            dateObj.setMonth(dateObj.getMonth() + 6);
        } else {
            dateObj.setFullYear(dateObj.getFullYear() + 1);
            dateObj.setDate(dateObj.getDate() - 1);
        }

        const expDay = String(dateObj.getDate()).padStart(2, '0');
        const expMonth = String(dateObj.getMonth() + 1).padStart(2, '0');
        const expYear = dateObj.getFullYear();
        return `${expDay}/${expMonth}/${expYear}`;
    }

    // Helper: Hitung Exp Date format ISO YYYY-MM-DD untuk input date HTML
    function calcExpDateIso(tglStr, isIfm) {
        if (!tglStr) return '';
        const parts = tglStr.split('-');
        if (parts.length !== 3) return '';
        
        const y = parseInt(parts[0]);
        const m = parseInt(parts[1]) - 1;
        const d = parseInt(parts[2]);
        const dateObj = new Date(y, m, d);

        if (isIfm) {
            dateObj.setMonth(dateObj.getMonth() + 6);
        } else {
            dateObj.setFullYear(dateObj.getFullYear() + 1);
            dateObj.setDate(dateObj.getDate() - 1);
        }

        const expYear = dateObj.getFullYear();
        const expMonth = String(dateObj.getMonth() + 1).padStart(2, '0');
        const expDay = String(dateObj.getDate()).padStart(2, '0');
        return `${expYear}-${expMonth}-${expDay}`;
    }

    // Helper: Dapatkan format batch tanggal (DD MM YYYY) dari field tanggal produksi
    function getDateBatch() {
        const tgl = document.getElementById('produksi_tgl')?.value || '';
        if (!tgl) return '-';
        const parts = tgl.split('-');
        if (parts.length === 3) {
            return `${parts[2]} ${parts[1]} ${parts[0]}`;
        }
        return '-';
    }

    // Helper: Tentukan apakah produk adalah WIP Curah (perlu format batch tanggal, bukan range karton)
    // WIP Curah = Berko, Asin Barco, Asin Sawit, No Salt, Balo Gelombang — semua produk curah timbangan
    function isCurahWipProduct(cd, nm) {
        const c = (cd || '').toUpperCase();
        const n = (nm || '').toUpperCase();
        return (
            c.includes('BRK') || n.includes('BERKO') ||
            c.includes('LGT') || n.includes('LENGKET') ||
            c.includes('RMH') || n.includes('REMAHAN') ||
            c.includes('ASB') || n.includes('ASIN BARCO') ||
            c.includes('ASW') || n.includes('ASIN SAWIT') ||
            c.includes('NSL') || n.includes('NO SALT') ||
            c.includes('BLQ') || n.includes('BALO') || n.includes('GELOMBANG') ||
            // WIP curah lain yang tidak berkode FCC/IFL = curah
            (c.startsWith('WIP-') && !c.includes('FCC') && !c.includes('IFL') && !c.includes('IFM'))
        );
    }

    // Helper: Terapkan badge batch yang tepat ke satu baris tabel output (idx)
    function applyBatchBadgeToRow(idx, sel) {
        const badgeFgBatch = document.getElementById(`badgeFgBatch_${idx}`);
        const batchFgInput = document.getElementById(`batchFg_${idx}`);
        if (!badgeFgBatch) return;

        const opt = sel?.selectedOptions?.[0];
        const cd = opt?.dataset?.cd || '';
        const nm = opt?.dataset?.nm || '';

        // Status per baris: 'active' (nomor batch diterbitkan) atau 'excluded' (ditangguhkan / belum terbit)
        let state = badgeFgBatch.dataset.batchState;
        if (!state) {
            const txt = badgeFgBatch.textContent.trim();
            if (txt === 'BELUM TERBIT' || txt === 'PENDING') {
                state = 'excluded';
            } else if (txt && txt !== '-') {
                state = 'active';
            } else {
                const chkBatch = document.getElementById('chk_enable_batch');
                const isMasterOff = (window._batchOption === 'exclude') || (chkBatch && !chkBatch.checked);
                state = isMasterOff ? 'excluded' : 'active';
            }
            badgeFgBatch.dataset.batchState = state;
        }

        const isRowExcluded = (state === 'excluded');
        const isCurah = isCurahWipProduct(cd, nm);
        const baseDate = getDateBatch();
        const currentBatch = document.getElementById('batch_wip_no')?.value || '-';
        
        let batchForItem = '';
        if (isRowExcluded) {
            batchForItem = 'BELUM TERBIT';
        } else if (isCurah) {
            batchForItem = (baseDate && baseDate !== '-') ? baseDate : '-';
        } else {
            batchForItem = (currentBatch && currentBatch !== 'BELUM TERBIT') ? currentBatch : (baseDate || '-');
        }

        badgeFgBatch.textContent = batchForItem;
        if (batchForItem === 'BELUM TERBIT' || batchForItem === 'PENDING') {
            // Amber tegas: Nomor batch ditangguhkan (Buffer Stock)
            badgeFgBatch.textContent = 'BELUM TERBIT';
            badgeFgBatch.style.background = '#fffbeb';
            badgeFgBatch.style.color = '#b45309';
            badgeFgBatch.style.borderColor = '#f59e0b';
            badgeFgBatch.style.borderWidth = '1.5px';
            badgeFgBatch.style.borderStyle = 'solid';
            badgeFgBatch.style.fontWeight = '700';
            badgeFgBatch.style.cursor = 'pointer';
            badgeFgBatch.title = 'Status: Ditangguhkan (Buffer Stock). Klik untuk menerbitkan nomor batch baris ini.';
        } else if (isCurah) {
            // Hijau: Curah/Berko -> Tanggal murni
            badgeFgBatch.style.background = '#f0fdf4';
            badgeFgBatch.style.color = '#065f46';
            badgeFgBatch.style.borderColor = '#a7f3d0';
            badgeFgBatch.style.borderWidth = '1px';
            badgeFgBatch.style.borderStyle = 'solid';
            badgeFgBatch.style.cursor = 'pointer';
            badgeFgBatch.title = 'Status: Batch Curah Diterbitkan. Klik untuk menangguhkan (Buffer Stock).';
        } else if (batchForItem && batchForItem !== '-') {
            // Biru: Kemasan karton IFM -> Format range
            badgeFgBatch.style.background = '#f0f9ff';
            badgeFgBatch.style.color = '#0369a1';
            badgeFgBatch.style.borderColor = '#bae6fd';
            badgeFgBatch.style.borderWidth = '1px';
            badgeFgBatch.style.borderStyle = 'solid';
            badgeFgBatch.style.cursor = 'pointer';
            badgeFgBatch.title = 'Status: Batch Karton Diterbitkan. Klik untuk menangguhkan (Buffer Stock).';
        } else {
            // Abu-abu: belum ada batch
            badgeFgBatch.style.background = '#f8fafc';
            badgeFgBatch.style.color = '#64748b';
            badgeFgBatch.style.borderColor = '#cbd5e1';
            badgeFgBatch.style.borderWidth = '1px';
            badgeFgBatch.style.borderStyle = 'solid';
            badgeFgBatch.style.cursor = 'pointer';
            badgeFgBatch.title = 'Kode Batch otomatis setelah Lini dipilih';
        }

        if (batchFgInput) {
            batchFgInput.value = (batchForItem === 'BELUM TERBIT' || batchForItem === '-') ? '' : batchForItem;
        }
    }

    // Toggle status batch per baris secara independen (Override Manual Click-to-Toggle)
    window.toggleSingleRowBatch = function (idx) {
        const badgeFgBatch = document.getElementById(`badgeFgBatch_${idx}`);
        const tr = document.getElementById(`fgRow_${idx}`);
        if (!badgeFgBatch || !tr) return;

        // Toggle state baris ini: active <-> excluded
        const currentState = badgeFgBatch.dataset.batchState || 
            (badgeFgBatch.textContent.trim() === 'BELUM TERBIT' ? 'excluded' : 'active');
        const nextState = (currentState === 'active') ? 'excluded' : 'active';
        badgeFgBatch.dataset.batchState = nextState;

        const sel = tr.querySelector('.select-fg-barang');
        applyBatchBadgeToRow(idx, sel);

        // Sinkronisasi dua arah ke Master Switch di Card 3
        if (typeof window.syncMasterSwitchFromTable === 'function') {
            window.syncMasterSwitchFromTable();
        }
    };

    // Helper: Sinkronisasi status Master Switch di Card 3 secara dua arah berdasarkan status baris di tabel
    window.syncMasterSwitchFromTable = function () {
        const rows = document.querySelectorAll('#tbodyOutputFg .row-output-fg');
        if (rows.length === 0) return;

        let totalRows = rows.length;
        let activeRows = 0;
        let excludedRows = 0;

        rows.forEach(tr => {
            const badge = tr.querySelector('.badge-batch-wip');
            const state = badge?.dataset?.batchState || 
                (badge?.textContent.trim() === 'BELUM TERBIT' || badge?.textContent.trim() === 'PENDING' || !badge?.textContent.trim() ? 'excluded' : 'active');
            if (state === 'active') {
                activeRows++;
            } else {
                excludedRows++;
            }
        });

        const chkBatch = document.getElementById('chk_enable_batch');
        const hiddenOpt = document.getElementById('batch_option');
        const sectionBody = document.getElementById('section_body_batch');
        const cardHeaderBadge = document.getElementById('cardHeaderBadge');

        if (activeRows === totalRows) {
            // SEMUA BARIS AKTIF -> Switch Card 3 OTOMATIS MENYALA (ON)
            if (chkBatch) chkBatch.checked = true;
            if (hiddenOpt) hiddenOpt.value = 'include';
            window._batchOption = 'include';

            if (sectionBody) {
                sectionBody.style.opacity = '1';
                sectionBody.style.pointerEvents = 'auto';
            }

            const inputAwal = document.getElementById('no_karton_awal');
            if (inputAwal && inputAwal.dataset.prevVal) {
                inputAwal.value = inputAwal.dataset.prevVal;
            }

            // Hitung ulang kode batch resmi di Card 3 (skip sync table agar tidak menimpa status baris tabel)
            window.updateKartonRangeAndBatch(true);
        } else if (excludedRows === totalRows) {
            // SEMUA BARIS DITANGGUHKAN -> Switch Card 3 OTOMATIS MATI (OFF)
            if (chkBatch) chkBatch.checked = false;
            if (hiddenOpt) hiddenOpt.value = 'exclude';
            window._batchOption = 'exclude';

            if (sectionBody) {
                sectionBody.style.opacity = '0.45';
                sectionBody.style.pointerEvents = 'none';
            }

            const liveBatch = document.getElementById('liveBatchCode');
            if (liveBatch) liveBatch.textContent = 'BELUM TERBIT';

            const inputBatch = document.getElementById('batch_wip_no');
            if (inputBatch) inputBatch.value = 'BELUM TERBIT';

            if (cardHeaderBadge) {
                cardHeaderBadge.textContent = 'Ditangguhkan (Buffer Stock)';
                cardHeaderBadge.style.background = '#f1f5f9';
                cardHeaderBadge.style.color = '#475569';
                cardHeaderBadge.style.borderColor = '#cbd5e1';
            }
        } else {
            // SEBAGIAN BARIS AKTIF (CAMPURAN) -> Switch Card 3 tetap MENYALA (ON)
            if (chkBatch) chkBatch.checked = true;
            if (hiddenOpt) hiddenOpt.value = 'include';
            window._batchOption = 'include';

            if (sectionBody) {
                sectionBody.style.opacity = '1';
                sectionBody.style.pointerEvents = 'auto';
            }

            // Hitung ulang kode batch resmi di Card 3 (skip sync table agar tidak menimpa status baris tabel)
            window.updateKartonRangeAndBatch(true);

            if (cardHeaderBadge) {
                cardHeaderBadge.textContent = `Batch Aktif Sebagian (${activeRows}/${totalRows} Baris)`;
                cardHeaderBadge.style.background = '#f0fdf4';
                cardHeaderBadge.style.color = '#15803d';
                cardHeaderBadge.style.borderColor = '#bbf7d0';
            }
        }
    };

    // Toggle Switch di Card 3: On = Aktifkan Semua Baris, Off = Tangguhkan Semua Baris
    window.toggleBatchSwitch = function (isEnabled) {
        const mode = isEnabled ? 'include' : 'exclude';
        window._batchOption = mode;

        const hiddenOpt = document.getElementById('batch_option');
        if (hiddenOpt) hiddenOpt.value = mode;

        const sectionBody = document.getElementById('section_body_batch');
        if (sectionBody) {
            sectionBody.style.opacity = isEnabled ? '1' : '0.45';
            sectionBody.style.pointerEvents = isEnabled ? 'auto' : 'none';
        }

        const inputAwal = document.getElementById('no_karton_awal');
        const inputAkhir = document.getElementById('no_karton_akhir');
        if (!isEnabled) {
            if (inputAwal) {
                if (!inputAwal.dataset.prevVal && inputAwal.value) {
                    inputAwal.dataset.prevVal = inputAwal.value;
                }
                inputAwal.value = '';
            }
            if (inputAkhir) inputAkhir.value = '';
        } else {
            if (inputAwal) {
                if (inputAwal.dataset.prevVal) {
                    inputAwal.value = inputAwal.dataset.prevVal;
                } else if (!inputAwal.value) {
                    inputAwal.value = '1';
                }
            }
        }

        // Terapkan status master switch ke seluruh baris tabel secara serentak
        const targetState = isEnabled ? 'active' : 'excluded';
        document.querySelectorAll('#tbodyOutputFg .badge-batch-wip').forEach(el => {
            el.dataset.batchState = targetState;
        });

        // Hitung ulang kode batch di Card 3
        window.updateKartonRangeAndBatch(true);
        // Sinkronkan seluruh baris tabel sesuai batchState masing-masing
        window.syncBatchToOutputItems();
    };

    // Alias kompatibilitas jika dipanggil dengan mode string
    window.toggleBatchOption = function (mode) {
        const isEnabled = (mode !== 'exclude');
        const chk = document.getElementById('chk_enable_batch');
        if (chk) chk.checked = isEnabled;
        window.toggleBatchSwitch(isEnabled);
    };

    // Helper: Sinkronisasi Kode Batch Bagian 2 ke seluruh Baris Output Barang Jadi (Bagian 7)
    // Per baris: menghormati batchState ('active' vs 'excluded') masing-masing baris
    window.syncBatchToOutputItems = function () {
        const tbody = document.getElementById('tbodyOutputFg');
        if (!tbody) return;

        tbody.querySelectorAll('.row-output-fg').forEach(tr => {
            const sel = tr.querySelector('.select-fg-barang');
            if (!sel) return;

            // Ambil idx dari id row (fgRow_N)
            const rowId = tr.id || '';
            const idxMatch = rowId.match(/fgRow_(\d+)/);
            if (!idxMatch) return;
            const idx = idxMatch[1];

            applyBatchBadgeToRow(idx, sel);
        });
    };


    // 3. Kalkulasi Rentang Karton & Kode Batch WIP / Barang Jadi
    window.updateKartonRangeAndBatch = function (skipSyncTable = false) {
        const shift = document.getElementById('shift_cd')?.value || 'A';
        const rawAwalVal = document.getElementById('no_karton_awal')?.value;
        const hasKartonInput = rawAwalVal !== undefined && rawAwalVal.trim() !== '' && parseInt(rawAwalVal) > 0;
        const noAwal = hasKartonInput ? parseInt(rawAwalVal) : 1;
        const qtyKarton = parseInt(document.getElementById('qty_karton')?.value) || 0;
        const liniSelect = document.getElementById('lini_produksi');
        const selectedOpt = liniSelect?.selectedOptions?.[0];
        const lini = liniSelect?.value || '';
        const selectedTipe = selectedOpt?.getAttribute('data-tipe');
        const selectedKategori = selectedOpt?.getAttribute('data-kategori') || '';
        const selectedCd = selectedOpt?.getAttribute('data-cd') || '';
        const tgl = document.getElementById('produksi_tgl')?.value || '';

        // Cek produk hasil produksi utama pada baris pertama tabel Bagian 7
        const firstFgSel = document.querySelector('#tableOutputFg tbody tr:first-child .select-fg-barang')
            || document.querySelector('.select-fg-barang');
        const firstOpt = firstFgSel?.selectedOptions?.[0];
        const prodCd = (firstOpt?.dataset?.cd || firstOpt?.getAttribute('data-cd') || '').toUpperCase();
        const prodNm = (firstOpt?.dataset?.nm || firstOpt?.getAttribute('data-nm') || firstOpt?.textContent || '').toUpperCase();
        const prodJenis = (firstOpt?.dataset?.jenis || firstOpt?.getAttribute('data-jenis') || '').toUpperCase();
        const hasExplicitProduct = Boolean(firstOpt && firstOpt.value);

        // Standar Indofood IFM (Box 6 Kg berkarton urut):
        // 1. Jika produk sudah dipilih di tabel: aktif HANYA jika produknya adalah IFM / WIP-FCC
        // 2. Jika produk belum dipilih (baru buka form atau baru pilih Lini Produksi):
        //    - Default awal adalah Standar Karton Pabrik (Box 6 Kg, 3 kolom karton lengkap).
        //    - Hanya beralih ke non-IFM jika user memilih lini selain IFL (Manual, Berko, Barco, dll)
        let isIfm = false;
        if (hasExplicitProduct) {
            isIfm = Boolean(
                prodCd.includes('FCC') || 
                prodCd.includes('IFM') || 
                prodNm.includes('WIP-FCC') || 
                (prodNm.includes('IFM') && !prodNm.includes('MANUAL')) ||
                (prodNm.includes('TANPA BUMBU') && !prodNm.includes('JUMBO'))
            );
        } else {
            const isNonIfmLini = Boolean(
                lini && (
                    lini.toUpperCase().includes('MANUAL') || 
                    lini.toUpperCase().includes('BERKO') || 
                    lini.toUpperCase().includes('BARCO') || 
                    lini.toUpperCase().includes('SAWIT') || 
                    lini.toUpperCase().includes('NO SALT') || 
                    lini.toUpperCase().includes('BALQI') ||
                    lini.toUpperCase().includes('RETAIL')
                ) && !lini.toUpperCase().includes('IFL') && !lini.toUpperCase().includes('IFM')
            );
            isIfm = !isNonIfmLini;
        }

        // Cek apakah produk adalah Olahan Curah WIP (Timbangan kg di Bagian 7)
        const isWip = Boolean(
            !isIfm && (
                prodJenis === 'WIP' ||
                selectedKategori.toUpperCase().includes('WIP') || 
                lini.toUpperCase().includes('BERKO') || 
                lini.toUpperCase().includes('BARCO') || 
                lini.toUpperCase().includes('SAWIT') || 
                lini.toUpperCase().includes('NO SALT') || 
                lini.toUpperCase().includes('BALQI')
            )
        );

        // Elemen-elemen DOM
        const sectionShift = document.getElementById('sectionShiftSelection');
        const cardHeaderTitle = document.getElementById('cardHeaderTitle');
        const cardHeaderBadge = document.getElementById('cardHeaderBadge');
        const labelKartonTitle = document.getElementById('labelKartonTitle');
        const liveEstimasiDesc = document.getElementById('liveEstimasiDesc');
        const unitKartonSuffix = document.getElementById('unitKartonSuffix');
        const rowShiftKartonGrid = document.getElementById('rowShiftKartonGrid');
        const colQtyKarton = document.getElementById('colQtyKarton');
        const colAwal = document.getElementById('colKartonAwal');
        const colAkhir = document.getElementById('colKartonAkhir');

        if (sectionShift) sectionShift.style.display = 'block';

        if (isIfm) {
            // MODE IFM: Nomor Karton Awal & Akhir WAJIB tampil (3 kolom: Qty Karton, No Awal, No Akhir)
            if (colAwal) colAwal.style.display = 'block';
            if (colAkhir) colAkhir.style.display = 'block';
            if (colQtyKarton) colQtyKarton.style.display = 'block';
            if (rowShiftKartonGrid) {
                rowShiftKartonGrid.style.display = 'grid';
                rowShiftKartonGrid.style.gridTemplateColumns = '1.2fr 1fr 1fr';
            }

            if (cardHeaderTitle) cardHeaderTitle.textContent = '3. Penomoran Batch & Kemasan Karton (Standar Indofood IFL / IFM)';
            if (labelKartonTitle) labelKartonTitle.innerHTML = 'Karton Selesai <span style="color: #ef4444;">*</span>';
            if (unitKartonSuffix) unitKartonSuffix.textContent = 'Box 6 Kg';
            if (liveEstimasiDesc) liveEstimasiDesc.textContent = '(Netto 6 Kg/Box)';
        } else {
            // MODE SELAIN IFM: Nomor Karton Awal & Akhir DISEMBUNYIKAN (tidak relevan)
            if (colAwal) colAwal.style.display = 'none';
            if (colAkhir) colAkhir.style.display = 'none';

            if (isWip) {
                // Curah WIP: Kuantitas diinput langsung pada timbangan kg di Bagian 2
                if (colQtyKarton) colQtyKarton.style.display = 'none';
                if (rowShiftKartonGrid) rowShiftKartonGrid.style.display = 'none';

                if (cardHeaderTitle) cardHeaderTitle.textContent = '3. Kode Batch Produksi (Olahan Curah WIP)';
                if (cardHeaderBadge) {
                    cardHeaderBadge.innerHTML = 'Format Batch: Tanggal (DD MM YYYY)';
                    cardHeaderBadge.style.background = '#f1f5f9';
                    cardHeaderBadge.style.color = '#334155';
                    cardHeaderBadge.style.borderColor = '#cbd5e1';
                }
            } else {
                // Barang Jadi Reguler Mirasa (Ping-Ping, Maksi, Jumbo, dll)
                if (colQtyKarton) colQtyKarton.style.display = 'block';
                if (rowShiftKartonGrid) {
                    rowShiftKartonGrid.style.display = 'grid';
                    rowShiftKartonGrid.style.gridTemplateColumns = 'minmax(250px, 360px)';
                }

                if (cardHeaderTitle) cardHeaderTitle.textContent = '3. Penomoran Batch & Kemasan Hasil Produksi';
                if (cardHeaderBadge) {
                    cardHeaderBadge.innerHTML = 'Format Batch: Tanggal (DD MM YYYY)';
                    cardHeaderBadge.style.background = '#f1f5f9';
                    cardHeaderBadge.style.color = '#334155';
                    cardHeaderBadge.style.borderColor = '#cbd5e1';
                }
                if (labelKartonTitle) labelKartonTitle.innerHTML = 'Kemasan / Bal Selesai';
                if (unitKartonSuffix) unitKartonSuffix.textContent = 'Kemasan';
                if (liveEstimasiDesc) liveEstimasiDesc.textContent = '(Kemasan Retail Mirasa)';
            }
        }

        const chkSwitch = document.getElementById('chk_enable_batch');
        const isBatchExcluded = (chkSwitch ? !chkSwitch.checked : false) || (window._batchOption === 'exclude') || (document.getElementById('batch_option')?.value === 'exclude');

        // Hitung nomor akhir: awal + qty - 1 (hanya jika nomor karton diisi dan tidak di-exclude)
        let noAkhir = noAwal;
        const inputAkhir = document.getElementById('no_karton_akhir');
        if (isBatchExcluded) {
            if (inputAkhir) inputAkhir.value = '';
        } else if (hasKartonInput) {
            if (qtyKarton > 0) {
                noAkhir = noAwal + qtyKarton - 1;
            }
            if (inputAkhir) inputAkhir.value = noAkhir;
        } else {
            if (inputAkhir) inputAkhir.value = '';
        }

        // Kode Batch: IFM vs Reguler Mirasa vs Ditangguhkan (Buffer Stock)
        let batchCode = '';
        if (isBatchExcluded) {
            batchCode = 'BELUM TERBIT';
            if (cardHeaderBadge) {
                cardHeaderBadge.textContent = 'Ditangguhkan (Buffer Stock)';
                cardHeaderBadge.style.background = '#f1f5f9';
                cardHeaderBadge.style.color = '#475569';
                cardHeaderBadge.style.borderColor = '#cbd5e1';
            }
        } else if (isIfm && hasKartonInput) {
            if (qtyKarton > 0) {
                batchCode = `${shift}${pad4(noAwal)} - ${shift}${pad4(noAkhir)}`;
            } else {
                batchCode = `${shift}${pad4(noAwal)} - ${shift}${pad4(noAwal)}`;
            }
            if (cardHeaderBadge) {
                cardHeaderBadge.textContent = 'Format: [Shift][NoAwal] - [Shift][NoAkhir]';
                cardHeaderBadge.style.background = '#f1f5f9';
                cardHeaderBadge.style.color = '#334155';
                cardHeaderBadge.style.borderColor = '#cbd5e1';
            }
        } else {
            // Format Batch Tanggal Murni: DD MM YYYY
            if (tgl) {
                const parts = tgl.split('-');
                if (parts.length === 3) {
                    batchCode = `${parts[2]} ${parts[1]} ${parts[0]}`;
                }
            } else {
                const now = new Date();
                const d = String(now.getDate()).padStart(2, '0');
                const m = String(now.getMonth() + 1).padStart(2, '0');
                const y = now.getFullYear();
                batchCode = `${d} ${m} ${y}`;
            }

            if (cardHeaderBadge) {
                cardHeaderBadge.textContent = 'Format: Otomatis Standar Lini';
                cardHeaderBadge.style.background = '#f1f5f9';
                cardHeaderBadge.style.color = '#334155';
                cardHeaderBadge.style.borderColor = '#cbd5e1';
            }
        }

        const liveBatch = document.getElementById('liveBatchCode');
        if (liveBatch) {
            liveBatch.textContent = batchCode;
        }

        const inputBatch = document.getElementById('batch_wip_no');
        if (inputBatch) inputBatch.value = batchCode;

        // Update input tanggal expired date (default otomatis namun dapat diedit manual)
        const expDateIso = calcExpDateIso(tgl, isIfm);
        const inputExpDate = document.getElementById('exp_date');
        if (inputExpDate && (!window._expDateUserModified || !inputExpDate.value)) {
            inputExpDate.value = expDateIso;
        }

        const expDescEl = document.getElementById('expDateBadgeDesc');
        if (expDescEl && !window._expDateUserModified) {
            expDescEl.textContent = isIfm ? '(Standar +6 Bulan)' : '(Standar 1 Thn - 1 Hr)';
        }

        // Update estimasi kg
        const estimasiKg = isIfm ? (qtyKarton * 6.0) : qtyKarton;
        const liveEstimasiKg = document.getElementById('liveEstimasiKg');
        if (liveEstimasiKg) {
            liveEstimasiKg.textContent = formatNumber(estimasiKg, 2) + (isIfm ? ' Kg' : ' Unit/Kg');
        }

        // Sinkronkan kode batch ke baris output barang jadi di Bagian 7 HANYA jika tidak di-skip
        if (!skipSyncTable) {
            window.syncBatchToOutputItems();
        }

        // Sinkronisasi otomatis ke baris QTY barang di tabel Bagian 7 jika dipicu dari perubahan Karton Selesai
        if (!skipSyncTable) {
            const firstQtyInp = document.getElementById('qtyHasil_0');
            if (firstQtyInp && (isIfm || firstQtyInp.value === '' || firstQtyInp.dataset.syncedByKarton === 'true')) {
                if (qtyKarton > 0 && parseFloat(firstQtyInp.value) !== qtyKarton) {
                    firstQtyInp.value = qtyKarton;
                    firstQtyInp.dataset.syncedByKarton = 'true';
                    if (typeof window.onQtyHasilInput === 'function') {
                        window.onQtyHasilInput(0, true);
                    }
                }
            }
        }

        // Update tampilan stiker karton / kemasan
        window.updateStickerPreview();

        // Sinkronisasi otomatis ke kemasan karton sirkular jika baru input Qty Karton
        if (typeof window.updateKartonMode === 'function') {
            window.updateKartonMode(isIfm);
        }
        const elBaruQty = document.getElementById('karton_baru_qty');
        const elBekasQty = document.getElementById('karton_bekas_qty');
        if (qtyKarton > 0 && elBekasQty && (!elBaruQty || !elBaruQty.value || parseFloat(elBaruQty.value) === 0)) {
            if (!elBekasQty.value || parseFloat(elBekasQty.value) === 0 || elBekasQty.dataset.syncedByKarton === 'true') {
                elBekasQty.value = qtyKarton;
                elBekasQty.dataset.syncedByKarton = 'true';
                if (typeof window.calcKemasanItem === 'function') {
                    window.calcKemasanItem('karton_bekas_qty');
                }
            }
        }

        // Update highlight dokumen pengeluaran gudang yang cocok
        window.highlightMatchingPakai();

        if (typeof window.calcAll === 'function') {
            window.calcAll();
        }
    };

    // Handler Interaktif saat Lini Produksi Berubah:
    // Otomatis menyesuaikan produk default di tabel hasil produksi, nomor batch, dan kartu kemasan
    window.onLiniProduksiChange = function () {
        const liniSelect = document.getElementById('lini_produksi');
        const lini = (liniSelect?.value || '').toUpperCase();
        const isIfm = lini.includes('IFM') || lini.includes('IFL') || lini.includes('INDOFOOD');

        // Sesuaikan mode kemasan karton sirkular (IFM vs Manual)
        if (typeof window.updateKartonMode === 'function') {
            window.updateKartonMode(isIfm);
        }

        // Auto-select baris pertama di tabel hasil produksi sesuai lini yang dipilih
        const tbody = document.getElementById('tbodyOutputFg');
        if (tbody) {
            if (tbody.querySelectorAll('.row-output-fg').length === 0) {
                window.addFgRow();
            } else {
                autoSelectLiniBarang(0);
            }
        }

        // Pastikan setiap baris tabel memiliki state batch yang konsisten dengan Master Switch
        const chkBatchEl = document.getElementById('chk_enable_batch');
        const isMasterOffNow = (window._batchOption === 'exclude') || (chkBatchEl && !chkBatchEl.checked);
        document.querySelectorAll('.badge-batch-wip').forEach(el => {
            if (!el.dataset.batchState) {
                el.dataset.batchState = isMasterOffNow ? 'excluded' : 'active';
            }
        });

        window.updateKartonRangeAndBatch();
        window.syncBatchToOutputItems();
        window.updateStickerPreview();
        window.syncLaborFromKaryawan(false, true);
    };

    // 4. Update Tampilan Stiker Karton Fisik / Kemasan Retail
    window.updateStickerPreview = function () {
        const shift = document.getElementById('shift_cd')?.value || 'A';
        const noAwal = parseInt(document.getElementById('no_karton_awal')?.value) || 1;
        const qtyKarton = parseInt(document.getElementById('qty_karton')?.value) || 0;
        const tgl = document.getElementById('produksi_tgl')?.value || '';
        const jam = document.getElementById('jam_produksi')?.value || '14:03';
        const varietas = document.getElementById('varietas_singkong')?.value || 'STP / MGU';
        const liniSelect = document.getElementById('lini_produksi');
        const selectedOpt = liniSelect?.selectedOptions?.[0];
        const lini = liniSelect?.value || '';

        // Deteksi produk terpilih di baris pertama tabel output hasil produksi
        const firstFgSel = document.querySelector('#tableOutputFg tbody tr:first-child .select-fg-barang')
            || document.querySelector('.select-fg-barang');
        const firstOpt = firstFgSel?.selectedOptions?.[0];
        const prodCd = (firstOpt?.dataset?.cd || firstOpt?.getAttribute('data-cd') || '').toUpperCase();
        const prodNm = (firstOpt?.dataset?.nm || firstOpt?.getAttribute('data-nm') || firstOpt?.textContent || '').trim();
        const prodSatuan = (firstOpt?.dataset?.satuan || firstOpt?.getAttribute('data-satuan') || '').trim();

        // Format Standar Indofood IFM Box 6 Kg:
        // 1. Jika produk sudah dipilih di tabel: aktif HANYA jika produknya adalah IFM / WIP-FCC
        // 2. Jika produk belum dipilih (baru pilih Lini Produksi): default ke standar karton pabrik kecuali memilih lini non-IFM
        const hasExplicitProduct = Boolean(firstOpt && firstOpt.value);
        let isIfm = false;
        if (hasExplicitProduct) {
            isIfm = Boolean(
                prodCd.includes('FCC') || 
                prodCd.includes('IFM') || 
                prodNm.toUpperCase().includes('WIP-FCC') || 
                (prodNm.toUpperCase().includes('IFM') && !prodNm.toUpperCase().includes('MANUAL')) ||
                (prodNm.toUpperCase().includes('TANPA BUMBU') && !prodNm.toUpperCase().includes('JUMBO'))
            );
        } else {
            const isNonIfmLini = Boolean(
                lini && (
                    lini.toUpperCase().includes('MANUAL') || 
                    lini.toUpperCase().includes('BERKO') || 
                    lini.toUpperCase().includes('BARCO') || 
                    lini.toUpperCase().includes('SAWIT') || 
                    lini.toUpperCase().includes('NO SALT') || 
                    lini.toUpperCase().includes('BALQI') ||
                    lini.toUpperCase().includes('RETAIL')
                ) && !lini.toUpperCase().includes('IFL') && !lini.toUpperCase().includes('IFM')
            );
            isIfm = !isNonIfmLini;
        }

        // Format tanggal sticker DD MMM YYYY (cth: 19 AUG 2022 / 02 OKT 2026)
        const monthNamesUpper = ['JAN', 'FEB', 'MAR', 'APR', 'MEI', 'JUN', 'JUL', 'AUG', 'SEP', 'OKT', 'NOV', 'DES'];
        let tglFormatted = '-';
        let expFormatted = '-';

        if (tgl) {
            const parts = tgl.split('-');
            if (parts.length === 3) {
                const day = parts[2];
                const monIdx = parseInt(parts[1]) - 1;
                const year = parts[0];
                tglFormatted = `${day} ${monthNamesUpper[monIdx] || parts[1]} ${year}`;

                // Hitung kadaluarsa
                const dObj = new Date(parseInt(year), monIdx, parseInt(day));
                if (isIfm) {
                    dObj.setMonth(dObj.getMonth() + 6);
                } else {
                    dObj.setFullYear(dObj.getFullYear() + 1);
                    dObj.setDate(dObj.getDate() - 1);
                }
                const expD = String(dObj.getDate()).padStart(2, '0');
                const expM = monthNamesUpper[dObj.getMonth()] || String(dObj.getMonth() + 1);
                const expY = dObj.getFullYear();
                expFormatted = `${expD} ${expM} ${expY}`;
            }
        }

        const batchCode = document.getElementById('batch_wip_no')?.value || tglFormatted;

        try {
            // Update Sticker DOM Elements
            const stMainTitle = document.getElementById('stMainTitle');
            const stNetto = document.getElementById('stNetto');
            const stGross = document.getElementById('stGross');
            const stVarietas = document.getElementById('stVarietas');
            const stDate = document.getElementById('stDate');
            const stExpDate = document.getElementById('stExpDate');
            const stShiftKarton = document.getElementById('stShiftKarton');
            const stTime = document.getElementById('stTime');
            const stPlantCode = document.getElementById('stPlantCode');

            if (isIfm) {
                if (stMainTitle) stMainTitle.textContent = 'WIP-FCC';
                if (stNetto) stNetto.textContent = '6 kg';
                if (stGross) stGross.textContent = '7.08 kg';
                if (stShiftKarton) {
                    // Format persis fisik pabrik: A / 0001 atau B / 2063 (4 digit leading zeros)
                    const cartonNum = noAwal > 0 ? noAwal : 1;
                    const paddedNo = String(cartonNum).padStart(4, '0');
                    stShiftKarton.textContent = `${shift} / ${paddedNo}`;
                }
                if (stPlantCode) stPlantCode.textContent = 'M029 / - / ISA';
            } else {
                // Mode Barang Jadi Reguler Mirasa (Jumbo, Maksi, Ping-Ping, dll)
                const cleanName = prodNm ? prodNm.replace(/^\[.*?\]\s*/, '') : (lini ? lini.replace('PRODUKSI ', '') : 'KEMASAN RETAIL');
                if (stMainTitle) stMainTitle.textContent = cleanName.toUpperCase();
                if (stNetto) stNetto.textContent = prodSatuan ? prodSatuan.toUpperCase() : 'KEMASAN BAL';
                if (stGross) stGross.textContent = 'STANDAR';
                if (stShiftKarton) {
                    const cartonNum = noAwal > 0 ? noAwal : 1;
                    const paddedNo = String(cartonNum).padStart(4, '0');
                    stShiftKarton.textContent = `${shift} / ${paddedNo}`;
                }
                if (stPlantCode) stPlantCode.textContent = 'MIRASA / FG';
            }

            if (stVarietas) stVarietas.textContent = varietas;
            if (stDate) stDate.textContent = tglFormatted;
            if (stExpDate) stExpDate.textContent = expFormatted;
            if (stTime) stTime.textContent = jam.replace(':', '.');
        } catch (err) {
            console.warn('Gagal memuat pratinjau stiker:', err);
        }
    };

    // 4.B Sinkronisasi Data Tenaga Kerja (Dialihkan ke formulir penetapan HPP Keuangan: /produksi/hpp/create)
    window.syncLaborFromKaryawan = function () {
        // No-op di formulir operator fisik produksi. Dialihkan ke HPP Keuangan.
    };

    window.onTkJumlahChange = function () {
        // No-op
    };

    // 5. Salin Hasil Karton ke Timbangan Output WIP Kg
    window.copyKartonToWipKg = function (targetField = 'asin_barco_qty') {
        const qtyKarton = parseInt(document.getElementById('qty_karton')?.value) || 0;
        if (qtyKarton <= 0) {
            alert('Silakan isi jumlah karton terlebih dahulu.');
            return;
        }

        const totalKg = qtyKarton * 6.0; // Standar 6 kg per karton
        const el = document.getElementById(targetField);
        if (el) {
            el.value = totalKg;
            el.dispatchEvent(new Event('input', { bubbles: true }));
            window.calcAll();

            // Efek highlight visual
            el.style.transition = 'all 0.3s ease';
            el.style.backgroundColor = '#dcfce7';
            setTimeout(() => {
                el.style.backgroundColor = '';
            }, 800);
        }
    };

    // 6. Update Hari Label & Nomor Dokumen Produksi jika tanggal berubah
    window.updateHariLabel = function () {
        const val = document.getElementById('produksi_tgl')?.value;
        if (!val) return;
        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const d = new Date(val);
        const dayNm = days[d.getDay()];
        const el = document.getElementById('hariLabel');
        if (el) el.textContent = `Hari: ${dayNm}`;
    };

    let userHasManuallyEditedProduksiNo = false;
    document.getElementById('produksi_no')?.addEventListener('input', () => {
        userHasManuallyEditedProduksiNo = true;
    });

    window.fetchAndApplyProduksiNo = function (targetDateStr) {
        const prodNoInput = document.getElementById('produksi_no');
        const prodNoHelp = document.getElementById('produksi_no_help');
        if (!prodNoInput || !targetDateStr) return;

        // Jika pengguna sudah mengetik nomor manual sendiri, jangan ditimpa otomatis
        if (userHasManuallyEditedProduksiNo && prodNoInput.value.trim() !== '') {
            return;
        }

        prodNoInput.style.opacity = '0.6';
        if (prodNoHelp) {
            prodNoHelp.style.display = 'block';
            prodNoHelp.innerHTML = `<span style="color:#0284c7;">⚡ Memeriksa urutan No. PRD...</span>`;
        }

        fetch(`/ajax/generate-code?type=prd&date=${encodeURIComponent(targetDateStr)}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success' && data.code) {
                    prodNoInput.value = data.code;
                    prodNoInput.style.background = '#f0fdf4';
                    setTimeout(() => { prodNoInput.style.background = '#ffffff'; }, 1000);
                }
            })
            .catch(err => console.error('Error fetchAndApplyProduksiNo:', err))
            .finally(() => {
                prodNoInput.style.opacity = '1';
                if (prodNoHelp) prodNoHelp.style.display = 'none';
            });
    };

    // 7. Hitung Biaya Gas CNG (Integrasi Otomatis Kurs JISDOR, Nominal Harian, & Fleksibel Edit/0)
    let userHasManuallyEditedKurs = false;

    // Deteksi jika user mengetik sendiri angka pada input kurs JISDOR
    document.addEventListener('DOMContentLoaded', () => {
        const inputKurs = document.getElementById('cng_kurs_jisdor');
        if (inputKurs) {
            inputKurs.addEventListener('input', () => {
                userHasManuallyEditedKurs = true;
            });
        }
    });

    // Otomatis tarik kurs JISDOR saat tanggal produksi berubah (tanpa tombol)
    window.fetchKursJisdor = function (forceUpdate = false) {
        const elDate = document.getElementById('produksi_tgl');
        const elKurs = document.getElementById('cng_kurs_jisdor');
        if (!elDate || !elKurs || !window.appConfig?.kursJisdorUrl) return;

        // Jika user secara sengaja telah mengedit kurs secara manual (misal diganti 0), jangan ditimpa
        if (userHasManuallyEditedKurs && !forceUpdate) return;

        const dateVal = elDate.value;
        if (!dateVal) return;

        fetch(`${window.appConfig.kursJisdorUrl}?date=${encodeURIComponent(dateVal)}&tgl=${encodeURIComponent(dateVal)}`)
            .then(res => res.json())
            .then(res => {
                const kursVal = (res && res.kurs !== undefined) ? res.kurs : (res && res.rate !== undefined ? res.rate : undefined);
                if (kursVal !== undefined && (!userHasManuallyEditedKurs || forceUpdate)) {
                    elKurs.value = kursVal;
                    window.calcCng('kurs');

                    const elText = document.getElementById('cng_calc_text');
                    if (elText && res.tanggal_acuan) {
                        const currentHtml = elText.innerHTML;
                        if (!currentHtml.includes('Acuan BI')) {
                            elText.innerHTML += ` &bull; <span style="color: #0284c7; font-weight: 600;">(Acuan BI: ${res.tanggal_acuan})</span>`;
                        }
                    }
                }
            })
            .catch(err => {
                console.warn('Gagal memuat kurs JISDOR harian otomatis:', err);
            });
    };

    window.calcCng = function (fromField) {
        const elMmbtu = document.getElementById('cng_mmbtu');
        const elUsd = document.getElementById('cng_tarif_usd');
        const elKurs = document.getElementById('cng_kurs_jisdor');
        const elTarif = document.getElementById('cng_tarif');
        const elNilai = document.getElementById('cng_nilai');
        const elText = document.getElementById('cng_calc_text');

        const usd = parseFloat(elUsd?.value) || 0;
        const rawKurs = elKurs?.value;
        const kurs = (rawKurs !== '' && !isNaN(rawKurs)) ? parseFloat(rawKurs) : 0;
        const mmbtu = parseFloat(elMmbtu?.value) || 0;

        // Jika perubahan berasal dari USD atau Kurs JISDOR:
        if (fromField === 'usd' || fromField === 'kurs' || (!fromField && (!elTarif?.value || elTarif?.value == 0))) {
            if (elTarif) {
                if (kurs === 0) {
                    // Jika kurs diisi 0, otomatis tarif jadi 0 saat event kurs dipicu
                    if (fromField === 'kurs') {
                        elTarif.value = 0;
                    }
                } else if (usd > 0) {
                    elTarif.value = Math.round(usd * kurs);
                }
            }
        }

        const tarif = parseFloat(elTarif?.value) || 0;
        const totalNilai = Math.round(mmbtu * tarif);

        // Jika perubahan bukan dari input nominal manual, otomatis hitung nominal harian
        if (elNilai && fromField !== 'nilai') {
            elNilai.value = totalNilai;
        }

        // Tampilkan simulasi interaktif
        if (elText) {
            const formatRp = (num) => Math.round(num).toLocaleString('id-ID');
            if (kurs === 0) {
                elText.innerHTML = `⚠️ <strong>Kurs JISDOR diisi 0.</strong> Tarif: <strong>Rp ${formatRp(tarif)}/MMBTU</strong> &bull; Nominal: <strong>Rp ${formatRp(parseFloat(elNilai?.value) || 0)}</strong> (Tarif/Nominal harian dapat diisi manual jika sistem flat rate).`;
            } else {
                elText.innerHTML = `💡 <strong>Kontrak Gas:</strong> $${usd.toFixed(2)} &times; Kurs JISDOR Rp ${formatRp(kurs)} = <strong>Rp ${formatRp(tarif)}/MMBTU</strong> &bull; <strong>Nominal Harian:</strong> ${mmbtu.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 4 })} MMBTU = <strong style="color: #059669;">Rp ${formatRp(totalNilai)}</strong>`;
            }
        }

        window.calcAll();
    };

    // 8. Hitung Biaya Tenaga Kerja
    window.calcTk = function () {
        const lgs = parseInt(document.getElementById('tk_langsung_org')?.value) || 0;
        const tdk = parseInt(document.getElementById('tk_tidak_langsung_org')?.value) || 0;
        const trn = parseInt(document.getElementById('tk_training_org')?.value) || 0;
        const tarif = parseFloat(document.getElementById('tk_tarif_per_org')?.value) || 91300;
        const el = document.getElementById('tk_total_nilai');
        if (el) el.value = Math.round((lgs + tdk + trn) * tarif);
        window.calcAll();
    };

    // 9. Standar Harga Satuan & Hitung Otomatis Bahan Kemasan & Pembantu (Master Data Integration)
    const initialKartonRates = window.appConfig?.kartonRates || {};
    const defaultKartonBaruPrice = parseFloat(initialKartonRates.baru) || 8741;
    const defaultKartonPuteranPrice = parseFloat(initialKartonRates.puteran) || 1748.20;

    window.standarHargaKemasan = {
        bumbu_qty: 35000,
        karton_baru_qty: defaultKartonBaruPrice,
        karton_bekas_qty: defaultKartonPuteranPrice,
        plastik_hd_qty: 29400,
        lakban_besar_qty: 15000,
        lakban_kecil_qty: 6140,
        tali_rafia_qty: 10000
    };

    const kemasanKeyMap = {
        bumbu_qty: 'bumbu_nilai',
        karton_baru_qty: 'karton_baru_nilai',
        karton_bekas_qty: 'karton_bekas_nilai',
        plastik_hd_qty: 'plastik_hd_nilai',
        lakban_besar_qty: 'lakban_besar_nilai',
        lakban_kecil_qty: 'lakban_kecil_nilai',
        tali_rafia_qty: 'tali_rafia_nilai'
    };

    // Mode Kemasan Karton Sirkular (Lini IFM vs Lini Manual)
    window.updateKartonMode = function (isIfm) {
        const rates = window.appConfig?.kartonRates || { baru: 8741, puteran: 1748.20, reject: 0 };
        const cardBaru = document.getElementById('card_karton_baru_item');
        const lblBaruTitle = document.getElementById('label_karton_baru_title');
        const badgeBaruDesc = document.getElementById('badge_karton_baru_desc');
        const lblPuteranTitle = document.getElementById('label_karton_puteran_title');
        const badgePuteranDesc = document.getElementById('badge_karton_puteran_desc');
        const badgePuteranType = document.getElementById('badge_karton_puteran_type');
        const inputBaruQty = document.getElementById('karton_baru_qty');
        const inputBaruNilai = document.getElementById('karton_baru_nilai');
        const inputBekasQty = document.getElementById('karton_bekas_qty');
        const inputBekasNilai = document.getElementById('karton_bekas_nilai');

        const formatRp = (n) => 'Rp ' + Math.round(n).toLocaleString('id-ID');

        if (isIfm) {
            // MODE LINI IFM:
            // Hanya gunakan Karton Sirkular IFM (Tarif Flat 1/5 Siklus: Rp 1.748/box)
            // Sembunyikan opsi Karton Baru agar HPP IFM tidak melonjak/kacau
            if (cardBaru) cardBaru.style.display = 'none';
            if (inputBaruQty) inputBaruQty.value = 0;
            if (inputBaruNilai) inputBaruNilai.value = 0;

            if (lblPuteranTitle) lblPuteranTitle.textContent = 'Karton Sirkular IFM';
            if (badgePuteranDesc) {
                badgePuteranDesc.innerHTML = 'Standar Sirkular 5x Pakai';
                badgePuteranDesc.style.color = '#059669';
            }
            if (badgePuteranType) {
                badgePuteranType.textContent = 'Sirkular 5x';
                badgePuteranType.style.background = '#ecfdf5';
                badgePuteranType.style.color = '#059669';
                badgePuteranType.style.borderColor = '#a7f3d0';
            }

            window.standarHargaKemasan.karton_baru_qty = 0;
            window.standarHargaKemasan.karton_bekas_qty = parseFloat(rates.puteran) || 1748.20;

            if (inputBekasNilai) inputBekasNilai.readOnly = false;
        } else {
            // MODE LINI MANUAL:
            // 1. Karton Reject / Rusak (Default Rp 0)
            // 2. Karton Box Baru (Opsi tambahan jika ada pesanan khusus baru)
            if (cardBaru) cardBaru.style.display = 'block';
            if (lblBaruTitle) lblBaruTitle.textContent = 'Karton Box Baru (Opsional)';
            if (badgeBaruDesc) {
                badgeBaruDesc.innerHTML = 'Khusus Pesanan Baru Sekali Pakai';
                badgeBaruDesc.style.color = '#b45309';
            }

            if (lblPuteranTitle) lblPuteranTitle.textContent = 'Karton Reject / Rusak (Ex-IFM)';
            if (badgePuteranDesc) {
                badgePuteranDesc.innerHTML = 'Limbah Sirkulasi Lunas di IFM';
                badgePuteranDesc.style.color = '#0284c7';
            }
            if (badgePuteranType) {
                badgePuteranType.textContent = 'Reject Rp 0';
                badgePuteranType.style.background = '#f0f9ff';
                badgePuteranType.style.color = '#0284c7';
                badgePuteranType.style.borderColor = '#bae6fd';
            }

            window.standarHargaKemasan.karton_baru_qty = parseFloat(rates.baru) || 8741;
            window.standarHargaKemasan.karton_bekas_qty = 0;

            if (inputBekasNilai) {
                inputBekasNilai.value = 0;
                inputBekasNilai.readOnly = true;
            }
        }

        if (typeof window.calcKemasanItem === 'function') {
            window.calcKemasanItem('karton_bekas_qty');
            window.calcKemasanItem('karton_baru_qty');
        }
    };

    window.calcKemasanItem = function (qtyInputId) {
        const valInputId = kemasanKeyMap[qtyInputId];
        if (!valInputId) return;

        const qtyEl = document.getElementById(qtyInputId);
        const valEl = document.getElementById(valInputId);
        if (!qtyEl || !valEl) return;

        const qty = parseFloat(qtyEl.value) || 0;
        const unitPrice = window.standarHargaKemasan[qtyInputId] || 0;

        if (qty > 0) {
            valEl.value = Math.round(qty * unitPrice);
        } else {
            valEl.value = 0;
        }

        window.calcAll();
    };

    window.initAutoKemasanCalc = function () {
        Object.keys(kemasanKeyMap).forEach(qtyId => {
            const qtyEl = document.getElementById(qtyId);
            const valId = kemasanKeyMap[qtyId];
            const valEl = document.getElementById(valId);

            if (qtyEl) {
                qtyEl.addEventListener('input', () => {
                    delete qtyEl.dataset.syncedByKarton;
                    window.calcKemasanItem(qtyId);
                });
            }

            if (valEl && qtyEl) {
                valEl.addEventListener('input', () => {
                    const currentQty = parseFloat(qtyEl.value) || 0;
                    const currentVal = parseFloat(valEl.value) || 0;
                    if (currentQty > 0 && currentVal > 0) {
                        window.standarHargaKemasan[qtyId] = currentVal / currentQty;
                    }
                    window.calcAll();
                });
            }
        });
    };

    // Standar Tarif Alokasi Biaya Overhead Pabrik (FOH) Dinamis dari Master Data
    const initialFoh = window.appConfig?.initialFohRates || {};
    window.FOH_RATES = {
        QC: parseFloat(initialFoh.qc) || 49.97,
        LISTRIK: parseFloat(initialFoh.listrik) || 223.80,
        PEMELIHARAAN: parseFloat(initialFoh.pemeliharaan) || 23.34,
        PENYUSUTAN: parseFloat(initialFoh.penyusutan) || 66.44,
        KIMIA: parseFloat(initialFoh.kimia) || 45.09,
        FOTOCOPY: parseFloat(initialFoh.fotocopy) || 28.00,
        LIMBAH_PADAT_SHIFT: parseFloat(initialFoh.limbah_padat) || 180000.00
    };

    // Quick Modal Handlers
    window.openQuickTarifModal = function () {
        const modal = document.getElementById('modalQuickTarif');
        if (modal) {
            modal.style.display = 'flex';
        }
    };

    window.closeQuickTarifModal = function () {
        const modal = document.getElementById('modalQuickTarif');
        if (modal) {
            modal.style.display = 'none';
        }
    };

    window.submitQuickTarif = function () {
        const btn = document.getElementById('btnSaveQuickTarif');
        const form = document.getElementById('formQuickTarif');
        if (!form) return;

        const formData = new FormData(form);
        const url = window.appConfig?.quickUpdateTarifUrl;
        if (!url) return;

        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span>Menyimpan...</span>';
        }

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg><span>Simpan &amp; Terapkan ke Form</span>';
            }

            if (data.success) {
                // Update window.FOH_RATES
                if (data.foh_rates) {
                    window.FOH_RATES.QC = parseFloat(data.foh_rates.qc) || window.FOH_RATES.QC;
                    window.FOH_RATES.LISTRIK = parseFloat(data.foh_rates.listrik) || window.FOH_RATES.LISTRIK;
                    window.FOH_RATES.PEMELIHARAAN = parseFloat(data.foh_rates.pemeliharaan) || window.FOH_RATES.PEMELIHARAAN;
                    window.FOH_RATES.PENYUSUTAN = parseFloat(data.foh_rates.penyusutan) || window.FOH_RATES.PENYUSUTAN;
                    window.FOH_RATES.KIMIA = parseFloat(data.foh_rates.kimia) || window.FOH_RATES.KIMIA;
                    window.FOH_RATES.FOTOCOPY = parseFloat(data.foh_rates.fotocopy) || window.FOH_RATES.FOTOCOPY;
                    window.FOH_RATES.LIMBAH_PADAT_SHIFT = parseFloat(data.foh_rates.limbah_padat) || window.FOH_RATES.LIMBAH_PADAT_SHIFT;
                }

                // Update UI Badges & Labels
                const updateBadge = (id, text) => {
                    const el = document.getElementById(id);
                    if (el) el.textContent = text;
                };

                updateBadge('badge_foh_fotocopy', '2 lbr × Rp ' + formatNumber(window.FOH_RATES.FOTOCOPY, 0));
                updateBadge('sublabel_foh_fotocopy', '(Karton × 2 lbr) × Rp ' + formatNumber(window.FOH_RATES.FOTOCOPY, 0));
                updateBadge('badge_foh_qc', 'x ' + formatNumber(window.FOH_RATES.QC, 2));
                updateBadge('sublabel_foh_qc', 'Rp ' + formatNumber(window.FOH_RATES.QC, 2) + ' / Kg WIP');
                updateBadge('badge_foh_listrik', 'x ' + formatNumber(window.FOH_RATES.LISTRIK, 2));
                updateBadge('sublabel_foh_listrik', 'Rp ' + formatNumber(window.FOH_RATES.LISTRIK, 2) + ' / Kg WIP');
                updateBadge('badge_foh_pemeliharaan', 'x ' + formatNumber(window.FOH_RATES.PEMELIHARAAN, 2));
                updateBadge('sublabel_foh_pemeliharaan', 'Rp ' + formatNumber(window.FOH_RATES.PEMELIHARAAN, 2) + ' / Kg WIP');
                updateBadge('badge_foh_penyusutan', 'x ' + formatNumber(window.FOH_RATES.PENYUSUTAN, 2));
                updateBadge('sublabel_foh_penyusutan', 'Rp ' + formatNumber(window.FOH_RATES.PENYUSUTAN, 2) + ' / Kg WIP');
                updateBadge('badge_foh_kimia', 'x ' + formatNumber(window.FOH_RATES.KIMIA, 2));
                updateBadge('sublabel_foh_kimia', 'Rp ' + formatNumber(window.FOH_RATES.KIMIA, 2) + ' / Kg WIP (IPAL)');
                updateBadge('badge_foh_limbah_padat', 'Flat Rp ' + Math.round(window.FOH_RATES.LIMBAH_PADAT_SHIFT).toLocaleString('id-ID'));

                // Update CNG & TK rates if returned
                if (data.energi_tk) {
                    if (data.energi_tk.cng_tarif) {
                        const elCng = document.getElementById('cng_tarif');
                        if (elCng) elCng.value = data.energi_tk.cng_tarif;
                        window.calcCng();
                    }
                    if (data.energi_tk.tk_tarif_per_org) {
                        const elTk = document.getElementById('tk_tarif_per_org');
                        if (elTk) elTk.value = data.energi_tk.tk_tarif_per_org;
                        const elLabelTk = document.getElementById('label_tk_tarif');
                        if (elLabelTk) elLabelTk.textContent = Math.round(data.energi_tk.tk_tarif_per_org).toLocaleString('id-ID');
                        window.calcTk();
                    }
                }

                // Recalculate FOH with new rates
                window.recalcFohStandard(true);
                window.calcAll();

                closeQuickTarifModal();

                if (window.Swal) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Tarif Berhasil Diperbarui!',
                        text: 'Standar pengali FOH dan tarif acuan langsung diterapkan ke formulir.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            } else {
                alert('Gagal menyimpan tarif: ' + (data.message || 'Terjadi kesalahan sistem'));
            }
        })
        .catch(err => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg><span>Simpan &amp; Terapkan ke Form</span>';
            }
            alert('Terjadi kesalahan jaringan: ' + err.message);
        });
    };

    // Helper: Hitung kuantitas Karton IFL/IFM untuk dasar FOH Fotocopy (Rumus Excel: =(AF/6)*2*$V$2)
    function getKartonIflCount(totalOutputKg = 0) {
        let count = parseFloat(document.getElementById('qty_karton')?.value) || 0;
        if (count <= 0) {
            let foundIfl = false;
            document.querySelectorAll('.row-output-fg').forEach(tr => {
                const sel = tr.querySelector('.select-fg-barang');
                const opt = sel?.selectedOptions?.[0];
                const cd = (opt?.dataset?.cd || '').toUpperCase();
                const nm = (opt?.dataset?.nm || '').toUpperCase();
                const st = (opt?.dataset?.satuan || '').toUpperCase();
                const qtyInp = tr.querySelector('[id^="qtyHasil_"]');
                const kgInp = tr.querySelector('[id^="qtyKg_"]');
                if (cd.includes('FCC') || cd.includes('IFL') || cd.includes('IFM') || nm.includes('IFL') || nm.includes('IFM') || st.includes('KARTON')) {
                    foundIfl = true;
                    if (qtyInp && parseFloat(qtyInp.value) > 0) {
                        count += parseFloat(qtyInp.value);
                    } else if (kgInp && parseFloat(kgInp.value) > 0) {
                        count += (parseFloat(kgInp.value) / 6.0);
                    }
                }
            });
            // Fallback jika lini IFL/IFM dipilih dan ada output Kg
            if (!foundIfl && totalOutputKg > 0) {
                const lini = (document.getElementById('lini_produksi')?.value || '').toUpperCase();
                if (lini.includes('IFL') || lini.includes('IFM') || lini.includes('INDOFOOD')) {
                    count = totalOutputKg / 6.0;
                }
            }
        }
        return count;
    }

    // Fungsi untuk mereset dan menghitung ulang seluruh nilai FOH (Dinonaktifkan / diset 0 karena FOH dialihkan ke Jurnal Keuangan)
    window.recalcFohStandard = function (forceReset = false) {
        const syncField = (id, val) => {
            const el = document.getElementById(id);
            if (!el) return;
            el.value = val;
            if (forceReset) delete el.dataset.userModified;
        };

        syncField('qc_pengawasan_nilai', 0);
        syncField('limbah_kimia_nilai', 0);
        syncField('fotocopy_nilai', 0);
        syncField('sarung_tangan_plastik_nilai', 0);
        syncField('sarung_tangan_kain_nilai', 0);
        syncField('listrik_air_telp_nilai', 0);
        syncField('pemeliharaan_mesin_nilai', 0);
        syncField('penyusutan_mesin_nilai', 0);
        syncField('limbah_padat_nilai', 0);

        window.calcAll();
    };

    // 9. Kalkulasi Menyeluruh (All Fields, Rendemen, HPP)
    window.calcAll = function () {
        // 1. Bahan
        const singkongQty = parseFloat(document.getElementById('singkong_qty')?.value) || 0;
        const singkongNilai = parseFloat(document.getElementById('singkong_nilai')?.value) || 0;
        const sawitQty = parseFloat(document.getElementById('minyak_sawit_qty')?.value) || 0;
        const kelapaQty = parseFloat(document.getElementById('minyak_kelapa_qty')?.value) || 0;
        const minyakNilai = parseFloat(document.getElementById('minyak_nilai')?.value) || 0;

        const bumbu = parseFloat(document.getElementById('bumbu_nilai')?.value) || 0;
        const kBaru = parseFloat(document.getElementById('karton_baru_nilai')?.value) || 0;
        const kBekas = parseFloat(document.getElementById('karton_bekas_nilai')?.value) || 0;
        const plastik = parseFloat(document.getElementById('plastik_hd_nilai')?.value) || 0;
        const lakbanB = parseFloat(document.getElementById('lakban_besar_nilai')?.value) || 0;
        const lakbanK = parseFloat(document.getElementById('lakban_kecil_nilai')?.value) || 0;
        const tali = parseFloat(document.getElementById('tali_rafia_nilai')?.value) || 0;

        const subtotalBahan = singkongNilai + minyakNilai + bumbu + kBaru + kBekas + plastik + lakbanB + lakbanK + tali;
        const badgeBahan = document.getElementById('badgeSubtotalBahan');
        if (badgeBahan) badgeBahan.textContent = 'Subtotal Bahan: ' + formatRupiah(subtotalBahan);

        // Rasio Minyak
        const rasioMinyak = singkongQty > 0 ? ((sawitQty + kelapaQty) / singkongQty) * 100 : 0;
        const elMinyakRasio = document.getElementById('liveMinyakRasio');
        if (elMinyakRasio) elMinyakRasio.textContent = 'Rasio Minyak: ' + rasioMinyak.toFixed(2) + '%';

        // 2. Energi CNG & TK
        const cngNilai = parseFloat(document.getElementById('cng_nilai')?.value) || 0;
        const tkNilai = parseFloat(document.getElementById('tk_total_nilai')?.value) || 0;

        // 3. Output Hasil Produksi (Tabel Terpadu FG & WIP)
        let totalOutputKg = 0;
        let totalBerkoKg = 0;
        let totalLengketKg = 0;
        let totalRemahanKg = 0;

        document.querySelectorAll('.row-output-fg').forEach(row => {
            const kgInp = row.querySelector('.input-fg-qty-kg');
            const kgVal = parseFloat(kgInp?.value) || 0;
            if (kgVal <= 0) return;

            totalOutputKg += kgVal;

            const selBarang = row.querySelector('.select-fg-barang');
            const opt = selBarang?.selectedOptions?.[0];
            const nm = (opt?.dataset?.nm || opt?.getAttribute('data-nm') || '').toUpperCase();
            const cd = (opt?.dataset?.cd || opt?.getAttribute('data-cd') || '').toUpperCase();

            if (cd.includes('LGT') || nm.includes('LENGKET')) {
                totalLengketKg += kgVal;
            } else if (cd.includes('RMH') || nm.includes('REMAHAN')) {
                totalRemahanKg += kgVal;
            } else if (cd.includes('BRK') || nm.includes('BERKO')) {
                totalBerkoKg += kgVal;
            }
        });

        // Hitung dan perbarui persentase porsi fisik per baris hasil produksi secara otomatis
        let totalPorsiPct = 0;
        document.querySelectorAll('.row-output-fg').forEach(row => {
            const kgInp = row.querySelector('.input-fg-qty-kg');
            const kgVal = parseFloat(kgInp?.value) || 0;
            const porsiEl = row.querySelector('.label-fg-porsi');
            if (porsiEl) {
                const porsiPct = totalOutputKg > 0 ? (kgVal / totalOutputKg) * 100 : 0;
                totalPorsiPct += porsiPct;
                porsiEl.textContent = formatNumber(porsiPct, 2) + '%';
            }
        });

        // Ringkasan Total Output & Porsi pada Badge Header Atas
        const badgeTotalOutput = document.getElementById('badgeTotalOutputKg');
        if (badgeTotalOutput) {
            badgeTotalOutput.textContent = 'Total Output: ' + formatNumber(totalOutputKg, 2) + ' kg';
        }

        const badgeTotalPorsi = document.getElementById('badgeTotalPorsi');
        if (badgeTotalPorsi) {
            if (totalOutputKg > 0) {
                badgeTotalPorsi.textContent = 'Total Porsi: 100,00%';
                badgeTotalPorsi.style.display = 'inline-block';
            } else {
                badgeTotalPorsi.style.display = 'none';
            }
        }

        const badgeBerko = document.getElementById('badgeBerkoOutput');
        if (badgeBerko) {
            if (totalBerkoKg > 0) {
                const berkoPct = totalOutputKg > 0 ? (totalBerkoKg / totalOutputKg) * 100 : 0;
                badgeBerko.textContent = 'Berko: ' + formatNumber(totalBerkoKg, 2) + ' kg (' + formatNumber(berkoPct, 1) + '%)';
                badgeBerko.style.display = 'inline-block';
            } else {
                badgeBerko.style.display = 'none';
            }
        }

        const badgeRemahan = document.getElementById('badgeRemahanOutput');
        if (badgeRemahan) {
            if (totalRemahanKg > 0) {
                const remahanPct = totalOutputKg > 0 ? (totalRemahanKg / totalOutputKg) * 100 : 0;
                badgeRemahan.textContent = 'Remahan: ' + formatNumber(totalRemahanKg, 2) + ' kg (' + formatNumber(remahanPct, 1) + '%)';
                badgeRemahan.style.display = 'inline-block';
            } else {
                badgeRemahan.style.display = 'none';
            }
        }

        const badgeLengket = document.getElementById('badgeLengketOutput');
        if (badgeLengket) {
            if (totalLengketKg > 0) {
                let txt = 'Limbah Lengket: ' + formatNumber(totalLengketKg, 2) + ' kg';
                if (singkongQty > 0) {
                    const lengketPct = (totalLengketKg / singkongQty) * 100;
                    txt += ' (' + formatNumber(lengketPct, 1) + '%)';
                }
                badgeLengket.textContent = txt;
                badgeLengket.style.display = 'inline-block';
            } else {
                badgeLengket.style.display = 'none';
            }
        }

        // Sinkronisasi Hidden Inputs untuk Limbah HACCP
        const inpLengket = document.getElementById('waste_lengket_qty');
        if (inpLengket) inpLengket.value = totalLengketKg > 0 ? totalLengketKg.toFixed(4) : (inpLengket.value || 0);

        const inpLengketPct = document.getElementById('waste_lengket_persen');
        if (inpLengketPct) {
            const pct = singkongQty > 0 ? (totalLengketKg / singkongQty) * 100 : 0;
            inpLengketPct.value = pct.toFixed(2);
        }

        const inpWasteTot = document.getElementById('waste_total_qty');
        if (inpWasteTot) {
            inpWasteTot.value = (totalLengketKg + totalBerkoKg).toFixed(4);
        }

        const liniVal = (document.getElementById('lini_produksi')?.value || '').toUpperCase();
        const inpBerkoIfl = document.getElementById('berko_ifl_qty');
        const inpBerkoNonIfl = document.getElementById('berko_non_ifl_qty');
        if (liniVal.includes('IFL') || liniVal.includes('IFM')) {
            if (inpBerkoIfl) inpBerkoIfl.value = totalBerkoKg.toFixed(4);
            if (inpBerkoNonIfl) inpBerkoNonIfl.value = '0';
        } else {
            if (inpBerkoIfl) inpBerkoIfl.value = '0';
            if (inpBerkoNonIfl) inpBerkoNonIfl.value = totalBerkoKg.toFixed(4);
        }

        // 4. Biaya Overhead Pabrik (FOH) dialihkan ke Jurnal Keuangan / Accounting (Tidak masuk HPP Harian Produksi)
        // Nilai input FOH disinkronkan ke 0
        const autoZeroFoh = (id) => {
            const el = document.getElementById(id);
            if (el) el.value = 0;
        };
        ['qc_pengawasan_nilai', 'limbah_kimia_nilai', 'fotocopy_nilai', 'sarung_tangan_plastik_nilai', 'sarung_tangan_kain_nilai', 'listrik_air_telp_nilai', 'pemeliharaan_mesin_nilai', 'penyusutan_mesin_nilai', 'limbah_padat_nilai'].forEach(autoZeroFoh);

        // 5. FOH Overhead
        const fc = parseFloat(document.getElementById('fotocopy_nilai')?.value) || 0;
        const stP = parseFloat(document.getElementById('sarung_tangan_plastik_nilai')?.value) || 0;
        const stK = parseFloat(document.getElementById('sarung_tangan_kain_nilai')?.value) || 0;
        const qc = parseFloat(document.getElementById('qc_pengawasan_nilai')?.value) || 0;
        const listrik = parseFloat(document.getElementById('listrik_air_telp_nilai')?.value) || 0;
        const pemlhr = parseFloat(document.getElementById('pemeliharaan_mesin_nilai')?.value) || 0;
        const penys = parseFloat(document.getElementById('penyusutan_mesin_nilai')?.value) || 0;
        const lmbP = parseFloat(document.getElementById('limbah_padat_nilai')?.value) || 0;
        const lmbK = parseFloat(document.getElementById('limbah_kimia_nilai')?.value) || 0;

        const subtotalOverhead = fc + stP + stK + qc + listrik + pemlhr + penys + lmbP + lmbK;
        const badgeFoh = document.getElementById('badgeSubtotalOverhead');
        if (badgeFoh) badgeFoh.textContent = 'Subtotal FOH: ' + formatRupiah(subtotalOverhead);

        // Total Biaya Produksi (Kolom Kuning Emas Excel)
        const totalBiaya = subtotalBahan + cngNilai + tkNilai + subtotalOverhead;
        const elTotalBiaya = document.getElementById('liveTotalBiaya');
        if (elTotalBiaya) elTotalBiaya.textContent = formatRupiah(totalBiaya);

        // 6. Rendemen & HPP
        const rendemen = singkongQty > 0 ? (totalOutputKg / singkongQty) * 100 : 0;
        const hppPerKg = totalOutputKg > 0 ? (totalBiaya / totalOutputKg) : 0;

        // Update Kartu Monitor Fisik Lantai Pabrik
        const elSingkongFisik = document.getElementById('liveSingkongFisik');
        if (elSingkongFisik) elSingkongFisik.textContent = formatNumber(singkongQty, 2) + ' kg';

        const elOutputFisik = document.getElementById('liveOutputFisik');
        if (elOutputFisik) elOutputFisik.textContent = formatNumber(totalOutputKg, 2) + ' kg';

        const elMinyakDisp = document.getElementById('liveMinyakRasioDisplay');
        if (elMinyakDisp) elMinyakDisp.textContent = formatNumber(rasioMinyak, 2) + '%';

        const liveRendemenDisp = document.getElementById('liveRendemenDisplay');
        const liveRendemenStatusDisp = document.getElementById('liveRendemenStatusDisplay');
        if (liveRendemenDisp) liveRendemenDisp.textContent = formatNumber(rendemen, 2) + '%';
        if (liveRendemenStatusDisp) {
            if (rendemen >= 33.0) {
                if (liveRendemenDisp) liveRendemenDisp.style.color = '#16a34a';
                liveRendemenStatusDisp.textContent = 'Rendemen Optimal (≥ 33%)';
                liveRendemenStatusDisp.style.color = '#15803d';
            } else if (rendemen >= 30.0) {
                if (liveRendemenDisp) liveRendemenDisp.style.color = '#d97706';
                liveRendemenStatusDisp.textContent = 'Rendemen Sedang (30% - 33%)';
                liveRendemenStatusDisp.style.color = '#b45309';
            } else {
                if (liveRendemenDisp) liveRendemenDisp.style.color = '#dc2626';
                liveRendemenStatusDisp.textContent = rendemen > 0 ? 'Rendemen Rendah (< 30%)' : 'Menunggu timbangan';
                liveRendemenStatusDisp.style.color = rendemen > 0 ? '#b91c1c' : '#64748b';
            }
        }

        const liveRendemen = document.getElementById('liveRendemen');
        const liveRendemenStatus = document.getElementById('liveRendemenStatus');

        if (liveRendemen) liveRendemen.textContent = formatNumber(rendemen, 2) + '%';
        if (liveRendemenStatus) {
            if (rendemen >= 33.0) {
                if (liveRendemen) liveRendemen.style.color = '#059669';
                liveRendemenStatus.textContent = 'Rendemen Optimal (Di atas target 33%)';
                liveRendemenStatus.style.color = '#059669';
            } else if (rendemen >= 30.0) {
                if (liveRendemen) liveRendemen.style.color = '#d97706';
                liveRendemenStatus.textContent = 'Rendemen Sedang (Target 33%)';
                liveRendemenStatus.style.color = '#d97706';
            } else {
                if (liveRendemen) liveRendemen.style.color = '#dc2626';
                liveRendemenStatus.textContent = rendemen > 0 ? 'Rendemen Rendah' : 'Menunggu timbangan';
                liveRendemenStatus.style.color = rendemen > 0 ? '#dc2626' : '#64748b';
            }
        }

        const elHpp = document.getElementById('liveHppPerKg') || document.getElementById('liveHpp');
        if (elHpp) elHpp.textContent = formatRupiah(hppPerKg) + ' / kg';

        // Hitung dan perbarui tabel Breakdown Alokasi HPP per Barang Hasil Produksi
        if (typeof window.calcHppBreakdown === 'function') {
            window.calcHppBreakdown(totalBiaya, totalOutputKg);
        }
    };

    // ==============================================================
    // 9.B. FITUR ALOKASI & BREAKDOWN HPP PER PRODUK (HYBRID JOINT COSTING)
    // Standar Rasio Mutu (2.5x) & Input Penyesuaian Manual Office
    // ==============================================================
    window.hppBreakdownState = {
        mode: 'CUSTOM', // 'CUSTOM', 'STANDAR_RASIO', 'EQUAL'
        customPrices: {} // idx -> number
    };

    window.applyStandarRasio = function () {
        const selectBasis = document.getElementById('selectBasisBarang');
        if (selectBasis && !selectBasis.value) {
            alert('Silakan pilih baris barang yang menjadi acuan pengali terlebih dahulu pada dropdown di samping tombol Hitung.');
            selectBasis.focus();
            return;
        }
        window.hppBreakdownState.mode = 'STANDAR_RASIO';
        window.hppBreakdownState.customPrices = {};
        window.calcAll();
    };

    window.onSelectBasisChange = function (val) {
        if (val) {
            window.applyStandarRasio();
        } else {
            window.hppBreakdownState.mode = 'CUSTOM';
            window.calcAll();
        }
    };
    // Alias kompatibilitas
    window.applyModeBuIda = window.applyStandarRasio;

    window.resetModeBagiRata = function () {
        window.hppBreakdownState.mode = 'EQUAL';
        window.hppBreakdownState.customPrices = {};
        window.calcAll();
    };

    window.onBreakdownPriceChange = function (idx, rawVal) {
        window.hppBreakdownState.mode = 'CUSTOM';
        const val = parseFloat(rawVal);
        if (isNaN(val) || rawVal === '') {
            delete window.hppBreakdownState.customPrices[idx];
        } else {
            window.hppBreakdownState.customPrices[idx] = val;
        }
        window.calcAll();
    };

    window.resetPrimaryAuto = function (primaryIdx) {
        if (window.hppBreakdownState.customPrices[primaryIdx] !== undefined) {
            delete window.hppBreakdownState.customPrices[primaryIdx];
        }
        window.calcAll();
    };

    window.calcHppBreakdown = function (totalBiaya, totalOutputKg) {
        const tbodyBreakdown = document.getElementById('tbodyBreakdownHpp');
        if (!tbodyBreakdown) return;

        const fgRows = document.querySelectorAll('#tbodyOutputFg tr.row-output-fg');
        const items = [];

        fgRows.forEach(row => {
            const rowId = row.id || '';
            const idxMatch = rowId.match(/\d+/);
            const idx = idxMatch ? idxMatch[0] : 0;

            const selectEl = row.querySelector('.select-fg-barang');
            const qtyKgEl = row.querySelector('.input-fg-qty-kg');
            const qtyHasilEl = row.querySelector(`#qtyHasil_${idx}`);

            const barangId = selectEl?.value || '';
            const selectedOpt = selectEl?.options[selectEl.selectedIndex];
            const barangCd = (selectedOpt?.getAttribute('data-cd') || '').trim().toUpperCase();
            let barangNm = (selectedOpt?.getAttribute('data-nm') || selectedOpt?.text || '').trim();
            const qtyKg = parseFloat(qtyKgEl?.value) || 0;
            const qtyHasil = parseFloat(qtyHasilEl?.value) || 0;
            const satuan = row.querySelector(`#labelSatuan_${idx}`)?.textContent?.trim() || 'KG';

            // Filter ketat: jika barang belum dipilih di Bagian 2, jangan masukkan baris kosong ke daftar alokasi HPP
            if (!barangId || barangNm.startsWith('-- Pilih') || barangNm === '') {
                return;
            }

            let role = 'UTAMA';
            let roleLabel = 'Produk Utama';
            let badgeClass = 'badge-peran-utama';
            let defaultPrice = 16000;

            const checkStr = `${barangCd} ${barangNm}`.toUpperCase();
            if (checkStr.includes('BERKO') || checkStr.includes('BRK')) {
                role = 'BERKO';
                roleLabel = 'Sortir (Berko)';
                badgeClass = 'badge-peran-berko';
                defaultPrice = 16000;
            } else if (checkStr.includes('LENGKET') || checkStr.includes('LGT')) {
                role = 'LENGKET';
                roleLabel = 'Limbah Lengket';
                badgeClass = 'badge-peran-lengket';
                defaultPrice = 5000;
            } else if (checkStr.includes('REMAHAN') || checkStr.includes('RMH') || checkStr.includes('BS') || checkStr.includes('KW')) {
                role = 'REMAHAN';
                roleLabel = 'Remahan / KW';
                badgeClass = 'badge-peran-remahan';
                defaultPrice = 5000;
            } else {
                role = 'UTAMA';
                roleLabel = 'Produk Utama';
                badgeClass = 'badge-peran-utama';
            }

            items.push({
                idx: idx,
                barangId: barangId,
                barangCd: barangCd,
                barangNm: barangNm,
                qtyKg: qtyKg,
                qtyHasil: qtyHasil,
                satuan: satuan,
                role: role,
                roleLabel: roleLabel,
                badgeClass: badgeClass,
                defaultPrice: defaultPrice,
                price: 0,
                totalNilai: 0
            });
        });

        const elTotalKgFoot = document.getElementById('footerBreakdownTotalKg');
        const elTotalNilaiFoot = document.getElementById('footerBreakdownTotalNilai');
        const elTotalPorsiFoot = document.getElementById('footerBreakdownTotalPorsi');
        const toolbarMetode = document.getElementById('toolbarMetodeAlokasi');
        const noticeSingle = document.getElementById('noticeHppSingleProduct');

        // Jika belum ada barang yang dipilih pada Bagian 2 (Empty State Profesional):
        if (items.length === 0) {
            tbodyBreakdown.innerHTML = `
                <tr>
                    <td colspan="7" style="padding: 2.5rem 1rem; text-align: center; color: #94a3b8; background: #ffffff;">
                        <div style="font-size: 0.875rem; font-weight: 700; color: #475569;">Belum Ada Barang Hasil Produksi yang Dipilih</div>
                        <div style="font-size: 0.775rem; color: #94a3b8; margin-top: 0.35rem;">Silakan pilih barang hasil olahan setengah jadi (WIP) pada Bagian 2 di atas untuk mengaktifkan tabel alokasi HPP.</div>
                    </td>
                </tr>
            `;
            if (elTotalKgFoot) elTotalKgFoot.textContent = '0.00 kg';
            if (elTotalNilaiFoot) elTotalNilaiFoot.textContent = 'Rp 0';
            if (toolbarMetode) toolbarMetode.style.display = 'inline-flex';
            if (noticeSingle) noticeSingle.style.display = 'none';
            return;
        }

        // Identifikasi item utama (primary): jika tidak ada yang berlabel UTAMA, jadikan baris pertama sebagai UTAMA
        let primaryIdx = items.findIndex(it => it.role === 'UTAMA');
        if (primaryIdx === -1 && items.length > 0) {
            primaryIdx = 0;
            items[0].role = 'UTAMA';
            items[0].roleLabel = 'Produk Utama';
            items[0].badgeClass = 'badge-peran-utama';
        }

        const primaryItem = primaryIdx !== -1 ? items[primaryIdx] : null;
        const nonPrimaryItems = items.filter(it => it !== primaryItem);
        const totalKgAll = items.reduce((acc, it) => acc + it.qtyKg, 0);

        // Toolbar metode alokasi selalu tampil agar pengguna dapat mengatur rasio & acuan pengali
        if (toolbarMetode) toolbarMetode.style.display = 'inline-flex';
        if (noticeSingle) {
            noticeSingle.style.display = (nonPrimaryItems.length === 0 && items.length > 0) ? 'inline' : 'none';
        }

        // Sinkronisasi opsi dropdown barang acuan pengali (selectBasisBarang)
        const selectBasis = document.getElementById('selectBasisBarang');
        let selectedBasisVal = selectBasis ? selectBasis.value : '';
        if (selectBasis) {
            if (nonPrimaryItems.length > 0) {
                let optionsHtml = '<option value="">-- Pilih Baris Acuan --</option>';
                nonPrimaryItems.forEach(it => {
                    const optVal = String(it.idx);
                    const isSelected = (selectedBasisVal === optVal);
                    const rowNum = parseInt(it.idx) + 1;
                    optionsHtml += `<option value="${optVal}" ${isSelected ? 'selected' : ''}>Baris ${rowNum}: ${it.barangNm}</option>`;
                });

                const currentOpts = Array.from(selectBasis.options).map(o => o.value).join(',');
                const newOpts = [''].concat(nonPrimaryItems.map(it => String(it.idx))).join(',');
                if (currentOpts !== newOpts) {
                    selectBasis.innerHTML = optionsHtml;
                    if (selectedBasisVal) {
                        selectBasis.value = selectedBasisVal;
                    }
                }
            } else {
                // Tampilan netral jika belum ada produk sampingan yang dipilih di Bagian 2
                selectBasis.innerHTML = `<option value="">-- Pilih Baris Acuan --</option>`;
            }
        }

        // Tentukan barang acuan rasio (basisItem) - HANYA jika dipilih eksplisit oleh pengguna
        let basisItem = null;
        if (selectBasis && selectBasis.value) {
            basisItem = items.find(it => String(it.idx) === String(selectBasis.value)) || null;
        }

        // Perhitungan alokasi biaya
        if (totalKgAll <= 0 || totalBiaya <= 0) {
            items.forEach(it => {
                it.price = 0;
                it.totalNilai = 0;
            });
        } else if (items.length === 1 || !items.some(it => it !== primaryItem)) {
            // Hanya 1 jenis barang (100% dialokasikan ke produk utama)
            if (primaryItem) {
                primaryItem.price = primaryItem.qtyKg > 0 ? (totalBiaya / primaryItem.qtyKg) : 0;
                primaryItem.totalNilai = totalBiaya;
            }
        } else {
            // Campuran Produk Utama + Produk Sampingan
            if (window.hppBreakdownState.mode === 'EQUAL') {
                const avgPrice = totalKgAll > 0 ? (totalBiaya / totalKgAll) : 0;
                items.forEach(it => {
                    it.price = avgPrice;
                    it.totalNilai = it.qtyKg * avgPrice;
                });
            } else if ((window.hppBreakdownState.mode === 'STANDAR_RASIO' || window.hppBreakdownState.mode === 'RATIO_BU_IDA') && basisItem) {
                // Standar Rasio Mutu Dinamis: HPP Produk Utama = rasio × HPP Barang Acuan (basisItem)
                const ratioInput = document.getElementById('inputRasioMutu');
                const ratio = ratioInput ? (parseFloat(ratioInput.value) || 2.5) : 2.5;

                let otherSideCost = 0;
                items.forEach(it => {
                    if (it !== primaryItem && it !== basisItem) {
                        const customVal = window.hppBreakdownState.customPrices[it.idx];
                        it.price = customVal !== undefined ? customVal : it.defaultPrice;
                        it.totalNilai = it.qtyKg * it.price;
                        otherSideCost += it.totalNilai;
                    }
                });

                const remainingCost = Math.max(0, totalBiaya - otherSideCost);
                const primaryKg = primaryItem ? primaryItem.qtyKg : 0;
                const basisKg = basisItem ? basisItem.qtyKg : 0;

                const denominator = (ratio * primaryKg) + basisKg;
                const basisPrice = denominator > 0 ? (remainingCost / denominator) : 0;
                const primaryPrice = basisPrice * ratio;

                if (basisItem) {
                    basisItem.price = basisPrice;
                    basisItem.totalNilai = basisItem.qtyKg * basisPrice;
                }
                if (primaryItem) {
                    primaryItem.price = primaryPrice;
                    primaryItem.totalNilai = primaryItem.qtyKg * primaryPrice;
                }
            } else {
                // Mode 'CUSTOM' (Input Manual Office - Pak Rahmat)
                let totalSideCost = 0;
                items.forEach(it => {
                    if (it !== primaryItem) {
                        const customVal = window.hppBreakdownState.customPrices[it.idx];
                        it.price = customVal !== undefined ? customVal : it.defaultPrice;
                        it.totalNilai = it.qtyKg * it.price;
                        totalSideCost += it.totalNilai;
                    }
                });

                // Produk Utama otomatis menyerap sisa modal produksi (kecuali jika diketik manual)
                if (primaryItem) {
                    const isPrimaryManual = (window.hppBreakdownState.customPrices[primaryItem.idx] !== undefined);
                    if (isPrimaryManual) {
                        primaryItem.price = window.hppBreakdownState.customPrices[primaryItem.idx];
                        primaryItem.totalNilai = primaryItem.qtyKg * primaryItem.price;
                    } else {
                        const primaryCost = Math.max(0, totalBiaya - totalSideCost);
                        primaryItem.price = primaryItem.qtyKg > 0 ? (primaryCost / primaryItem.qtyKg) : 0;
                        primaryItem.totalNilai = primaryCost;
                    }
                }
            }
        }

        // Render baris ke tabel #tbodyBreakdownHpp
        let html = '';
        let totalNilaiAllocated = 0;
        const ratioInputEl = document.getElementById('inputRasioMutu');
        const activeRatio = ratioInputEl ? (parseFloat(ratioInputEl.value) || 2.5) : 2.5;

        items.forEach((it, i) => {
            totalNilaiAllocated += it.totalNilai;
            const porsi = totalKgAll > 0 ? ((it.qtyKg / totalKgAll) * 100).toFixed(1) : '0.0';
            const isPrimary = (it === primaryItem);
            const isBasis = (it === basisItem);
            const isPrimaryManual = (isPrimary && window.hppBreakdownState.customPrices[it.idx] !== undefined);

            // Simpan nilai satuan & total ke input hidden formulir
            const hppSatuanHidden = document.getElementById(`hppSatuan_${it.idx}`);
            if (hppSatuanHidden) hppSatuanHidden.value = Math.round(it.price);
            const totalNilaiHidden = document.getElementById(`totalNilai_${it.idx}`);
            if (totalNilaiHidden) totalNilaiHidden.value = Math.round(it.totalNilai);

            html += `
                <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                    <td style="padding: 0.65rem 0.85rem; text-align: center; font-weight: 700; color: #64748b;">
                        ${i + 1}
                    </td>
                    <td style="padding: 0.65rem 0.85rem;">
                        <div style="font-weight: 700; color: #0f172a; font-size: 0.84rem;">${it.barangNm}</div>
                        <div style="color: #64748b; font-family: ui-monospace, monospace; font-size: 0.725rem; margin-top: 0.1rem;">${it.barangCd || '-'}</div>
                    </td>
                    <td style="padding: 0.65rem 0.85rem; text-align: center;">
                        <span class="badge-peran-tag ${it.badgeClass}">${it.roleLabel}</span>
                    </td>
                    <td style="padding: 0.65rem 0.85rem; text-align: right; font-weight: 700; color: #0f172a; font-variant-numeric: tabular-nums;">
                        ${formatNumber(it.qtyKg, 2)} kg
                    </td>
                    <td style="padding: 0.65rem 0.85rem; text-align: right; color: #64748b; font-weight: 600; font-variant-numeric: tabular-nums;">
                        ${porsi}%
                    </td>
                    <td style="padding: 0.65rem 0.85rem; text-align: right;">
                        <div style="display: inline-flex; align-items: center; border: 1px solid #cbd5e1; border-radius: 6px; background: #ffffff; overflow: hidden; height: 32px; width: 145px; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
                            <span style="background: #f8fafc; padding: 0 0.5rem; font-size: 0.75rem; color: #64748b; font-weight: 600; border-right: 1px solid #cbd5e1; line-height: 30px;">Rp</span>
                            <input type="number" step="1" min="0" 
                                value="${Math.round(it.price)}" 
                                placeholder="0" 
                                oninput="onBreakdownPriceChange(${it.idx}, this.value)"
                                style="border: none; padding: 0 0.5rem; text-align: right; font-size: 0.85rem; font-weight: 700; outline: none; width: 100%; color: #0f172a; height: 100%; font-family: ui-monospace, SFMono-Regular, monospace;"
                                title="Ketik manual HPP satuan ${it.barangNm} per kg">
                        </div>
                        ${isPrimary ? (
                            isPrimaryManual ? `
                                <div style="font-size: 0.7rem; margin-top: 0.2rem; color: #64748b;">
                                    <span style="color: #0284c7; font-weight: 700;">Manual</span> &bull; 
                                    <a href="javascript:void(0)" onclick="resetPrimaryAuto(${it.idx})" style="color: #059669; text-decoration: underline; font-weight: 600;">
                                        Kembalikan ke Auto
                                    </a>
                                </div>
                            ` : `
                                <div style="color: #059669; font-weight: 600; font-size: 0.7rem; margin-top: 0.2rem;">
                                    ${window.hppBreakdownState.mode === 'STANDAR_RASIO' && basisItem ? `(${activeRatio}× ${basisItem.barangNm})` : '✓ Otomatis Serap Sisa'}
                                </div>
                            `
                        ) : (
                            isBasis && window.hppBreakdownState.mode === 'STANDAR_RASIO' ? `
                                <div style="color: #b45309; font-weight: 600; font-size: 0.7rem; margin-top: 0.2rem;">
                                    Acuan Rasio (${activeRatio}×)
                                </div>
                            ` : ''
                        )}
                    </td>
                    <td style="padding: 0.65rem 0.85rem; text-align: right; font-weight: 800; font-size: 0.875rem; color: #0f172a; font-variant-numeric: tabular-nums;" class="cell-nilai-total">
                        Rp ${formatNumber(Math.round(it.totalNilai), 0)}
                    </td>
                </tr>
            `;
        });

        tbodyBreakdown.innerHTML = html;

        // Update footer tabel
        if (elTotalKgFoot) elTotalKgFoot.textContent = formatNumber(totalKgAll, 2) + ' kg';
        if (elTotalNilaiFoot) elTotalNilaiFoot.textContent = 'Rp ' + formatNumber(Math.round(totalNilaiAllocated), 0);

        // Rekonsiliasi Balance Check
        const diff = Math.round(totalBiaya - totalNilaiAllocated);
        const badgeBalance = document.getElementById('badgeHppBalance');
        const diffEl = document.getElementById('reconciliationDiff');

        if (Math.abs(diff) <= 20) {
            if (badgeBalance) {
                badgeBalance.style.background = '#ecfdf5';
                badgeBalance.style.color = '#059669';
                badgeBalance.style.borderColor = '#a7f3d0';
                badgeBalance.textContent = 'Alokasi Klop (100%)';
            }
            if (diffEl) {
                diffEl.textContent = 'Selisih Alokasi: Rp 0';
                diffEl.style.color = '#059669';
            }
        } else {
            if (badgeBalance) {
                badgeBalance.style.background = '#fef2f2';
                badgeBalance.style.color = '#dc2626';
                badgeBalance.style.borderColor = '#fecaca';
                badgeBalance.textContent = `Selisih Alokasi: Rp ${formatNumber(diff, 0)}`;
            }
            if (diffEl) {
                diffEl.textContent = `Selisih Alokasi: Rp ${formatNumber(diff, 0)}`;
                diffEl.style.color = '#dc2626';
            }
        }

        // Sinkronkan kartu live HPP ringkasan bawah dengan harga Produk Utama
        if (primaryItem && primaryItem.price > 0) {
            const elHpp = document.getElementById('liveHppPerKg') || document.getElementById('liveHpp');
            if (elHpp) {
                elHpp.innerHTML = `
                    <span>Rp ${formatNumber(Math.round(primaryItem.price), 0)} / kg</span>
                    <small style="display: block; font-size: 0.7rem; font-weight: 600; color: #047857; margin-top: 0.15rem;">
                        (Produk Utama: ${primaryItem.barangNm})
                    </small>
                `;
            }
        }
    };

    // 10. Highlight Dokumen Gudang (BPPB) yang Sesuai dengan Lini Produksi
    window.highlightMatchingPakai = function () {
        const selectLini = document.getElementById('lini_produksi');
        const selectPakai = document.getElementById('pakai_id');
        if (!selectLini || !selectPakai) return;

        const currentLini = (selectLini.value || '').trim().toUpperCase();
        let matchCount = 0;

        for (let i = 0; i < selectPakai.options.length; i++) {
            const opt = selectPakai.options[i];
            if (!opt.value) continue;

            if (!opt.dataset.originalText) {
                opt.dataset.originalText = opt.text;
            }

            const rawOriginal = opt.dataset.originalText;
            const tujuan = (opt.dataset.tujuan || '').trim().toUpperCase();

            const isMatch = Boolean(currentLini && tujuan && (tujuan === currentLini || tujuan.includes(currentLini) || currentLini.includes(tujuan)));

            if (isMatch) {
                matchCount++;
                opt.text = '[SESUAI LINI] ' + rawOriginal.replace(/^\[SESUAI LINI\]\s*/, '');
                opt.style.fontWeight = 'bold';
                opt.style.color = '#0284c7';
                opt.style.backgroundColor = '#f0f9ff';
            } else {
                opt.text = rawOriginal.replace(/^\[SESUAI LINI\]\s*/, '');
                opt.style.fontWeight = 'normal';
                opt.style.color = '';
                opt.style.backgroundColor = '';
            }
        }

        const noticeEl = document.getElementById('pakaiMatchNotice');
        if (noticeEl) {
            if (currentLini && matchCount > 0) {
                noticeEl.textContent = `Tersedia ${matchCount} dokumen pengeluaran gudang bertarget ${currentLini} (ditandai [SESUAI LINI]).`;
                noticeEl.style.color = '#0284c7';
                noticeEl.style.display = 'block';
            } else if (currentLini) {
                noticeEl.textContent = `Belum ada dokumen pengeluaran gudang khusus untuk ${currentLini}. Anda tetap dapat memilih dokumen lain atau mengisi bahan secara mandiri.`;
                noticeEl.style.color = '#64748b';
                noticeEl.style.display = 'block';
            } else {
                noticeEl.style.display = 'none';
            }
        }
    };

    // Helper: Mencocokkan teks tujuan pemakaian BPPB dengan opsi dropdown lini_produksi
    function findMatchingLiniIndex(elLini, targetRaw) {
        if (!elLini || !targetRaw) return -1;
        const target = targetRaw.trim().toUpperCase();

        // Normalisasi: bersihkan prefix umum operasional seperti PRODUKSI, PENGEMASAN, PACKAGING, PACKING, dan KS
        const normalizeStr = (s) => s.replace(/^(PRODUKSI|PENGEMASAN|PACKAGING|PACKING)\s+/i, '')
                                     .replace(/\bKS\s+/i, '')
                                     .replace(/[^A-Z0-9]/g, '');

        const targetNorm = normalizeStr(target);

        // 1. Pencocokan langsung (exact match)
        for (let i = 0; i < elLini.options.length; i++) {
            const opt = elLini.options[i];
            const val = (opt.value || '').trim().toUpperCase();
            if (val && val === target) return i;
        }

        // 2. Pencocokan Normalized (bebas spasi, tanda strip '-', dan prefix)
        for (let i = 0; i < elLini.options.length; i++) {
            const opt = elLini.options[i];
            const val = (opt.value || '').trim().toUpperCase();
            const cd = (opt.getAttribute('data-cd') || '').trim().toUpperCase();
            const valNorm = normalizeStr(val);
            const cdNorm = cd.replace(/[^A-Z0-9]/g, '');

            if (valNorm && (valNorm === targetNorm || cdNorm === targetNorm)) {
                return i;
            }
        }

        // 3. Pencocokan kata kunci prioritas IFM, IFL, atau MANUAL
        if (target.includes('IFM') || target.includes('IFL') || target.includes('INDOFOOD')) {
            for (let i = 0; i < elLini.options.length; i++) {
                const opt = elLini.options[i];
                const val = (opt.value || '').trim().toUpperCase();
                const cd = (opt.getAttribute('data-cd') || '').trim().toUpperCase();
                if (val.includes('IFM') || cd === 'IFM') return i;
            }
            for (let i = 0; i < elLini.options.length; i++) {
                const opt = elLini.options[i];
                const val = (opt.value || '').trim().toUpperCase();
                const cd = (opt.getAttribute('data-cd') || '').trim().toUpperCase();
                if (val.includes('IFL') || cd === 'IFL') return i;
            }
        }

        if (target.includes('MANUAL')) {
            for (let i = 0; i < elLini.options.length; i++) {
                const opt = elLini.options[i];
                const val = (opt.value || '').trim().toUpperCase();
                const cd = (opt.getAttribute('data-cd') || '').trim().toUpperCase();
                if (val.includes('MANUAL') || cd === 'MANUAL') return i;
            }
        }

        // 4. Fallback substring matching
        for (let i = 0; i < elLini.options.length; i++) {
            const opt = elLini.options[i];
            const val = (opt.value || '').trim().toUpperCase();
            if (!val) continue;
            const valNorm = normalizeStr(val);
            if (valNorm && targetNorm && (valNorm.includes(targetNorm) || targetNorm.includes(valNorm))) {
                return i;
            }
        }

        return -1;
    }

    // 11. Load Pemakaian Data dari Dokumen Gudang (AJAX)
    window.loadPakaiData = function (pakaiId) {
        const noticeEl = document.getElementById('pakaiMatchNotice');
        const wrapper = document.getElementById('wrapperTabelPemakaian');
        const tbody = document.getElementById('tbodyTabelPemakaian');
        const tfoot = document.getElementById('tfootTabelPemakaian');
        const btnTarik = document.getElementById('btnTarikPakai');
        const btnText = document.getElementById('btnTarikPakaiText');
        const iconTarik = document.getElementById('iconTarikPakai');
        const pakaiSelect = document.getElementById('pakai_id');

        if (!pakaiId) {
            if (noticeEl) noticeEl.style.display = 'none';
            if (wrapper) wrapper.style.display = 'none';
            return;
        }

        // Sinkronisasi instan lini produksi dari atribut data-tujuan option terpilih
        const selectedPakaiOpt = pakaiSelect?.querySelector(`option[value="${pakaiId}"]`);
        const quickTujuan = selectedPakaiOpt?.getAttribute('data-tujuan');
        if (quickTujuan) {
            const elLini = document.getElementById('lini_produksi');
            if (elLini) {
                const qIdx = findMatchingLiniIndex(elLini, quickTujuan);
                if (qIdx !== -1 && elLini.selectedIndex !== qIdx) {
                    elLini.selectedIndex = qIdx;
                    elLini.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }
        }

        // UI Feedback: Aktifkan status loading seperti pada modul pemakaian gudang
        if (btnTarik) btnTarik.disabled = true;
        if (btnText) btnText.textContent = 'Memuat BPPB...';
        if (iconTarik) iconTarik.classList.add('animate-spin');

        if (wrapper && tbody) {
            wrapper.style.display = 'block';
            if (tfoot) tfoot.style.display = 'none';
            const singkongNotice = document.getElementById('singkongGradeNotice');
            if (singkongNotice) singkongNotice.style.display = 'none';

            tbody.innerHTML = `
                <tr>
                    <td colspan="9" style="padding: 2.25rem 1rem; text-align: center; background: #ffffff;">
                        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.65rem;">
                            <svg class="animate-spin" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: #0284c7;">
                                <circle cx="12" cy="12" r="10" stroke="#bae6fd" stroke-width="2.5"></circle>
                                <path d="M12 2a10 10 0 0 1 10 10"></path>
                            </svg>
                            <div>
                                <strong style="color: #0f172a; font-size: 0.875rem; display: block;">Memuat Rincian Bahan Baku dari Gudang...</strong>
                                <span style="font-size: 0.75rem; color: #64748b; margin-top: 3px; display: block;">
                                    Mengambil alokasi batch, grade bahan, dan menyinkronkan rincian lini produksi.
                                </span>
                            </div>
                        </div>
                    </td>
                </tr>
            `;
        }

        const url = `${window.appConfig.pakaiDataUrl}/${pakaiId}`;
        fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(async res => {
                const json = await res.json().catch(() => null);
                if (!res.ok) {
                    const msg = (json && json.message) ? json.message : `HTTP ${res.status}: ${res.statusText}`;
                    throw new Error(msg);
                }
                return json;
            })
            .then(res => {
                if (btnTarik) btnTarik.disabled = false;
                if (btnText) btnText.textContent = 'Tarik Ulang';
                if (iconTarik) iconTarik.classList.remove('animate-spin');

                if (res.status === 'success') {
                    const d = res.data;
                    try {
                        const setVal = (id, val) => {
                            const el = document.getElementById(id);
                            if (el) el.value = val || 0;
                        };

                        // Catatan: Tanggal produksi sengaja dipertahankan tanggal hari ini (tidak mengikuti tanggal BPPB)

                        // 1. Auto-sinkronkan Lini Produksi sesuai Tujuan BPPB
                        let isLiniMatched = false;
                        if (d.tujuan_pemakaian) {
                            const elLini = document.getElementById('lini_produksi');
                            if (elLini) {
                                const matchedIndex = findMatchingLiniIndex(elLini, d.tujuan_pemakaian);
                                if (matchedIndex !== -1) {
                                    isLiniMatched = true;
                                    if (elLini.selectedIndex !== matchedIndex) {
                                        elLini.selectedIndex = matchedIndex;
                                        elLini.dispatchEvent(new Event('change', { bubbles: true }));
                                    }
                                }
                                if (typeof window.updateKartonRangeAndBatch === 'function') window.updateKartonRangeAndBatch();
                            }
                        }

                        // 3. Auto-sinkronkan Gudang Asal jika belum terpilih
                        if (d.gudang_id) {
                            const elGdg = document.getElementById('gudang_id');
                            if (elGdg && (!elGdg.value || elGdg.value === '')) {
                                elGdg.value = d.gudang_id;
                            }
                        }

                        // 4. Salin rincian kuantitas & nilai bahan baku
                        setVal('singkong_qty', d.singkong_qty);
                        setVal('singkong_nilai', d.singkong_nilai);
                        setVal('minyak_sawit_qty', d.minyak_sawit_qty);
                        setVal('minyak_kelapa_qty', d.minyak_kelapa_qty);
                        setVal('minyak_nilai', d.minyak_nilai);
                        setVal('bumbu_qty', d.bumbu_qty);
                        setVal('bumbu_nilai', d.bumbu_nilai);
                        setVal('karton_baru_qty', d.karton_baru_qty);
                        setVal('karton_baru_nilai', d.karton_baru_nilai);
                        setVal('karton_bekas_qty', d.karton_bekas_qty);
                        setVal('karton_bekas_nilai', d.karton_bekas_nilai);
                        setVal('plastik_hd_qty', d.plastik_hd_qty);
                        setVal('plastik_hd_nilai', d.plastik_hd_nilai);
                        setVal('lakban_besar_qty', d.lakban_besar_qty);
                        setVal('lakban_besar_nilai', d.lakban_besar_nilai);
                        setVal('lakban_kecil_qty', d.lakban_kecil_qty);
                        setVal('lakban_kecil_nilai', d.lakban_kecil_nilai);
                        setVal('tali_rafia_qty', d.tali_rafia_qty);
                        setVal('tali_rafia_nilai', d.tali_rafia_nilai);

                        // Sinkronkan harga satuan riil dari dokumen BPPB ke memori kalkulasi
                        if (d.bumbu_qty > 0 && d.bumbu_nilai > 0) window.standarHargaKemasan.bumbu_qty = d.bumbu_nilai / d.bumbu_qty;
                        if (d.karton_baru_qty > 0 && d.karton_baru_nilai > 0) window.standarHargaKemasan.karton_baru_qty = d.karton_baru_nilai / d.karton_baru_qty;
                        if (d.karton_bekas_qty > 0 && d.karton_bekas_nilai > 0) window.standarHargaKemasan.karton_bekas_qty = d.karton_bekas_nilai / d.karton_bekas_qty;
                        if (d.plastik_hd_qty > 0 && d.plastik_hd_nilai > 0) window.standarHargaKemasan.plastik_hd_qty = d.plastik_hd_nilai / d.plastik_hd_qty;
                        if (d.lakban_besar_qty > 0 && d.lakban_besar_nilai > 0) window.standarHargaKemasan.lakban_besar_qty = d.lakban_besar_nilai / d.lakban_besar_qty;
                        if (d.lakban_kecil_qty > 0 && d.lakban_kecil_nilai > 0) window.standarHargaKemasan.lakban_kecil_qty = d.lakban_kecil_nilai / d.lakban_kecil_qty;
                        if (d.tali_rafia_qty > 0 && d.tali_rafia_nilai > 0) window.standarHargaKemasan.tali_rafia_qty = d.tali_rafia_nilai / d.tali_rafia_qty;

                        // Auto-fill Sarung Tangan jika ada dari dokumen BPPB Gudang
                        if (d.sarung_tangan_plastik_qty && d.sarung_tangan_plastik_qty > 0) {
                            setVal('sarung_tangan_plastik_qty', d.sarung_tangan_plastik_qty);
                        }
                        if (d.sarung_tangan_plastik_nilai && d.sarung_tangan_plastik_nilai > 0) {
                            setVal('sarung_tangan_plastik_nilai', d.sarung_tangan_plastik_nilai);
                        }
                        if (d.sarung_tangan_kain_qty && d.sarung_tangan_kain_qty > 0) {
                            setVal('sarung_tangan_kain_qty', d.sarung_tangan_kain_qty);
                        }
                        if (d.sarung_tangan_kain_nilai && d.sarung_tangan_kain_nilai > 0) {
                            setVal('sarung_tangan_kain_nilai', d.sarung_tangan_kain_nilai);
                        }

                        // Tampilkan info kuantitas unit sarung tangan dari BPPB jika ada
                        const elSubSTP = document.getElementById('sublabel_foh_sarung_plastik');
                        if (elSubSTP && d.sarung_tangan_plastik_qty > 0) {
                            elSubSTP.innerHTML = `Terdata <strong>${d.sarung_tangan_plastik_qty} unit</strong> di BPPB Gudang ${d.sarung_tangan_plastik_nilai > 0 ? '' : '(sesuai nota belanja)'}`;
                            elSubSTP.style.color = '#0284c7';
                        }
                        const elSubSTK = document.getElementById('sublabel_foh_sarung_kain');
                        if (elSubSTK && d.sarung_tangan_kain_qty > 0) {
                            elSubSTK.innerHTML = `Terdata <strong>${d.sarung_tangan_kain_qty} unit</strong> di BPPB Gudang ${d.sarung_tangan_kain_nilai > 0 ? '' : '(sesuai nota belanja)'}`;
                            elSubSTK.style.color = '#0284c7';
                        }

                        // Auto-fill varietas singkong jika ada
                        if (d.varietas_singkong) {
                            const elVar = document.getElementById('varietas_singkong');
                            if (elVar) elVar.value = d.varietas_singkong;
                        }

                        // Auto-suggest estimasi karton jika karton digunakan di gudang
                        if (d.karton_estimasi && d.karton_estimasi > 0) {
                            const elKarton = document.getElementById('qty_karton');
                            if (elKarton && (!elKarton.value || elKarton.value === '0')) {
                                elKarton.value = d.karton_estimasi;
                                if (typeof window.updateKartonRangeAndBatch === 'function') window.updateKartonRangeAndBatch();
                            }
                        }

                        // 5. Render Tabel Pemakaian Bahan Baku (Blueprint 9.B.1)
                        if (d.items && Array.isArray(d.items)) {
                            window.renderTabelPemakaian(d.items, d);
                        }

                        // Notifikasi status penarikan data BPPB
                        if (noticeEl) {
                            if (isLiniMatched) {
                                const elLini = document.getElementById('lini_produksi');
                                const selectedLiniName = elLini?.options[elLini.selectedIndex]?.text || d.tujuan_pemakaian;
                                noticeEl.innerHTML = `Dokumen <strong>[${d.pakai_no || ''}]</strong> berhasil ditarik: Lini otomatis disinkronkan ke <strong>${selectedLiniName}</strong> dan seluruh rincian bahan terisi lengkap.`;
                                noticeEl.style.color = '#0284c7';
                            } else {
                                noticeEl.innerHTML = `Dokumen <strong>[${d.pakai_no || ''}]</strong> berhasil ditarik: Seluruh bahan terisi lengkap. Tujuan dokumen adalah <strong>"${d.tujuan_pemakaian || '-'}"</strong>. Mohon pilih <strong>Lini Produksi</strong> secara manual di bawah.`;
                                noticeEl.style.color = '#b45309';
                            }
                            noticeEl.style.display = 'block';
                        }

                        // Notifikasi rincian komposisi grade singkong
                        const gradeNoticeEl = document.getElementById('singkongGradeNotice');
                        const gradeNoticeText = document.getElementById('singkongGradeNoticeText');
                        if (gradeNoticeEl && gradeNoticeText) {
                            if (d.singkong_grade_desc && d.singkong_grade_desc !== '-') {
                                gradeNoticeText.innerHTML = `Komposisi Grade Bahan Baku Singkong: <strong>${d.singkong_grade_desc}</strong>`;
                                gradeNoticeEl.style.display = 'flex';
                            } else {
                                gradeNoticeEl.style.display = 'none';
                            }
                        }

                        if (typeof window.calcAll === 'function') window.calcAll();
                    } catch (domErr) {
                        console.error('Error applying data to form:', domErr);
                    }
                } else {
                    alert('Gagal: ' + (res.message || 'Respon server tidak valid'));
                }
            })
            .catch(err => {
                if (btnTarik) btnTarik.disabled = false;
                if (btnText) btnText.textContent = 'Tarik Ulang';
                if (iconTarik) iconTarik.classList.remove('animate-spin');
                console.error('Error loadPakaiData:', err);
                alert('Terjadi kesalahan saat memuat data pemakaian bahan: ' + err.message);
            });
    };

    // 12. Render Tabel Pemakaian Bahan (Blueprint 9.B.1)
    window.renderTabelPemakaian = function (items, summary) {
        const wrapper = document.getElementById('wrapperTabelPemakaian');
        const tbody = document.getElementById('tbodyTabelPemakaian');
        const tfoot = document.getElementById('tfootTabelPemakaian');
        const badgeCount = document.getElementById('badgeJumlahItemPakai');
        const metaDoc = document.getElementById('bppbMetaDoc');
        const badgeQty = document.getElementById('badgeTotalBahanQty');
        const badgeNilai = document.getElementById('badgeTotalBahanNilai');
        const tfootQty = document.getElementById('tfootTotalQty');
        const tfootNilai = document.getElementById('tfootTotalNilai');

        if (!wrapper || !tbody) return;

        if (!items || items.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" style="padding: 1.5rem 1rem; text-align: center; color: #94a3b8; font-style: italic;">
                        Tidak ada rincian bahan pada dokumen pengeluaran gudang ini.
                    </td>
                </tr>
            `;
            if (badgeCount) badgeCount.textContent = '0 Item Bahan';
            if (tfoot) tfoot.style.display = 'none';
            wrapper.style.display = 'block';
            return;
        }

        // Tampilkan metadata dokumen di header tabel
        if (summary) {
            if (metaDoc) {
                metaDoc.innerHTML = `&bull; <strong>[${summary.pakai_no || '-'}]</strong> &bull; ${summary.gudang_nm || 'Gudang Bahan'} &bull; ${summary.pakai_tgl_formatted || '-'}`;
            }
            if (badgeQty) {
                badgeQty.textContent = `Total Bahan: ${formatNumber(summary.total_bahan_qty || 0, 2)} KG`;
            }
            if (badgeNilai) {
                badgeNilai.style.display = 'none';
            }
        }

        let html = '';
        let totalQtyCalc = 0;
        let totalNilaiCalc = 0;

        items.forEach((it, idx) => {
            const kat = it.kategori || 'Bahan Penolong';
            const itemSubtotal = parseFloat(it.subtotal || 0);
            const itemQty = parseFloat(it.qty_keluar || 0);
            const sisaStok = parseFloat(it.sisa_stok || 0);
            totalQtyCalc += itemQty;
            totalNilaiCalc += itemSubtotal;

            // Render Grade secara konsisten untuk seluruh item yang memiliki data grade
            const rawGrade = (it.grade_cd || '').trim().toUpperCase();
            let gradeHtml = '<span style="color: #94a3b8; font-weight: 500;">-</span>';

            if (rawGrade && rawGrade !== '-') {
                const isGradeA = rawGrade === 'A' || rawGrade === 'GRADE A';
                const isGradeB = rawGrade === 'B' || rawGrade === 'GRADE B';
                const gText = rawGrade.startsWith('GRADE') ? rawGrade : `Grade ${rawGrade}`;

                if (isGradeA) {
                    gradeHtml = `<span style="display: inline-block; font-size: 0.725rem; font-weight: 700; color: #065f46; background: #ecfdf5; border: 1px solid #a7f3d0; padding: 0.1rem 0.45rem; border-radius: 4px;">${gText}</span>`;
                } else if (isGradeB) {
                    gradeHtml = `<span style="display: inline-block; font-size: 0.725rem; font-weight: 700; color: #92400e; background: #fef3c7; border: 1px solid #fde68a; padding: 0.1rem 0.45rem; border-radius: 4px;">${gText}</span>`;
                } else {
                    gradeHtml = `<span style="display: inline-block; font-size: 0.725rem; font-weight: 700; color: #334155; background: #f1f5f9; border: 1px solid #cbd5e1; padding: 0.1rem 0.45rem; border-radius: 4px;">${gText}</span>`;
                }
            }

            const rowBg = (idx % 2 === 1) ? '#f8fafc' : '#ffffff';

            html += `
                <tr style="background: ${rowBg}; border-bottom: 1px solid #e2e8f0; transition: background 0.12s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='${rowBg}'">
                    <td style="padding: 0.6rem 0.75rem; text-align: center; color: #64748b; font-weight: 600; font-size: 0.8rem;">${idx + 1}</td>
                    <td style="padding: 0.6rem 0.75rem; color: #475569; font-weight: 500; font-size: 0.8rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${kat}</td>
                    <td style="padding: 0.6rem 0.75rem; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 0.775rem; font-weight: 600; color: #334155; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${it.barang_cd || '-'}</td>
                    <td style="padding: 0.6rem 0.75rem; font-weight: 600; color: #0f172a; font-size: 0.825rem; line-height: 1.35;">${it.barang_nm || '-'}</td>
                    <td style="padding: 0.6rem 0.75rem; text-align: center;">${gradeHtml}</td>
                    <td style="padding: 0.6rem 0.75rem; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 0.775rem; font-weight: 600; color: #334155; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${it.batch_no || '-'}</td>
                    <td style="padding: 0.6rem 0.75rem; text-align: right; font-weight: 700; color: #0284c7; font-size: 0.85rem; font-variant-numeric: tabular-nums;">
                        ${formatNumber(itemQty, 2)} <span style="font-weight: 500; font-size: 0.725rem; color: #64748b;">${it.satuan_cd || 'KG'}</span>
                    </td>
                    <td style="padding: 0.6rem 0.75rem; text-align: right; font-weight: 700; color: ${sisaStok > 0 ? '#059669' : '#dc2626'}; font-size: 0.85rem; font-variant-numeric: tabular-nums;">
                        ${formatNumber(sisaStok, 2)} <span style="font-weight: 500; font-size: 0.725rem; color: #64748b;">${it.satuan_cd || 'KG'}</span>
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
        if (badgeCount) badgeCount.textContent = `${items.length} Item Bahan`;

        if (tfoot) {
            tfoot.style.display = 'table-footer-group';
            if (tfootQty) tfootQty.textContent = `${formatNumber(totalQtyCalc, 2)} KG`;
            if (tfootNilai) tfootNilai.style.display = 'none';
        }

        wrapper.style.display = 'block';
    };

    // Handler perubahan Grade pada baris output WIP (KW 1 vs KW 2)
    window.onGradeOutputChange = function (sel, idx) {
        if (!sel) return;
        const val = sel.value;
        if (sel.tagName === 'SELECT') {
            if (val === 'KW2') {
                sel.style.background = '#fef3c7';
                sel.style.color = '#92400e';
                sel.style.borderColor = '#fde68a';
            } else {
                sel.style.background = '#f0fdf4';
                sel.style.color = '#166534';
                sel.style.borderColor = '#bbf7d0';
            }
        }

        if (typeof window.updateFgDuplicateProtection === 'function') {
            window.updateFgDuplicateProtection();
        }
        if (typeof window.syncMasterSwitchFromTable === 'function') {
            window.syncMasterSwitchFromTable();
        }
    };

    // Helper: Proteksi Anti-Duplikat Barang Hasil Produksi
    // Mencegah pemilihan barang dengan grade yang sama di baris lain dalam 1 formulir dokumen produksi
    window.updateFgDuplicateProtection = function () {
        const tbody = document.getElementById('tbodyOutputFg');
        if (!tbody) return;

        const rows = Array.from(tbody.querySelectorAll('.row-output-fg'));
        const itemCounts = {};
        rows.forEach(r => {
            const bId = r.querySelector('.select-fg-barang')?.value;
            if (bId) {
                itemCounts[bId] = (itemCounts[bId] || 0) + 1;
            }
        });

        const selects = Array.from(tbody.querySelectorAll('.select-fg-barang'));
        selects.forEach(sel => {
            const currentVal = sel.value ? sel.value.toString() : '';

            Array.from(sel.options).forEach(opt => {
                if (!opt.value) return; // Lewati placeholder
                const optVal = opt.value.toString();

                if (!opt.dataset.origText) {
                    opt.dataset.origText = opt.textContent.trim().replace(/\s*──\s*\(Sudah Dipilih\)/g, '').replace(/\s*──\s*\(Maks 2 Grade Terpakai\)/g, '');
                }

                const count = itemCounts[optVal] || 0;
                // Maksimum 2 baris untuk barang yang sama (1 untuk KW 1 dan 1 untuk KW 2)
                if (count >= 2 && optVal !== currentVal) {
                    opt.disabled = true;
                    if (!opt.textContent.includes('(Maks 2 Grade Terpakai)')) {
                        opt.textContent = `${opt.dataset.origText} ── (Maks 2 Grade Terpakai)`;
                    }
                } else {
                    opt.disabled = false;
                    opt.textContent = opt.dataset.origText;
                }
            });
        });
    };

    // 13. Logika Baris Dinamis Output Barang Hasil Produksi (Tabel Terpadu)
    let fgRowCounter = document.querySelectorAll('.row-output-fg').length || 1;
    window.addFgRow = function (initial) {
        const tbody = document.getElementById('tbodyOutputFg');
        if (!tbody) return;

        const idx = fgRowCounter++;
        const currentBatch = document.getElementById('batch_wip_no')?.value || '';
        const currentQtyKarton = parseFloat(document.getElementById('qty_karton')?.value) || 0;
        const isFirstRow = tbody.children.length === 0;
        const defaultQtyHasil = initial?.qty_hasil !== undefined 
            ? initial.qty_hasil 
            : (isFirstRow && currentQtyKarton > 0 ? currentQtyKarton : '');
        const defaultBatch = initial?.batch_no || (isFirstRow ? currentBatch : '-');

        const chkBatch = document.getElementById('chk_enable_batch');
        const isMasterExcluded = (window._batchOption === 'exclude') || (chkBatch && !chkBatch.checked);
        const defaultBatchState = isMasterExcluded ? 'excluded' : 'active';

        const barangList = window.appConfig.barangHasilList || [];
        
        const fccItems = [];
        const seasoningItems = [];
        const tawarItems = [];
        const kw2Items = [];

        const seasoningCodes = ['WIP-ASB', 'WIP-ASW', 'WIP-BLQ', 'WIP-UCM', 'WIP-KCL', 'WIP-BBB', 'WIP-AJB', 'WIP-PJB', 'WIP-OPA', 'WIP-OPB', 'WIP-PYA', 'WIP-PYB', 'WIP-UBI', 'WIP-UBU'];

        barangList.forEach(b => {
            const cd = (b.barang_cd || '').toUpperCase();
            const nm = (b.barang_nm || '').toUpperCase();
            const grade = (b.grade_cd || '').toUpperCase();
            
            // 1. Indofood FCC Non Bumbu
            if (cd.startsWith('WIP-FCC') || nm.includes('IFL') || nm.includes('INDOFOOD') || nm.includes('TANPA BUMBU')) {
                fccItems.push(b);
            } 
            // 2. Curah Berbumbu (Seasoning)
            else if (seasoningCodes.includes(cd) || nm.includes('ASIN') || nm.includes('BUMBU') || nm.includes('BALADO') || nm.includes('LEMON') || nm.includes('PEDAS') || nm.includes('BARCO') || nm.includes('SAWIT') || nm.includes('BALQI') || nm.includes('UCAMP') || nm.includes('OPAK') || nm.includes('PUYUR')) {
                seasoningItems.push(b);
            } 
            // 3. Curah Tawar & Hasil Sampingan / HACCP (Berko, Remahan, Lengket, dll.)
            else {
                tawarItems.push(b);
            }
        });

        const buildOptGroup = (items) => {
            let html = '';
            items.forEach(b => {
                const jenis = 'WIP';
                const satuan = b.satuan_dasar?.satuan_cd || 'KG';
                const selected = (initial && initial.barang_id == b.barang_id) ? 'selected' : '';
                html += `<option value="${b.barang_id}" data-cd="${b.barang_cd}" data-nm="${b.barang_nm}" data-jenis="${jenis}" data-satuan="${satuan}" ${selected}>
                    [${b.barang_cd}] ${b.barang_nm} (${satuan})
                </option>`;
            });
            return html;
        };

        // Buat opsi dropdown barang dengan 3 grup terpadu
        let optionsHtml = '<option value="">-- Pilih Hasil Penggorengan (WIP / FCC) --</option>';

        if (fccItems.length > 0) {
            optionsHtml += '<optgroup label="📦 1. KHUSUS INDOFOOD (FCC NON-BUMBU 6 KG/BOX)">';
            optionsHtml += buildOptGroup(fccItems);
            optionsHtml += '</optgroup>';
        }

        if (seasoningItems.length > 0) {
            optionsHtml += '<optgroup label="🧂 2. CURAH BERBUMBU (HASIL SEASONING)">';
            optionsHtml += buildOptGroup(seasoningItems);
            optionsHtml += '</optgroup>';
        }

        if (tawarItems.length > 0) {
            optionsHtml += '<optgroup label="🥔 3. CURAH TAWAR & HASIL SAMPINGAN (HACCP)">';
            optionsHtml += buildOptGroup(tawarItems);
            optionsHtml += '</optgroup>';
        }



        const tr = document.createElement('tr');
        tr.id = `fgRow_${idx}`;
        tr.className = 'row-output-fg';
        tr.style.borderBottom = '1px solid #e2e8f0';
        tr.innerHTML = `
            <td style="padding: 0.5rem 0.75rem; text-align: center; font-weight: 700; color: #64748b;" class="fg-row-number">
                ${tbody.children.length + 1}
            </td>
            <td style="padding: 0.5rem 0.75rem; text-align: center;">
                <span id="badgeJenis_${idx}" style="background: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1; padding: 0.2rem 0.45rem; border-radius: 4px; font-weight: 700; font-size: 0.75rem;">
                    ${initial?.jenis_cd || '-'}
                </span>
                <input type="hidden" name="output_items[${idx}][jenis_cd]" id="inputJenis_${idx}" value="${initial?.jenis_cd || ''}">
            </td>
            <td style="padding: 0.5rem 0.75rem;">
                <select name="output_items[${idx}][barang_id]" class="form-control select-fg-barang" style="font-size: 0.8rem; font-weight: 600;" onchange="onFgBarangChange(this, ${idx})">
                    ${optionsHtml}
                </select>
                <input type="hidden" name="output_items[${idx}][grade_cd]" id="gradeCd_${idx}" class="select-fg-grade" value="${initial?.grade_cd || 'KW1'}">
            </td>
            <td style="padding: 0.5rem 0.75rem;">
                <input type="number" step="0.0001" min="0" name="output_items[${idx}][qty_hasil]" id="qtyHasil_${idx}" class="form-control calc-trigger" style="text-align: right; font-weight: 700; color: #0f172a;" placeholder="0" value="${defaultQtyHasil}" oninput="onQtyHasilInput(${idx})">
            </td>
            <td style="padding: 0.5rem 0.75rem; text-align: center;">
                <span id="labelSatuan_${idx}" style="font-weight: 600; color: #475569; font-size: 0.775rem;">${initial?.satuan_cd || '-'}</span>
                <input type="hidden" name="output_items[${idx}][satuan_cd]" id="inputSatuan_${idx}" value="${initial?.satuan_cd || ''}">
            </td>
            <td style="padding: 0.5rem 0.75rem;">
                <input type="number" step="0.0001" min="0" name="output_items[${idx}][qty_kg]" id="qtyKg_${idx}" class="form-control input-fg-qty-kg calc-trigger" style="text-align: right; font-weight: 700; color: #0f172a; background: #ffffff;" placeholder="0" value="${initial?.qty_kg || ''}" oninput="calcAll()">
            </td>
            <td style="padding: 0.5rem 0.75rem; text-align: right;">
                <span class="label-fg-porsi" id="porsiFg_${idx}" style="font-weight: 700; font-size: 0.8rem; color: #0284c7; background: #f0f9ff; border: 1px solid #bae6fd; padding: 0.2rem 0.5rem; border-radius: 4px; display: inline-block;">
                    0,00%
                </span>
            </td>
            <td style="padding: 0.5rem 0.75rem; text-align: center;">
                <span class="badge-batch-wip" id="badgeFgBatch_${idx}" data-batch-state="${defaultBatchState}" onclick="toggleSingleRowBatch(${idx})" style="font-size: 0.75rem; font-weight: 700; color: #334155; background: #f8fafc; border: 1px solid #cbd5e1; padding: 0.2rem 0.5rem; border-radius: 4px; display: inline-block; cursor: pointer; user-select: none;" title="Status: Diterbitkan. Klik untuk menangguhkan (Buffer Stock)">
                    ${defaultBatch}
                </span>
                <input type="hidden" name="output_items[${idx}][batch_no]" id="batchFg_${idx}" value="${defaultBatch !== '-' ? defaultBatch : ''}">
                <input type="hidden" name="output_items[${idx}][hpp_satuan]" id="hppSatuan_${idx}" value="${initial?.hpp_satuan || 0}">
                <input type="hidden" name="output_items[${idx}][total_nilai]" id="totalNilai_${idx}" value="${initial?.total_nilai || 0}">
            </td>
            <td style="padding: 0.5rem 0.75rem; text-align: center;">
                <button type="button" onclick="removeFgRow(${idx})" class="btn btn-sm btn-remove-fg" id="btnRemoveFg_${idx}" style="padding: 0.2rem 0.45rem; font-size: 0.75rem; background: #ffffff; border: 1px solid #cbd5e1; color: #dc2626; font-weight: 600;" title="Hapus baris ini">
                    Hapus
                </button>
            </td>
        `;

        tbody.appendChild(tr);

        // Auto selection barang lini HANYA untuk baris pertama (jika tabel baru memiliki 1 baris)
        // Baris ke-2 dst tidak boleh di-autoselect agar operator bebas memilih barang tambahan (Berko, Lengket, dll)
        if (!initial && tbody.querySelectorAll('.row-output-fg').length === 1) {
            autoSelectLiniBarang(idx);
        }

        // Sinkronkan batch per jenis produk (range karton vs tanggal) setelah baris ditambahkan
        window.syncBatchToOutputItems();

        renumberFgRows();
        window.calcAll();
        if (typeof window.updateFgDuplicateProtection === 'function') {
            window.updateFgDuplicateProtection();
        }
        if (typeof window.syncMasterSwitchFromTable === 'function') {
            window.syncMasterSwitchFromTable();
        }
    };


    window.removeFgRow = function (idx) {
        const tbody = document.getElementById('tbodyOutputFg');
        const rows = tbody ? tbody.querySelectorAll('.row-output-fg') : [];
        if (rows.length <= 1) {
            return; // Minimal 1 baris utama dan tidak dapat dihapus
        }
        const row = document.getElementById(`fgRow_${idx}`);
        if (!row) return;

        // Proteksi mutlak: Baris pertama di tabel tidak boleh dihapus
        if (rows[0] === row) {
            return;
        }

        row.remove();
        renumberFgRows();
        window.calcAll();
        if (typeof window.updateFgDuplicateProtection === 'function') {
            window.updateFgDuplicateProtection();
        }
        if (typeof window.syncMasterSwitchFromTable === 'function') {
            window.syncMasterSwitchFromTable();
        }
    };

    function renumberFgRows() {
        const tbody = document.getElementById('tbodyOutputFg');
        if (!tbody) return;
        const rows = Array.from(tbody.querySelectorAll('.row-output-fg'));
        rows.forEach((tr, i) => {
            const numEl = tr.querySelector('.fg-row-number');
            if (numEl) numEl.textContent = i + 1;
            
            const btnRemove = tr.querySelector('.btn-remove-fg') || tr.querySelector('button[onclick^="removeFgRow"]');
            if (btnRemove) {
                if (i === 0) {
                    btnRemove.title = 'Baris utama tidak dapat dihapus';
                    btnRemove.style.opacity = '0.4';
                    btnRemove.style.cursor = 'not-allowed';
                    btnRemove.style.color = '#94a3b8';
                    btnRemove.disabled = true;
                } else {
                    btnRemove.title = 'Hapus baris ini';
                    btnRemove.style.opacity = '1';
                    btnRemove.style.cursor = 'pointer';
                    btnRemove.style.color = '#dc2626';
                    btnRemove.disabled = false;
                }
            }
        });
    }

    // Helper untuk mereset satu baris tabel hasil barang produksi ke status default kosong
    function resetFgBarangRow(selBarang, idx) {
        if (!selBarang) return;
        selBarang.selectedIndex = 0;
        selBarang.value = '';

        const badgeJenis = document.getElementById(`badgeJenis_${idx}`) || selBarang.closest('tr')?.querySelector('[id^="badgeJenis_"]');
        const inputJenis = document.getElementById(`inputJenis_${idx}`) || selBarang.closest('tr')?.querySelector('[id^="inputJenis_"]');
        const labelSatuan = document.getElementById(`labelSatuan_${idx}`) || selBarang.closest('tr')?.querySelector('[id^="labelSatuan_"]');
        const inputSatuan = document.getElementById(`inputSatuan_${idx}`) || selBarang.closest('tr')?.querySelector('[id^="inputSatuan_"]');
        const badgeFgBatch = document.getElementById(`badgeFgBatch_${idx}`) || selBarang.closest('tr')?.querySelector('[id^="badgeFgBatch_"]');
        const batchFg = document.getElementById(`batchFg_${idx}`) || selBarang.closest('tr')?.querySelector('[id^="batchFg_"]');

        if (badgeJenis) {
            badgeJenis.textContent = '-';
            badgeJenis.style.background = '#f1f5f9';
            badgeJenis.style.color = '#64748b';
            badgeJenis.style.borderColor = '#cbd5e1';
        }
        if (inputJenis) inputJenis.value = '';
        if (labelSatuan) labelSatuan.textContent = '-';
        if (inputSatuan) inputSatuan.value = '';
        if (badgeFgBatch) badgeFgBatch.textContent = '-';
        if (batchFg) batchFg.value = '';

        window.calcAll();
    }

    window.onFgBarangChange = function (sel, idx) {
        const opt = sel.selectedOptions[0];
        if (!opt || !opt.value) {
            resetFgBarangRow(sel, idx);
            if (typeof window.updateFgDuplicateProtection === 'function') {
                window.updateFgDuplicateProtection();
            }
            return;
        }

        const cd = (opt.dataset.cd || '').toUpperCase();
        const nm = (opt.dataset.nm || '').toUpperCase();
        const gradeSel = document.getElementById(`gradeCd_${idx}`);

        // Deteksi default grade: jika nama/kode mengandung KW 2 / Berko / Remahan / Lengket, set KW2; jika tidak set KW1
        // Namun jika barang ini sudah ada di baris lain dengan grade KW1, otomatis alihkan baris ini ke KW2
        let targetGrade = 'KW1';
        if (cd.startsWith('WIP-KW') || nm.includes('KW 2') || nm.includes('KW2') || nm.includes('BERKO') || nm.includes('REMAHAN') || nm.includes('LENGKET')) {
            targetGrade = 'KW2';
        } else {
            const tbody = document.getElementById('tbodyOutputFg');
            const otherRows = Array.from(tbody?.querySelectorAll('.row-output-fg') || []).filter(r => r.id !== `fgRow_${idx}`);
            const hasSameWithKw1 = otherRows.some(r => {
                const otherB = r.querySelector('.select-fg-barang')?.value;
                const otherG = r.querySelector('.select-fg-grade')?.value || 'KW1';
                return otherB === sel.value && otherG === 'KW1';
            });
            if (hasSameWithKw1) {
                targetGrade = 'KW2';
            }
        }

        if (gradeSel) {
            gradeSel.value = targetGrade;
            if (typeof window.onGradeOutputChange === 'function') {
                window.onGradeOutputChange(gradeSel, idx);
            }
        }

        // Proteksi Anti-Duplikat: Cek apakah barang ini sudah dipilih di baris lain
        const currentVal = sel.value;
        if (currentVal) {
            const tbody = document.getElementById('tbodyOutputFg');
            const otherRows = Array.from(tbody?.querySelectorAll('.row-output-fg') || []).filter(r => r.id !== `fgRow_${idx}`);
            const isDuplicate = otherRows.some(r => {
                const otherB = r.querySelector('.select-fg-barang')?.value;
                return otherB === currentVal;
            });
            if (isDuplicate) {
                alert(`⚠️ Barang ini sudah dipilih pada baris lain!\n\nSilakan gabungkan kuantitas hasilnya pada baris yang sudah ada.`);
                sel.value = '';
                resetFgBarangRow(sel, idx);
                if (typeof window.updateFgDuplicateProtection === 'function') {
                    window.updateFgDuplicateProtection();
                }
                return;
            }
        }

        const jenis = opt.dataset.jenis || 'FG';
        const satuan = opt.dataset.satuan || 'KARTON';

        const badgeJenis = document.getElementById(`badgeJenis_${idx}`);
        const inputJenis = document.getElementById(`inputJenis_${idx}`);
        const labelSatuan = document.getElementById(`labelSatuan_${idx}`);
        const inputSatuan = document.getElementById(`inputSatuan_${idx}`);

        if (badgeJenis) {
            badgeJenis.textContent = jenis;
            if (jenis === 'WIP') {
                badgeJenis.style.background = '#e0f2fe';
                badgeJenis.style.color = '#0369a1';
                badgeJenis.style.borderColor = '#bae6fd';
            } else {
                badgeJenis.style.background = '#ecfdf5';
                badgeJenis.style.color = '#065f46';
                badgeJenis.style.borderColor = '#a7f3d0';
            }
        }
        if (inputJenis) inputJenis.value = jenis;
        if (labelSatuan) labelSatuan.textContent = satuan;
        if (inputSatuan) inputSatuan.value = satuan;

        // Auto-assign batch format berdasarkan jenis produk
        window.onQtyHasilInput(idx);

        if (typeof window.updateKartonRangeAndBatch === 'function') {
            window.updateKartonRangeAndBatch(true);
        }
        if (typeof window.updateStickerPreview === 'function') {
            window.updateStickerPreview();
        }
        if (typeof window.updateFgDuplicateProtection === 'function') {
            window.updateFgDuplicateProtection();
        }
        if (typeof window.syncMasterSwitchFromTable === 'function') {
            window.syncMasterSwitchFromTable();
        }
    };

    window.onQtyHasilInput = function (idx, skipSyncKarton = false) {
        const qtyInp = document.getElementById(`qtyHasil_${idx}`);
        const kgInp = document.getElementById(`qtyKg_${idx}`);
        const selBarang = document.querySelector(`select[name="output_items[${idx}][barang_id]"]`);
        if (!qtyInp || !kgInp) return;

        const val = parseFloat(qtyInp.value) || 0;
        const opt = selBarang?.selectedOptions?.[0];
        const nm = (opt?.dataset?.nm || '').toUpperCase();
        const cd = (opt?.dataset?.cd || '').toUpperCase();
        const satuan = (opt?.dataset?.satuan || '').toUpperCase();
        const jenis = (opt?.dataset?.jenis || '').toUpperCase();

        // Ekstrak bobot per unit dari satuan_cd (misal: "KARTON 6KG" → 6, "KARTON 7KG" → 7, "KG" → 1)
        // Pola: cari angka desimal di dalam string satuan (6, 7, 5, 3.8, dsb)
        let beratPerUnit = 1;
        const matchBerat = satuan.match(/(\d+[.,]?\d*)\s*KG/);
        if (matchBerat) {
            beratPerUnit = parseFloat(matchBerat[1].replace(',', '.')) || 1;
        }

        const isKgUnit = satuan === 'KG' || satuan === 'KILOGRAM';

        if (isKgUnit) {
            // Satuan KG langsung: Total Berat = QTY × 1
            kgInp.value = val > 0 ? val : '';
            kgInp.dataset.autoFilled = 'true';
        } else if (beratPerUnit > 1) {
            // Satuan KARTON xKG: Total Berat = QTY × beratPerUnit
            if (!kgInp.value || parseFloat(kgInp.value) === 0 || kgInp.dataset.autoFilled === 'true') {
                kgInp.value = val > 0 ? (val * beratPerUnit).toFixed(2) : '';
                kgInp.dataset.autoFilled = 'true';
            }
        } else {
            // Fallback: coba deteksi dari nama barang jika satuan tidak informatif
            if (!kgInp.value || parseFloat(kgInp.value) === 0 || kgInp.dataset.autoFilled === 'true') {
                if (nm.includes('IFM') || cd.includes('FCC')) {
                    kgInp.value = (val * 6.0).toFixed(2);
                    kgInp.dataset.autoFilled = 'true';
                } else if (nm.includes('500')) {
                    kgInp.value = (val * 5.0).toFixed(2);
                    kgInp.dataset.autoFilled = 'true';
                } else if (nm.includes('1000')) {
                    kgInp.value = (val * 6.0).toFixed(2);
                    kgInp.dataset.autoFilled = 'true';
                } else if (val > 0) {
                    kgInp.value = val;
                    kgInp.dataset.autoFilled = 'true';
                }
            }
        }

        // Sinkronisasi otomatis dua arah ke kolom Karton Selesai di Bagian 2 (jika produk berkemasan karton / IFM)
        if (!skipSyncKarton && (satuan.includes('KARTON') || nm.includes('IFM') || cd.includes('FCC'))) {
            const elKarton = document.getElementById('qty_karton');
            if (elKarton && parseFloat(elKarton.value) !== val && val > 0) {
                elKarton.value = Math.round(val);
                if (typeof window.updateKartonRangeAndBatch === 'function') {
                    window.updateKartonRangeAndBatch(true);
                }
            }
        }

        window.calcAll();
    };


    function autoSelectLiniBarang(idx) {
        const selectLini = document.getElementById('lini_produksi');
        const selBarang = document.querySelector(`select[name="output_items[${idx}][barang_id]"]`) 
            || document.querySelector('#tableOutputFg tbody tr:first-child .select-fg-barang');
        if (!selectLini || !selBarang) return;

        const currentLini = (selectLini.value || '').trim().toUpperCase();
        const selectedOpt = selectLini.selectedOptions?.[0];
        const currentCd = (selectedOpt?.getAttribute('data-cd') || '').trim().toUpperCase();
        const currentTipe = (selectedOpt?.getAttribute('data-tipe') || '').trim().toUpperCase();

        if (!currentLini) {
            // Jika user memilih kembali placeholder "-- Pilih Lini Produksi --", reset pilihan barang
            resetFgBarangRow(selBarang, idx);
            return;
        }

        // Jika user sudah memilih barang di baris ini, jangan ditimpa / di-overwrite
        if (selBarang.value && selBarang.selectedIndex > 0) {
            return;
        }

        // LINI IFL & LINI MANUAL: Merupakan lini mesin penggorengan umum (Continuous Fryer atau Manual).
        // Dapat digunakan untuk memproduksi produk apa saja (Produk Jumbo, IFM, Maksi, Ping-Ping, Berko, dll).
        // Jangan paksa / kunci ke WIP-FCC, beri kebebasan operator memilih produk hasil produksi di tabel Bagian 7.
        const isIflOrManual = currentLini.includes('IFL') || currentLini.includes('MANUAL') || currentCd === 'IFL' || currentCd === 'MANUAL';
        if (isIflOrManual) {
            return;
        }

        const isIfm = currentLini.includes('IFM') || 
                      currentLini.includes('INDOFOOD') || 
                      currentCd === 'IFM' || 
                      currentTipe === 'IFM';

        const tbody = document.getElementById('tbodyOutputFg');
        const otherSelects = Array.from(tbody?.querySelectorAll('.select-fg-barang') || []).filter(s => s !== selBarang);

        for (let i = 0; i < selBarang.options.length; i++) {
            const opt = selBarang.options[i];
            if (!opt.value) continue;
            // Jika barang ini sudah dipilih pada baris lain, jangan dipilih ulang
            if (otherSelects.some(s => s.value === opt.value)) continue;

            const nm = (opt.dataset.nm || opt.getAttribute('data-nm') || '').toUpperCase();
            const cd = (opt.dataset.cd || opt.getAttribute('data-cd') || '').toUpperCase();
            const txt = (opt.text || opt.textContent || '').toUpperCase();

            if (isIfm) {
                // Sesuai aturan: jika lini IFM, otomatis terisi barang hasil produksi IFM (WIP-FCC Keripik Singkong Tanpa Bumbu IFL)
                if (cd.includes('FCC') || cd.includes('IFM') || cd.includes('IFL') || 
                    nm.includes('IFM') || nm.includes('IFL') || nm.includes('INDOFOOD') || 
                    nm.includes('TANPA BUMBU') || txt.includes('FCC') || txt.includes('IFM') || txt.includes('IFL')) {
                    selBarang.value = opt.value;
                    selBarang.selectedIndex = i;
                    opt.selected = true;
                    window.onFgBarangChange(selBarang, idx);
                    break;
                }
            } else if (currentLini.includes('PING-PING') && nm.includes('PING-PING')) {
                selBarang.selectedIndex = i;
                window.onFgBarangChange(selBarang, idx);
                break;
            } else if (currentLini.includes('MAKSI') && nm.includes('MAKSI')) {
                selBarang.selectedIndex = i;
                window.onFgBarangChange(selBarang, idx);
                break;
            } else if (currentLini.includes('JUMBO') && nm.includes('JUMBO')) {
                selBarang.selectedIndex = i;
                window.onFgBarangChange(selBarang, idx);
                break;
            } else if (currentLini.includes('EKSPOR') && nm.includes('EKSPOR')) {
                selBarang.selectedIndex = i;
                window.onFgBarangChange(selBarang, idx);
                break;
            } else if (currentLini.includes('BARCO') && (nm.includes('BARCO') || cd.includes('ASB'))) {
                selBarang.selectedIndex = i;
                window.onFgBarangChange(selBarang, idx);
                break;
            } else if (currentLini.includes('SAWIT') && (nm.includes('SAWIT') || cd.includes('ASW'))) {
                selBarang.selectedIndex = i;
                window.onFgBarangChange(selBarang, idx);
                break;
            } else if (currentLini.includes('BERKO ME') && (nm.includes('BERKO ME') || cd.includes('BRK-ME'))) {
                selBarang.selectedIndex = i;
                window.onFgBarangChange(selBarang, idx);
                break;
            } else if (currentLini.includes('BERKO') && (nm.includes('BERKO') || cd.includes('BRK'))) {
                selBarang.selectedIndex = i;
                window.onFgBarangChange(selBarang, idx);
                break;
            } else if (currentLini.includes('NO SALT') && (nm.includes('NO SALT') || cd.includes('NSL'))) {
                selBarang.selectedIndex = i;
                window.onFgBarangChange(selBarang, idx);
                break;
            } else if ((currentLini.includes('BALO') || currentLini.includes('BALQI')) && (nm.includes('BALO') || cd.includes('BLQ'))) {
                selBarang.selectedIndex = i;
                window.onFgBarangChange(selBarang, idx);
                break;
            }
        }
    }

    // Helper: Hitung Limbah Waste HACCP & Persentase Singkong Lengket
    window.calcWasteHaccp = function () {
        if (typeof window.calcAll === 'function') {
            window.calcAll();
        }
    };

    // Initialize on DOM Ready
    document.addEventListener('DOMContentLoaded', () => {
        window.updateHariLabel();
        window.fetchNextKarton();
        window.calcTk();
        window.calcCng();
        window.calcWasteHaccp();
        window.calcAll();
        window.initAutoKemasanCalc();
        window.highlightMatchingPakai();

        // Pastikan default untuk tabel inputan hasil barang produksi sudah ada minimal 1 baris
        const tbodyFg = document.getElementById('tbodyOutputFg');
        if (tbodyFg && tbodyFg.querySelectorAll('.row-output-fg').length === 0) {
            const oldItems = window.appConfig?.oldOutputItems;
            if (oldItems && typeof oldItems === 'object' && Object.keys(oldItems).length > 0) {
                Object.values(oldItems).forEach(item => window.addFgRow(item));
            } else {
                window.addFgRow();
            }
        }

        renumberFgRows();

        // Cek apakah ada dokumen BPPB yang sudah terpilih (dari old input atau query param)
        const elPakai = document.getElementById('pakai_id');
        if (elPakai && elPakai.value) {
            window.loadPakaiData(elPakai.value);
        } else {
            window.onLiniProduksiChange();
        }

        // Event listener jika user mengedit tanggal kedaluwarsa secara manual
        const elExp = document.getElementById('exp_date');
        if (elExp) {
            elExp.addEventListener('input', () => {
                window._expDateUserModified = true;
                const desc = document.getElementById('expDateBadgeDesc');
                if (desc) desc.textContent = '(Disesuaikan Manual)';
            });
        }

        // Jika tanggal produksi diubah, reset flag user modified agar exp date menghitung ulang dari tanggal baru & sinkronkan karyawan
        const elTglProd = document.getElementById('produksi_tgl');
        if (elTglProd) {
            elTglProd.addEventListener('change', () => {
                window._expDateUserModified = false;
                window.updateKartonRangeAndBatch();
                window.syncLaborFromKaryawan(false, true);
            });
        }

        // Event listener jika user mengedit nilai FOH secara manual
        document.querySelectorAll('.input-foh-auto').forEach(inp => {
            inp.addEventListener('input', () => {
                if (inp.value === '') {
                    delete inp.dataset.userModified;
                } else {
                    inp.dataset.userModified = 'true';
                }
            });
        });

        // Event listener jika user mengedit total upah secara manual
        const elTkTotal = document.getElementById('tk_total_nilai');
        if (elTkTotal) {
            elTkTotal.addEventListener('input', () => {
                elTkTotal.dataset.userEdited = 'true';
            });
        }

        // Inisialisasi sinkronisasi data tenaga kerja dari modul karyawan saat halaman dibuka
        window.syncLaborFromKaryawan(false, false);

        // Inisialisasi proteksi anti-duplikat pilihan barang hasil produksi
        if (typeof window.updateFgDuplicateProtection === 'function') {
            window.updateFgDuplicateProtection();
        }

        // Inisialisasi status toggler batch (aktif standar vs buffer stock IFM)
        const chkBatch = document.getElementById('chk_enable_batch');
        if (chkBatch) {
            window.toggleBatchSwitch(chkBatch.checked);
        }
    });

    // ==============================================================
    // 10. MODAL KONFIRMASI SIMPAN (PRE-FLIGHT OPERATIONAL VERIFICATION)
    // ==============================================================
    function formatTglIndo(tglStr) {
        if (!tglStr) return '-';
        const parts = tglStr.split('-');
        if (parts.length !== 3) return tglStr;
        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        const d = parts[2];
        const m = months[parseInt(parts[1]) - 1] || parts[1];
        const y = parts[0];
        return `${d} ${m} ${y}`;
    }

    window.openModalConfirmCreate = function () {
        // 1. Validasi Input Kunci
        const inpDoc = document.getElementById('produksi_no');
        if (!inpDoc || !inpDoc.value.trim()) {
            alert('Nomor Dokumen Produksi belum diisi. Silakan isi terlebih dahulu.');
            inpDoc?.focus();
            return;
        }

        const inpTgl = document.getElementById('produksi_tgl');
        if (!inpTgl || !inpTgl.value) {
            alert('Tanggal Produksi belum dipilih.');
            inpTgl?.focus();
            return;
        }

        const selLini = document.getElementById('lini_produksi');
        if (!selLini || !selLini.value) {
            alert('Silakan pilih Lini Produksi terlebih dahulu.');
            selLini?.focus();
            return;
        }

        const selGdg = document.getElementById('gudang_id');
        if (!selGdg || !selGdg.value) {
            alert('Silakan pilih Gudang Penerima Hasil Produksi.');
            selGdg?.focus();
            return;
        }

        // Cek tabel hasil output (minimal 1 baris hasil dengan kuantitas > 0)
        const rows = document.querySelectorAll('#tableOutputFg .row-output-fg');
        let totalOutputKg = 0;
        let validItemCount = 0;
        let itemsSummaryList = [];

        rows.forEach((row, idx) => {
            const selBarang = row.querySelector('.select-fg-barang');
            const opt = selBarang?.selectedOptions?.[0];
            const barangNm = opt?.dataset?.nm || opt?.text || '';
            const barangCd = opt?.dataset?.cd || '';
            const qtyHasil = parseFloat(row.querySelector('input[name*="[qty_hasil]"]')?.value) || 0;
            const satuan = row.querySelector('.label-fg-satuan, [id*="labelSatuan_"]')?.textContent || '';
            const qtyKg = parseFloat(row.querySelector('.input-fg-qty-kg')?.value) || 0;
            const batchText = row.querySelector('.badge-batch-wip')?.textContent.trim() || '-';

            if (selBarang?.value && qtyKg > 0) {
                validItemCount++;
                totalOutputKg += qtyKg;
                itemsSummaryList.push({
                    no: validItemCount,
                    cd: barangCd,
                    nm: barangNm,
                    qty: qtyHasil,
                    satuan: satuan,
                    kg: qtyKg,
                    batch: batchText
                });
            }
        });

        if (validItemCount === 0 || totalOutputKg <= 0) {
            alert('Kuantitas hasil produksi belum diisi. Masukkan minimal 1 baris hasil timbangan fisik barang setengah jadi (WIP).');
            document.querySelector('#tableOutputFg input[name*="[qty_hasil]"]')?.focus();
            return;
        }

        // 2. Ekstrak Data untuk Ringkasan Modal
        const docNo = inpDoc.value.trim();
        const tglStr = inpTgl.value;
        const shift = document.getElementById('shift_cd')?.value || 'A';
        const liniText = selLini.options[selLini.selectedIndex]?.text || selLini.value;
        const gdgText = selGdg.options[selGdg.selectedIndex]?.text || selGdg.value;
        const singkongQty = parseFloat(document.getElementById('singkong_qty')?.value) || 0;
        const rendemen = singkongQty > 0 ? (totalOutputKg / singkongQty) * 100 : 0;

        // Pasang data ke elemen modal
        const elDocNo = document.getElementById('modalSummaryDocNo');
        if (elDocNo) elDocNo.textContent = docNo;

        const elTglShift = document.getElementById('modalSummaryTglShift');
        if (elTglShift) elTglShift.textContent = `${formatTglIndo(tglStr)} (Shift ${shift})`;

        const elLini = document.getElementById('modalSummaryLini');
        if (elLini) elLini.textContent = liniText;

        const elGdg = document.getElementById('modalSummaryGudang');
        if (elGdg) elGdg.textContent = gdgText;

        const elSingkong = document.getElementById('modalSummarySingkong');
        if (elSingkong) elSingkong.textContent = formatNumber(singkongQty, 2) + ' kg';

        const elOutput = document.getElementById('modalSummaryOutput');
        if (elOutput) elOutput.textContent = formatNumber(totalOutputKg, 2) + ' kg';

        const elItemCount = document.getElementById('modalSummaryItemCount');
        if (elItemCount) elItemCount.textContent = `${validItemCount} Baris Olahan`;

        const elRendemen = document.getElementById('modalSummaryRendemen');
        const elRendemenStatus = document.getElementById('modalSummaryRendemenStatus');
        if (elRendemen) {
            elRendemen.textContent = formatNumber(rendemen, 2) + '%';
            if (rendemen >= 33.0) {
                elRendemen.style.color = '#16a34a';
                if (elRendemenStatus) {
                    elRendemenStatus.textContent = 'Rendemen Optimal (≥ 33%)';
                    elRendemenStatus.style.color = '#15803d';
                }
            } else if (rendemen >= 30.0) {
                elRendemen.style.color = '#d97706';
                if (elRendemenStatus) {
                    elRendemenStatus.textContent = 'Rendemen Standar (30 - 33%)';
                    elRendemenStatus.style.color = '#b45309';
                }
            } else {
                elRendemen.style.color = '#dc2626';
                if (elRendemenStatus) {
                    elRendemenStatus.textContent = 'Rendemen Rendah (< 30%)';
                    elRendemenStatus.style.color = '#b91c1c';
                }
            }
        }

        // Render Rincian Item di Tabel Modal
        const elTbody = document.getElementById('modalSummaryItemsTableBody');
        const elTableTotal = document.getElementById('modalSummaryTableTotalKg');
        if (elTbody) {
            let html = '';
            itemsSummaryList.forEach((item, i) => {
                const isBelumTerbit = item.batch === 'BELUM TERBIT';
                const batchBadge = isBelumTerbit 
                    ? `<span style="color: #92400e; background: #fffbeb; border: 1px solid #fde68a; padding: 1px 6px; border-radius: 4px; font-weight: 700; font-size: 0.7rem;">BELUM TERBIT</span>`
                    : `<span style="color: #166534; background: #f0fdf4; border: 1px solid #bbf7d0; padding: 1px 6px; border-radius: 4px; font-weight: 700; font-size: 0.7rem;">${item.batch}</span>`;
                
                html += `
                    <tr>
                        <td style="text-align: center; color: #64748b; font-weight: 600;">${i + 1}</td>
                        <td style="font-weight: 600; color: #0f172a;">${item.nm}</td>
                        <td style="text-align: right; font-variant-numeric: tabular-nums;">${item.qty > 0 ? formatNumber(item.qty, 0) : '-'}</td>
                        <td style="text-align: center; color: #475569; font-size: 0.75rem;">${item.satuan || '-'}</td>
                        <td style="text-align: right; font-weight: 700; color: #0f172a; font-variant-numeric: tabular-nums;">${formatNumber(item.kg, 2)} kg</td>
                        <td style="text-align: center;">${batchBadge}</td>
                    </tr>
                `;
            });
            elTbody.innerHTML = html;
        }
        if (elTableTotal) {
            elTableTotal.textContent = formatNumber(totalOutputKg, 2) + ' kg';
        }

        // Tampilkan Modal
        const modal = document.getElementById('modalConfirmCreate');
        if (modal) {
            modal.classList.add('show');
            modal.style.display = 'flex';
        }
    };

    window.closeModalConfirmCreate = function () {
        const modal = document.getElementById('modalConfirmCreate');
        if (modal) {
            modal.classList.remove('show');
            modal.style.display = 'none';
        }
    };

    window.submitCreateForm = function () {
        const btnSubmit = document.getElementById('btnSubmitConfirm');
        const btnCancel = document.getElementById('btnCancelConfirm');
        const btnCloseX = document.getElementById('btnCloseModalX');
        const form = document.getElementById('formProduksi');

        if (!form) return;

        // Kunci tombol agar tidak double submit
        if (btnSubmit) {
            btnSubmit.disabled = true;
            btnSubmit.style.opacity = '0.85';
            btnSubmit.style.cursor = 'not-allowed';
            btnSubmit.innerHTML = `
                <svg class="spinner-btn-anim" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="vertical-align: middle;">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25"/>
                    <path d="M12 2a10 10 0 0 1 10 10"/>
                </svg>
                <span>Menyimpan Dokumen...</span>
            `;
        }

        if (btnCancel) {
            btnCancel.disabled = true;
            btnCancel.style.pointerEvents = 'none';
        }

        if (btnCloseX) {
            btnCloseX.disabled = true;
            btnCloseX.style.pointerEvents = 'none';
        }

        // Kirim form ke server
        form.submit();
    };

})();
