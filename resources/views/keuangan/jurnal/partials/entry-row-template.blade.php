<template id="entryRowTemplate">
    <tr class="entry-row" data-row-idx="__INDEX__">
        <td style="text-align: center; font-weight: 700; color: #64748b;">
            <span class="row-number">__INDEX__</span>
        </td>
        <td>
            <div class="coa-cell-wrapper">
                <input type="hidden" name="entries[__INDEX__][coa_id]" class="input-coa-id" required>
                <button type="button" 
                        class="btn-pick-coa" 
                        onclick="openCoaPickerForIndex(__INDEX__)" 
                        title="Klik untuk memilih akun COA">
                    <span class="coa-display-code" style="display: none;">-</span>
                    <span class="coa-display-name">-- Klik untuk Pilih Akun --</span>
                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-left: auto; color: #94a3b8;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
            </div>
        </td>
        <td>
            <input type="text" 
                   name="entries[__INDEX__][keterangan]" 
                   class="jurnal-input input-keterangan" 
                   placeholder="Keterangan baris...">
        </td>
        <td>
            <div class="input-currency-group">
                <span class="prefix-rp">Rp</span>
                <input type="number" 
                       name="entries[__INDEX__][debet]" 
                       class="jurnal-input input-currency input-debet" 
                       step="any" 
                       min="0" 
                       placeholder="0">
            </div>
        </td>
        <td>
            <div class="input-currency-group">
                <span class="prefix-rp">Rp</span>
                <input type="number" 
                       name="entries[__INDEX__][kredit]" 
                       class="jurnal-input input-currency input-kredit" 
                       step="any" 
                       min="0" 
                       placeholder="0">
            </div>
        </td>
        <td style="text-align: center;">
            <div style="display: inline-flex; align-items: center; gap: 4px;">
                <button type="button" 
                        class="btn-row-action btn-balance-helper" 
                        onclick="balanceThisRow(__INDEX__)" 
                        title="Isi otomatis nilai penyeimbang selisih (Auto Balance)">
                    ⚖️
                </button>
                <button type="button" 
                        class="btn-row-action btn-remove-row" 
                        onclick="removeEntryRow(this)" 
                        title="Hapus baris transaksi">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </div>
        </td>
    </tr>
</template>
