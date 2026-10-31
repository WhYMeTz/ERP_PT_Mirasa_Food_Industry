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
    calculateAllTotals();
}

function calculateRow(element) {
    const row = element.closest('tr');
    const qty = parseFloat(row.querySelector('.input-qty')?.value) || 0;
    const harga = parseFloat(row.querySelector('.input-harga')?.value) || 0;
    const rowSubtotal = qty * harga;

    const cellSubtotal = row.querySelector('.row-subtotal');
    if (cellSubtotal) {
        cellSubtotal.textContent = 'Rp ' + Math.round(rowSubtotal).toLocaleString('id-ID');
    }

    calculateAllTotals();
}

function calculateAllTotals() {
    let totalQty = 0;
    let totalBruto = 0;
    let rowCount = 0;

    document.querySelectorAll('#tbodyPoItems tr.po-item-row').forEach(row => {
        rowCount++;
        const qty = parseFloat(row.querySelector('.input-qty')?.value) || 0;
        const harga = parseFloat(row.querySelector('.input-harga')?.value) || 0;
        const rowSubtotal = qty * harga;

        const cellSubtotal = row.querySelector('.row-subtotal');
        if (cellSubtotal) {
            cellSubtotal.textContent = 'Rp ' + Math.round(rowSubtotal).toLocaleString('id-ID');
        }

        totalQty += qty;
        totalBruto += rowSubtotal;
    });

    const diskonPersen = Math.min(100, Math.max(0, parseFloat(document.getElementById('global_diskon_persen')?.value) || 0));
    const potonganNominal = Math.max(0, parseFloat(document.getElementById('global_potongan_nominal')?.value) || 0);

    const pajakSelect = document.getElementById('global_pajak_id');
    const selectedOpt = pajakSelect ? pajakSelect.options[pajakSelect.selectedIndex] : null;
    const tarif = selectedOpt ? (parseFloat(selectedOpt.dataset.tarif) || 0) : 0;
    const sifat = selectedOpt ? (selectedOpt.dataset.sifat || 'TAMBAH') : 'TAMBAH';
    const isInc = document.getElementById('is_harga_termasuk_pajak')?.checked || false;

    const totalDiskon = totalBruto * (diskonPersen / 100);
    const totalPotongan = potonganNominal;
    const subtotalNetto = Math.max(0, totalBruto - totalDiskon - totalPotongan);

    let totalDpp = 0;
    let totalPpn = 0;
    let grandTotal = 0;

    if (isInc && tarif > 0 && sifat !== 'POTONG') {
        totalDpp = Math.round((subtotalNetto / (1 + (tarif / 100))) * 10000) / 10000;
        totalPpn = Math.round((subtotalNetto - totalDpp) * 10000) / 10000;
        grandTotal = subtotalNetto;
    } else {
        totalDpp = subtotalNetto;
        totalPpn = Math.round((totalDpp * (tarif / 100)) * 10000) / 10000;
        grandTotal = (sifat === 'POTONG')
            ? Math.max(0, totalDpp - totalPpn)
            : (totalDpp + totalPpn);
    }

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
        elDpp.innerHTML = 'Rp ' + Math.round(totalDpp).toLocaleString('id-ID') + (isInc && tarif > 0 && sifat !== 'POTONG' ? ' <small style="color: #64748b; font-size: 0.7rem; font-weight: 500;">(DPP)</small>' : '');
    }
    if (elPpn) {
        if (totalPpn > 0) {
            if (sifat === 'POTONG') {
                elPpn.innerHTML = `<span style="color: #dc2626;">- Rp ${Math.round(totalPpn).toLocaleString('id-ID')}</span> <small style="color: #ef4444; font-size: 0.7rem; font-weight: 600;">(Potong)</small>`;
            } else if (isInc) {
                elPpn.innerHTML = `<span style="color: #16a34a;">Rp ${Math.round(totalPpn).toLocaleString('id-ID')}</span> <small style="color: #15803d; font-size: 0.7rem; font-weight: 600;">(Termasuk)</small>`;
            } else {
                elPpn.innerHTML = `<span style="color: #0284c7;">+ Rp ${Math.round(totalPpn).toLocaleString('id-ID')}</span>`;
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
    calculateAllTotals();
    initPoDateNumberSync();
    initPreviousMonthProtection();
});
