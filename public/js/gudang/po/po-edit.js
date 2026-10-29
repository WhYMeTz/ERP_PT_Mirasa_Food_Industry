/**
 * =========================================================================
 * ERP PT MIRASA - PURCHASE ORDER EDIT JAVASCRIPT
 * =========================================================================
 */

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
            badge.innerText = 'Sudah Termasuk PPN (Gross)';
            badge.style.background = '#dcfce7';
            badge.style.color = '#15803d';
        } else {
            badge.innerText = 'Belum Termasuk PPN (Net)';
            badge.style.background = '#e2e8f0';
            badge.style.color = '#475569';
        }
    }
    document.querySelectorAll('.item-include-tax').forEach(cb => {
        cb.checked = isInc;
    });
    calculateAllTotals();
}

function calculateRow(element) {
    const row = element.closest('tr');
    const qty = parseFloat(row.querySelector('.input-qty')?.value) || 0;
    const harga = parseFloat(row.querySelector('.input-harga')?.value) || 0;
    const diskonPersen = Math.min(100, Math.max(0, parseFloat(row.querySelector('.input-diskon')?.value) || 0));
    const potonganNominal = parseFloat(row.querySelector('.input-potongan')?.value) || 0;

    const pajakSelect = row.querySelector('.select-pajak');
    const selectedOpt = pajakSelect ? pajakSelect.options[pajakSelect.selectedIndex] : null;
    const tarif = selectedOpt ? (parseFloat(selectedOpt.dataset.tarif) || 0) : 0;
    const sifat = selectedOpt ? (selectedOpt.dataset.sifat || 'TAMBAH') : 'TAMBAH';
    const isInc = row.querySelector('.item-include-tax')?.checked || false;

    const diskonUnit = harga * (diskonPersen / 100);
    const hargaNetto = Math.max(0, harga - diskonUnit);
    const grossRowTotal = Math.max(0, (qty * hargaNetto) - potonganNominal);

    let subtotalNetto = 0;
    let ppnNominal = 0;
    let subtotalTagihan = 0;

    if (isInc && tarif > 0) {
        subtotalNetto = Math.round((grossRowTotal / (1 + (tarif / 100))) * 10000) / 10000;
        ppnNominal = Math.round((grossRowTotal - subtotalNetto) * 10000) / 10000;
        subtotalTagihan = grossRowTotal;
    } else {
        subtotalNetto = grossRowTotal;
        ppnNominal = Math.round((subtotalNetto * (tarif / 100)) * 10000) / 10000;
        subtotalTagihan = (sifat === 'POTONG')
            ? Math.max(0, subtotalNetto - ppnNominal)
            : (subtotalNetto + ppnNominal);
    }

    const cellSubtotal = row.querySelector('.row-subtotal');
    if (cellSubtotal) {
        cellSubtotal.textContent = 'Rp ' + Math.round(subtotalTagihan).toLocaleString('id-ID');
    }

    calculateAllTotals();
}

