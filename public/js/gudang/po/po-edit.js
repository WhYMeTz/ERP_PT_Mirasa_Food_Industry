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

function calculateRow(element) {
    const row = element.closest('tr');
    const qty = parseFloat(row.querySelector('.input-qty').value) || 0;
    const harga = parseFloat(row.querySelector('.input-harga').value) || 0;
    const diskonPersen = parseFloat(row.querySelector('.input-diskon').value) || 0;
    const potonganNominal = parseFloat(row.querySelector('.input-potongan').value) || 0;
    const ppnTipe = row.querySelector('.select-ppn').value;

    const diskonUnit = harga * (diskonPersen / 100);
    const hargaNetto = Math.max(0, harga - diskonUnit);
    const subtotalNetto = Math.max(0, (qty * hargaNetto) - potonganNominal);
    const ppnNominal = (ppnTipe === 'PPN_11') ? Math.round(subtotalNetto * 0.11) : 0;
    const subtotalTagihan = subtotalNetto + ppnNominal;

    row.querySelector('.row-subtotal').textContent = 'Rp ' + subtotalTagihan.toLocaleString('id-ID');
    row.setAttribute('data-subtotal-bruto', (qty * harga));
    row.setAttribute('data-diskon-total', (qty * diskonUnit) + potonganNominal);
    row.setAttribute('data-dpp', subtotalNetto);
    row.setAttribute('data-ppn', ppnNominal);
    row.setAttribute('data-grand-total', subtotalTagihan);

    calculateAllTotals();
}

function calculateAllTotals() {
    let totalBruto = 0;
    let totalDiskon = 0;
    let totalDpp = 0;
    let totalPpn = 0;
    let grandTotal = 0;

    document.querySelectorAll('#tbodyPoItems tr.po-item-row').forEach(row => {
        const qty = parseFloat(row.querySelector('.input-qty').value) || 0;
        const harga = parseFloat(row.querySelector('.input-harga').value) || 0;
        const diskonPersen = parseFloat(row.querySelector('.input-diskon').value) || 0;
        const potonganNominal = parseFloat(row.querySelector('.input-potongan').value) || 0;
        const ppnTipe = row.querySelector('.select-ppn').value;

        const diskonUnit = harga * (diskonPersen / 100);
        const hargaNetto = Math.max(0, harga - diskonUnit);
        const subtotalNetto = Math.max(0, (qty * hargaNetto) - potonganNominal);
        const ppnNominal = (ppnTipe === 'PPN_11') ? Math.round(subtotalNetto * 0.11) : 0;
        const subtotalTagihan = subtotalNetto + ppnNominal;

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

    if (elSubtotalBruto) elSubtotalBruto.textContent = 'Rp ' + totalBruto.toLocaleString('id-ID');
    if (elDiskon) elDiskon.textContent = '- Rp ' + totalDiskon.toLocaleString('id-ID');
    if (elDpp) elDpp.textContent = 'Rp ' + totalDpp.toLocaleString('id-ID');
    if (elPpn) elPpn.textContent = 'Rp ' + totalPpn.toLocaleString('id-ID');
    if (elGrandTotal) elGrandTotal.textContent = 'Rp ' + grandTotal.toLocaleString('id-ID');

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
