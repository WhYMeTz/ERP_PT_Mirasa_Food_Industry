<template id="itemRowTemplate">
    <tr class="item-row" data-index="__INDEX__">
        <td class="row-num" style="font-weight: 700; text-align: center; color: #475569; background: #f8fafc;">__NUM__</td>
        <td>
            <select name="items[__INDEX__][barang_id]" class="form-control item-barang" onchange="updateRowSatuan(this)" required>
                <option value="">-- Pilih Barang (Bahan Baku / Penolong) --</option>
                
                <optgroup label="BAHAN BAKU (RAW MATERIAL)" class="grp-bb">
                    @foreach($barangList->filter(fn($b) => in_array($b->jenisBarang?->jenis_barang_cd, ['RAW', 'BB'])) as $b)
                        <option value="{{ $b->barang_id }}" data-category="BB" data-code="{{ $b->barang_cd }}" data-satuan="{{ $b->satuanDasar?->satuan_nm ?? '-' }}" data-harga="{{ (float) ($b->harga_beli_standar ?? 0) }}">
                            {{ $b->barang_nm }} ({{ $b->barang_cd }}) - Std: Rp {{ number_format((float) ($b->harga_beli_standar ?? 0), 0, ',', '.') }}/{{ $b->satuanDasar?->satuan_cd }}
                        </option>
                    @endforeach
                </optgroup>

                <optgroup label="BUMBU &amp; BAHAN PENOLONG" class="grp-bumbu">
                    @foreach($barangList->filter(fn($b) => in_array($b->jenisBarang?->jenis_barang_cd, ['SUPP', 'BUMBU', 'BP']) && !preg_match('/(PLASTIK|KARTON|ROLL|LAKBAN|SARUNG|RAFIA)/i', $b->barang_nm)) as $b)
                        <option value="{{ $b->barang_id }}" data-category="BUMBU" data-code="{{ $b->barang_cd }}" data-satuan="{{ $b->satuanDasar?->satuan_nm ?? '-' }}" data-harga="{{ (float) ($b->harga_beli_standar ?? 0) }}">
                            {{ $b->barang_nm }} ({{ $b->barang_cd }}) - Std: Rp {{ number_format((float) ($b->harga_beli_standar ?? 0), 0, ',', '.') }}/{{ $b->satuanDasar?->satuan_cd }}
                        </option>
                    @endforeach
                </optgroup>

                <optgroup label="BAHAN KEMASAN &amp; PACKAGING" class="grp-pack">
                    @foreach($barangList->filter(fn($b) => in_array($b->jenisBarang?->jenis_barang_cd, ['PACK']) || (in_array($b->jenisBarang?->jenis_barang_cd, ['BP']) && preg_match('/(PLASTIK|KARTON|ROLL|LAKBAN|SARUNG|RAFIA)/i', $b->barang_nm))) as $b)
                        <option value="{{ $b->barang_id }}" data-category="PACK" data-code="{{ $b->barang_cd }}" data-satuan="{{ $b->satuanDasar?->satuan_nm ?? '-' }}" data-harga="{{ (float) ($b->harga_beli_standar ?? 0) }}">
                            {{ $b->barang_nm }} ({{ $b->barang_cd }}) - Std: Rp {{ number_format((float) ($b->harga_beli_standar ?? 0), 0, ',', '.') }}/{{ $b->satuanDasar?->satuan_cd }}
                        </option>
                    @endforeach
                </optgroup>
            </select>
            <div class="row-barang-hint" style="font-size: 0.68rem; margin-top: 2px; color: #64748b; display: none;"></div>
        </td>
        <td class="col-supplier" style="__SUP_DISPLAY__">
            <select name="items[__INDEX__][supplier_id]" class="form-control item-supplier" onchange="calculateGrandTotal()" __SUP_REQUIRED__>
                {{-- Diisi secara dinamis via JS --}}
            </select>
            <div class="row-supplier-hint" style="font-size: 0.68rem; margin-top: 2px; display: none;"></div>
        </td>
        <td style="text-align: center;">
            <span class="row-satuan" style="font-weight: 600; color: #475569;">-</span>
        </td>
        <td>
            <input type="number" step="0.0001" min="0.0001" name="items[__INDEX__][pesan_qty]" class="form-control item-qty" value="1" oninput="calculateSubtotal(this)" style="text-align: right; font-weight: 700;" required>
        </td>
        <td>
            <input type="number" step="0.01" min="0" name="items[__INDEX__][harga_nominal]" class="form-control item-harga" value="0" oninput="calculateSubtotal(this)" placeholder="0" style="text-align: right; font-weight: 600;">
        </td>
        <td>
            <input type="number" step="0.1" min="0" max="100" name="items[__INDEX__][diskon_persen]" class="form-control item-diskon" value="0" oninput="calculateSubtotal(this)" placeholder="0" style="text-align: right;">
        </td>
        <td>
            <input type="number" step="0.01" min="0" name="items[__INDEX__][potongan_nominal]" class="form-control item-potongan" value="0" oninput="calculateSubtotal(this)" placeholder="0" style="text-align: right;">
        </td>
        <td>
            <select name="items[__INDEX__][ppn_tipe]" class="form-control item-ppn-tipe" onchange="calculateSubtotal(this)" style="font-size: 0.775rem; font-weight: 600;">
                <option value="NON_PPN">Non (0%)</option>
                <option value="PPN_11">PPN 11%</option>
            </select>
        </td>
        <td style="text-align: right; font-weight: 700; color: #0f172a; font-size: 0.85rem; font-family: monospace;" class="row-subtotal">
            Rp 0
        </td>
        <td style="text-align: center;">
            <button type="button" onclick="removeRow(this)" class="btn btn-danger btn-sm" style="padding: 0.15rem 0.4rem; font-size: 0.8rem;" title="Hapus Baris">&times;</button>
        </td>
    </tr>
</template>
