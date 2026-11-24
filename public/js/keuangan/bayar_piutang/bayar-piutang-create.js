/**
 * JavaScript Form Terima Pembayaran Customer (BKM AR) - ERP PT Mirasa
 * WaveDash Architecture - Clean, Fast, Accessible & Robust
 */

document.addEventListener('DOMContentLoaded', function () {
    // --------------------------------------------------------------------------
    // 1. DOM Elements & Config Initialization
    // --------------------------------------------------------------------------
    const customerSelect = document.getElementById('selectCustomerId');
    const inputCustomer = document.getElementById('customer_input');
    const customerDropdown = document.getElementById('customerDropdown');
    const customerSuggestionsList = document.getElementById('customerSuggestionsList');
    const btnClearCustomer = document.getElementById('btnClearCustomer');
    const badgeCustomer = document.getElementById('customerSelectedBadge');
    const customerChips = document.querySelectorAll('.customer-chip');

    const selectKasBank = document.getElementById('selectKasBankId');
    const liveSaldoDisplay = document.getElementById('liveSaldoDisplay');
    const inputDiterimaDari = document.getElementById('inputDiterimaDari');

    const fakturTableBody = document.getElementById('fakturTableBody');
    const emptyNotice = document.getElementById('emptyFakturNotice');
    const loadingNotice = document.getElementById('loadingFakturNotice');
    const checkAllBox = document.getElementById('checkAllFaktur');
    const formEl = document.getElementById('formBayarPiutang');

    // Dock Footer Elements
    const sisaSaldoDisplay = document.getElementById('sisaSaldoDisplay');
    const totalBayarDisplay = document.getElementById('dockTotalBayar');
    const terbilangDisplay = document.getElementById('dockTerbilang');
    const countSelectedDisplay = document.getElementById('dockCountSelected');
    const sisaTagihanDisplay = document.getElementById('dockSisaTagihan');

    const customerList = (window.appConfig && window.appConfig.customerList) ? window.appConfig.customerList : [];
    let currentKasSaldo = null;
    let currentGrandTotal = 0;
    let totalSisaFakturLoaded = 0;

    // --------------------------------------------------------------------------
    // 2. SMART CUSTOMER AUTOCOMPLETE
    // --------------------------------------------------------------------------
    function renderCustomerSuggestions(query = '') {
        if (!customerSuggestionsList) return;
        customerSuggestionsList.innerHTML = '';
        const cleanQuery = query.toLowerCase().trim();

        const filtered = customerList.filter(c => {
            const name = (c.customer_nm || '').toLowerCase();
            const code = (c.customer_cd || '').toLowerCase();
            return name.includes(cleanQuery) || code.includes(cleanQuery);
        });

        if (filtered.length === 0) {
            customerSuggestionsList.innerHTML = `
                <div style="padding: 1rem; text-align: center; color: #94a3b8; font-size: 0.8125rem;">
                    Tidak ditemukan pelanggan dengan kata kunci "${escapeHtml(query)}"
                </div>
            `;
            return;
        }

        filtered.slice(0, 20).forEach(c => {
            const item = document.createElement('div');
            item.className = 'autocomplete-item';

            const hasUnpaid = c.count_faktur > 0;
            const badgeHtml = hasUnpaid
                ? `<span class="item-tagihan-badge">${c.count_faktur} Faktur &bull; Rp ${formatRupiah(c.total_sisa)}</span>`
                : `<span style="font-size: 0.7rem; color: #64748b;">Tagihan Lunas</span>`;

            item.innerHTML = `
                <div>
                    <div class="item-main-text">${escapeHtml(c.customer_nm)}</div>
                    <div class="item-sub-text">Kode: ${escapeHtml(c.customer_cd || 'CUST')}</div>
                </div>
                ${badgeHtml}
            `;

            item.addEventListener('click', function () {
                selectCustomer(c);
                closeCustomerDropdown();
            });

            customerSuggestionsList.appendChild(item);
        });
    }

    function openCustomerDropdown() {
        if (!customerDropdown) return;
        renderCustomerSuggestions(inputCustomer ? inputCustomer.value : '');
        customerDropdown.style.display = 'block';
    }

    function closeCustomerDropdown() {
        if (!customerDropdown) return;
        customerDropdown.style.display = 'none';
    }

    function selectCustomer(cust) {
        if (!cust) return;

        if (inputCustomer) {
            inputCustomer.value = cust.customer_nm;
        }
        if (customerSelect) {
            customerSelect.value = cust.customer_id;
        }
        if (btnClearCustomer) {
            btnClearCustomer.style.display = 'inline-block';
        }
        if (badgeCustomer) {
            badgeCustomer.textContent = cust.customer_cd || 'Pelanggan Terpilih';
            badgeCustomer.style.background = '#f0fdf4';
            badgeCustomer.style.color = '#15803d';
            badgeCustomer.style.borderColor = '#dcfce7';
        }

        // Auto-fill Diterima Dari
        if (inputDiterimaDari && (!inputDiterimaDari.value || inputDiterimaDari.dataset.autoFilled === 'true')) {
            inputDiterimaDari.value = cust.customer_nm;
            inputDiterimaDari.dataset.autoFilled = 'true';
        }

        handleCustomerChange(cust.customer_id);
    }

    if (inputCustomer) {
        inputCustomer.addEventListener('focus', function () {
            openCustomerDropdown();
        });

        inputCustomer.addEventListener('input', function () {
            const val = this.value;
            if (btnClearCustomer) {
                btnClearCustomer.style.display = val.length > 0 ? 'inline-block' : 'none';
            }
            renderCustomerSuggestions(val);
            if (customerDropdown) customerDropdown.style.display = 'block';
        });

        document.addEventListener('click', function (e) {
            const container = document.getElementById('customerAutocompleteContainer');
            if (container && !container.contains(e.target)) {
                closeCustomerDropdown();
            }
        });
    }

    if (btnClearCustomer) {
        btnClearCustomer.addEventListener('click', function () {
            if (inputCustomer) {
                inputCustomer.value = '';
                inputCustomer.focus();
            }
            if (customerSelect) {
                customerSelect.value = '';
            }
            if (badgeCustomer) {
                badgeCustomer.textContent = 'Pilih Pelanggan';
                badgeCustomer.style.background = '#f1f5f9';
                badgeCustomer.style.color = '#475569';
                badgeCustomer.style.borderColor = '#e2e8f0';
            }
            btnClearCustomer.style.display = 'none';
            handleCustomerChange('');
            openCustomerDropdown();
        });
    }

    // --------------------------------------------------------------------------
    // 3. CHIPS AKSES CEPAT CUSTOMER
    // --------------------------------------------------------------------------
    customerChips.forEach(chip => {
        chip.addEventListener('click', function () {
            const customerId = this.dataset.customerId;
            const match = customerList.find(c => String(c.customer_id) === String(customerId));
            if (match) {
                selectCustomer(match);
            }
        });
    });

    // --------------------------------------------------------------------------
    // 4. LIVE SALDO KAS / BANK & ESTIMASI REAL-TIME
    // --------------------------------------------------------------------------
    function updateLiveSaldo() {
        if (!selectKasBank) return;
        const opt = selectKasBank.options[selectKasBank.selectedIndex];

        if (opt && opt.value && opt.dataset.saldo !== undefined) {
            currentKasSaldo = parseFloat(opt.dataset.saldo) || 0;
            if (liveSaldoDisplay) {
                liveSaldoDisplay.textContent = 'Rp ' + formatRupiah(currentKasSaldo);
            }
        } else {
            currentKasSaldo = null;
            if (liveSaldoDisplay) {
                liveSaldoDisplay.textContent = 'Rp 0';
            }
        }
        updateDockSaldoPreview();
    }

    function updateDockSaldoPreview() {
        if (!sisaSaldoDisplay) return;
        if (currentKasSaldo === null) {
            sisaSaldoDisplay.textContent = 'Pilih akun Kas/Bank untuk estimasi mutasi saldo';
            return;
        }

        const saldoAkhir = currentKasSaldo + currentGrandTotal;
        const formattedSaldoAwal = 'Rp ' + formatRupiah(currentKasSaldo);
        const formattedSaldoAkhir = 'Rp ' + formatRupiah(saldoAkhir);

        sisaSaldoDisplay.textContent = `Saldo Awal: ${formattedSaldoAwal} → Estimasi Baru: ${formattedSaldoAkhir}`;
    }

    if (selectKasBank) {
        selectKasBank.addEventListener('change', updateLiveSaldo);
        updateLiveSaldo();
    }

    // --------------------------------------------------------------------------
    // 5. FETCH & RENDER DAFTAR FAKTUR CUSTOMER
    // --------------------------------------------------------------------------
    function handleCustomerChange(customerId, preselectedFakturId = null) {
        // Highlight matching chip
        customerChips.forEach(c => {
            if (String(c.dataset.customerId) === String(customerId)) {
                c.classList.add('active');
            } else {
                c.classList.remove('active');
            }
        });

        if (!customerId) {
            if (fakturTableBody) fakturTableBody.innerHTML = '';
            if (emptyNotice) {
                emptyNotice.style.display = 'block';
                emptyNotice.querySelector('.faktur-empty-title').textContent = 'Silakan pilih pelanggan di atas';
                emptyNotice.querySelector('.faktur-empty-sub').textContent = 'Faktur penjualan belum lunas akan dimuat secara otomatis di sini.';
            }
            if (loadingNotice) loadingNotice.style.display = 'none';
            totalSisaFakturLoaded = 0;
            recalcTotal();
            return;
        }

        if (emptyNotice) emptyNotice.style.display = 'none';
        if (loadingNotice) loadingNotice.style.display = 'block';
        if (fakturTableBody) fakturTableBody.innerHTML = '';

        fetch(`${window.appConfig.baseUrl}/keuangan/bayar-piutang/faktur-customer/${customerId}`)
            .then(res => res.json())
            .then(res => {
                if (loadingNotice) loadingNotice.style.display = 'none';
                if (res.status === 'success' && res.data && res.data.length > 0) {
                    renderFakturRows(res.data, preselectedFakturId);
                } else {
                    if (emptyNotice) {
                        emptyNotice.style.display = 'block';
                        emptyNotice.querySelector('.faktur-empty-title').textContent = 'Tidak ada faktur terbuka';
                        emptyNotice.querySelector('.faktur-empty-sub').textContent = 'Semua faktur penjualan milik pelanggan ini telah berstatus LUNAS.';
                    }
                    totalSisaFakturLoaded = 0;
                    recalcTotal();
                }
            })
            .catch(err => {
                if (loadingNotice) loadingNotice.style.display = 'none';
                if (emptyNotice) {
                    emptyNotice.style.display = 'block';
                    emptyNotice.querySelector('.faktur-empty-title').textContent = 'Gagal memuat faktur';
                    emptyNotice.querySelector('.faktur-empty-sub').textContent = err.message;
                }
            });
    }

    function renderFakturRows(fakturs, preselectedFakturId) {
        if (!fakturTableBody) return;
        fakturTableBody.innerHTML = '';
        totalSisaFakturLoaded = 0;

        fakturs.forEach((f, idx) => {
            totalSisaFakturLoaded += parseFloat(f.sisa_piutang || 0);

            const isPreselected = preselectedFakturId && parseInt(preselectedFakturId) === f.faktur_id;
            const tr = document.createElement('tr');
            tr.id = `faktur-row-${f.faktur_id}`;
            if (isPreselected) tr.classList.add('selected-row');

            const sisaRupiah = formatRupiah(f.sisa_piutang);
            const totalRupiah = formatRupiah(f.total_tagihan);
            const sudahBayarRupiah = formatRupiah(f.total_dibayar);

            let statusBadge = `<span style="background: #fffbeb; color: #b45309; border: 1px solid #fef3c7; font-size: 0.7rem; font-weight: 700; padding: 2px 7px; border-radius: 4px;">Cicilan</span>`;
            if (f.status_pembayaran === 'BELUM_LUNAS') {
                statusBadge = `<span style="background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; font-size: 0.7rem; font-weight: 700; padding: 2px 7px; border-radius: 4px;">Belum Bayar</span>`;
            }

            let tempoWarning = '';
            if (f.is_overdue) {
                tempoWarning = `<div style="color: #dc2626; font-size: 0.725rem; font-weight: 700; margin-top: 2px;">Terlambat (${Math.abs(f.hari_jatuh_tempo)} hr)</div>`;
            } else if (f.hari_jatuh_tempo !== null && f.hari_jatuh_tempo <= 3) {
                tempoWarning = `<div style="color: #d97706; font-size: 0.725rem; font-weight: 700; margin-top: 2px;">Jatuh tempo ${f.hari_jatuh_tempo} hr lagi</div>`;
            }

            tr.innerHTML = `
                <td style="text-align: center; width: 45px;">
                    <input type="checkbox"
                           class="checkbox-faktur"
                           id="cb_faktur_${f.faktur_id}"
                           data-id="${f.faktur_id}"
                           data-sisa="${f.sisa_piutang}"
                           ${isPreselected ? 'checked' : ''}
                           aria-label="Pilih Faktur ${f.faktur_no}"
                           style="width: 17px; height: 17px; cursor: pointer;">
                </td>
                <td>
                    <div class="faktur-no-text">${escapeHtml(f.faktur_no)}</div>
                    <div class="faktur-meta-sub">PO: ${escapeHtml(f.customer_po_no || '-')}</div>
                </td>
                <td>
                    <div style="font-weight: 600; color: #334155;">${f.faktur_tgl}</div>
                    <div class="faktur-meta-sub">TOP: ${escapeHtml(f.top_txt || '-')}</div>
                </td>
                <td>
                    <div style="font-weight: 600; color: #334155;">${f.tgl_jatuh_tempo}</div>
                    ${tempoWarning}
                </td>
                <td class="amount-tabular" style="text-align: right;">
                    <div style="font-weight: 600; color: #0f172a;">Rp ${totalRupiah}</div>
                    <div class="faktur-meta-sub">Terbayar: Rp ${sudahBayarRupiah}</div>
                </td>
                <td class="amount-tabular amount-sisa" style="text-align: right;">
                    Rp ${sisaRupiah}
                </td>
                <td style="text-align: center;">
                    ${statusBadge}
                </td>
                <td style="text-align: right; min-width: 260px;">
                    <div class="input-bayar-group">
                        <input type="hidden" name="allocations[${idx}][faktur_id]" value="${f.faktur_id}">
                        <input type="number"
                               step="0.01"
                               min="0"
                               max="${f.sisa_piutang}"
                               name="allocations[${idx}][nominal_bayar]"
                               id="input_bayar_${f.faktur_id}"
                               class="input-nominal-large input-nominal-bayar"
                               placeholder="0"
                               value="${isPreselected ? f.sisa_piutang : ''}"
                               aria-label="Nominal bayar faktur ${f.faktur_no}">
                        <button type="button"
                                class="btn-lunasi-penuh"
                                onclick="setFullPay(${f.faktur_id}, ${f.sisa_piutang})">
                            Lunasi Penuh
                        </button>
                    </div>
                </td>
            `;

            fakturTableBody.appendChild(tr);

            // Row Event Listeners
            const cb = tr.querySelector('.checkbox-faktur');
            const inp = tr.querySelector('.input-nominal-bayar');

            cb.addEventListener('change', function () {
                if (this.checked) {
                    tr.classList.add('selected-row');
                    if (!inp.value || parseFloat(inp.value) <= 0) {
                        inp.value = f.sisa_piutang;
                    }
                } else {
                    tr.classList.remove('selected-row');
                    inp.value = '';
                }
                recalcTotal();
            });

            inp.addEventListener('input', function () {
                const val = parseFloat(this.value || 0);
                if (val > 0) {
                    cb.checked = true;
                    tr.classList.add('selected-row');
                } else {
                    cb.checked = false;
                    tr.classList.remove('selected-row');
                }
                recalcTotal();
            });
        });

        recalcTotal();
    }

    // --------------------------------------------------------------------------
    // 6. QUICK ACTIONS: SET FULL PAY & CHECK ALL
    // --------------------------------------------------------------------------
    window.setFullPay = function (fakturId, sisaNominal) {
        const inp = document.getElementById(`input_bayar_${fakturId}`);
        const cb = document.getElementById(`cb_faktur_${fakturId}`);
        const tr = document.getElementById(`faktur-row-${fakturId}`);

        if (inp && cb && tr) {
            inp.value = sisaNominal;
            cb.checked = true;
            tr.classList.add('selected-row');
            recalcTotal();
        }
    };

    window.lunasiSemuaFaktur = function () {
        const checkboxes = document.querySelectorAll('.checkbox-faktur');
        checkboxes.forEach(cb => {
            cb.checked = true;
            const row = cb.closest('tr');
            const sisa = parseFloat(cb.dataset.sisa || 0);
            const input = row.querySelector('.input-nominal-bayar');
            row.classList.add('selected-row');
            if (input) input.value = sisa;
        });
        if (checkAllBox) checkAllBox.checked = true;
        recalcTotal();
    };

    window.kosongkanSemuaFaktur = function () {
        const checkboxes = document.querySelectorAll('.checkbox-faktur');
        checkboxes.forEach(cb => {
            cb.checked = false;
            const row = cb.closest('tr');
            const input = row.querySelector('.input-nominal-bayar');
            row.classList.remove('selected-row');
            if (input) input.value = '';
        });
        if (checkAllBox) checkAllBox.checked = false;
        recalcTotal();
    };

    if (checkAllBox) {
        checkAllBox.addEventListener('change', function () {
            if (this.checked) {
                window.lunasiSemuaFaktur();
            } else {
                window.kosongkanSemuaFaktur();
            }
        });
    }

    // --------------------------------------------------------------------------
    // 7. REAL-TIME CALCULATION & TERBILANG RUPIAH
    // --------------------------------------------------------------------------
    function recalcTotal() {
        let total = 0;
        let count = 0;

        const inputs = document.querySelectorAll('.input-nominal-bayar');
        inputs.forEach(inp => {
            const val = parseFloat(inp.value || 0);
            if (val > 0) {
                total += val;
                count++;
            }
        });

        currentGrandTotal = total;

        if (totalBayarDisplay) {
            totalBayarDisplay.textContent = 'Rp ' + formatRupiah(total);
        }

        if (terbilangDisplay) {
            terbilangDisplay.textContent = '# ' + terbilang(total) + ' #';
        }

        if (countSelectedDisplay) {
            countSelectedDisplay.textContent = `${count} Faktur Terpilih`;
        }

        if (sisaTagihanDisplay) {
            const remaining = Math.max(0, totalSisaFakturLoaded - total);
            sisaTagihanDisplay.textContent = 'Sisa Tagihan Belum Terbayar: Rp ' + formatRupiah(remaining);
        }

        updateDockSaldoPreview();
    }

    // Form Submit Guard
    if (formEl) {
        formEl.addEventListener('submit', function (e) {
            if (!customerSelect || !customerSelect.value) {
                e.preventDefault();
                alert('Silakan pilih pelanggan / customer terlebih dahulu.');
                if (inputCustomer) inputCustomer.focus();
                return;
            }

            if (!selectKasBank || !selectKasBank.value) {
                e.preventDefault();
                alert('Silakan pilih akun kas / bank penerima.');
                selectKasBank.focus();
                return;
            }

            if (currentGrandTotal <= 0) {
                e.preventDefault();
                alert('Pilih minimal satu faktur penjualan dengan mengisi nominal pembayaran lebih dari Rp 0.');
            }
        });
    }

    // Initial Load Execution
    if (window.appConfig && window.appConfig.selectedCustomerId) {
        const found = customerList.find(c => String(c.customer_id) === String(window.appConfig.selectedCustomerId));
        if (found) {
            selectCustomer(found);
        } else {
            handleCustomerChange(window.appConfig.selectedCustomerId, window.appConfig.preselectedFakturId);
        }
    }

    // --------------------------------------------------------------------------
    // 8. HELPER FUNCTIONS
    // --------------------------------------------------------------------------
    function formatRupiah(num) {
        return Math.round(num || 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function escapeHtml(text) {
        if (!text) return '';
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return text.toString().replace(/[&<>"']/g, m => map[m]);
    }

    function terbilang(bilangan) {
        bilangan = Math.abs(Math.round(bilangan || 0));
        const angka = ["", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas"];
        let kata = "";

        if (bilangan < 12) {
            kata = " " + angka[bilangan];
        } else if (bilangan < 20) {
            kata = terbilang(bilangan - 10) + " Belas";
        } else if (bilangan < 100) {
            kata = terbilang(Math.floor(bilangan / 10)) + " Puluh" + terbilang(bilangan % 10);
        } else if (bilangan < 200) {
            kata = " Seratus" + terbilang(bilangan - 100);
        } else if (bilangan < 1000) {
            kata = terbilang(Math.floor(bilangan / 100)) + " Ratus" + terbilang(bilangan % 100);
        } else if (bilangan < 2000) {
            kata = " Seribu" + terbilang(bilangan - 1000);
        } else if (bilangan < 1000000) {
            kata = terbilang(Math.floor(bilangan / 1000)) + " Ribu" + terbilang(bilangan % 1000);
        } else if (bilangan < 1000000000) {
            kata = terbilang(Math.floor(bilangan / 1000000)) + " Juta" + terbilang(bilangan % 1000000);
        } else if (bilangan < 1000000000000) {
            kata = terbilang(Math.floor(bilangan / 1000000000)) + " Milyar" + terbilang(bilangan % 1000000000);
        }

        const res = kata.trim();
        return res ? (res + " Rupiah") : "Nol Rupiah";
    }
});
