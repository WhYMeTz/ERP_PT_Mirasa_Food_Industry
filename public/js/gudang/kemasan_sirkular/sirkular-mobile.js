/**
 * Logika Interaktif QC Mobile Penerimaan Kemasan Sirkular - ERP PT Mirasa Food Industry
 * Khusus Form Mobile Lapangan Pintu Bongkar Muat (Single Vertical Flow)
 */

document.addEventListener('DOMContentLoaded', function () {
    // 1. Stepper Touch (+10, +1, -1, -10)
    window.stepQty = function (fieldId, delta) {
        const inp = document.getElementById(fieldId);
        if (!inp) return;

        let val = parseFloat(inp.value) || 0;
        val = Math.max(0, Math.round(val + delta));
        inp.value = val;
        window.calcMobileSortir();
    };

    // 2. Set Direct Qty (Quick Reset / Preset)
    window.setDirectQty = function (fieldId, val) {
        const inp = document.getElementById(fieldId);
        if (!inp) return;
        inp.value = Math.max(0, val);
        window.calcMobileSortir();
    };

    // 3. Quick Preset: Samakan dengan Surat Jalan DO
    window.setAllFromDo = function () {
        const inpKirim = document.getElementById('m_kirim_qty');
        const kirim = parseFloat(inpKirim?.value) || 0;

        const inpMulus = document.getElementById('m_mulus_qty');
        const inpBmg = document.getElementById('m_bmg_qty');
        const inpRusak = document.getElementById('m_rusak_qty');

        if (inpMulus) inpMulus.value = kirim;
        if (inpBmg) inpBmg.value = 0;
        if (inpRusak) inpRusak.value = 0;

        window.calcMobileSortir();
    };

    // 4. Auto-Fill dari Surat Jalan Pengiriman (DO)
    const selPengeluaran = document.getElementById('m_pengeluaran_id');
    if (selPengeluaran) {
        selPengeluaran.addEventListener('change', function () {
            const id = this.value;
            if (!id) {
                resetMobilePengeluaranInfo();
                return;
            }

            const url = window.appConfig?.ajaxPengeluaranUrl?.replace(':id', id) || `/gudang/kemasan-sirkular/pengeluaran-detail/${id}`;
            fetch(url)
                .then(res => res.json())
                .then(res => {
                    if (res.success && res.data) {
                        applyMobilePengeluaranData(res.data);
                    }
                })
                .catch(err => console.error('Gagal mengambil data pengeluaran:', err));
        });
    }

    function applyMobilePengeluaranData(data) {
        if (data.customer_id) {
            const custSel = document.getElementById('m_customer_id');
            if (custSel) custSel.value = data.customer_id;
        }
        if (data.supir_nm) {
            const supirInp = document.getElementById('m_supir_nm');
            if (supirInp) supirInp.value = data.supir_nm;
        }
        if (data.nopol_kendaraan) {
            const nopolInp = document.getElementById('m_nopol_kendaraan');
            if (nopolInp) nopolInp.value = data.nopol_kendaraan;
        }
        if (data.ekspedisi_nm) {
            const ekspedisiInp = document.getElementById('m_ekspedisi_nm');
            if (ekspedisiInp) ekspedisiInp.value = data.ekspedisi_nm;
        }

        const kirimBox = parseFloat(data.total_box_kirim) || 0;
        const inpKirim = document.getElementById('m_kirim_qty');
        if (inpKirim) {
            inpKirim.value = kirimBox;
        }

        const statKirim = document.getElementById('m_stat_kirim');
        if (statKirim) statKirim.textContent = kirimBox.toLocaleString('id-ID');

        // Otomatis isi mulus jika saat ini masih 0
        const inpMulus = document.getElementById('m_mulus_qty');
        if (inpMulus && parseFloat(inpMulus.value) === 0) {
            inpMulus.value = kirimBox;
        }

        window.calcMobileSortir();
    }

    function resetMobilePengeluaranInfo() {
        const inpKirim = document.getElementById('m_kirim_qty');
        if (inpKirim) inpKirim.value = 0;
        const statKirim = document.getElementById('m_stat_kirim');
        if (statKirim) statKirim.textContent = '0';
        window.calcMobileSortir();
    }

    // 5. Kalkulasi Live Rekonsiliasi & Indikator Selisih
    window.calcMobileSortir = function () {
        const inpKirim = document.getElementById('m_kirim_qty');
        const inpMulus = document.getElementById('m_mulus_qty');
        const inpBmg = document.getElementById('m_bmg_qty');
        const inpRusak = document.getElementById('m_rusak_qty');
        const inpKembali = document.getElementById('m_kembali_qty');

        const kirim = parseFloat(inpKirim?.value) || 0;
        const mulus = parseFloat(inpMulus?.value) || 0;
        const bmg = parseFloat(inpBmg?.value) || 0;
        const rusak = parseFloat(inpRusak?.value) || 0;

        const totalKembali = mulus + bmg + rusak;
        if (inpKembali) inpKembali.value = totalKembali;

        const selisih = Math.max(0, kirim - totalKembali);

        // Update Stat Badges
        const statKirim = document.getElementById('m_stat_kirim');
        const statKembali = document.getElementById('m_stat_kembali');
        const statSelisih = document.getElementById('m_stat_selisih');

        if (statKirim) statKirim.textContent = kirim.toLocaleString('id-ID');
        if (statKembali) statKembali.textContent = totalKembali.toLocaleString('id-ID');
        if (statSelisih) {
            statSelisih.textContent = selisih.toLocaleString('id-ID');
            statSelisih.style.color = selisih > 0 ? '#dc2626' : '#059669';
        }

        // Update Tally Footer Summary
        const textFooterKembali = document.getElementById('textFooterKembali');
        if (textFooterKembali) {
            textFooterKembali.textContent = totalKembali.toLocaleString('id-ID') + ' Pcs';
        }

        const badgeFooterStatus = document.getElementById('badgeFooterStatus');
        if (badgeFooterStatus) {
            if (kirim > 0 && selisih > 0) {
                badgeFooterStatus.textContent = 'Kurang ' + selisih.toLocaleString('id-ID') + ' Pcs';
                badgeFooterStatus.className = 'tally-footer-badge badge-diff';
            } else if (kirim > 0 && totalKembali === kirim) {
                badgeFooterStatus.textContent = 'Cocok (Lengkap)';
                badgeFooterStatus.className = 'tally-footer-badge badge-match';
            } else if (totalKembali > kirim && kirim > 0) {
                badgeFooterStatus.textContent = 'Lebih +' + (totalKembali - kirim).toLocaleString('id-ID') + ' Pcs';
                badgeFooterStatus.className = 'tally-footer-badge badge-diff';
            } else {
                badgeFooterStatus.textContent = totalKembali > 0 ? 'Fisik: ' + totalKembali.toLocaleString('id-ID') + ' Pcs' : 'Belum Diisi';
                badgeFooterStatus.className = 'tally-footer-badge';
            }
        }

        const chipKirim = document.getElementById('chip_kirim_target');
        if (chipKirim) {
            chipKirim.textContent = kirim.toLocaleString('id-ID');
        }
    };

    // 6. Form Submit Protection
    const form = document.getElementById('formMobileSirkular');
    if (form) {
        form.addEventListener('submit', function (e) {
            const gudangSel = document.getElementById('m_gudang_id');
            if (gudangSel && !gudangSel.value) {
                alert('Silakan pilih Gudang Simpan terlebih dahulu.');
                gudangSel.focus();
                e.preventDefault();
                return;
            }

            const inpKembali = document.getElementById('m_kembali_qty');
            const totalKembali = parseFloat(inpKembali?.value) || 0;

            if (totalKembali <= 0) {
                const lanjut = confirm('Kuantitas fisik kembali masih 0 Pcs. Apakah Anda yakin ingin menyimpan?');
                if (!lanjut) {
                    e.preventDefault();
                    return;
                }
            }

            const btnSubmit = document.getElementById('btnBottomSubmit');
            if (btnSubmit) {
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = `
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="spin-animate">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" opacity="0.3"></circle>
                        <path fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span>Menyimpan Penerimaan...</span>
                `;
            }
        });
    }

    // Inisialisasi awal saat halaman dimuat
    window.calcMobileSortir();
});
