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

// Hitung total awal saat halaman dimuat
document.addEventListener('DOMContentLoaded', function () {
    calculateAllTotals();
});
