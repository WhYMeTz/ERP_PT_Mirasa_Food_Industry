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
                <option value="C">Grade C</option>
                <option value="REJECT">Reject</option>
            </select>
        </td>
        <td>
            <input type="number" step="0.0001" min="0" name="items[__INDEX__][terima_qty]" value="1" class="form-control item-terima-qty" style="text-align: right; font-weight: 700;" oninput="calculateTotalTerima()" required>
        </td>
        <td>
            <input type="number" step="0.0001" min="0" name="items[__INDEX__][reject_qty]" value="0" class="form-control item-reject-qty" placeholder="0" style="text-align: right;" oninput="calculateTotalTerima()">
        </td>
        <td style="text-align: right; font-weight: 700; color: #047857; background: #f0fdf4;" class="row-netto">
            1,00
        </td>
        <td style="text-align: center;">
            <span class="row-satuan" style="font-weight: 600; color: #475569; font-size: 0.8rem;">-</span>
        </td>
        <td>
            <input type="number" step="0.01" min="0" name="items[__INDEX__][harga_nominal]" value="0" class="form-control item-harga" placeholder="0" style="text-align: right; font-weight: 600;" oninput="calculateTotalTerima()">
            {{-- Nilai Finansial/Pajak Disimpan di Hidden Input untuk Finance --}}
            <input type="hidden" name="items[__INDEX__][diskon_persen]" value="0" class="item-diskon">
            <input type="hidden" name="items[__INDEX__][potongan_nominal]" value="0" class="item-potongan">
            <input type="hidden" name="items[__INDEX__][ppn_tipe]" value="NON_PPN" class="item-ppn-tipe">
        </td>
        <td style="text-align: right; font-weight: 700; color: #0f172a; font-size: 0.875rem; font-family: monospace;" class="row-subtotal">
            Rp 0
        </td>
        <td style="text-align: center;">
            <button type="button" onclick="removeTerimaRow(this)" class="btn btn-danger btn-sm" style="padding: 0.2rem 0.45rem; font-size: 0.8rem;" title="Hapus Baris">&times;</button>
        </td>
    </tr>
</template>
