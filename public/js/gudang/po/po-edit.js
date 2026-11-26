/**
 * =========================================================================
 * ERP PT MIRASA - PURCHASE ORDER EDIT JAVASCRIPT
 * =========================================================================
 */

function togglePoHeaderAccordion() {
    const body = document.getElementById('poHeaderAccordionBody');
    const btn = document.getElementById('btnToggleAccordion');
    if (!body) return;
    body.classList.toggle('open');
    const isOpen = body.classList.contains('open');
    if (btn) {
        btn.innerHTML = isOpen
            ? '▴ Sembunyikan Detail (Gudang, Penyetuju, TOP, Estimasi, Franco, Catatan, Pajak)'
            : '▾ Lihat Selengkapnya (Gudang, Penyetuju, TOP, Estimasi, Franco, Catatan, Pajak)';
    }
}
window.togglePoHeaderAccordion = togglePoHeaderAccordion;

function handleBarangChange(selectEl) {
    const row = selectEl.closest('tr');
    const selectedOpt = selectEl.options[selectEl.selectedIndex];
    const satuanBadge = row.querySelector('.satuan-badge');
    const inputHarga = row.querySelector('.input-harga');

    if (selectedOpt && selectedOpt.value) {
        satuanBadge.textContent = selectedOpt.getAttribute('data-satuan') || 'KG';
        const defaultHarga = parseFloat(selectedOpt.getAttribute('data-harga')) || 0;
        if (parseFloat(inputHarga.value) === 0 && defaultHarga > 0) {
            inputHarga.value = defaultHarga;
        }
    } else {
        satuanBadge.textContent = '-';
    }
    calculateRow(selectEl);
}

function onHeaderTaxInclusiveToggle(headerCheckbox) {
    const isInc = headerCheckbox.checked;
    const badge = document.getElementById('badgeTaxMode');
    if (badge) {
        if (isInc) {
            badge.innerText = 'Gross';
            badge.style.background = '#dcfce7';
            badge.style.color = '#15803d';
        } else {
            badge.innerText = 'Net';
            badge.style.background = '#e2e8f0';
            badge.style.color = '#475569';
        }
    }

    // Sinkronisasi otomatis ke SEMUA baris barang (1 barang maupun lebih dari 1 barang)
    document.querySelectorAll('.item-include-tax').forEach(cb => {
        cb.checked = isInc;
    });

    calculateAllTotals();
}
window.onHeaderTaxInclusiveToggle = onHeaderTaxInclusiveToggle;

function onRowTaxInclusiveChange(rowCheckbox) {
    const rowCheckboxes = document.querySelectorAll('.item-include-tax');
    const headerCheckbox = document.getElementById('is_harga_termasuk_pajak');
    const badge = document.getElementById('badgeTaxMode');

    if (rowCheckboxes.length > 0 && headerCheckbox) {
        let allChecked = true;
        let anyChecked = false;
        rowCheckboxes.forEach(cb => {
            if (cb.checked) anyChecked = true;
            else allChecked = false;
        });

        if (allChecked) {
            headerCheckbox.checked = true;
            if (badge) {
                badge.innerText = 'Gross';
                badge.style.background = '#dcfce7';
                badge.style.color = '#15803d';
            }
        } else if (!anyChecked) {
            headerCheckbox.checked = false;
            if (badge) {
                badge.innerText = 'Net';
                badge.style.background = '#e2e8f0';
                badge.style.color = '#475569';
            }
        }
    }

    calculateAllTotals();
}
window.onRowTaxInclusiveChange = onRowTaxInclusiveChange;

function syncHeaderTaxLabel() {
    const lbl = document.getElementById('lblHeaderTaxInclusive');
    if (!lbl) return;
    const headerPajakSelect = document.getElementById('global_pajak_id');
    if (!headerPajakSelect || headerPajakSelect.selectedIndex < 0) {
        lbl.textContent = 'Harga Termasuk Pajak';
        return;
    }
    const selectedOpt = headerPajakSelect.options[headerPajakSelect.selectedIndex];
    if (!selectedOpt || !selectedOpt.value) {
        lbl.textContent = 'Harga Termasuk Pajak';
        return;
    }
    const sifat = (selectedOpt.dataset.sifat || '').toUpperCase();
    const text = (selectedOpt.text || '').toUpperCase();
    const tarif = parseFloat(selectedOpt.dataset.tarif) || 0;

    if (tarif === 0) {
        lbl.textContent = 'Harga Termasuk Pajak';
    } else if (sifat === 'POTONG' || text.includes('PPH')) {
        lbl.textContent = 'Harga Termasuk PPh';
    } else if (sifat === 'TAMBAH' || text.includes('PPN')) {
        lbl.textContent = 'Harga Termasuk PPN';
    } else {
        lbl.textContent = 'Harga Termasuk Pajak';
    }
}
window.syncHeaderTaxLabel = syncHeaderTaxLabel;

