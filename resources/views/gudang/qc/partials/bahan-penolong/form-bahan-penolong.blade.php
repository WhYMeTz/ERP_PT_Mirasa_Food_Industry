{{-- ========================================================================= --}}
{{-- PARTIAL: FORM INSPEKSI QC KHUSUS BAHAN PENOLONG (MSG, GARAM, PERENYAH)   --}}
{{-- NO DOKUMEN: MFI/HACCP-04/FRM-03/032/VIII/2021 (MSG)                      --}}
{{-- NO DOKUMEN: MFI/HACCP-04/FRM-03/033/VIII/2021 (GARAM)                    --}}
{{-- NO DOKUMEN: MFI/HACCP-04/FRM-03/063/IX/2023 (PERENYAH)                   --}}
{{-- ========================================================================= --}}
<div id="bahanPenolongContainer" style="display: {{ in_array(old('kategori_barang', $initialKomoditas ?? 'SINGKONG'), ['MSG', 'GARAM', 'PERENYAH']) ? 'flex' : 'none' }}; padding: 1.4rem; flex-direction: column; gap: 1.25rem;">
    <div class="form-group" style="margin-bottom: 0;">
        <label class="form-label" style="font-weight: 700;" id="labelBahanPenolongItem">Nama Bahan Penolong <span style="color:red;">*</span></label>
        <select name="bp_barang_id" id="bpBarangSelect" class="form-control" style="font-weight: 700;" onchange="syncNamaRmFromSelect(this)">
            @foreach ($barangs as $b)
                @php
                    $nm = strtoupper($b->barang_nm);
                    $katItem = null;
                    if (str_contains($nm, 'MSG') || str_contains($nm, 'MONOSODIUM') || str_contains($nm, 'PENYEDAP')) {
                        $katItem = 'MSG';
                    } elseif (str_contains($nm, 'GARAM') || str_contains($nm, 'SEASALT') || str_contains($nm, 'SALT')) {
                        $katItem = 'GARAM';
                    } elseif (str_contains($nm, 'PERENYAH')) {
                        $katItem = 'PERENYAH';
                    } elseif (str_contains($nm, 'BUMBU') && !str_contains($nm, 'SEASALT')) {
                        $katItem = 'BUMBU';
                    }
                @endphp
                @if ($katItem !== null)
                    <option value="{{ $b->barang_id }}" data-commodity="{{ $katItem }}" data-nama="{{ $b->barang_nm }}" data-name="{{ $nm }}">
                        {{ $b->barang_cd }} - {{ $b->barang_nm }} ({{ $b->satuanDasar?->satuan_nm ?? 'KG' }})
                    </option>
                @endif
            @endforeach
        </select>
    </div>

    {{-- ISI RAW MATERIAL & KEMASAN STATUS --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1rem;">
        <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 1rem;">
            <span style="font-weight: 800; font-size: 0.85rem; color: #0f172a;">2. ISI RAW MATERIAL :</span>
            <div style="display: flex; gap: 1.25rem; margin-top: 0.5rem;">
                <label style="display: flex; align-items: center; gap: 0.35rem; font-weight: 700; color: #15803d; cursor: pointer;">
                    <input type="radio" name="bp_status_raw_material" value="OK" checked> OK
                </label>
                <label style="display: flex; align-items: center; gap: 0.35rem; font-weight: 700; color: #dc2626; cursor: pointer;">
                    <input type="radio" name="bp_status_raw_material" value="TDK_STD"> TDK STD
                </label>
            </div>
        </div>

        <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 1rem;">
            <span style="font-weight: 800; font-size: 0.85rem; color: #0f172a;">KEMASAN :</span>
            <div style="display: flex; gap: 1.25rem; margin-top: 0.5rem;">
                <label style="display: flex; align-items: center; gap: 0.35rem; font-weight: 700; color: #15803d; cursor: pointer;">
                    <input type="radio" name="bp_kemasan_kondisi" value="OK" checked> OK
                </label>
                <label style="display: flex; align-items: center; gap: 0.35rem; font-weight: 700; color: #dc2626; cursor: pointer;">
                    <input type="radio" name="bp_kemasan_kondisi" value="TIDAK_STANDARD"> TIDAK STANDARD
                </label>
            </div>
        </div>
    </div>

    {{-- CHECKBOX KONDISI ISI (BAHAN) & KONDISI KEMASAN (PERSIS FORM EXCEL MFI) --}}
    <div style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 1rem;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            {{-- SISI KIRI: KONDISI ISI BAHAN --}}
            <div style="border-right: 1.5px solid #e2e8f0; padding-right: 1rem;">
                <div style="font-weight: 800; font-size: 0.825rem; color: #0f172a; margin-bottom: 0.75rem;">
                    🧪 KONDISI FISIK BAHAN (Pilih kondisi):
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; font-size: 0.85rem;">
                    <label style="display: flex; align-items: center; gap: 0.45rem; cursor: pointer; color: #15803d; font-weight: 700;">
                        <input type="checkbox" name="bp_isi_kering" value="1" checked> KERING
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.45rem; cursor: pointer; color: #0284c7; font-weight: 600;">
                        <input type="checkbox" name="bp_isi_basah" value="1"> BASAH
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.45rem; cursor: pointer; color: #b45309; font-weight: 600;">
                        <input type="checkbox" name="bp_isi_gumpal" value="1"> GUMPAL
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.45rem; cursor: pointer; color: #475569; font-weight: 600;">
                        <input type="checkbox" name="bp_isi_berminyak" value="1"> BERMINYAK
                    </label>
                </div>
            </div>

            {{-- SISI KANAN: KONDISI KEMASAN --}}
            <div>
                <div style="font-weight: 800; font-size: 0.825rem; color: #0f172a; margin-bottom: 0.75rem;">
                    📦 KONDISI KEMASAN / ZAK / DUS:
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; font-size: 0.85rem;">
                    <label style="display: flex; align-items: center; gap: 0.45rem; cursor: pointer;">
                        <input type="checkbox" name="bp_kemasan_kotor" value="1"> KOTOR
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.45rem; cursor: pointer;">
                        <input type="checkbox" name="bp_kemasan_apek" value="1"> APEK
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.45rem; cursor: pointer; color: #b45309; font-weight: 700;">
                        <input type="checkbox" name="bp_kemasan_jamur" value="1"> JAMUR
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.45rem; cursor: pointer; color: #dc2626; font-weight: 700;">
                        <input type="checkbox" name="bp_kemasan_sobek" value="1"> SOBEK
                    </label>
                </div>
            </div>
        </div>
    </div>

    {{-- KUANTITAS TIMBANGAN / DITERIMA --}}
    <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 1rem;">
        <div style="font-weight: 800; font-size: 0.85rem; color: #0f172a; margin-bottom: 0.5rem;">
            ⚖️ KUANTITAS DITERIMA DI PABRIK
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 0.75rem;">
            <div>
                <label class="form-label" style="font-weight: 700;">Jumlah Lolos / Netto (KG / Zak)</label>
                <input type="number" step="0.01" min="0" name="bp_qty_gross" id="bpQtyGross" class="form-control" placeholder="0.00" value="{{ old('bp_qty_gross') }}">
            </div>
            <div>
                <label class="form-label" style="color: #dc2626; font-weight: 700;">Qty Reject / Rusak (KG / Zak)</label>
                <input type="number" step="0.01" min="0" name="bp_qty_reject" id="bpQtyReject" class="form-control" placeholder="0.00" value="{{ old('bp_qty_reject', 0) }}">
            </div>
        </div>
    </div>

    {{-- KOMENTAR & KESIMPULAN --}}
    <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 1rem;">
        <div class="form-group" style="margin-bottom: 0.75rem;">
            <label class="form-label" style="font-weight: 700;">KOMENTAR PEMERIKSAAN :</label>
            <textarea name="bp_komentar" rows="2" class="form-control" placeholder="Komentar hasil uji mutu bahan penolong...">{{ old('bp_komentar') }}</textarea>
        </div>
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; border-top: 1px solid #e2e8f0; padding-top: 0.75rem;">
            <span style="font-weight: 900; font-size: 0.95rem; color: #0f172a;">KESIMPULAN QC :</span>
            <div style="display: flex; gap: 1.5rem;">
                <label style="display: flex; align-items: center; gap: 0.4rem; font-size: 1rem; font-weight: 800; color: #15803d; cursor: pointer;">
                    <input type="radio" name="bp_kesimpulan" value="TERIMA" checked> ✅ TERIMA
                </label>
                <label style="display: flex; align-items: center; gap: 0.4rem; font-size: 1rem; font-weight: 800; color: #dc2626; cursor: pointer;">
                    <input type="radio" name="bp_kesimpulan" value="TOLAK"> ❌ TOLAK
                </label>
            </div>
        </div>
    </div>
</div>
