/**
 * =========================================================================
 * ERP PT MIRASA - MASTER FORMULA RESEP PRODUKSI (BOM) FORM JAVASCRIPT
 * =========================================================================
 */

let materialIndex = 0;

/**
 * Handle perubahan produk target jadi / WIP
 */
function onTargetProductChanged(selectEl) {
    if (!selectEl) return;
    const selectedOpt = selectEl.selectedOptions[0];
    const satuan = selectedOpt ? selectedOpt.getAttribute('data-satuan') : 'Unit';
    const labelEl = document.getElementById('targetSatuanLabel');
    if (labelEl) {
        labelEl.textContent = satuan || 'Unit';
    }
}

/**
 * Tambah baris bahan baku/penolong baru ke tabel BOM
 */
function addMaterialRow(initialData = null) {
    const bahanList = (window.appConfig && window.appConfig.bahanBakuList) 
        ? window.appConfig.bahanBakuList 
        : [];

    const tbody = document.getElementById('materialsBody');
    if (!tbody) return;

    const tr = document.createElement('tr');
    tr.id = `mat-row-${materialIndex}`;
    tr.style.borderBottom = '1px solid #f1f5f9';

    let options = '<option value="">-- Pilih Bahan Baku / Penolong --</option>';
    bahanList.forEach(b => {
        const jenis = b.jenis_barang ? b.jenis_barang.jenis_barang_cd : '';
        const isSel = (initialData && parseInt(initialData.barang_mentah_id) === parseInt(b.barang_id)) ? 'selected' : '';
        options += `<option value="${b.barang_id}" data-satuan="${b.satuan_dasar?.satuan_nm || ''}" ${isSel}>[${b.barang_cd}] ${b.barang_nm} (${jenis})</option>`;
    });

    const qtyVal = initialData ? initialData.kebutuhan_qty : '';
    const catVal = initialData ? (initialData.catatan_txt || '') : '';
    const satuanVal = initialData ? (initialData.satuan_nm || '') : '';

    tr.innerHTML = `
        <td style="padding: 0.5rem 0.75rem;">
            <select name="items[${materialIndex}][barang_mentah_id]" class="form-control mat-select" style="font-size: 0.875rem;" required onchange="onMaterialSelected(this)">
                ${options}
            </select>
        </td>
        <td style="padding: 0.5rem 0.75rem;">
            <input type="number" step="0.0001" min="0.0001" name="items[${materialIndex}][kebutuhan_qty]" value="${qtyVal}" class="form-control" placeholder="0" style="font-weight: 700; text-align: right;" required>
        </td>
        <td style="padding: 0.5rem 0.75rem; color: #475569; font-weight: 600; font-size: 0.85rem;" class="satuan-display">
            ${satuanVal}
        </td>
        <td style="padding: 0.5rem 0.75rem;">
            <input type="text" name="items[${materialIndex}][catatan_txt]" value="${catVal}" class="form-control" placeholder="Misal: Grade A, 1.5kg/karton" style="font-size: 0.85rem;">
        </td>
        <td style="padding: 0.5rem 0.75rem; text-align: center;">
            <button type="button" class="btn-remove-material" onclick="removeMaterialRow(this)" title="Hapus Baris">&times;</button>
        </td>
    `;

    tbody.appendChild(tr);

    // Update satuan jika sudah terpilih
    const selectElem = tr.querySelector('.mat-select');
    if (selectElem && selectElem.value) {
        onMaterialSelected(selectElem);
    }

    materialIndex++;
}

/**
 * Hapus baris bahan baku
 */
function removeMaterialRow(btn) {
    const tbody = document.getElementById('materialsBody');
    if (!tbody) return;

    if (tbody.children.length > 1) {
        btn.closest('tr').remove();
    } else {
        // Jangan gunakan alert bawaan browser jika bisa dicegah, gunakan inline notice atau minimal 1 baris
        const tr = btn.closest('tr');
        const inputs = tr.querySelectorAll('input, select');
        inputs.forEach(inp => inp.value = '');
        const satDisp = tr.querySelector('.satuan-display');
        if (satDisp) satDisp.textContent = '-';
    }
}

/**
 * Update display satuan saat bahan dipilih
 */
function onMaterialSelected(selectEl) {
    const row = selectEl.closest('tr');
    if (!row) return;
    const selectedOpt = selectEl.selectedOptions[0];
    const satuan = selectedOpt ? selectedOpt.getAttribute('data-satuan') : '-';
    const satEl = row.querySelector('.satuan-display');
    if (satEl) {
        satEl.textContent = satuan || '-';
    }
}

/**
 * Inisialisasi formulir BOM
 */
document.addEventListener('DOMContentLoaded', () => {
    const initialDetails = (window.appConfig && window.appConfig.initialDetails) 
        ? window.appConfig.initialDetails 
        : [];

    if (initialDetails.length > 0) {
        initialDetails.forEach(dtl => {
            addMaterialRow(dtl);
        });
    } else {
        // Buat baris kosong pertama
        addMaterialRow();
    }

    // Set label satuan awal produk jadi
    const targetSelect = document.getElementById('barang_jadi_id');
    if (targetSelect) {
        onTargetProductChanged(targetSelect);
    }
});
