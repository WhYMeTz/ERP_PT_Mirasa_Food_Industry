{{-- ========================================================================= --}}
{{-- PARTIAL: FORM INSPEKSI QC KHUSUS MINYAK GORENG                            --}}
{{-- NO DOKUMEN: MFI/HACCP-04/FRM-03/029/VIII/2021                             --}}
{{-- ========================================================================= --}}

{{-- WIDGET INFORMASI STOK GUDANG MINYAK GORENG (REAL-TIME) --}}
<div id="minyakStockWidget" style="display: {{ old('kategori_barang', $initialKomoditas ?? 'SINGKONG') === 'MINYAK' ? 'block' : 'none' }}; margin: 0 0 1.25rem 0; background: #fffdf5; border: 1.5px solid #fde047; border-radius: 12px; padding: 0.85rem 1.15rem; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.6rem; flex-wrap: wrap; gap: 0.4rem;">
        <div style="font-size: 0.8rem; font-weight: 800; color: #78350f; display: flex; align-items: center; gap: 0.4rem;">
            <span>🛢️</span> <span>STATUS STOK GUDANG MINYAK GORENG SAAT INI (REAL-TIME):</span>
        </div>
        <span style="font-size: 0.7rem; color: #b45309; font-weight: 700; background: #fef3c7; padding: 2px 7px; border-radius: 4px;">
            Data Riil Gudang Persediaan
        </span>
    </div>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.75rem;">
        {{-- MINYAK SAWIT --}}
        <div style="background: #ffffff; border: 1.5px solid #fde68a; border-radius: 8px; padding: 0.65rem 0.85rem; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <span style="font-size: 0.725rem; font-weight: 800; color: #92400e; text-transform: uppercase;">🛢️ Minyak Sawit (MSW00G-BP2)</span>
                <div style="font-size: 1.2rem; font-weight: 900; color: #b45309;">
                    {{ number_format($stokMinyakSawit ?? 0, 0, ',', '.') }} <span style="font-size: 0.75rem; font-weight: 700; color: #92400e;">KG</span>
                </div>
            </div>
            <span style="font-size: 0.7rem; font-weight: 700; color: {{ ($stokMinyakSawit ?? 0) <= 200 ? '#b91c1c' : '#15803d' }}; background: {{ ($stokMinyakSawit ?? 0) <= 200 ? '#fee2e2' : '#dcfce7' }}; padding: 3px 8px; border-radius: 6px;">
                {{ ($stokMinyakSawit ?? 0) <= 200 ? '⚠️ Menipis' : 'Stok Tersedia' }}
            </span>
        </div>
        {{-- MINYAK KELAPA --}}
        <div style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 0.65rem 0.85rem; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <span style="font-size: 0.725rem; font-weight: 800; color: #334155; text-transform: uppercase;">🥥 Minyak Kelapa (MKP00G-BP1)</span>
                <div style="font-size: 1.2rem; font-weight: 900; color: #1e293b;">
                    {{ number_format($stokMinyakKelapa ?? 0, 0, ',', '.') }} <span style="font-size: 0.75rem; font-weight: 700; color: #475569;">Liter</span>
                </div>
            </div>
            <span style="font-size: 0.7rem; font-weight: 700; color: {{ ($stokMinyakKelapa ?? 0) <= 100 ? '#b91c1c' : '#0284c7' }}; background: {{ ($stokMinyakKelapa ?? 0) <= 100 ? '#fee2e2' : '#e0f2fe' }}; padding: 3px 8px; border-radius: 6px;">
                {{ ($stokMinyakKelapa ?? 0) <= 100 ? '⚠️ Menipis' : 'Stok Tersedia' }}
            </span>
        </div>
        {{-- TOTAL GUDANG --}}
        <div style="background: #ffffff; border: 1.5px solid #86efac; border-radius: 8px; padding: 0.65rem 0.85rem; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <span style="font-size: 0.725rem; font-weight: 800; color: #166534; text-transform: uppercase;">📊 Total Stok Minyak</span>
                <div style="font-size: 1.2rem; font-weight: 900; color: #15803d;">
                    {{ number_format($stokMinyakTotal ?? 0, 0, ',', '.') }} <span style="font-size: 0.75rem; font-weight: 700; color: #166534;">KG/L</span>
                </div>
            </div>
            <span style="font-size: 0.7rem; font-weight: 700; color: #166534; background: #dcfce7; padding: 3px 8px; border-radius: 6px;">
                Gudang Bahan
            </span>
        </div>
    </div>
    <div style="font-size: 0.725rem; color: #92400e; margin-top: 0.45rem; line-height: 1.35; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.35rem;">
        <span>💡 <em>Pedoman QC: Periksa hasil uji asam lemak bebas (FFA) pada COA dan uji lab. Minyak yang lolos QC akan menambah persediaan tangki/jerigen di atas saat penerimaan barang (GRN).</em></span>
        @if(isset($stokMinyakBatches) && $stokMinyakBatches->isNotEmpty())
            <span style="font-weight: 700; color: #78350f;">{{ $stokMinyakBatches->count() }} Batch Aktif Tersedia</span>
        @endif
    </div>
    @if(isset($stokMinyakBatches) && $stokMinyakBatches->isNotEmpty())
        <div style="margin-top: 0.5rem; padding-top: 0.45rem; border-top: 1px dashed #fde047; font-size: 0.725rem; color: #78350f; display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: center;">
            <span style="font-weight: 700;">📦 Batch Aktif di Gudang:</span>
            @foreach($stokMinyakBatches as $b)
                <span style="background: #ffffff; border: 1px solid #fde68a; padding: 2px 6px; border-radius: 4px; font-family: monospace;">
                    {{ $b->batch_no }}: <strong>{{ number_format($b->sisa_qty, 0, ',', '.') }}</strong> {{ $b->barang?->satuanDasar?->satuan_cd ?? 'KG' }} (Exp: {{ $b->expired_tgl ? date('d/m/Y', strtotime($b->expired_tgl)) : '-' }})
                </span>
            @endforeach
        </div>
    @endif