function onHeaderPajakChange(headerSelect) {
    syncHeaderTaxLabel();
    calculateAllTotals();
}
window.onHeaderPajakChange = onHeaderPajakChange;

function calculateRow(element) {
    calculateAllTotals();
}

function calculateAllTotals() {
    let totalQty = 0;
    let totalBruto = 0;
    let rowCount = 0;

    const diskonPersen = Math.min(100, Math.max(0, parseFloat(document.getElementById('global_diskon_persen')?.value) || 0));
    const potonganNominal = Math.max(0, parseFloat(document.getElementById('global_potongan_nominal')?.value) || 0);

    const headerPajakSelect = document.getElementById('global_pajak_id');
    const headerOpt = headerPajakSelect ? headerPajakSelect.options[headerPajakSelect.selectedIndex] : null;
    const headerTarif = headerOpt ? (parseFloat(headerOpt.dataset.tarif) || 0) : 0;
    const headerSifat = headerOpt ? (headerOpt.dataset.sifat || 'TAMBAH') : 'TAMBAH';
    const headerIsInc = document.getElementById('is_harga_termasuk_pajak')?.checked || false;

    const rows = document.querySelectorAll('#tbodyPoItems tr.po-item-row');

    // Pass 1: Total kuantitas & bruto untuk prorata diskon & potongan
    rows.forEach(row => {
        const qty = parseFloat(row.querySelector('.input-qty')?.value) || 0;
        const harga = parseFloat(row.querySelector('.input-harga')?.value) || 0;
        totalQty += qty;
        totalBruto += (qty * harga);
    });

    const totalDiskon = totalBruto * (diskonPersen / 100);
    const totalPotongan = potonganNominal;
    const sumGrossAfterDiskon = Math.max(0, totalBruto - totalDiskon);

    let totalDpp = 0;
    let totalPpn = 0;
    let grandTotal = 0;
    let hasIncTax = false;
    let hasPotongTax = false;

    // Pass 2: Per-row calculation
    rows.forEach(row => {
        rowCount++;
        const qty = parseFloat(row.querySelector('.input-qty')?.value) || 0;
        const harga = parseFloat(row.querySelector('.input-harga')?.value) || 0;
        const rowDiskonUnit = harga * (diskonPersen / 100);
        const rowHargaNetto = Math.max(0, harga - rowDiskonUnit);

        let rowPotongan = 0;
        if (totalPotongan > 0 && sumGrossAfterDiskon > 0) {
            const rowGross = qty * rowHargaNetto;
            rowPotongan = (rowGross / sumGrossAfterDiskon) * totalPotongan;
        }

        const grossRowTotal = Math.max(0, (qty * rowHargaNetto) - rowPotongan);

        // Pajak PO diterapkan ke baris, dengan status inclusive/exclusive per baris
        const rowTarif = headerTarif;
        const rowSifat = headerSifat;

        const rowIncCheck = row.querySelector('.item-include-tax');
        const rowIsInc = rowIncCheck ? rowIncCheck.checked : headerIsInc;

        let rowDpp = 0;
        let rowPpn = 0;
        let rowTagihan = 0;

        if (rowIsInc && rowTarif > 0) {
            hasIncTax = true;
            if (rowSifat === 'POTONG') {
                hasPotongTax = true;
                rowDpp = grossRowTotal;
                rowPpn = Math.round((rowDpp * (rowTarif / 100)) * 10000) / 10000;
                rowTagihan = grossRowTotal;
            } else {
                rowDpp = Math.round((grossRowTotal / (1 + (rowTarif / 100))) * 10000) / 10000;
                rowPpn = Math.round((grossRowTotal - rowDpp) * 10000) / 10000;
                rowTagihan = grossRowTotal;
            }
        } else {
            rowDpp = grossRowTotal;
            rowPpn = Math.round((rowDpp * (rowTarif / 100)) * 10000) / 10000;
            rowTagihan = (rowSifat === 'POTONG')
                ? Math.max(0, rowDpp - rowPpn)
                : (rowDpp + rowPpn);
            if (rowSifat === 'POTONG' && rowTarif > 0) hasPotongTax = true;
        }

        const cellSubtotal = row.querySelector('.row-subtotal');
        if (cellSubtotal) {
            cellSubtotal.textContent = 'Rp ' + Math.round(rowTagihan).toLocaleString('id-ID');
        }

        totalDpp += rowDpp;
        totalPpn += rowPpn;
        grandTotal += rowTagihan;
    });

    const elTotalQty = document.getElementById('totalQtyDisplay');
    const elTotalSubtotalDisplay = document.getElementById('totalSubtotalDisplay');
    if (elTotalQty) elTotalQty.innerText = totalQty.toFixed(2);
    if (elTotalSubtotalDisplay) elTotalSubtotalDisplay.innerText = 'Rp ' + Math.round(totalBruto).toLocaleString('id-ID');

    const elSummaryTotalQty = document.getElementById('summaryTotalQty');
    const elSubtotalBruto = document.getElementById('summarySubtotalBruto');
    const elDiskon = document.getElementById('summaryDiskon');
    const elPotongan = document.getElementById('summaryPotongan');
    const elDpp = document.getElementById('summaryDpp');
    const elPpn = document.getElementById('summaryPpn');
    const elGrandTotal = document.getElementById('summaryGrandTotal');
    const elBadgeItemCount = document.getElementById('badgeItemCount');

    if (elSummaryTotalQty) elSummaryTotalQty.textContent = totalQty.toFixed(2);
    if (elSubtotalBruto) elSubtotalBruto.textContent = 'Rp ' + Math.round(totalBruto).toLocaleString('id-ID');
    if (elDiskon) elDiskon.textContent = totalDiskon > 0 ? '- Rp ' + Math.round(totalDiskon).toLocaleString('id-ID') : 'Rp 0';
    if (elPotongan) elPotongan.textContent = totalPotongan > 0 ? '- Rp ' + Math.round(totalPotongan).toLocaleString('id-ID') : 'Rp 0';
    if (elDpp) {
        elDpp.innerHTML = 'Rp ' + Math.round(totalDpp).toLocaleString('id-ID') + (hasIncTax ? ' <small style="color: #64748b; font-size: 0.7rem; font-weight: 500;">(DPP)</small>' : '');
    }
    if (elPpn) {
        if (totalPpn > 0) {
            if (hasPotongTax && !hasIncTax) {
                elPpn.innerHTML = `<span style="color: #92400e;">- Rp ${Math.round(totalPpn).toLocaleString('id-ID')}</span> <small style="color: #b45309; font-size: 0.7rem; font-weight: 600;">(Potong)</small>`;
            } else if (hasIncTax) {
                elPpn.innerHTML = `<span style="color: #334155;">Rp ${Math.round(totalPpn).toLocaleString('id-ID')}</span> <small style="color: #64748b; font-size: 0.7rem; font-weight: 600;">(Termasuk)</small>`;
            } else {
                elPpn.innerHTML = `<span style="color: #0f172a;">+ Rp ${Math.round(totalPpn).toLocaleString('id-ID')}</span>`;
            }
        } else {
            elPpn.innerHTML = '<span style="color: #64748b;">Rp 0</span>';
        }
    }
    if (elGrandTotal) elGrandTotal.textContent = 'Rp ' + Math.round(grandTotal).toLocaleString('id-ID');

    if (elBadgeItemCount) elBadgeItemCount.textContent = `(${rowCount} item)`;

    // Live KPI Widget di Siku Kanan Atas
    const topLiveGrandTotal = document.getElementById('topLiveGrandTotal');
    const topLiveItemCount = document.getElementById('topLiveItemCount');
    if (topLiveGrandTotal) topLiveGrandTotal.innerText = 'Rp ' + Math.round(grandTotal).toLocaleString('id-ID');
    if (topLiveItemCount) topLiveItemCount.innerText = rowCount + ' Item';
}

