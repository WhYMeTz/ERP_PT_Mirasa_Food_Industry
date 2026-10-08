<div class="card" style="margin-bottom: 1.25rem; border: 1px solid #cbd5e1; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); background: #ffffff;">
    <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 0.875rem 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
        <div>
            <strong style="color: #0f172a; font-size: 0.95rem; display: flex; align-items: center; gap: 0.4rem;">
                <span>6. Hasil Barang Produksi (Barang Jadi &amp; Olahan Curah)</span>
            </strong>
            <span style="font-size: 0.775rem; color: #64748b; display: block; margin-top: 0.15rem;">
                Pencatatan rincian output barang yang dihasilkan (kemasan barang jadi FG maupun timbangan olahan curah WIP).
            </span>
        </div>
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <span id="badgeTotalOutputKg" style="font-size: 0.825rem; font-weight: 700; color: #0284c7; background: #f0f9ff; border: 1px solid #bae6fd; padding: 0.3rem 0.75rem; border-radius: 6px;">
                Total Output: 0.00 kg
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
                        $fgItems = isset($barangHasilList) ? $barangHasilList->filter(fn($b) => !str_starts_with($b->barang_cd, 'WIP') && ($b->jenisBarang?->jenis_barang_cd ?? '') !== 'WIP') : collect([]);
                        $wipItems = isset($barangHasilList) ? $barangHasilList->filter(fn($b) => str_starts_with($b->barang_cd, 'WIP') || ($b->jenisBarang?->jenis_barang_cd ?? '') === 'WIP') : collect([]);
                        $oldBarangId = old('output_items.0.barang_id');
                        $oldJenis = old('output_items.0.jenis_cd', '-');
                        $oldSatuan = old('output_items.0.satuan_cd', '-');
                    @endphp
                    <tr id="fgRow_0" class="row-output-fg" style="border-bottom: 1px solid #e2e8f0;">
                        <td style="padding: 0.5rem 0.75rem; text-align: center; font-weight: 700; color: #64748b;" class="fg-row-number">
                            1
                        </td>
                        <td style="padding: 0.5rem 0.75rem; text-align: center;">
                            <span id="badgeJenis_0" style="background: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1; padding: 0.2rem 0.45rem; border-radius: 4px; font-weight: 700; font-size: 0.75rem;">
                                {{ $oldJenis }}
                            </span>
                            <input type="hidden" name="output_items[0][jenis_cd]" id="inputJenis_0" value="{{ $oldJenis !== '-' ? $oldJenis : '' }}">
                        </td>
                        <td style="padding: 0.5rem 0.75rem;">
                            <select name="output_items[0][barang_id]" class="form-control select-fg-barang" style="font-size: 0.8rem; font-weight: 600;" onchange="onFgBarangChange(this, 0)">
                                <option value="">-- Pilih Barang Hasil Produksi --</option>
                                @if($fgItems->isNotEmpty())
                                    <optgroup label="── BARANG JADI (FINISH GOOD / FG) ──">
                                        @foreach($fgItems as $b)
                                            @php $satuan = $b->satuanDasar?->satuan_cd ?? 'KARTON'; @endphp
                                            <option value="{{ $b->barang_id }}" data-cd="{{ $b->barang_cd }}" data-nm="{{ $b->barang_nm }}" data-jenis="FG" data-satuan="{{ $satuan }}" {{ $oldBarangId == $b->barang_id ? 'selected' : '' }}>
                                                [{{ $b->barang_cd }}] {{ $b->barang_nm }} ({{ $satuan }})
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif
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
                            <span class="badge-batch-wip" id="badgeFgBatch_0" style="font-size: 0.75rem; font-weight: 700; color: #334155; background: #f8fafc; border: 1px solid #cbd5e1; padding: 0.2rem 0.5rem; border-radius: 4px; display: inline-block;">
                                -
                            </span>
                            <input type="hidden" name="output_items[0][batch_no]" id="batchFg_0" value="{{ old('output_items.0.batch_no', '') }}">
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

        {{-- CATATAN PRODUKSI / KUALITAS --}}
        <div>
            <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #334155; margin-bottom: 0.25rem;">
                Catatan Produksi / Kualitas Shift
            </label>
            <textarea name="catatan_txt" id="catatan_txt" class="form-control" rows="2" placeholder="Catatan shift, deviasi bumbu, atau kendala pengerjaan...">{{ old('catatan_txt') }}</textarea>
        </div>
    </div>
</div>