</div>

{{-- FORM INPUT KHUSUS MINYAK GORENG --}}
<div id="minyakContainer" style="display: {{ old('kategori_barang', $initialKomoditas ?? 'SINGKONG') === 'MINYAK' ? 'flex' : 'none' }}; flex-direction: column; gap: 1.25rem;">
    {{-- 1. PILIH MASTER ITEM MINYAK GORENG --}}
    <div class="form-group" style="margin-bottom: 0;">
        <label class="form-label" style="font-weight: 800; color: #0f172a;">Komoditas Minyak Goreng <span style="color:red;">*</span></label>
        <select name="minyak_barang_id" id="minyakBarangSelect" class="form-control" style="font-weight: 700;" onchange="syncNamaRmFromSelect(this)">
            @foreach ($barangs as $b)
                @if (stripos($b->barang_nm, 'minyak') !== false)
                    <option value="{{ $b->barang_id }}" data-nama="{{ $b->barang_nm }}">{{ $b->barang_cd }} - {{ $b->barang_nm }} ({{ $b->satuanDasar?->satuan_nm ?? 'KG' }})</option>
                @endif
            @endforeach
        </select>
    </div>

    {{-- 2. ISI RAW MATERIAL & KONDISI WADAH --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1rem;">
        <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 1rem;">
            <span style="font-weight: 800; font-size: 0.85rem; color: #0f172a;">2. ISI RAW MATERIAL :</span>
            <div style="display: flex; gap: 1.25rem; margin-top: 0.5rem;">
                <label style="display: flex; align-items: center; gap: 0.35rem; font-weight: 700; color: #15803d; cursor: pointer;">
                    <input type="radio" name="minyak_status_raw_material" value="OK" checked> OK
                </label>
                <label style="display: flex; align-items: center; gap: 0.35rem; font-weight: 700; color: #dc2626; cursor: pointer;">
                    <input type="radio" name="minyak_status_raw_material" value="TDK_STD"> TDK STD
                </label>
            </div>
        </div>

        <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 1rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                <span style="font-weight: 800; font-size: 0.85rem; color: #0f172a;">KONDISI WADAH :</span>
                <div style="display: flex; gap: 0.75rem; font-size: 0.8rem; font-weight: 700;">
                    <label style="cursor: pointer;"><input type="radio" name="minyak_tipe_wadah" value="TANGKI" checked> TANGKI</label>
                    <label style="cursor: pointer;"><input type="radio" name="minyak_tipe_wadah" value="JERIGEN"> JERIGEN</label>
                </div>
            </div>
            <div style="display: flex; gap: 1.25rem; margin-top: 0.5rem;">
                <label style="display: flex; align-items: center; gap: 0.35rem; font-weight: 700; color: #15803d; cursor: pointer;">
                    <input type="radio" name="minyak_kondisi_wadah" value="OK" checked> OK
                </label>
                <label style="display: flex; align-items: center; gap: 0.35rem; font-weight: 700; color: #dc2626; cursor: pointer;">
                    <input type="radio" name="minyak_kondisi_wadah" value="TIDAK_STANDARD"> TIDAK STANDARD
                </label>
            </div>
        </div>
    </div>

    {{-- 3. PEMERIKSAAN ASAM LEMAK BEBAS (FFA) & KEBERSIHAN FISIK --}}
    <div style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 1rem;">
        <div style="font-weight: 800; font-size: 0.85rem; color: #0f172a; margin-bottom: 0.75rem;">
            🔬 HASIL PEMERIKSAAN ASAM LEMAK BEBAS (FFA) &amp; FISIK
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: center;">
            <div>
                <label class="form-label" style="font-weight: 700;">FFA DI COA (Pabrik Produsen)</label>
                <input type="number" step="0.001" min="0" max="100" name="minyak_ffa_coa" class="form-control" placeholder="Contoh: 0.080" value="{{ old('minyak_ffa_coa') }}">
            </div>
            <div>
                <label class="form-label" style="font-weight: 700; color: #0284c7;">FFA CEK QC MIRASA (Lab)</label>
                <input type="number" step="0.001" min="0" max="100" name="minyak_ffa_qc" class="form-control" placeholder="Contoh: 0.085" value="{{ old('minyak_ffa_qc') }}" style="font-weight: 800; color: #0f172a;">
            </div>
        </div>

        <div style="display: flex; gap: 1.5rem; margin-top: 1rem; padding-top: 0.75rem; border-top: 1px dashed #cbd5e1; flex-wrap: wrap;">
            <label style="display: flex; align-items: center; gap: 0.45rem; font-weight: 700; color: #15803d; cursor: pointer;">
                <input type="checkbox" name="minyak_jernih_st" value="1" checked> MINYAK JERNIH
            </label>
            <label style="display: flex; align-items: center; gap: 0.45rem; font-weight: 700; color: #15803d; cursor: pointer;">
                <input type="checkbox" name="minyak_tangki_bersih_st" value="1" checked> TANGKI BAGIAN DALAM BERSIH
            </label>
        </div>
    </div>

    {{-- 4. KUANTITAS & TIMBANGAN NETTO --}}
    <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 1rem;">
        <div style="font-weight: 800; font-size: 0.85rem; color: #0f172a; margin-bottom: 0.5rem;">
            ⚖️ KUANTITAS &amp; HASIL PENIMBANGAN
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 0.75rem;">
            <div>
                <label class="form-label" style="font-weight: 700;">Timbangan Netto (KG)</label>
                <input type="number" step="0.01" min="0" name="minyak_qty_gross" id="minyakQtyGross" class="form-control" placeholder="0.00" value="{{ old('minyak_qty_gross') }}">
            </div>
            <div>
                <label class="form-label" style="color: #dc2626; font-weight: 700;">Qty Reject / Tolak (KG)</label>
                <input type="number" step="0.01" min="0" name="minyak_qty_reject" id="minyakQtyReject" class="form-control" placeholder="0.00" value="{{ old('minyak_qty_reject', 0) }}">
            </div>
        </div>
    </div>

    {{-- 5. KOMENTAR PEMERIKSAAN & KESIMPULAN QC --}}
    <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 1rem;">
        <div class="form-group" style="margin-bottom: 0.75rem;">
            <label class="form-label" style="font-weight: 700;">KOMENTAR PEMERIKSAAN :</label>
            <textarea name="minyak_komentar" rows="2" class="form-control" placeholder="Komentar hasil uji minyak goreng...">{{ old('minyak_komentar') }}</textarea>
        </div>

        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; border-top: 1px solid #e2e8f0; padding-top: 0.75rem;">
            <span style="font-weight: 900; font-size: 0.95rem; color: #0f172a;">KESIMPULAN QC :</span>
            <div style="display: flex; gap: 1.5rem;">
                <label style="display: flex; align-items: center; gap: 0.4rem; font-size: 1rem; font-weight: 800; color: #15803d; cursor: pointer;">
                    <input type="radio" name="minyak_kesimpulan" value="TERIMA" checked> ✅ TERIMA
                </label>
                <label style="display: flex; align-items: center; gap: 0.4rem; font-size: 1rem; font-weight: 800; color: #dc2626; cursor: pointer;">
                    <input type="radio" name="minyak_kesimpulan" value="TOLAK"> ❌ TOLAK
                </label>
            </div>
        </div>
    </div>
</div>