function addNewItemRow() {
    const tbody = document.getElementById('tbodyPoItems');
    const template = document.getElementById('templateRow');
    if (!tbody || !template) return;

    window.editRowIndex = (window.editRowIndex || tbody.querySelectorAll('tr.po-item-row').length) + 1;
    const currentCount = tbody.querySelectorAll('tr.po-item-row').length + 1;

    const html = template.innerHTML
        .replace(/__INDEX__/g, window.editRowIndex)
        .replace(/__NUM__/g, currentCount);

    tbody.insertAdjacentHTML('beforeend', html);

    const newRow = tbody.lastElementChild;
    if (newRow) {
        const headerIncCheck = document.getElementById('is_harga_termasuk_pajak');
        const rowIncCheck = newRow.querySelector('.item-include-tax');
        if (rowIncCheck && headerIncCheck) {
            rowIncCheck.checked = headerIncCheck.checked;
        }
    }

    renumberRows();
    calculateAllTotals();
}

function removeRow(btn) {
    const tbody = document.getElementById('tbodyPoItems');
    const rows = tbody.querySelectorAll('tr.po-item-row');
    if (rows.length <= 1) {
        alert('Minimal harus ada 1 item barang dalam Purchase Order.');
        return;
    }

    btn.closest('tr').remove();
    renumberRows();
    calculateAllTotals();
}

