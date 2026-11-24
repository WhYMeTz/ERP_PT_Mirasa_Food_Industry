/**
 * JavaScript Form Tagihan Gas Utilitas Bulanan - ERP PT Mirasa
 * Sesuai Standar Enterprise Kas Keluar (Clean Web Form & Zero Mental Math)
 * Kalkulasi Otomatis DPP, PPN, Total Tagihan, Selisih Pabrik, serta Dialog Konfirmasi
 */

(function () {
    'use strict';

    // State & Konfigurasi dari Blade
    const config = window.gasConfig || {
        totalMmbtuPabrik: 0,
        totalNm3Pabrik: 0,
        totalEstimasiAkrual: 0,
        avgKursJisdor: 16000,
        avgTarifUsd: 14.5,
        periodeBulan: ''
    };

    // DOM Elements - Form & Toolbar
    const formTagihanGas = document.getElementById('formTagihanGas');
    const inputPeriode = document.getElementById('inputPeriodeBulan');
    const selectSupplier = document.getElementById('selectSupplier');
    const inputNoFaktur = document.getElementById('inputNoFaktur');
    const inputTglTagihan = document.getElementById('inputTglTagihan');
    const inputTopHari = document.getElementById('inputTopHari');
    const inputTglJatuhTempo = document.getElementById('inputTglJatuhTempo');

    // DOM Elements - Nilai & Pemakaian
    const inputMmbtuVendor = document.getElementById('inputMmbtuVendor');
    const noteSelisihMmbtu = document.getElementById('noteSelisihMmbtu');

    const inputDppDisplay = document.getElementById('inputDppDisplay');
    const inputDppVendor = document.getElementById('inputDppVendor');
    const noteSelisihDpp = document.getElementById('noteSelisihDpp');

    const selectPpnMode = document.getElementById('selectPpnMode');
    const inputPpnDisplay = document.getElementById('inputPpnDisplay');
    const inputTotalPpn = document.getElementById('inputTotalPpn');
    const ppnStatusNote = document.getElementById('ppnStatusNote');
    const ppnModeSubBadge = document.getElementById('ppnModeSubBadge');

    const inputTotalTagihanDisplay = document.getElementById('inputTotalTagihanDisplay');
    const inputTotalTagihan = document.getElementById('inputTotalTagihan');
    const totalStatusNote = document.getElementById('totalStatusNote');
    const reconStatusBox = document.getElementById('reconStatusBox');

    // Modal Rincian Harian Elements
    const modalRincianHarian = document.getElementById('modalRincianHarian');
    const tableSearchInput = document.getElementById('inputTableSearch');
    const btnClearSearch = document.getElementById('btnClearSearch');
    const shiftFilterTags = document.querySelectorAll('.btn-shift-tag');
    const tableRows = document.querySelectorAll('.gas-table-row');
    const tableRowCount = document.getElementById('tableRowCount');
    const noFilterMatchRow = document.getElementById('noFilterMatchRow');

    // Modal Confirmation Elements
    const modalConfirm = document.getElementById('modalConfirmGas');

    let currentActiveShift = 'ALL';

    // Helper: Format Rupiah
    function formatRupiah(num) {
        return 'Rp ' + Math.round(num || 0).toLocaleString('id-ID');
    }

    // Helper: Format Number
    function formatNumber(num, decimals = 4) {
        return Number(num || 0).toLocaleString('id-ID', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        });
    }

    // Helper: Parse Number dari string
    function parseFormattedNumber(val) {
        if (!val) return 0;
        const cleaned = String(val).replace(/[^0-9]/g, '');
        return parseFloat(cleaned) || 0;
    }

    // Helper: Format Date YYYY-MM-DD
    function formatDateYMD(dateObj) {
        const y = dateObj.getFullYear();
        const m = String(dateObj.getMonth() + 1).padStart(2, '0');
        const d = String(dateObj.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    }

    // 1. Zero Mental Math: Otomasi TOP (Hari) & Tanggal Jatuh Tempo
    function updateDueDateFromTop() {
        if (!inputTglTagihan || !inputTglJatuhTempo) return;
        const tglStr = inputTglTagihan.value;
        if (!tglStr) return;

        const days = parseInt(inputTopHari?.value, 10) || 0;
        const tagihanDate = new Date(tglStr);
        if (isNaN(tagihanDate.getTime())) return;

        tagihanDate.setDate(tagihanDate.getDate() + days);
        inputTglJatuhTempo.value = formatDateYMD(tagihanDate);
    }

    function updateTopFromDueDate() {
        if (!inputTglTagihan || !inputTglJatuhTempo || !inputTopHari) return;
        const tglTagihanStr = inputTglTagihan.value;
        const tglTempoStr = inputTglJatuhTempo.value;
        if (!tglTagihanStr || !tglTempoStr) return;

        const dateTagihan = new Date(tglTagihanStr);
        const dateTempo = new Date(tglTempoStr);
        if (isNaN(dateTagihan.getTime()) || isNaN(dateTempo.getTime())) return;

        const diffTime = dateTempo.getTime() - dateTagihan.getTime();
        const diffDays = Math.round(diffTime / (1000 * 60 * 60 * 24));
        inputTopHari.value = diffDays >= 0 ? diffDays : 0;
    }

    if (inputTglTagihan) {
        inputTglTagihan.addEventListener('change', updateDueDateFromTop);
    }
    if (inputTopHari) {
        inputTopHari.addEventListener('input', updateDueDateFromTop);
    }
    if (inputTglJatuhTempo) {
        inputTglJatuhTempo.addEventListener('change', updateTopFromDueDate);
    }

    // 2. Salin Nilai Estimasi Pabrik
    window.useFactoryEstimate = function () {
        const estDpp = Math.round(config.totalEstimasiAkrual || 0);
        if (inputDppVendor) inputDppVendor.value = estDpp;
        if (inputDppDisplay) inputDppDisplay.value = estDpp > 0 ? estDpp.toLocaleString('id-ID') : '0';

        if (inputMmbtuVendor && config.totalMmbtuPabrik > 0) {
            inputMmbtuVendor.value = config.totalMmbtuPabrik;
        }

        calculateTotals();
    };

    // 3. Skema Pajak PPN Controller
    window.setPpnMode = function (mode) {
        if (selectPpnMode && selectPpnMode.value !== mode) {
            selectPpnMode.value = mode;
        }

        if (ppnModeSubBadge) {
            if (mode === '11') {
                ppnModeSubBadge.textContent = 'Tarif Pajak Masukan 11%';
            } else if (mode === '0') {
                ppnModeSubBadge.textContent = 'Bebas PPN (0%)';
            } else {
                ppnModeSubBadge.textContent = 'Input Manual (Koreksi Faktur)';
            }
        }

        if (inputPpnDisplay) {
            if (mode === 'manual') {
                inputPpnDisplay.removeAttribute('readonly');
                inputPpnDisplay.focus();
                if (ppnStatusNote) {
                    ppnStatusNote.textContent = 'Masukkan nilai nominal PPN secara bebas dari faktur vendor.';
                }
            } else {
                inputPpnDisplay.setAttribute('readonly', 'readonly');
                if (mode === '11') {
                    if (ppnStatusNote) {
                        ppnStatusNote.textContent = 'Dihitung otomatis 11% dari DPP.';
                    }
                } else {
                    if (ppnStatusNote) {
                        ppnStatusNote.textContent = 'Bebas PPN (0%). Tidak ada PPN masukan.';
                    }
                }
            }
        }

        calculateTotals();
    };

    // 4. Kalkulasi Utama & Real-Time Sync
    function calculateTotals() {
        const dpp = parseFloat(inputDppVendor?.value) || 0;
        const mode = selectPpnMode?.value || '11';
        let ppn = 0;

        if (mode === '11') {
            ppn = Math.round(dpp * 0.11);
            if (inputTotalPpn) inputTotalPpn.value = ppn;
            if (inputPpnDisplay) inputPpnDisplay.value = ppn > 0 ? ppn.toLocaleString('id-ID') : '0';
        } else if (mode === '0') {
            ppn = 0;
            if (inputTotalPpn) inputTotalPpn.value = 0;
            if (inputPpnDisplay) inputPpnDisplay.value = '0';
        } else {
            ppn = parseFloat(inputTotalPpn?.value) || 0;
            if (inputPpnDisplay && !inputPpnDisplay.matches(':focus')) {
                inputPpnDisplay.value = ppn > 0 ? ppn.toLocaleString('id-ID') : '0';
            }
        }

        const totalTagihan = dpp + ppn;

        // Update Hidden & Display Total Tagihan
        if (inputTotalTagihan) {
            inputTotalTagihan.value = totalTagihan;
        }
        if (inputTotalTagihanDisplay) {
            inputTotalTagihanDisplay.value = totalTagihan > 0 ? totalTagihan.toLocaleString('id-ID') : '0';
        }

        // Feedback Selisih MMBTU
        const pabrikMmbtu = config.totalMmbtuPabrik || 0;
        const vendorMmbtu = parseFloat(inputMmbtuVendor?.value) || 0;
        const selisihMmbtu = vendorMmbtu - pabrikMmbtu;

        if (noteSelisihMmbtu) {
            if (Math.abs(selisihMmbtu) < 0.0001) {
                noteSelisihMmbtu.textContent = '(Cocok)';
                noteSelisihMmbtu.style.color = '#166534';
            } else if (selisihMmbtu > 0) {
                noteSelisihMmbtu.textContent = '(+' + formatNumber(selisihMmbtu, 4) + ' MMBTU)';
                noteSelisihMmbtu.style.color = '#dc2626';
            } else {
                noteSelisihMmbtu.textContent = '(-' + formatNumber(Math.abs(selisihMmbtu), 4) + ' MMBTU)';
                noteSelisihMmbtu.style.color = '#166534';
            }
        }

        // Feedback Selisih DPP
        const akrualPabrik = config.totalEstimasiAkrual || 0;
        const selisihDpp = dpp - akrualPabrik;

        if (noteSelisihDpp) {
            if (Math.abs(selisihDpp) < 100) {
                noteSelisihDpp.textContent = '(Cocok)';
                noteSelisihDpp.style.color = '#166534';
            } else if (selisihDpp > 0) {
                noteSelisihDpp.textContent = '(+' + formatRupiah(selisihDpp) + ')';
                noteSelisihDpp.style.color = '#dc2626';
            } else {
                noteSelisihDpp.textContent = '(-' + formatRupiah(Math.abs(selisihDpp)) + ')';
                noteSelisihDpp.style.color = '#166534';
            }
        }

        // Feedback Status Rekonsiliasi Tagihan Terhadap Catatan Pabrik
        if (totalStatusNote) {
            if (Math.abs(selisihDpp) < 100) {
                if (reconStatusBox) reconStatusBox.className = 'gas-recon-status-box recon-match';
                totalStatusNote.innerHTML = '<svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M5 13l4 4L19 7"/></svg><span>Cocok dengan fisik dapur (Selisih Rp 0)</span>';
            } else if (selisihDpp > 0) {
                if (reconStatusBox) reconStatusBox.className = 'gas-recon-status-box recon-over';
                totalStatusNote.innerHTML = '<svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg><span>Selisih: +' + formatRupiah(selisihDpp) + '</span>';
            } else {
                if (reconStatusBox) reconStatusBox.className = 'gas-recon-status-box recon-under';
                totalStatusNote.innerHTML = '<svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M5 13l4 4L19 7"/></svg><span>Selisih: -' + formatRupiah(Math.abs(selisihDpp)) + ' (Hemat)</span>';
            }
        }
    }

    // 5. Event Listeners Input Nilai
    if (inputMmbtuVendor) {
        inputMmbtuVendor.addEventListener('input', calculateTotals);
    }

    if (inputDppDisplay) {
        inputDppDisplay.addEventListener('input', function () {
            const rawVal = parseFormattedNumber(this.value);
            if (inputDppVendor) inputDppVendor.value = rawVal;
            this.value = rawVal > 0 ? rawVal.toLocaleString('id-ID') : (this.value ? '0' : '');
            calculateTotals();
        });

        inputDppDisplay.addEventListener('blur', function () {
            const rawVal = parseFormattedNumber(this.value);
            this.value = rawVal > 0 ? rawVal.toLocaleString('id-ID') : '0';
        });
    }

    if (inputPpnDisplay) {
        inputPpnDisplay.addEventListener('input', function () {
            const mode = selectPpnMode?.value || '11';
            if (mode === 'manual') {
                const rawVal = parseFormattedNumber(this.value);
                if (inputTotalPpn) inputTotalPpn.value = rawVal;
                this.value = rawVal > 0 ? rawVal.toLocaleString('id-ID') : (this.value ? '0' : '');

                const dpp = parseFloat(inputDppVendor?.value) || 0;
                const total = dpp + rawVal;
                if (inputTotalTagihan) inputTotalTagihan.value = total;
                if (inputTotalTagihanDisplay) inputTotalTagihanDisplay.value = total > 0 ? total.toLocaleString('id-ID') : '0';
            }
        });

        inputPpnDisplay.addEventListener('blur', function () {
            const rawVal = parseFormattedNumber(this.value);
            this.value = rawVal > 0 ? rawVal.toLocaleString('id-ID') : '0';
        });
    }

    // 6. Modal Rincian Harian Controller & Search Filter
    window.openModalRincianHarian = function () {
        if (modalRincianHarian) {
            modalRincianHarian.style.display = 'flex';
            modalRincianHarian.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            if (tableSearchInput) {
                setTimeout(() => tableSearchInput.focus(), 100);
            }
        }
    };

    window.closeModalRincianHarian = function () {
        if (modalRincianHarian) {
            modalRincianHarian.style.display = 'none';
            modalRincianHarian.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }
    };

    function filterTable() {
        const query = (tableSearchInput?.value || '').trim().toLowerCase();
        let visibleCount = 0;

        tableRows.forEach(row => {
            const shift = row.dataset.shift || '';
            const text = row.textContent.toLowerCase();

            const matchesShift = currentActiveShift === 'ALL' || shift === currentActiveShift;
            const matchesQuery = !query || text.includes(query);

            if (matchesShift && matchesQuery) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        if (tableRowCount) {
            tableRowCount.textContent = visibleCount + ' Baris';
        }

        if (btnClearSearch) {
            btnClearSearch.style.display = query ? 'block' : 'none';
        }

        if (noFilterMatchRow) {
            if (visibleCount === 0 && tableRows.length > 0) {
                noFilterMatchRow.classList.remove('is-hidden');
            } else {
                noFilterMatchRow.classList.add('is-hidden');
            }
        }
    }

    if (tableSearchInput) {
        tableSearchInput.addEventListener('input', filterTable);
    }

    if (btnClearSearch) {
        btnClearSearch.addEventListener('click', function () {
            if (tableSearchInput) {
                tableSearchInput.value = '';
                filterTable();
                tableSearchInput.focus();
            }
        });
    }

    shiftFilterTags.forEach(pill => {
        pill.addEventListener('click', function () {
            shiftFilterTags.forEach(p => p.classList.remove('active'));
            this.classList.add('active');
            currentActiveShift = this.dataset.shift || 'ALL';
            filterTable();
        });
    });

    // 7. Modal Konfirmasi Rekonsiliasi
    window.openModalConfirmGas = function () {
        if (!formTagihanGas) return;

        if (!formTagihanGas.checkValidity()) {
            formTagihanGas.reportValidity();
            return;
        }

        const supplierText = selectSupplier?.options[selectSupplier.selectedIndex]?.text || '-';
        const faktur = inputNoFaktur?.value || '— (Tanpa No Faktur)';
        const dpp = parseFloat(inputDppVendor?.value) || 0;
        const ppn = parseFloat(inputTotalPpn?.value) || 0;
        const total = parseFloat(inputTotalTagihan?.value) || (dpp + ppn);
        const akrual = config.totalEstimasiAkrual || 0;
        const varians = dpp - akrual;

        const modalConfSupplier = document.getElementById('modalConfSupplier');
        const modalConfFaktur = document.getElementById('modalConfFaktur');
        const modalConfPeriode = document.getElementById('modalConfPeriode');
        const modalConfDpp = document.getElementById('modalConfDpp');
        const modalConfPpn = document.getElementById('modalConfPpn');
        const modalConfTotal = document.getElementById('modalConfTotal');
        const modalConfAkrual = document.getElementById('modalConfAkrual');
        const modalConfVarians = document.getElementById('modalConfVarians');

        if (modalConfSupplier) modalConfSupplier.textContent = supplierText;
        if (modalConfFaktur) modalConfFaktur.textContent = faktur;
        if (modalConfPeriode) modalConfPeriode.textContent = config.periodeBulan || inputPeriode?.value || '-';
        if (modalConfDpp) modalConfDpp.textContent = formatRupiah(dpp);
        if (modalConfPpn) modalConfPpn.textContent = formatRupiah(ppn);
        if (modalConfTotal) modalConfTotal.textContent = formatRupiah(total);
        if (modalConfAkrual) modalConfAkrual.textContent = formatRupiah(akrual);
        if (modalConfVarians) {
            modalConfVarians.textContent = (varians >= 0 ? '+' : '') + formatRupiah(varians);
            if (Math.abs(varians) < 100) {
                modalConfVarians.style.color = '#0f172a';
            } else if (varians > 0) {
                modalConfVarians.style.color = '#dc2626';
            } else {
                modalConfVarians.style.color = '#166534';
            }
        }

        if (modalConfirm) {
            modalConfirm.style.display = 'flex';
            modalConfirm.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }
    };

    window.closeModalConfirmGas = function () {
        if (modalConfirm) {
            modalConfirm.style.display = 'none';
            modalConfirm.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }
    };

    window.submitTagihanGas = function () {
        if (formTagihanGas) {
            formTagihanGas.submit();
        }
    };

    // Backdrop click & ESC key handler untuk semua modal
    if (modalConfirm) {
        modalConfirm.addEventListener('click', function (e) {
            if (e.target === modalConfirm) {
                window.closeModalConfirmGas();
            }
        });
    }

    if (modalRincianHarian) {
        modalRincianHarian.addEventListener('click', function (e) {
            if (e.target === modalRincianHarian) {
                window.closeModalRincianHarian();
            }
        });
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            if (modalConfirm && modalConfirm.style.display === 'flex') {
                window.closeModalConfirmGas();
            }
            if (modalRincianHarian && modalRincianHarian.style.display === 'flex') {
                window.closeModalRincianHarian();
            }
        }
    });

    // 8. Inisialisasi saat Halaman Dimuat
    document.addEventListener('DOMContentLoaded', function () {
        const initialDpp = parseFloat(inputDppVendor?.value) || 0;
        if (inputDppDisplay) {
            inputDppDisplay.value = initialDpp > 0 ? initialDpp.toLocaleString('id-ID') : '0';
        }

        const initialPpn = parseFloat(inputTotalPpn?.value) || 0;
        if (inputPpnDisplay) {
            inputPpnDisplay.value = initialPpn > 0 ? initialPpn.toLocaleString('id-ID') : '0';
        }

        updateDueDateFromTop();
        calculateTotals();
    });

})();
