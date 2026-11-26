<template id="terimaRowTemplate">
    <tr class="terima-row" data-index="__INDEX__" data-sisa="0">
        <td class="row-num" style="font-weight: 700; text-align: center; color: #475569; background: #f8fafc;">__NUM__</td>
        <td>
            <select name="items[__INDEX__][barang_id]" class="form-control item-barang" onchange="updateTerimaSatuanAndBatch(this)" required>
                <option value="">-- Pilih Barang (Bahan Baku / Penolong) --</option>
                
                <optgroup label="BAHAN BAKU (RAW MATERIAL)" class="grp-bb">
                    @foreach($barangList->filter(fn($b) => in_array($b->jenisBarang?->jenis_barang_cd, ['RAW', 'BB'])) as $b)
                        <option value="{{ $b->barang_id }}" 
                                data-category="BB"
                                data-cd="{{ $b->barang_cd }}" 
                                data-nm="{{ $b->barang_nm }}"
                                data-acronym="{{ app(\App\Services\Common\CodeGeneratorService::class)->extractBarangAcronym($b->barang_nm, $b->barang_cd) }}"
                                data-satuan="{{ $b->satuanDasar?->satuan_nm ?? '-' }}" 
                                data-harga="{{ (float) ($b->harga_beli_standar ?? 0) }}">
                            {{ $b->barang_nm }} ({{ $b->barang_cd }}) - Std: Rp {{ number_format((float) ($b->harga_beli_standar ?? 0), 0, ',', '.') }}/{{ $b->satuanDasar?->satuan_cd }}
                        </option>
                    @endforeach
                </optgroup>

                <optgroup label="BUMBU &amp; BAHAN PENOLONG" class="grp-bumbu">
                    @foreach($barangList->filter(fn($b) => in_array($b->jenisBarang?->jenis_barang_cd, ['SUPP', 'BUMBU', 'BP']) && !preg_match('/(PLASTIK|KARTON|ROLL|LAKBAN|SARUNG|RAFIA)/i', $b->barang_nm)) as $b)
                        <option value="{{ $b->barang_id }}" 
                                data-category="BUMBU"
                                data-cd="{{ $b->barang_cd }}" 
                                data-nm="{{ $b->barang_nm }}"
                                data-acronym="{{ app(\App\Services\Common\CodeGeneratorService::class)->extractBarangAcronym($b->barang_nm, $b->barang_cd) }}"
                                data-satuan="{{ $b->satuanDasar?->satuan_nm ?? '-' }}" 
                                data-harga="{{ (float) ($b->harga_beli_standar ?? 0) }}">
                            {{ $b->barang_nm }} ({{ $b->barang_cd }}) - Std: Rp {{ number_format((float) ($b->harga_beli_standar ?? 0), 0, ',', '.') }}/{{ $b->satuanDasar?->satuan_cd }}
                        </option>
                    @endforeach
                </optgroup>

                <optgroup label="BAHAN KEMASAN &amp; PACKAGING" class="grp-pack">
                    @foreach($barangList->filter(fn($b) => in_array($b->jenisBarang?->jenis_barang_cd, ['PACK']) || (in_array($b->jenisBarang?->jenis_barang_cd, ['BP']) && preg_match('/(PLASTIK|KARTON|ROLL|LAKBAN|SARUNG|RAFIA)/i', $b->barang_nm))) as $b)
                        <option value="{{ $b->barang_id }}" 
                                data-category="PACK"
                                data-cd="{{ $b->barang_cd }}" 
                                data-nm="{{ $b->barang_nm }}"
                                data-acronym="{{ app(\App\Services\Common\CodeGeneratorService::class)->extractBarangAcronym($b->barang_nm, $b->barang_cd) }}"
                                data-satuan="{{ $b->satuanDasar?->satuan_nm ?? '-' }}" 
                                data-harga="{{ (float) ($b->harga_beli_standar ?? 0) }}">
                            {{ $b->barang_nm }} ({{ $b->barang_cd }}) - Std: Rp {{ number_format((float) ($b->harga_beli_standar ?? 0), 0, ',', '.') }}/{{ $b->satuanDasar?->satuan_cd }}
                        </option>
                    @endforeach
                </optgroup>
            </select>
            <input type="hidden" name="items[__INDEX__][harga_nominal]" value="0" class="item-harga">
            <input type="hidden" name="items[__INDEX__][diskon_persen]" value="0" class="item-diskon">
            <input type="hidden" name="items[__INDEX__][potongan_nominal]" value="0" class="item-potongan">
            <input type="hidden" name="items[__INDEX__][ppn_tipe]" value="NON_PPN" class="item-ppn-tipe">
        </td>
        <td>
            <input type="text" name="items[__INDEX__][batch_no]" id="batch___INDEX__" value="" placeholder="Contoh: BC-... (ketik nomor batch faktur)" class="form-control item-batch" style="font-family: monospace; font-weight: 700; color: #0284c7; width: 100%;" required>
        </td>
        <td>
            <input type="date" name="items[__INDEX__][expired_tgl]" class="form-control" style="font-size: 0.8rem;">
        </td>
        <td>
            <select name="items[__INDEX__][grade_cd]" class="form-control po-grade-select">
                <option value="A" selected>Grade A</option>
                <option value="B">Grade B</option>
                <option value="NON-GRADE">Non Grade</option>
            </select>
        </td>
        <td>
            <input type="number" step="0.0001" min="0" name="items[__INDEX__][terima_qty]" value="1" class="form-control item-terima-qty" style="text-align: right; font-weight: 700;" oninput="calculateTotalTerima()" required>
        </td>
        <td>
            <input type="number" step="0.0001" min="0" name="items[__INDEX__][reject_qty]" value="0" class="form-control item-reject-qty" placeholder="0" style="text-align: right;" oninput="calculateTotalTerima()">
        </td>
        <td class="row-netto">
            1,00
        </td>
        <td style="text-align: center;">
            <span class="row-satuan" style="font-weight: 600; color: #475569; font-size: 0.8rem;">-</span>
        </td>
        <td style="text-align: center;">
            <button type="button" onclick="removeTerimaRow(this)" class="btn-remove-row" title="Hapus Baris">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
            </button>
        </td>
    </tr>
</template>
