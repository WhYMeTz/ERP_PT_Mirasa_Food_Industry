<div class="card" style="margin-bottom: 1.25rem; border: 1px solid #cbd5e1; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); background: #ffffff;">
    <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 0.75rem 1.25rem; display: flex; justify-content: space-between; align-items: center; gap: 1rem;">
        <div style="min-width: 0;">
            <strong style="color: #0f172a; font-size: 0.925rem; display: flex; align-items: center; gap: 0.4rem;">
                <span>2. Hasil Barang Produksi (Olahan Curah Setengah Jadi / WIP)</span>
            </strong>
            <span style="font-size: 0.75rem; color: #64748b; display: block; margin-top: 0.15rem;">
                Pencatatan rincian output olahan curah yang dihasilkan (WIP Curah, Berko, Remahan &amp; Limbah Lengket).
            </span>
        </div>
        <div style="display: flex; align-items: center; gap: 0.4rem; flex-shrink: 0;">
            <span id="badgeTotalOutputKg" style="font-size: 0.775rem; font-weight: 700; color: #0284c7; background: #f0f9ff; border: 1px solid #bae6fd; padding: 0.25rem 0.65rem; border-radius: 6px; white-space: nowrap;">
                Total Output: 0.00 kg
            </span>
            <span id="badgeBerkoOutput" style="font-size: 0.775rem; font-weight: 700; color: #b45309; background: #fffbeb; border: 1px solid #fde68a; padding: 0.25rem 0.65rem; border-radius: 6px; white-space: nowrap; display: none;">
                Berko: 0.00 kg
            </span>
            <span id="badgeRemahanOutput" style="font-size: 0.775rem; font-weight: 700; color: #4338ca; background: #e0e7ff; border: 1px solid #c7d2fe; padding: 0.25rem 0.65rem; border-radius: 6px; white-space: nowrap; display: none;">
                Remahan: 0.00 kg
            </span>
            <span id="badgeLengketOutput" style="font-size: 0.775rem; font-weight: 700; color: #b91c1c; background: #fef2f2; border: 1px solid #fecaca; padding: 0.25rem 0.65rem; border-radius: 6px; white-space: nowrap; display: none;">
                Limbah Lengket: 0.00 kg
            </span>
        </div>
    </div>

    <div style="padding: 1.25rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.65rem; flex-wrap: wrap; gap: 0.5rem;">
            <span style="font-size: 0.825rem; font-weight: 700; color: #1e293b;">
                Daftar Barang Hasil Produksi:
            </span>
            <button type="button" onclick="addFgRow()" class="btn btn-sm btn-primary" style="padding: 0.35rem 0.75rem; font-size: 0.775rem;">
                <span>+ Tambah Baris Barang</span>
            </button>
        </div>

        <div style="overflow-x: auto; border: 1px solid #cbd5e1; border-radius: 8px; margin-bottom: 1rem;">
            <table style="width: 100%; border-collapse: collapse; font-size: 0.8rem; text-align: left;" id="tableOutputFg">
                <thead>
                    <tr style="background: #f1f5f9; border-bottom: 1px solid #cbd5e1; color: #334155; font-weight: 700; font-size: 0.725rem; text-transform: uppercase;">
                        <th style="padding: 0.5rem 0.75rem; width: 35px; text-align: center;">No</th>
                        <th style="padding: 0.5rem 0.75rem; width: 85px; text-align: center;">Jenis</th>
                        <th style="padding: 0.5rem 0.75rem; min-width: 260px;">Nama &amp; Kode Barang</th>
                        <th style="padding: 0.5rem 0.75rem; width: 130px; text-align: right;">QTY Hasil</th>
                        <th style="padding: 0.5rem 0.75rem; width: 90px; text-align: center;">Satuan</th>
                        <th style="padding: 0.5rem 0.75rem; width: 130px; text-align: right;">Total Berat (Kg)</th>
                        <th style="padding: 0.5rem 0.75rem; min-width: 140px; text-align: center;">Kode Batch (Otomatis)</th>
                        <th style="padding: 0.5rem 0.75rem; width: 50px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tbodyOutputFg">
                    @php
                        $wipItems = isset($barangHasilList) ? $barangHasilList->filter(fn($b) => str_starts_with($b->barang_cd, 'WIP') || ($b->jenisBarang?->jenis_barang_cd ?? '') === 'WIP') : collect([]);
                        $oldBarangId = old('output_items.0.barang_id');
                        $oldJenis = old('output_items.0.jenis_cd', 'WIP');
                        $oldSatuan = old('output_items.0.satuan_cd', 'KG');
                    @endphp
                    <tr id="fgRow_0" class="row-output-fg" style="border-bottom: 1px solid #e2e8f0;">
                        <td style="padding: 0.5rem 0.75rem; text-align: center; font-weight: 700; color: #64748b;" class="fg-row-number">
                            1
                        </td>
                        <td style="padding: 0.5rem 0.75rem; text-align: center;">
                            <span id="badgeJenis_0" style="background: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1; padding: 0.2rem 0.45rem; border-radius: 4px; font-weight: 700; font-size: 0.75rem;">
                                {{ $oldJenis }}
                            </span>
                            <input type="hidden" name="output_items[0][jenis_cd]" id="inputJenis_0" value="{{ $oldJenis !== '-' ? $oldJenis : 'WIP' }}">
                        </td>
                        <td style="padding: 0.5rem 0.75rem;">
                            <select name="output_items[0][barang_id]" class="form-control select-fg-barang" style="font-size: 0.8rem; font-weight: 600;" onchange="onFgBarangChange(this, 0)">
                                <option value="">-- Pilih Olahan Setengah Jadi (WIP) --</option>
                                @if($wipItems->isNotEmpty())
                                    <optgroup label="── OLAHAN SETENGAH JADI (WIP CURAH) ──">
                                        @foreach($wipItems as $b)
                                            @php $satuan = $b->satuanDasar?->satuan_cd ?? 'KG'; @endphp
                                            <option value="{{ $b->barang_id }}" data-cd="{{ $b->barang_cd }}" data-nm="{{ $b->barang_nm }}" data-jenis="WIP" data-satuan="{{ $satuan }}" {{ $oldBarangId == $b->barang_id ? 'selected' : '' }}>
                                                [{{ $b->barang_cd }}] {{ $b->barang_nm }} ({{ $satuan }})
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            </select>
                        </td>
                        <td style="padding: 0.5rem 0.75rem;">
                            <input type="number" step="0.0001" min="0" name="output_items[0][qty_hasil]" id="qtyHasil_0" class="form-control calc-trigger" style="text-align: right; font-weight: 700; color: #0f172a;" placeholder="0" value="{{ old('output_items.0.qty_hasil', '') }}" oninput="onQtyHasilInput(0)">
                        </td>
                        <td style="padding: 0.5rem 0.75rem; text-align: center;">
                            <span id="labelSatuan_0" style="font-weight: 600; color: #475569; font-size: 0.775rem;">{{ $oldSatuan }}</span>
                            <input type="hidden" name="output_items[0][satuan_cd]" id="inputSatuan_0" value="{{ $oldSatuan !== '-' ? $oldSatuan : '' }}">
                        </td>
                        <td style="padding: 0.5rem 0.75rem;">
                            <input type="number" step="0.0001" min="0" name="output_items[0][qty_kg]" id="qtyKg_0" class="form-control input-fg-qty-kg calc-trigger" style="text-align: right; font-weight: 700; color: #0f172a; background: #ffffff;" placeholder="0" value="{{ old('output_items.0.qty_kg', '') }}" oninput="calcAll()">
                        </td>
                        <td style="padding: 0.5rem 0.75rem; text-align: center;">
                            <span class="badge-batch-wip" id="badgeFgBatch_0" style="font-size: 0.75rem; font-weight: 700; color: #065f46; background: #f0fdf4; border: 1px solid #a7f3d0; padding: 0.2rem 0.5rem; border-radius: 4px; display: inline-block;">
                                {{ old('output_items.0.batch_no', date('d m Y')) }}
                            </span>
                            <input type="hidden" name="output_items[0][batch_no]" id="batchFg_0" value="{{ old('output_items.0.batch_no', date('d m Y')) }}">
                        </td>
                        <td style="padding: 0.5rem 0.75rem; text-align: center;">
                            <button type="button" class="btn btn-sm btn-remove-fg" id="btnRemoveFg_0" style="padding: 0.2rem 0.45rem; font-size: 0.75rem; background: #ffffff; border: 1px solid #cbd5e1; color: #94a3b8; font-weight: 600; opacity: 0.4; cursor: not-allowed;" title="Baris utama tidak dapat dihapus" disabled>
                                Hapus
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- AREA CATATAN PRODUKSI (PO Buyer IFL disembunyikan sementara) --}}
        <div style="margin-top: 1rem; padding-top: 0.85rem; border-top: 1px dashed #e2e8f0;">
            <div>
                <label for="catatan_txt" style="display: block; font-size: 0.775rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                    Catatan Produksi / Kualitas Shift
                </label>
                <textarea name="catatan_txt" id="catatan_txt" class="form-control" rows="2" style="font-size: 0.825rem; resize: vertical;" placeholder="Catatan shift, deviasi bumbu, atau kendala pengerjaan...">{{ old('catatan_txt', $produksi->catatan_txt ?? '') }}</textarea>
                <input type="hidden" name="po_ifl_no" id="po_ifl_no" value="{{ old('po_ifl_no', $produksi->po_ifl_no ?? '') }}">
            </div>
        </div>

        {{-- Hidden Inputs untuk Sinkronisasi Otomatis Limbah HACCP ke Controller & Database --}}
        <input type="hidden" name="waste_lengket_qty" id="waste_lengket_qty" value="{{ old('waste_lengket_qty', $produksi->waste_lengket_qty ?? 0) }}">
        <input type="hidden" name="waste_lengket_persen" id="waste_lengket_persen" value="{{ old('waste_lengket_persen', $produksi->waste_lengket_persen ?? 0) }}">
        <input type="hidden" name="berko_ifl_qty" id="berko_ifl_qty" value="{{ old('berko_ifl_qty', $produksi->berko_ifl_qty ?? 0) }}">
        <input type="hidden" name="berko_non_ifl_qty" id="berko_non_ifl_qty" value="{{ old('berko_non_ifl_qty', $produksi->berko_non_ifl_qty ?? 0) }}">
        <input type="hidden" name="waste_total_qty" id="waste_total_qty" value="{{ old('waste_total_qty', $produksi->waste_total_qty ?? 0) }}">
        <input type="hidden" name="po_ifl_realisasi_qty" id="po_ifl_realisasi_qty" value="{{ old('po_ifl_realisasi_qty', $produksi->po_ifl_realisasi_qty ?? 0) }}">
        <input type="hidden" name="po_ifl_terpenuhi_st" id="po_ifl_terpenuhi_st" value="{{ old('po_ifl_terpenuhi_st', $produksi->po_ifl_terpenuhi_st ?? '') }}">
    </div>
</div>