function renumberRows() {
    document.querySelectorAll('#tbodyPoItems tr.po-item-row').forEach((row, idx) => {
        row.querySelector('.row-num').textContent = idx + 1;
    });
}

/**
 * =========================================================================
 * PROTEKSI PERIODE BULAN SEBELUMNYA & LIVE SYNC NOMOR PO DENGAN BULAN
 * =========================================================================
 */
function formatTanggalIndo(dateStr) {
    if (!dateStr) return '-';
    const months = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    const parts = dateStr.split('-');
    if (parts.length === 3) {
        const year = parts[0];
        const monthIdx = parseInt(parts[1], 10) - 1;
        const day = parseInt(parts[2], 10);
        return `${day} ${months[monthIdx] || ''} ${year}`;
    }
    return dateStr;
}

function openPreviousMonthModal(options) {
    const modal = document.getElementById('modalConfirmPreviousMonth');
    if (!modal) return;
    const elPoNo = document.getElementById('mcpmPoNo');
    const elPoTgl = document.getElementById('mcpmPoTgl');
    const elTitle = document.getElementById('mcpmTitle');
    const elSubtitle = document.getElementById('mcpmSubtitle');
    const elContent = document.getElementById('mcpmAlertContent');
    const elQuestion = document.getElementById('mcpmQuestion');

    if (elPoNo) elPoNo.textContent = options.poNo || '-';
    if (elPoTgl) elPoTgl.textContent = formatTanggalIndo(options.poTgl);
    if (elTitle) elTitle.textContent = options.title || 'Peringatan Dokumen Pengadaan';
    if (elSubtitle) elSubtitle.textContent = options.subtitle || 'Perhatian khusus penomoran dokumen & periode pembukuan';
    if (elContent) elContent.innerHTML = options.contentHtml || '';
    if (elQuestion) elQuestion.textContent = options.question || 'Apakah Anda yakin ingin melanjutkan penyimpanan?';

    modal.style.display = 'flex';
}

function closePreviousMonthModal() {
    const modal = document.getElementById('modalConfirmPreviousMonth');
    if (modal) modal.style.display = 'none';
}

function parsePoNoParts(val) {
    if (!val) return null;
    const match = val.trim().match(/^(\d+)\/PO\/MFI\/(\d{1,2})\/(\d{2,4})$/i);
    if (match) {
        const rawYear = match[3];
        const year2 = rawYear.length === 4 ? rawYear.slice(-2) : rawYear.padStart(2, '0');
        return {
            seq: match[1],
            month: match[2].padStart(2, '0'),
            year: year2,
        };
    }
    return null;
}

let lastSystemSuggestedPoNo = '';

