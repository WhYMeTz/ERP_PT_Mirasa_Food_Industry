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
                btnA.classList.add('active-a');
                btnB.classList.remove('active-b');
            } else {
                btnB.classList.add('active-b');
                btnA.classList.remove('active-a');
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

    // 3. Kalkulasi Rentang Karton & Kode Batch WIP / Barang Jadi
    window.updateKartonRangeAndBatch = function () {
        const shift = document.getElementById('shift_cd')?.value || 'A';
        const noAwal = parseInt(document.getElementById('no_karton_awal')?.value) || 1;
        const qtyKarton = parseInt(document.getElementById('qty_karton')?.value) || 0;
        const liniSelect = document.getElementById('lini_produksi');
        const lini = liniSelect?.value || 'PRODUKSI IFM';
        const selectedTipe = liniSelect?.selectedOptions?.[0]?.getAttribute('data-tipe');
        const tgl = document.getElementById('produksi_tgl')?.value || '';
        const isIfm = selectedTipe === 'IFM' || lini.toUpperCase().includes('IFM');

        // Tampilkan/Sembunyikan elemen spesifik IFM vs Barang Jadi Reguler
        const sectionShift = document.getElementById('sectionShiftSelection');
        const rowKartonRange = document.getElementById('rowKartonRange');
        const bannerRegularFG = document.getElementById('bannerRegularFG');
        const cardHeaderTitle = document.getElementById('cardHeaderTitle');
        const cardHeaderBadge = document.getElementById('cardHeaderBadge');
        const labelKartonTitle = document.getElementById('labelKartonTitle');
        const liveEstimasiDesc = document.getElementById('liveEstimasiDesc');

        const colAwal = document.getElementById('colKartonAwal');
        const colAkhir = document.getElementById('colKartonAkhir');

        if (isIfm) {
            if (sectionShift) sectionShift.style.display = 'block';
            if (colAwal) colAwal.style.display = 'block';
            if (colAkhir) colAkhir.style.display = 'block';
            if (bannerRegularFG) bannerRegularFG.style.display = 'none';
            if (cardHeaderTitle) cardHeaderTitle.textContent = '2. Shift Kerja, Penomoran Batch & Kemasan Karton (Standar Indofood IFM / WIP-FCC)';
            if (cardHeaderBadge) {
                cardHeaderBadge.innerHTML = 'Format Batch Karton: [Shift][NoAwal] - [Shift][NoAkhir]';
                cardHeaderBadge.style.background = '#eff6ff';
                cardHeaderBadge.style.color = '#1e40af';
                cardHeaderBadge.style.borderColor = '#bfdbfe';
            }
            if (labelKartonTitle) labelKartonTitle.textContent = 'Karton Selesai';
            if (liveEstimasiDesc) liveEstimasiDesc.textContent = '(Netto 6 Kg/Box)';
        } else {
            // Mode Barang Jadi Reguler Mirasa (Ping-Ping, Retail)
            if (sectionShift) sectionShift.style.display = 'none';
            if (colAwal) colAwal.style.display = 'none';
            if (colAkhir) colAkhir.style.display = 'none';
            if (bannerRegularFG) bannerRegularFG.style.display = 'block';
            if (cardHeaderTitle) cardHeaderTitle.textContent = '2. Penomoran Batch & Masa Simpan Barang Jadi (Standar Persediaan Mirasa)';
            if (cardHeaderBadge) {
                cardHeaderBadge.innerHTML = 'Format Batch: Tanggal (DD MM YYYY) &bull; Exp: 1 Tahun - 1 Hari';
                cardHeaderBadge.style.background = '#ecfdf5';
                cardHeaderBadge.style.color = '#065f46';
                cardHeaderBadge.style.borderColor = '#a7f3d0';
            }
            if (labelKartonTitle) labelKartonTitle.textContent = 'Kemasan / Bal Hasil Produksi';
            if (liveEstimasiDesc) liveEstimasiDesc.textContent = '(Kemasan Retail Mirasa)';
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
                batchCode = `${shift}0001 - ${shift}0001`;
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

        // Update keterangan expired date
        const expDateFormatted = calcExpDateFormatted(tgl, isIfm);
        const liveExpDate = document.getElementById('liveExpDate');
        if (liveExpDate) {
            liveExpDate.textContent = isIfm ? `${expDateFormatted} (+6 Bulan)` : `${expDateFormatted} (1 Tahun - 1 Hari)`;
        }

        // Update estimasi kg
        const estimasiKg = isIfm ? (qtyKarton * 6.0) : qtyKarton;
        const liveEstimasiKg = document.getElementById('liveEstimasiKg');
        if (liveEstimasiKg) {
            liveEstimasiKg.textContent = formatNumber(estimasiKg, 2) + (isIfm ? ' Kg' : ' Unit/Kg');
        }

        // Update tampilan stiker karton / kemasan
        window.updateStickerPreview();

        // Update highlight dokumen pengeluaran gudang yang cocok
        window.highlightMatchingPakai();
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
        const lini = liniSelect?.value || 'PRODUKSI IFM';
        const selectedTipe = liniSelect?.selectedOptions?.[0]?.getAttribute('data-tipe');
        const isIfm = selectedTipe === 'IFM' || lini.toUpperCase().includes('IFM');

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

        // Update Sticker DOM Elements
        const stMainTitle = document.getElementById('stMainTitle');
        const stNetto = document.getElementById('stNetto');
        const stGross = document.getElementById('stGross');
        const stVarietas = document.getElementById('stVarietas');
        const stDate = document.getElementById('stDate');
        const stExpDate = document.getElementById('stExpDate');
        const stShiftKarton = document.getElementById('stShiftKarton');
        const stTime = document.getElementById('stTime');

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
        } else {
            // Mode Barang Jadi Reguler Mirasa
            const productName = lini.replace('PRODUKSI ', '') || 'PING-PING 2000';
            if (stMainTitle) stMainTitle.textContent = productName;
            if (stNetto) stNetto.textContent = 'KEMASAN BAL';
            if (stGross) stGross.textContent = 'STANDAR';
            if (stShiftKarton) {
                stShiftKarton.textContent = batchCode || tglFormatted;
            }
        }

        const stPlantCode = document.getElementById('stPlantCode');
        if (stPlantCode) {
            stPlantCode.textContent = isIfm ? 'M029 / - / ISA' : 'MIRASA / FG';
        }

        if (stVarietas) stVarietas.textContent = varietas;
        if (stDate) stDate.textContent = tglFormatted;
        if (stExpDate) stExpDate.textContent = expFormatted;
        if (stTime) stTime.textContent = jam.replace(':', '.');
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

        // 3. FOH Overhead
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

        // 4. Output WIP
        const barco = parseFloat(document.getElementById('asin_barco_qty')?.value) || 0;
        const sawit = parseFloat(document.getElementById('asin_sawit_qty')?.value) || 0;
        const berko = parseFloat(document.getElementById('berko_qty')?.value) || 0;
        const berkoMe = parseFloat(document.getElementById('berko_me_qty')?.value) || 0;
        const balo = parseFloat(document.getElementById('balo_gelombang_qty')?.value) || 0;
        const noSalt = parseFloat(document.getElementById('no_salt_qty')?.value) || 0;

        const totalWip = barco + sawit + berko + berkoMe + balo + noSalt;
        const badgeWip = document.getElementById('badgeTotalWip');
        if (badgeWip) badgeWip.textContent = 'Total WIP: ' + formatNumber(totalWip, 2) + ' kg';

        // 5. Rendemen & HPP
        const rendemen = singkongQty > 0 ? (totalWip / singkongQty) * 100 : 0;
        const hppPerKg = totalWip > 0 ? (totalBiaya / totalWip) : 0;

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

        const elHpp = document.getElementById('liveHpp');
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

    // 11. Load Pemakaian Data dari Dokumen Gudang (AJAX)
    window.loadPakaiData = function (pakaiId) {
        if (!pakaiId) return;

        const loading = document.getElementById('pakaiLoading');
        if (loading) loading.style.display = 'block';

        const url = `${window.appConfig.pakaiDataUrl}/${pakaiId}`;
        fetch(url)
            .then(res => res.json())
            .then(res => {
                if (loading) loading.style.display = 'none';
                if (res.status === 'success') {
                    const d = res.data;
                    const setVal = (id, val) => {
                        const el = document.getElementById(id);
                        if (el) el.value = val || 0;
                    };

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
                            window.updateKartonRangeAndBatch();
                        }
                    }

                    window.calcAll();
                } else {
                    alert('Gagal: ' + res.message);
                }
            })
            .catch(err => {
                if (loading) loading.style.display = 'none';
                console.error(err);
                alert('Terjadi kesalahan saat memuat data pemakaian bahan.');
            });
    };

    // Initialize on DOM Ready
    document.addEventListener('DOMContentLoaded', () => {
        window.updateHariLabel();
        window.fetchNextKarton();
        window.calcTk();
        window.calcCng();
        window.calcAll();
        window.highlightMatchingPakai();
    });

})();