function calculateAllTotals() {
    let totalBruto = 0;
    let totalDiskon = 0;
    let totalDpp = 0;
    let totalPpn = 0;
    let grandTotal = 0;

    document.querySelectorAll('#tbodyPoItems tr.po-item-row').forEach(row => {
        const qty = parseFloat(row.querySelector('.input-qty')?.value) || 0;
        const harga = parseFloat(row.querySelector('.input-harga')?.value) || 0;
        const diskonPersen = Math.min(100, Math.max(0, parseFloat(row.querySelector('.input-diskon')?.value) || 0));
        const potonganNominal = parseFloat(row.querySelector('.input-potongan')?.value) || 0;

        const pajakSelect = row.querySelector('.select-pajak');
        const selectedOpt = pajakSelect ? pajakSelect.options[pajakSelect.selectedIndex] : null;
        const tarif = selectedOpt ? (parseFloat(selectedOpt.dataset.tarif) || 0) : 0;
        const sifat = selectedOpt ? (selectedOpt.dataset.sifat || 'TAMBAH') : 'TAMBAH';
        const isInc = row.querySelector('.item-include-tax')?.checked || false;

        const diskonUnit = harga * (diskonPersen / 100);
        const hargaNetto = Math.max(0, harga - diskonUnit);
        const grossRowTotal = Math.max(0, (qty * hargaNetto) - potonganNominal);

        let subtotalNetto = 0;
        let ppnNominal = 0;
        let subtotalTagihan = 0;

        if (isInc && tarif > 0) {
            subtotalNetto = Math.round((grossRowTotal / (1 + (tarif / 100))) * 10000) / 10000;
            ppnNominal = Math.round((grossRowTotal - subtotalNetto) * 10000) / 10000;
            subtotalTagihan = grossRowTotal;
        } else {
            subtotalNetto = grossRowTotal;
            ppnNominal = Math.round((subtotalNetto * (tarif / 100)) * 10000) / 10000;
            subtotalTagihan = (sifat === 'POTONG')
                ? Math.max(0, subtotalNetto - ppnNominal)
                : (subtotalNetto + ppnNominal);
        }

        const cellSubtotal = row.querySelector('.row-subtotal');
        if (cellSubtotal) {
            cellSubtotal.textContent = 'Rp ' + Math.round(subtotalTagihan).toLocaleString('id-ID');
        }

        totalBruto += (qty * harga);
        totalDiskon += (qty * diskonUnit) + potonganNominal;
        totalDpp += subtotalNetto;
        totalPpn += ppnNominal;
        grandTotal += subtotalTagihan;
    });

    const elSubtotalBruto = document.getElementById('summarySubtotalBruto');
    const elDiskon = document.getElementById('summaryDiskon');
    const elDpp = document.getElementById('summaryDpp');
    const elPpn = document.getElementById('summaryPpn');
    const elGrandTotal = document.getElementById('summaryGrandTotal');
    const elBadgeItemCount = document.getElementById('badgeItemCount');

    if (elSubtotalBruto) elSubtotalBruto.textContent = 'Rp ' + Math.round(totalBruto).toLocaleString('id-ID');
    if (elDiskon) elDiskon.textContent = totalDiskon > 0 ? '- Rp ' + Math.round(totalDiskon).toLocaleString('id-ID') : 'Rp 0';
    if (elDpp) elDpp.textContent = 'Rp ' + Math.round(totalDpp).toLocaleString('id-ID');
    if (elPpn) elPpn.textContent = totalPpn > 0 ? '+ Rp ' + Math.round(totalPpn).toLocaleString('id-ID') : 'Rp 0';
    if (elGrandTotal) elGrandTotal.textContent = 'Rp ' + Math.round(grandTotal).toLocaleString('id-ID');

    const rowCount = document.querySelectorAll('#tbodyPoItems tr.po-item-row').length;
    if (elBadgeItemCount) elBadgeItemCount.textContent = `(${rowCount} item)`;
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
    
    // Sinkronkan include tax default baris baru dengan header
    const headerIncCheck = document.getElementById('is_harga_termasuk_pajak');
    const newlyAddedRow = tbody.lastElementChild;
    if (newlyAddedRow && headerIncCheck) {
        const rowInc = newlyAddedRow.querySelector('.item-include-tax');
        if (rowInc) rowInc.checked = headerIncCheck.checked;
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

function openPreviousMonthModal(poNo, poTgl) {
    const modal = document.getElementById('modalConfirmPreviousMonth');
    if (!modal) return;
    const elPoNo = document.getElementById('mcpmPoNo');
    const elPoTgl = document.getElementById('mcpmPoTgl');
    if (elPoNo) elPoNo.textContent = poNo || '-';
    if (elPoTgl) elPoTgl.textContent = formatTanggalIndo(poTgl);
    modal.style.display = 'flex';
}

function closePreviousMonthModal() {
    const modal = document.getElementById('modalConfirmPreviousMonth');
    if (modal) modal.style.display = 'none';
}

function parsePoNoParts(val) {
    if (!val) return null;
    const match = val.trim().match(/^(\d+)\/PO\/MFI\/(\d{1,2})\/(\d{2})$/i);
    if (match) {
        return {
            seq: match[1],
            month: match[2].padStart(2, '0'),
            year: match[3],
        };
    }
    return null;
}

function initPoDateNumberSync() {
    const poTglInput = document.getElementById('po_tgl');
    const poNoInput = document.getElementById('po_no');
    const poNoHelp = document.getElementById('po_no_help');

    if (!poTglInput || !poNoInput) return;

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
        const originalTglVal = window.originalPoTgl || '';
        const currentServerYM = window.currentServerYearMonth || (new Date().toISOString().slice(0, 7));

        // Jika tanggal dokumen yang dipilih atau dokumen awal berada di bawah bulan berjalan
        const checkDate = poTglVal || originalTglVal;
        const docYM = checkDate ? checkDate.slice(0, 7) : '';

        if (docYM && docYM < currentServerYM) {
            e.preventDefault();
            openPreviousMonthModal(poNoInput ? poNoInput.value : '', checkDate);
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
