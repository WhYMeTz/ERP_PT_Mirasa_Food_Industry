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
        const opt = sel?.selectedOptions?.[0];
        const cd = opt?.dataset?.cd || '';
        const nm = opt?.dataset?.nm || '';

        const isCurah = isCurahWipProduct(cd, nm);
        const batchForItem = isCurah
            ? getDateBatch()                                               // Curah → tanggal
            : (document.getElementById('batch_wip_no')?.value || '-');    // Kemasan → range karton

        const badgeFgBatch = document.getElementById(`badgeFgBatch_${idx}`);
        const batchFgInput = document.getElementById(`batchFg_${idx}`);

        if (badgeFgBatch) {
            badgeFgBatch.textContent = batchForItem;
            if (isCurah) {
                // Hijau = curah/Berko → format tanggal
                badgeFgBatch.style.background = '#f0fdf4';
                badgeFgBatch.style.color = '#065f46';
                badgeFgBatch.style.borderColor = '#a7f3d0';
                badgeFgBatch.title = 'Batch Curah: Format Tanggal (DD MM YYYY)';
            } else if (batchForItem && batchForItem !== '-') {
                // Biru = kemasan karton IFM → format range
                badgeFgBatch.style.background = '#f0f9ff';
                badgeFgBatch.style.color = '#0369a1';
                badgeFgBatch.style.borderColor = '#bae6fd';
                badgeFgBatch.title = 'Batch Karton: Format Range Shift [NoAwal]-[NoAkhir]';
            } else {
                // Abu-abu = belum ada batch
                badgeFgBatch.style.background = '#f8fafc';
                badgeFgBatch.style.color = '#64748b';
                badgeFgBatch.style.borderColor = '#cbd5e1';
                badgeFgBatch.title = 'Kode Batch otomatis setelah Lini dipilih';
            }
        }
        if (batchFgInput) batchFgInput.value = batchForItem;
    }

    // Helper: Sinkronisasi Kode Batch Bagian 2 ke seluruh Baris Output Barang Jadi (Bagian 7)
    // Per baris: kemasan IFM → range, WIP curah → tanggal
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
        const noAwal = parseInt(document.getElementById('no_karton_awal')?.value) || 1;
        const qtyKarton = parseInt(document.getElementById('qty_karton')?.value) || 0;
        const liniSelect = document.getElementById('lini_produksi');
        const selectedOpt = liniSelect?.selectedOptions?.[0];
        const lini = liniSelect?.value || '';
        const selectedTipe = selectedOpt?.getAttribute('data-tipe');
        const selectedKategori = selectedOpt?.getAttribute('data-kategori') || '';
        const selectedCd = selectedOpt?.getAttribute('data-cd') || '';
        const tgl = document.getElementById('produksi_tgl')?.value || '';

        // Cek apakah Lini adalah Standar Indofood IFM / IFL (WIP-FCC berkarton urut 6 kg)
        const isIfm = Boolean(
            lini && (
                selectedTipe === 'IFM' || 
                lini.toUpperCase().includes('IFM') || 
                lini.toUpperCase().includes('IFL') || 
                lini.toUpperCase().includes('INDOFOOD') || 
                selectedCd === 'IFM' || 
                selectedCd === 'IFL'
            )
        );

        // Cek apakah Lini adalah Olahan Curah WIP (Penggorengan / Keripik)
        const isWip = Boolean(
            selectedKategori.toUpperCase().includes('WIP') || 
            lini.toUpperCase().includes('PENGGORENGAN') || 
            lini.toUpperCase().includes('BERKO') || 
            lini.toUpperCase().includes('BARCO') || 
            lini.toUpperCase().includes('SAWIT') || 
            lini.toUpperCase().includes('NO SALT') || 
            lini.toUpperCase().includes('BALQI') ||
            lini.toUpperCase().includes('KERIPIK')
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
            // MODE IFM: Nomor Karton Awal & Akhir WAJIB tampil
            if (colAwal) colAwal.style.display = 'block';
            if (colAkhir) colAkhir.style.display = 'block';
            if (colQtyKarton) colQtyKarton.style.display = 'block';
            if (rowShiftKartonGrid) rowShiftKartonGrid.style.gridTemplateColumns = '1.2fr 1fr 1fr 1fr';

            if (cardHeaderTitle) cardHeaderTitle.textContent = '2. Shift Kerja, Penomoran Batch & Kemasan Karton (Standar Indofood IFL / IFM)';
            if (cardHeaderBadge) {
                cardHeaderBadge.innerHTML = 'Format Batch Karton: [Shift][NoAwal] - [Shift][NoAkhir]';
                cardHeaderBadge.style.background = '#f1f5f9';
                cardHeaderBadge.style.color = '#334155';
                cardHeaderBadge.style.borderColor = '#cbd5e1';
            }
            if (labelKartonTitle) labelKartonTitle.innerHTML = 'Karton Selesai <span style="color: #ef4444;">*</span>';
            if (unitKartonSuffix) unitKartonSuffix.textContent = 'Box 6 Kg';
            if (liveEstimasiDesc) liveEstimasiDesc.textContent = '(Netto 6 Kg/Box)';
        } else {
            // MODE SELAIN IFM: Nomor Karton Awal & Akhir DISEMBUNYIKAN (tidak relevan)
            if (colAwal) colAwal.style.display = 'none';
            if (colAkhir) colAkhir.style.display = 'none';

            if (isWip) {
                // Curah WIP: Kuantitas diinput langsung pada timbangan kg di Bagian 7
                if (colQtyKarton) colQtyKarton.style.display = 'none';
                if (rowShiftKartonGrid) rowShiftKartonGrid.style.gridTemplateColumns = '1fr';

                if (cardHeaderTitle) cardHeaderTitle.textContent = '2. Shift Kerja & Kode Batch Produksi (Olahan Curah WIP)';
                if (cardHeaderBadge) {
                    cardHeaderBadge.innerHTML = 'Format Batch: Tanggal (DD MM YYYY)';
                    cardHeaderBadge.style.background = '#f1f5f9';
                    cardHeaderBadge.style.color = '#334155';
                    cardHeaderBadge.style.borderColor = '#cbd5e1';
                }
            } else {
                // Barang Jadi Reguler Mirasa (Ping-Ping, Maksi, Jumbo, dll)
                if (colQtyKarton) colQtyKarton.style.display = 'block';
                if (rowShiftKartonGrid) rowShiftKartonGrid.style.gridTemplateColumns = '1.2fr 1fr';

                if (cardHeaderTitle) cardHeaderTitle.textContent = '2. Shift Kerja, Penomoran Batch & Kemasan Hasil Produksi';
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

        // Hitung nomor akhir: awal + qty - 1
        let noAkhir = noAwal;
        if (qtyKarton > 0) {
            noAkhir = noAwal + qtyKarton - 1;
        }
        const inputAkhir = document.getElementById('no_karton_akhir');
        if (inputAkhir) inputAkhir.value = noAkhir;

        // Kode Batch: IFM vs Reguler Mirasa
        let batchCode = '';
        if (isIfm) {
            if (qtyKarton > 0) {
                batchCode = `${shift}${pad4(noAwal)} - ${shift}${pad4(noAkhir)}`;
            } else {
                batchCode = `${shift}${pad4(noAwal)} - ${shift}${pad4(noAwal)}`;
            }
        } else {
            // Format Batch Tanggal Persis Buku Persediaan Excel PT Mirasa: DD MM YYYY (cth: 02 01 2026)
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
        }

        const liveBatch = document.getElementById('liveBatchCode');
        if (liveBatch) liveBatch.textContent = batchCode;

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

        // Sinkronkan kode batch ke baris output barang jadi di Bagian 7
        window.syncBatchToOutputItems();

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

        // Update highlight dokumen pengeluaran gudang yang cocok
        window.highlightMatchingPakai();
    };

    // Handler Interaktif saat Lini Produksi Berubah:
    // Otomatis menyesuaikan produk default di tabel hasil produksi, nomor batch, dan kartu kemasan
    window.onLiniProduksiChange = function () {
        const liniSelect = document.getElementById('lini_produksi');
        const lini = liniSelect?.value || '';

        // Auto-select baris pertama di tabel hasil produksi sesuai lini yang dipilih
        const tbody = document.getElementById('tbodyOutputFg');
        if (tbody) {
            if (tbody.querySelectorAll('.row-output-fg').length === 0) {
                window.addFgRow();
            } else {
                autoSelectLiniBarang(0);
            }
        }

        window.updateKartonRangeAndBatch();
        window.syncBatchToOutputItems();
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
        const selectedTipe = selectedOpt?.getAttribute('data-tipe');
        const isIfm = Boolean(
            lini && (
                selectedTipe === 'IFM' || 
                lini.toUpperCase().includes('IFM') || 
                lini.toUpperCase().includes('IFL') || 
                lini.toUpperCase().includes('INDOFOOD')
            )
        );

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

            if (isIfm || !lini) {
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
                // Mode Barang Jadi Reguler Mirasa
                const productName = lini.replace('PRODUKSI ', '') || 'KEMASAN RETAIL';
                if (stMainTitle) stMainTitle.textContent = productName;
                if (stNetto) stNetto.textContent = 'KEMASAN BAL';
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

    // 6. Update Hari Label
    window.updateHariLabel = function () {
        const val = document.getElementById('produksi_tgl')?.value;
        if (!val) return;
        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const d = new Date(val);
        const dayNm = days[d.getDay()];
        const el = document.getElementById('hariLabel');
        if (el) el.textContent = `Hari: ${dayNm}`;
    };

    // 7. Hitung Biaya Gas CNG
    window.calcCng = function () {
        const mmbtu = parseFloat(document.getElementById('cng_mmbtu')?.value) || 0;
        const tarif = parseFloat(document.getElementById('cng_tarif')?.value) || 0;
        const el = document.getElementById('cng_nilai');
        if (el) el.value = Math.round(mmbtu * tarif);
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

                updateBadge('badge_foh_fotocopy', 'x ' + formatNumber(window.FOH_RATES.FOTOCOPY, 2));
                updateBadge('sublabel_foh_fotocopy', 'Rp ' + formatNumber(window.FOH_RATES.FOTOCOPY, 2) + ' / Kg WIP');
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

    // Fungsi untuk mereset dan menghitung ulang seluruh nilai FOH berdasarkan Total Output KG WIP
    window.recalcFohStandard = function (forceReset = false) {
        let totalOutputKg = 0;
        document.querySelectorAll('.input-fg-qty-kg').forEach(inp => {
            totalOutputKg += parseFloat(inp.value) || 0;
        });

        const syncField = (id, val) => {
            const el = document.getElementById(id);
            if (!el) return;
            if (forceReset || !el.dataset.userModified || el.value === '' || parseFloat(el.value) === 0) {
                el.value = (val > 0 ? Math.round(val) : (forceReset ? '0' : el.value));
                if (forceReset) delete el.dataset.userModified;
            }
        };

        if (totalOutputKg > 0 || forceReset) {
            syncField('qc_pengawasan_nilai', totalOutputKg * window.FOH_RATES.QC);
            syncField('listrik_air_telp_nilai', totalOutputKg * window.FOH_RATES.LISTRIK);
            syncField('pemeliharaan_mesin_nilai', totalOutputKg * window.FOH_RATES.PEMELIHARAAN);
            syncField('penyusutan_mesin_nilai', totalOutputKg * window.FOH_RATES.PENYUSUTAN);
            syncField('limbah_kimia_nilai', totalOutputKg * window.FOH_RATES.KIMIA);
            syncField('fotocopy_nilai', totalOutputKg * window.FOH_RATES.FOTOCOPY);

            const elLimbahP = document.getElementById('limbah_padat_nilai');
            if (elLimbahP && (forceReset || !elLimbahP.dataset.userModified || elLimbahP.value === '' || parseFloat(elLimbahP.value) === 0)) {
                elLimbahP.value = Math.round(window.FOH_RATES.LIMBAH_PADAT_SHIFT);
                if (forceReset) delete elLimbahP.dataset.userModified;
            }
        }

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
        document.querySelectorAll('.input-fg-qty-kg').forEach(inp => {
            totalOutputKg += parseFloat(inp.value) || 0;
        });

        const badgeTotalOutput = document.getElementById('badgeTotalOutputKg');
        if (badgeTotalOutput) {
            badgeTotalOutput.textContent = 'Total Output: ' + formatNumber(totalOutputKg, 2) + ' kg';
        }

        // 4. Kalkulasi Otomatis Standar FOH Sesuai Pengali Excel Asli PT Mirasa
        // Rumus: Total KG WIP × Tarif Standar Pengali (jika field belum diedit manual oleh user)
        if (totalOutputKg > 0 && typeof window.FOH_RATES === 'object') {
            const autoSyncFoh = (id, val) => {
                const el = document.getElementById(id);
                if (el && (!el.dataset.userModified || el.value === '' || parseFloat(el.value) === 0)) {
                    el.value = Math.round(val);
                }
            };
            autoSyncFoh('qc_pengawasan_nilai', totalOutputKg * window.FOH_RATES.QC);
            autoSyncFoh('listrik_air_telp_nilai', totalOutputKg * window.FOH_RATES.LISTRIK);
            autoSyncFoh('pemeliharaan_mesin_nilai', totalOutputKg * window.FOH_RATES.PEMELIHARAAN);
            autoSyncFoh('penyusutan_mesin_nilai', totalOutputKg * window.FOH_RATES.PENYUSUTAN);
            autoSyncFoh('limbah_kimia_nilai', totalOutputKg * window.FOH_RATES.KIMIA);
            autoSyncFoh('fotocopy_nilai', totalOutputKg * window.FOH_RATES.FOTOCOPY);

            const elLimbahP = document.getElementById('limbah_padat_nilai');
            if (elLimbahP && (!elLimbahP.dataset.userModified || elLimbahP.value === '' || parseFloat(elLimbahP.value) === 0)) {
                elLimbahP.value = Math.round(window.FOH_RATES.LIMBAH_PADAT_SHIFT);
            }
        }

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

        const liveRendemen = document.getElementById('liveRendemen');
        const liveRendemenStatus = document.getElementById('liveRendemenStatus');

        if (liveRendemen) liveRendemen.textContent = rendemen.toFixed(2) + '%';
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
        const targetClean = target.replace(/^PRODUKSI\s+/, '').trim();

        // 1. Pencocokan langsung (exact match)
        for (let i = 0; i < elLini.options.length; i++) {
            const opt = elLini.options[i];
            const val = (opt.value || '').trim().toUpperCase();
            if (val && val === target) return i;
        }

        // 2. Pencocokan tanpa prefix 'PRODUKSI ' atau berdasarkan kode lini (data-cd)
        for (let i = 0; i < elLini.options.length; i++) {
            const opt = elLini.options[i];
            const val = (opt.value || '').trim().toUpperCase();
            const cd = (opt.getAttribute('data-cd') || '').trim().toUpperCase();
            const valClean = val.replace(/^PRODUKSI\s+/, '').trim();
            if (valClean && (valClean === targetClean || cd === target || cd === targetClean)) {
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

        // 4. Fallback substring
        for (let i = 0; i < elLini.options.length; i++) {
            const opt = elLini.options[i];
            const val = (opt.value || '').trim().toUpperCase();
            if (!val) continue;
            const valClean = val.replace(/^PRODUKSI\s+/, '').trim();
            if (targetClean && (valClean.includes(targetClean) || targetClean.includes(valClean))) {
                return i;
            }
        }

        return -1;
    }

    // 11. Load Pemakaian Data dari Dokumen Gudang (AJAX)
    window.loadPakaiData = function (pakaiId) {
        if (!pakaiId) {
            const noticeEl = document.getElementById('pakaiMatchNotice');
            if (noticeEl) noticeEl.style.display = 'none';
            return;
        }

        // Sinkronisasi instan lini produksi dari atribut data-tujuan option terpilih
        const pakaiSelect = document.getElementById('pakai_id');
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

        const loading = document.getElementById('pakaiLoading');
        if (loading) loading.style.display = 'block';

        const url = `${window.appConfig.pakaiDataUrl}/${pakaiId}`;
        fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(res => {
                if (!res.ok) {
                    throw new Error(`HTTP ${res.status}: ${res.statusText}`);
                }
                return res.json();
            })
            .then(res => {
                if (loading) loading.style.display = 'none';
                if (res.status === 'success') {
                    const d = res.data;
                    try {
                        const setVal = (id, val) => {
                            const el = document.getElementById(id);
                            if (el) el.value = val || 0;
                        };

                        // Catatan: Tanggal produksi sengaja dipertahankan tanggal hari ini (tidak mengikuti tanggal BPPB)

                        // 1. Auto-sinkronkan Lini Produksi sesuai Tujuan BPPB
                        if (d.tujuan_pemakaian) {
                            const elLini = document.getElementById('lini_produksi');
                            if (elLini) {
                                const matchedIndex = findMatchingLiniIndex(elLini, d.tujuan_pemakaian);
                                if (matchedIndex !== -1 && elLini.selectedIndex !== matchedIndex) {
                                    elLini.selectedIndex = matchedIndex;
                                    elLini.dispatchEvent(new Event('change', { bubbles: true }));
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
                        setVal('bumbu_nilai', d.bumbu_nilai);
                        setVal('karton_baru_nilai', d.karton_baru_nilai);
                        setVal('karton_bekas_nilai', d.karton_bekas_nilai);
                        setVal('plastik_hd_nilai', d.plastik_hd_nilai);
                        setVal('lakban_besar_nilai', d.lakban_besar_nilai);
                        setVal('lakban_kecil_nilai', d.lakban_kecil_nilai);
                        setVal('tali_rafia_nilai', d.tali_rafia_nilai);

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
                            window.renderTabelPemakaian(d.items);
                        }

                        // Notifikasi sukses tarik data
                        const noticeEl = document.getElementById('pakaiMatchNotice');
                        if (noticeEl) {
                            const isNoticeManual = d.tujuan_pemakaian && d.tujuan_pemakaian.toUpperCase().includes('MANUAL');
                            const isNoticeIfm = d.tujuan_pemakaian && (d.tujuan_pemakaian.toUpperCase().includes('IFM') || d.tujuan_pemakaian.toUpperCase().includes('IFL'));
                            if (isNoticeManual) {
                                noticeEl.innerHTML = `Dokumen <strong>[${d.pakai_no || ''}]</strong> berhasil ditarik: Lini disinkronkan ke <strong>${d.tujuan_pemakaian}</strong>. Silakan tentukan barang hasil produksi secara manual.`;
                            } else if (isNoticeIfm) {
                                noticeEl.innerHTML = `Dokumen <strong>[${d.pakai_no || ''}]</strong> berhasil ditarik: Lini disinkronkan ke <strong>${d.tujuan_pemakaian}</strong> dan barang hasil produksi IFM terisi otomatis.`;
                            } else {
                                noticeEl.innerHTML = `Dokumen <strong>[${d.pakai_no || ''}]</strong> berhasil ditarik: Lini disinkronkan ke <strong>${d.tujuan_pemakaian || '-'}</strong> dan seluruh bahan terisi otomatis.`;
                            }
                            noticeEl.style.color = '#0284c7';
                            noticeEl.style.display = 'block';
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
                if (loading) loading.style.display = 'none';
                console.error('Error loadPakaiData:', err);
                alert('Terjadi kesalahan saat memuat data pemakaian bahan: ' + err.message);
            });
    };

    // 12. Render Tabel Pemakaian Bahan (Blueprint 9.B.1)
    window.renderTabelPemakaian = function (items) {
        const wrapper = document.getElementById('wrapperTabelPemakaian');
        const tbody = document.getElementById('tbodyTabelPemakaian');
        const badgeCount = document.getElementById('badgeJumlahItemPakai');
        if (!wrapper || !tbody) return;

        if (!items || items.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" style="padding: 1rem; text-align: center; color: #94a3b8; font-style: italic;">
                        Tidak ada rincian bahan pada dokumen pengeluaran gudang ini.
                    </td>
                </tr>
            `;
            if (badgeCount) badgeCount.textContent = '0 Item';
            wrapper.style.display = 'block';
            return;
        }

        let html = '';
        items.forEach((it, idx) => {
            html += `
                <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                    <td style="padding: 0.6rem 0.85rem; text-align: center; font-weight: 600; color: #64748b;">${idx + 1}</td>
                    <td style="padding: 0.6rem 0.85rem; color: #334155; font-weight: 600;">${it.pakai_tgl || '-'}</td>
                    <td style="padding: 0.6rem 0.85rem;">
                        <span style="background: #e0f2fe; color: #0369a1; padding: 0.15rem 0.45rem; border-radius: 4px; font-weight: 700; font-family: monospace; font-size: 0.75rem;">
                            ${it.barang_cd || '-'}
                        </span>
                    </td>
                    <td style="padding: 0.6rem 0.85rem; font-weight: 700; color: #0f172a;">${it.barang_nm || '-'}</td>
                    <td style="padding: 0.6rem 0.85rem; text-align: right; font-weight: 800; color: #0284c7;">
                        ${formatNumber(it.qty_keluar, 2)} <span style="font-size: 0.725rem; font-weight: 600; color: #64748b;">${it.satuan_cd || 'KG'}</span>
                    </td>
                    <td style="padding: 0.6rem 0.85rem; text-align: right; font-weight: 700; color: #059669;">
                        ${formatNumber(it.sisa_stok, 2)} <span style="font-size: 0.725rem; font-weight: 600; color: #64748b;">${it.satuan_cd || 'KG'}</span>
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
        if (badgeCount) badgeCount.textContent = `${items.length} Item Bahan`;
        wrapper.style.display = 'block';
    };

    // 13. Logika Baris Dinamis Output Barang Hasil Produksi (Tabel Terpadu)
    let fgRowCounter = document.querySelectorAll('.row-output-fg').length || 1;
    window.addFgRow = function (initial) {
        const tbody = document.getElementById('tbodyOutputFg');
        if (!tbody) return;

        const idx = fgRowCounter++;
        const currentBatch = document.getElementById('batch_wip_no')?.value || '';
        const currentQtyKarton = parseFloat(document.getElementById('qty_karton')?.value) || 0;

        const barangList = window.appConfig.barangHasilList || [];
        
        const fgItems = [];
        const wipItems = [];
        barangList.forEach(b => {
            const jenis = (b.jenis_barang?.jenis_barang_cd || (b.barang_cd.startsWith('WIP') ? 'WIP' : 'FG')).toUpperCase();
            if (jenis === 'WIP') wipItems.push(b);
            else fgItems.push(b);
        });

        // Buat opsi dropdown barang dengan pengelompokan optgroup yang rapi
        let optionsHtml = '<option value="">-- Pilih Barang Hasil Produksi --</option>';

        if (fgItems.length > 0) {
            optionsHtml += '<optgroup label="── BARANG JADI (FINISH GOOD / FG) ──">';
            fgItems.forEach(b => {
                const jenis = 'FG';
                const satuan = b.satuan_dasar?.satuan_cd || 'KARTON';
                const selected = (initial && initial.barang_id == b.barang_id) ? 'selected' : '';
                optionsHtml += `<option value="${b.barang_id}" data-cd="${b.barang_cd}" data-nm="${b.barang_nm}" data-jenis="${jenis}" data-satuan="${satuan}" ${selected}>
                    [${b.barang_cd}] ${b.barang_nm} (${satuan})
                </option>`;
            });
            optionsHtml += '</optgroup>';
        }

        if (wipItems.length > 0) {
            optionsHtml += '<optgroup label="── OLAHAN SETENGAH JADI (WIP CURAH) ──">';
            wipItems.forEach(b => {
                const jenis = 'WIP';
                const satuan = b.satuan_dasar?.satuan_cd || 'KG';
                const selected = (initial && initial.barang_id == b.barang_id) ? 'selected' : '';
                optionsHtml += `<option value="${b.barang_id}" data-cd="${b.barang_cd}" data-nm="${b.barang_nm}" data-jenis="${jenis}" data-satuan="${satuan}" ${selected}>
                    [${b.barang_cd}] ${b.barang_nm} (${satuan})
                </option>`;
            });
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
            </td>
            <td style="padding: 0.5rem 0.75rem;">
                <input type="number" step="0.0001" min="0" name="output_items[${idx}][qty_hasil]" id="qtyHasil_${idx}" class="form-control calc-trigger" style="text-align: right; font-weight: 700; color: #0f172a;" placeholder="0" value="${initial?.qty_hasil || (currentQtyKarton > 0 ? currentQtyKarton : '')}" oninput="onQtyHasilInput(${idx})">
            </td>
            <td style="padding: 0.5rem 0.75rem; text-align: center;">
                <span id="labelSatuan_${idx}" style="font-weight: 600; color: #475569; font-size: 0.775rem;">${initial?.satuan_cd || '-'}</span>
                <input type="hidden" name="output_items[${idx}][satuan_cd]" id="inputSatuan_${idx}" value="${initial?.satuan_cd || ''}">
            </td>
            <td style="padding: 0.5rem 0.75rem;">
                <input type="number" step="0.0001" min="0" name="output_items[${idx}][qty_kg]" id="qtyKg_${idx}" class="form-control input-fg-qty-kg calc-trigger" style="text-align: right; font-weight: 700; color: #0f172a; background: #ffffff;" placeholder="0" value="${initial?.qty_kg || ''}" oninput="calcAll()">
            </td>
            <td style="padding: 0.5rem 0.75rem; text-align: center;">
                <span class="badge-batch-wip" id="badgeFgBatch_${idx}" style="font-size: 0.75rem; font-weight: 700; color: #334155; background: #f8fafc; border: 1px solid #cbd5e1; padding: 0.2rem 0.5rem; border-radius: 4px; display: inline-block;">
                    ${currentBatch || '-'}
                </span>
                <input type="hidden" name="output_items[${idx}][batch_no]" id="batchFg_${idx}" value="${initial?.batch_no || currentBatch}">
            </td>
            <td style="padding: 0.5rem 0.75rem; text-align: center;">
                <button type="button" onclick="removeFgRow(${idx})" class="btn btn-sm btn-remove-fg" id="btnRemoveFg_${idx}" style="padding: 0.2rem 0.45rem; font-size: 0.75rem; background: #ffffff; border: 1px solid #cbd5e1; color: #dc2626; font-weight: 600;" title="Hapus baris ini">
                    Hapus
                </button>
            </td>
        `;

        tbody.appendChild(tr);

        // Jika ada initial value atau default auto selection
        if (!initial) {
            autoSelectLiniBarang(idx);
        }

        // Sinkronkan batch per jenis produk (range karton vs tanggal) setelah baris ditambahkan
        window.syncBatchToOutputItems();

        renumberFgRows();
        window.calcAll();
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
            return;
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

        // ── Auto-assign batch format berdasarkan jenis produk:
        // Kemasan karton (IFM/FCC) → format range (A0001 - A0243)
        // WIP Curah / Berko dll    → format tanggal (07 10 2026)
        applyBatchBadgeToRow(idx, sel);

        window.onQtyHasilInput(idx);
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

        const isManual = currentLini.includes('MANUAL') || currentCd === 'MANUAL';
        if (isManual) {
            // Sesuai aturan: jika lini produksinya manual, tabel hasil barang produksi tetap tampilan default "pilih barang hasil produksi"
            resetFgBarangRow(selBarang, idx);
            return;
        }

        const isIfm = currentLini.includes('IFM') || 
                      currentLini.includes('IFL') || 
                      currentLini.includes('INDOFOOD') || 
                      currentCd === 'IFM' || 
                      currentCd === 'IFL' || 
                      currentTipe === 'IFM';

        for (let i = 0; i < selBarang.options.length; i++) {
            const opt = selBarang.options[i];
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

    // Initialize on DOM Ready
    document.addEventListener('DOMContentLoaded', () => {
        window.updateHariLabel();
        window.fetchNextKarton();
        window.calcTk();
        window.calcCng();
        window.calcAll();
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

        // Jika tanggal produksi diubah, reset flag user modified agar exp date menghitung ulang dari tanggal baru
        const elTglProd = document.getElementById('produksi_tgl');
        if (elTglProd) {
            elTglProd.addEventListener('change', () => {
                window._expDateUserModified = false;
                window.updateKartonRangeAndBatch();
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
    });

})();