function initPoDateNumberSync() {
    const poTglInput = document.getElementById('po_tgl');
    const poNoInput = document.getElementById('po_no');
    const poNoHelp = document.getElementById('po_no_help');

    if (!poTglInput || !poNoInput) return;

    lastSystemSuggestedPoNo = poNoInput.value.trim();
    let initialParts = parsePoNoParts(poNoInput.value);
    let activeMonthYear = initialParts 
        ? `${initialParts.year}-${initialParts.month}` 
        : (poTglInput.value ? poTglInput.value.slice(2, 4) + '-' + poTglInput.value.slice(5, 7) : '');

    function fetchAndApplyPoCode(targetDateStr, reasonText) {
        poNoInput.style.opacity = '0.5';
        poNoInput.style.pointerEvents = 'none';
        if (poNoHelp) {
            poNoHelp.innerHTML = `<span style="color:#0284c7;">⚡ Memeriksa urutan nomor PO untuk periode ${formatTanggalIndo(targetDateStr)}...</span>`;
        }

        const excludeParam = window.poId ? `&exclude_id=${encodeURIComponent(window.poId)}` : '';
        fetch(`/ajax/generate-code?type=po&date=${encodeURIComponent(targetDateStr)}${excludeParam}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success' && data.code) {
                    poNoInput.value = data.code;
                    lastSystemSuggestedPoNo = data.code;
                    const newParts = parsePoNoParts(data.code);
                    if (newParts) {
                        activeMonthYear = `${newParts.year}-${newParts.month}`;
                    }

                    // Visual highlight
                    poNoInput.style.background = '#f0fdf4';
                    setTimeout(() => {
                        poNoInput.style.background = '#ffffff';
                    }, 1200);

                    if (poNoHelp) {
                        poNoHelp.innerHTML = `<span style="color:#15803d; font-weight:600;">✓ Nomor PO disesuaikan otomatis (${reasonText}): <strong>${data.code}</strong></span>`;
                    }
                }
            })
            .catch(err => {
                console.error('Gagal generate nomor PO:', err);
                if (poNoHelp) {
                    poNoHelp.innerHTML = `<span style="color:#dc2626;">Gagal menyinkronkan nomor urut otomatis. Anda dapat mengetiknya secara manual.</span>`;
                }
            })
            .finally(() => {
                poNoInput.style.opacity = '1';
                poNoInput.style.pointerEvents = 'auto';
            });
    }

    // 1. KETIKA TANGGAL PO DIUBAH PENGGUNA (LANGSUNG LIVE)
    ['change', 'input'].forEach(evtName => {
        poTglInput.addEventListener(evtName, function () {
            const newDate = this.value;
            if (!newDate) return;

            const newYM = newDate.slice(2, 4) + '-' + newDate.slice(5, 7);
            if (newYM !== activeMonthYear) {
                fetchAndApplyPoCode(newDate, `urutan bulan ${formatTanggalIndo(newDate)}`);
            }
        });
    });

    // 2. KETIKA BULAN/TAHUN PADA INPUT NOMOR PO DIUBAH LANGSUNG (INSTANT / TANPA HARUS KLIK LUAR)
    let poNoDebounceTimer = null;

    function handlePoNoInputSync() {
        const parts = parsePoNoParts(poNoInput.value);
        if (!parts) return;

        const mNum = parseInt(parts.month, 10);
        if (isNaN(mNum) || mNum < 1 || mNum > 12) return;

        const typedYM = `${parts.year}-${parts.month}`;
        if (typedYM !== activeMonthYear) {
            const targetDate = `20${parts.year}-${parts.month}-01`;
            
            if (poTglInput.value) {
                const currentDay = poTglInput.value.slice(8, 10);
                const safeDate = `20${parts.year}-${parts.month}-${currentDay}`;
                const testD = new Date(safeDate);
                poTglInput.value = (!isNaN(testD.getTime())) ? safeDate : targetDate;
            } else {
                poTglInput.value = targetDate;
            }

            fetchAndApplyPoCode(targetDate, `bulan ${parts.month} tahun 20${parts.year}`);
        }
    }

    // Respon instan saat mengetik (debounce 300ms agar halus saat mengetik 2 digit bulan)
    poNoInput.addEventListener('input', function () {
        clearTimeout(poNoDebounceTimer);
        poNoDebounceTimer = setTimeout(handlePoNoInputSync, 300);
    });

    // Respon instan saat change atau blur
    ['change', 'blur'].forEach(evtName => {
        poNoInput.addEventListener(evtName, function () {
            clearTimeout(poNoDebounceTimer);
            handlePoNoInputSync();
        });
    });
}

function initPreviousMonthProtection() {
    const formEditPo = document.getElementById('formEditPo');
    if (!formEditPo) return;

    let previousMonthConfirmed = false;

    formEditPo.addEventListener('submit', function (e) {
        if (previousMonthConfirmed) return;

        const poTglInput = document.getElementById('po_tgl');
        const poNoInput = document.getElementById('po_no');
        const poTglVal = poTglInput ? poTglInput.value : '';
        const poNoVal = poNoInput ? poNoInput.value.trim() : '';
        const originalTglVal = window.originalPoTgl || '';
        const originalPoNoVal = window.originalPoNo || '';
        const currentServerYM = window.currentServerYearMonth || (new Date().toISOString().slice(0, 7));

        const checkDate = poTglVal || originalTglVal;
        const docYM = checkDate ? checkDate.slice(0, 7) : '';
        const isPreviousMonth = Boolean(docYM && docYM < currentServerYM);

        const currentParts = parsePoNoParts(poNoVal);
        const baselineParts = parsePoNoParts(lastSystemSuggestedPoNo || originalPoNoVal);
        const isManualSequence = Boolean(currentParts && baselineParts && (currentParts.seq !== baselineParts.seq));

        if (isPreviousMonth || isManualSequence) {
            e.preventDefault();

            let title = '';
            let subtitle = '';
            let alerts = [];

            if (isPreviousMonth && isManualSequence) {
                title = 'Peringatan: Dokumen Periode Sebelumnya & Nomor Urut Manual';
                subtitle = 'Dua penyesuaian khusus terdeteksi pada pembaruan dokumen ini';
            } else if (isPreviousMonth) {
                title = 'Konfirmasi Perubahan Dokumen Periode Sebelumnya';
                subtitle = 'Dokumen berasal dari bulan/periode yang telah lewat';
            } else {
                title = 'Peringatan: Nomor Urut PO Telah Diubah Manual';
                subtitle = 'Nomor urut berbeda dari standar nomor resmi sistem';
            }

            if (isPreviousMonth) {
                alerts.push(`
                    <div style="margin-bottom: 0.45rem;">
                        <strong>1. Periode Bulan Sebelumnya:</strong> Dokumen Purchase Order ini tercatat pada periode <strong>${formatTanggalIndo(checkDate)}</strong>, sedangkan periode aktif sistem saat ini adalah <strong>${formatTanggalIndo(new Date().toISOString().slice(0, 10))}</strong>. Mengubah data pada dokumen periode lalu berpotensi memengaruhi laporan penutupan buku (closing) dan rekonsiliasi pengadaan gudang.
                    </div>
                `);
            }

            if (isManualSequence) {
                alerts.push(`
                    <div style="margin-bottom: 0.45rem;">
                        <strong>${isPreviousMonth ? '2. ' : ''}Nomor Urut Manual:</strong> Anda memasukkan nomor urut <strong>${currentParts.seq}</strong> (dokumen: <code>${poNoVal}</code>), sedangkan nomor urut yang disarankan sistem/sebelumnya adalah <strong>${baselineParts.seq}</strong> (dokumen: <code>${lastSystemSuggestedPoNo || originalPoNoVal}</code>). Mengubah nomor urut secara manual dapat menyebabkan nomor loncat atau potensi duplikasi arsip.
                    </div>
                `);
            }

            openPreviousMonthModal({
                poNo: poNoVal,
                poTgl: checkDate,
                title: title,
                subtitle: subtitle,
                contentHtml: alerts.join(''),
                question: 'Apakah Anda yakin data perubahan ini sudah benar dan ingin tetap menyimpannya?'
            });
        }
    });

    const btnConfirm = document.getElementById('btnConfirmSubmitPreviousMonth');
    if (btnConfirm) {
        btnConfirm.addEventListener('click', function () {
            previousMonthConfirmed = true;
            closePreviousMonthModal();
            formEditPo.submit();
        });
    }
}

// Hitung total awal saat halaman dimuat
document.addEventListener('DOMContentLoaded', function () {
    syncHeaderTaxLabel();
    const headerIncCheck = document.getElementById('is_harga_termasuk_pajak');
    if (headerIncCheck && headerIncCheck.checked) {
        onHeaderTaxInclusiveToggle(headerIncCheck);
    }
    calculateAllTotals();
    initPoDateNumberSync();
    initPreviousMonthProtection();
});
